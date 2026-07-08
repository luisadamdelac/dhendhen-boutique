<?php
/**
 * Customer Portal - Checkout & Order Creation
 */
class Checkout extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['url', 'form', 'auth', 'address']);
        $this->load->database();
        load_scoped_session('customer');
        if (!ensure_logged_in()) {
            redirect('auth/login');
        }
        $this->load->config('dropsell');
        if ($this->session->userdata('user_type') !== ROLE_CUSTOMER) {
            deny_role_access();
        }
        $this->load->model(['order_model', 'settings_model']);
        require_once APPPATH . 'services/CartService.php';
        require_once APPPATH . 'services/StockService.php';
        require_once APPPATH . 'services/CommissionService.php';
        require_once APPPATH . 'services/NotificationService.php';
    }

    public function index() {
        $cart = $this->session->userdata('cart') ?: [];
        [$cartItems, $subtotal, $validCart] = CartService::getCartItems($cart);

        if (count($validCart) !== count($cart)) {
            $this->session->set_userdata('cart', $validCart);
        }

        if (empty($cartItems)) {
            redirect('cart');
        }

        $customer_id = $this->session->userdata('user_id');
        $addresses = $this->db->select('*')
            ->from(CUSTOMER_ADDRESS_TABLE)
            ->where('customer_id', $customer_id)
            ->order_by('is_default', 'DESC')
            ->order_by('address_id', 'DESC')
            ->get()->result_array();

        // Checkout only makes sense against a saved address (that's also what
        // Pasabay fee pricing keys off), so send the customer to add one
        // first rather than asking them to type a one-off address here.
        if (empty($addresses)) {
            $this->session->set_flashdata('info', 'Please add a delivery address before checking out.');
            redirect('addresses/add?return=checkout');
        }

        $data['cartItems'] = $cartItems;
        $data['subtotal'] = $subtotal;
        $data['page_title'] = 'Checkout - DropSell';
        $data['addresses'] = $addresses;
        $data['gcash_name'] = $this->settings_model->get('gcash_name') ?: 'DropSell';
        $data['gcash_number'] = $this->settings_model->get('gcash_number') ?: '09123456789';
        $data['gcash_qr_code'] = $this->settings_model->get('gcash_qr_code');
        $data['shipping_fees'] = $this->settings_model->get_shipping_fees();
        $data['barangay_data'] = get_oriental_mindoro_barangays();

        $this->load->view('customer/layouts/header', $data);
        $this->load->view('customer/cart/checkout', $data);
        $this->load->view('customer/layouts/footer', $data);
    }

    /**
     * Create one order per reseller represented in the cart (order_tbl
     * requires a single reseller_id per order). Every cart line is already
     * resolved to a specific reseller's listing (see CartService), so there's
     * nothing left unpurchasable at this point.
     */
    public function process() {
        $customer_id = $this->session->userdata('user_id');

        if ($this->input->method() !== 'post') {
            show_error('Invalid request', 400);
        }

        $this->load->library('form_validation');
        $this->form_validation->set_rules('delivery_type', 'Delivery Method', 'required|in_list[pickup,pasabay_jeep]');
        $this->form_validation->set_rules('address_id', 'Delivery Address', 'required|numeric');
        // Real GCash reference numbers are always exactly 13 digits, and the
        // GCash-registered mobile number the customer paid from is always an
        // 11-digit PH number starting with 09 — reject anything else outright
        // rather than storing an unusable reference for admin to reconcile.
        $this->form_validation->set_rules('gcash_reference', 'GCash Reference Number', 'required|trim|regex_match[/^\d{13}$/]',
            ['regex_match' => 'GCash Reference Number must be exactly 13 digits.']);
        $this->form_validation->set_rules('gcash_sender_number', 'GCash Number Used', 'required|trim|regex_match[/^09\d{9}$/]',
            ['regex_match' => 'GCash Number Used must be a valid 11-digit number starting with 09.']);

        if (!$this->form_validation->run()) {
            echo json_encode(['success' => FALSE, 'message' => validation_errors(' ', ' ')]);
            return;
        }

        $cart = $this->session->userdata('cart') ?: [];
        [$cartItems, , $validCart] = CartService::getCartItems($cart);
        if (empty($cartItems)) {
            echo json_encode(['success' => FALSE, 'message' => 'Cart is empty']);
            return;
        }

        $address = $this->db->where('address_id', $this->input->post('address_id', TRUE))
            ->where('customer_id', $customer_id)
            ->get(CUSTOMER_ADDRESS_TABLE)->row_array();

        if (!$address) {
            echo json_encode(['success' => FALSE, 'message' => 'Please select a valid delivery address.']);
            return;
        }

        if (empty($_FILES['gcash_receipt']['name'])) {
            echo json_encode(['success' => FALSE, 'message' => 'Please upload a screenshot of your GCash receipt.']);
            return;
        }

        $upload_path = FCPATH . 'public/uploads/receipts/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, TRUE);
        }
        $this->load->library('upload', [
            'upload_path'   => $upload_path,
            'allowed_types' => 'jpg|jpeg|png',
            'max_size'      => 2048,
            'encrypt_name'  => TRUE,
        ]);
        if (!$this->upload->do_upload('gcash_receipt')) {
            echo json_encode(['success' => FALSE, 'message' => 'Receipt upload failed: ' . $this->upload->display_errors('', '')]);
            return;
        }
        $receipt_image = 'public/uploads/receipts/' . $this->upload->data('file_name');

        $delivery_method = $this->input->post('delivery_type', TRUE) === 'pickup' ? 'pickup' : 'pasabay';
        $delivery_fee_total = $delivery_method === 'pasabay'
            ? $this->settings_model->get_shipping_fee_for_municipality($address['municipality'])
            : 0.00;
        $gcash_reference = $this->input->post('gcash_reference', TRUE);
        $gcash_sender_number = $this->input->post('gcash_sender_number', TRUE);
        $commission_rate = (float) $this->settings_model->get_commission_rate();

        $groups = [];
        foreach ($cartItems as $item) {
            $groups[$item['reseller_id']][] = $item;
        }

        $created_order_ids = [];
        $fee_remaining = $delivery_fee_total;

        $this->db->trans_start();

        try {
            foreach ($groups as $reseller_id => $items) {
                $group_subtotal = array_sum(array_column($items, 'item_total'));
                $fee_for_this_order = $fee_remaining;
                $fee_remaining = 0; // only charge delivery once, on the first order

                $order_id = $this->order_model->create([
                    'order_number' => 'ORD' . date('ymdHis') . str_pad((string) random_int(0, 99), 2, '0', STR_PAD_LEFT),
                    'customer_id' => $customer_id,
                    'reseller_id' => $reseller_id,
                    'total_amount' => $group_subtotal + $fee_for_this_order,
                    'delivery_method' => $delivery_method,
                    'delivery_street' => $address['street'],
                    'delivery_barangay' => $address['barangay'],
                    'delivery_city' => $address['municipality'],
                    'delivery_fee' => $fee_for_this_order,
                    'order_status' => 'pending',
                ]);

                foreach ($items as $item) {
                    $commission_earned = round($item['item_total'] * $commission_rate / 100, 2);

                    $this->db->insert(ORDER_DETAILS_TABLE, [
                        'order_id' => $order_id,
                        'product_id' => $item['product_id'],
                        'variation_id' => $item['variation_id'] ?? NULL,
                        'variation_label' => !empty($item['variation_type']) ? ($item['variation_type'] . ': ' . $item['variation_value']) : NULL,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'total_price' => $item['item_total'],
                        'commission_earned' => $commission_earned,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);

                    // Variation stock is tracked centrally on product_variation_tbl,
                    // independent of the branch/batch system deductStock() uses —
                    // so a variant line deducts there instead of touching branch stock.
                    if (!empty($item['variation_id'])) {
                        $updated = $this->db->where('variation_id', $item['variation_id'])
                            ->where('stock >=', $item['quantity'])
                            ->set('stock', 'stock - ' . (int) $item['quantity'], FALSE)
                            ->update(PRODUCT_VARIATION_TABLE);
                        if (!$updated || $this->db->affected_rows() === 0) {
                            throw new Exception('Insufficient stock for one or more selected variations. Please review your cart.');
                        }
                    } else {
                        // Race with another concurrent checkout can exhaust stock
                        // between cart validation and this point — deductStock()
                        // returns FALSE rather than throwing, so its result must be
                        // checked or the order/payment/commission below would be
                        // created without any stock actually backing them.
                        if (!StockService::deductStock($item['product_id'], $item['quantity'], 'sale', 'order', $order_id)) {
                            throw new Exception('Insufficient stock for one or more items. Please review your cart.');
                        }
                    }
                }

                $this->db->insert(PAYMENTS_TABLE, [
                    'payment_number' => 'PAY-' . date('Ymd-His') . '-' . random_int(10, 99),
                    'order_id' => $order_id,
                    'customer_id' => $customer_id,
                    'amount' => $group_subtotal + $fee_for_this_order,
                    'payment_method' => 'GCash',
                    'payment_reference' => $gcash_reference,
                    'gcash_sender_number' => $gcash_sender_number,
                    'receipt_image' => $receipt_image,
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                CommissionService::createCommission($order_id, $reseller_id, $group_subtotal, $commission_rate);

                $customer = $this->db->where('customer_id', $customer_id)->get(CUSTOMER_TABLE)->row_array();
                $account = $this->db->where('user_account_id', $this->session->userdata('user_account_id'))->get(USER_ACCOUNT_TABLE)->row_array();
                NotificationService::orderPlaced($order_id, $customer_id, $reseller_id, $account['email'] ?? '');

                $created_order_ids[] = $order_id;
            }
        } catch (Exception $e) {
            $this->db->trans_complete();
            echo json_encode(['success' => FALSE, 'message' => $e->getMessage()]);
            return;
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => FALSE, 'message' => 'Failed to place order. Please try again.']);
            return;
        }

        // Cart is fully consumed — every line that made it into $cartItems was purchased.
        $this->session->set_userdata('cart', []);

        echo json_encode([
            'success' => TRUE,
            'message' => 'Order placed successfully!',
            'order_id' => $created_order_ids[0],
            'redirect_url' => site_url('customer/orders/view/' . $created_order_ids[0]),
        ]);
    }
}
