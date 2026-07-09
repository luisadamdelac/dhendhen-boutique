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
        require_once APPPATH . 'services/PaymongoService.php';
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
    /**
     * Create pending order(s) + line items, then create a PayMongo Checkout
     * Session for the combined total and hand back its hosted URL. Stock is
     * NOT deducted and no commission is created here — that only happens
     * once PayMongo's webhook confirms payment (see
     * OrderFulfillmentService::finalizeOrderPayment(), called from
     * Webhooks::paymongo()). The cart is left intact until then too, since
     * the customer hasn't actually paid yet.
     */
    public function process() {
        $customer_id = $this->session->userdata('user_id');

        if ($this->input->method() !== 'post') {
            show_error('Invalid request', 400);
        }

        $this->load->library('form_validation');
        $this->form_validation->set_rules('delivery_type', 'Delivery Method', 'required|in_list[pickup,pasabay_jeep]');
        $this->form_validation->set_rules('address_id', 'Delivery Address', 'required|numeric');

        if (!$this->form_validation->run()) {
            echo json_encode(['success' => FALSE, 'message' => validation_errors(' ', ' ')]);
            return;
        }

        if (!PaymongoService::isEnabled()) {
            echo json_encode(['success' => FALSE, 'message' => 'Online payment is currently unavailable. Please try again later.']);
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

        $delivery_method = $this->input->post('delivery_type', TRUE) === 'pickup' ? 'pickup' : 'pasabay';
        $delivery_fee_total = $delivery_method === 'pasabay'
            ? $this->settings_model->get_shipping_fee_for_municipality($address['municipality'])
            : 0.00;
        // order_details.commission_earned is a per-line reporting snapshot
        // read directly by reseller/Sales.php (SUM(od.commission_earned))
        // and other reports — still computed and stored here even though
        // the actual commission_transaction_tbl ledger row (which drives
        // the reseller's wallet) isn't created until payment is confirmed.
        $commission_rate = (float) $this->settings_model->get_commission_rate();

        $groups = [];
        foreach ($cartItems as $item) {
            $groups[$item['reseller_id']][] = $item;
        }

        $created_order_ids = [];
        $fee_remaining = $delivery_fee_total;
        $line_items = [];

        $this->db->trans_start();

        foreach ($groups as $reseller_id => $items) {
            $group_subtotal = array_sum(array_column($items, 'item_total'));
            $fee_for_this_order = $fee_remaining;
            $fee_remaining = 0; // only charge delivery once, on the first order
            $order_total = $group_subtotal + $fee_for_this_order;

            $order_id = $this->order_model->create([
                'order_number' => 'ORD' . date('ymdHis') . str_pad((string) random_int(0, 99), 2, '0', STR_PAD_LEFT),
                'customer_id' => $customer_id,
                'reseller_id' => $reseller_id,
                'total_amount' => $order_total,
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

                $line_items[] = [
                    'name' => $item['product_name'] . (!empty($item['variation_value']) ? ' (' . $item['variation_value'] . ')' : ''),
                    'amount' => $item['price'],
                    'quantity' => $item['quantity'],
                ];
            }

            $this->db->insert(PAYMENTS_TABLE, [
                'payment_number' => 'PAY-' . date('Ymd-His') . '-' . random_int(10, 99),
                'order_id' => $order_id,
                'customer_id' => $customer_id,
                'amount' => $order_total,
                'payment_method' => 'PayMongo',
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $created_order_ids[] = $order_id;
        }

        if ($delivery_fee_total > 0) {
            $line_items[] = ['name' => 'Delivery Fee', 'amount' => $delivery_fee_total, 'quantity' => 1];
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => FALSE, 'message' => 'Failed to place order. Please try again.']);
            return;
        }

        $account = $this->db->where('user_account_id', $this->session->userdata('user_account_id'))->get(USER_ACCOUNT_TABLE)->row_array();
        $session = PaymongoService::createCheckoutSession(
            $line_items,
            $created_order_ids,
            $account['email'] ?? NULL,
            site_url('checkout/return') . '?session={CHECKOUT_SESSION_ID}',
            site_url('checkout')
        );

        if (!$session['success']) {
            // Nothing was deducted for these orders, so cancelling them
            // outright is a clean rollback — no stock/commission to reverse.
            $this->db->where_in('order_id', $created_order_ids)->update(ORDER_TABLE, ['order_status' => 'cancelled']);
            $this->db->where_in('order_id', $created_order_ids)->update(PAYMENTS_TABLE, ['status' => 'failed']);
            echo json_encode(['success' => FALSE, 'message' => $session['message']]);
            return;
        }

        $this->db->where_in('order_id', $created_order_ids)
            ->update(ORDER_TABLE, ['paymongo_checkout_session_id' => $session['session_id']]);
        $this->db->where_in('order_id', $created_order_ids)
            ->update(PAYMENTS_TABLE, ['paymongo_checkout_session_id' => $session['session_id']]);

        echo json_encode([
            'success' => TRUE,
            'checkout_url' => $session['checkout_url'],
        ]);
    }

    /**
     * Landing page after PayMongo redirects the customer back. The redirect
     * itself is NOT proof of payment — the webhook is the sole source of
     * truth — so this just renders a "confirming your payment" screen that
     * polls status() until the webhook has caught up (or the customer gives
     * up waiting).
     */
    public function return_page() {
        $session_id = $this->input->get('session', TRUE);
        $data['page_title'] = 'Confirming Payment - DropSell';
        $data['session_id'] = $session_id;
        $this->load->view('customer/layouts/header', $data);
        $this->load->view('customer/cart/checkout_return', $data);
        $this->load->view('customer/layouts/footer', $data);
    }

    /**
     * Polled by checkout_return.php while waiting for the webhook. Clears
     * the cart the moment payment is observed as confirmed — not before,
     * so an abandoned/failed payment leaves the cart intact.
     */
    public function status() {
        $session_id = $this->input->get('session', TRUE);
        $customer_id = $this->session->userdata('user_id');

        $order = $session_id
            ? $this->db->where('paymongo_checkout_session_id', $session_id)
                ->where('customer_id', $customer_id)
                ->order_by('order_id', 'ASC')
                ->get(ORDER_TABLE)->row_array()
            : NULL;

        if (!$order) {
            echo json_encode(['order_status' => 'unknown']);
            return;
        }

        if ($order['order_status'] === 'paid' || $order['order_status'] === 'processing') {
            $this->session->set_userdata('cart', []);
        }

        echo json_encode([
            'order_status' => $order['order_status'],
            'order_id' => $order['order_id'],
        ]);
    }
}
