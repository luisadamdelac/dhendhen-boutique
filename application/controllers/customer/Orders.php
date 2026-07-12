<?php
class Orders extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper(['url', 'form', 'auth']);
        $this->load->database();
        load_scoped_session('customer');
        if (!ensure_logged_in()) {
            redirect('auth/login');
        }
        $this->load->config('dropsell');
        if ($this->session->userdata('user_type') !== ROLE_CUSTOMER) {
            deny_role_access();
        }
    }

    public function index() {
        $data['page_title'] = 'My Orders';
        $customer_id = $this->session->userdata('user_id');
        $data['orders'] = $this->db->select('*')->from(ORDER_TABLE)->where('customer_id', $customer_id)->order_by('order_id', 'DESC')->get()->result_array();
        $this->load->view('customer/layouts/header', $data);
        $this->load->view('customer/orders/index', $data);
        $this->load->view('customer/layouts/footer', $data);
    }

    public function view($id = null) {
        if (empty($id)) {
            show_404();
        }
        $customer_id = $this->session->userdata('user_id');
        $data['order'] = $this->db->select('*')->from(ORDER_TABLE)->where('order_id', $id)->where('customer_id', $customer_id)->get()->row_array();
        if (empty($data['order'])) {
            show_404();
        }
        $data['order']['items'] = $this->db->select('od.*, od.total_price as subtotal, p.product_name, pi.image_path as product_image')
            ->from(ORDER_DETAILS_TABLE . ' od')
            ->join(PRODUCT_TABLE . ' p', 'p.product_id = od.product_id')
            ->join(PRODUCT_IMAGE_TABLE . ' pi', 'pi.product_id = p.product_id AND pi.is_primary = 1', 'left')
            ->where('od.order_id', $id)
            ->get()->result_array();

        $customer = $this->db->select('c.*, u.email')
            ->from(CUSTOMER_TABLE . ' c')
            ->join(USER_ACCOUNT_TABLE . ' u', 'c.user_account_id = u.user_account_id')
            ->where('c.customer_id', $customer_id)
            ->get()->row_array();

        $data['order']['customer_name'] = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));
        $data['order']['customer_email'] = $customer['email'] ?? '';
        $data['order']['customer_phone'] = $customer['contact_number'] ?? '';
        $data['order']['payment'] = $this->db->where('order_id', $id)->order_by('payment_id', 'DESC')->limit(1)->get(PAYMENTS_TABLE)->row_array();
        $data['order']['delivery_type'] = $data['order']['delivery_method'];
        $data['order']['delivery_address'] = trim(implode(', ', array_filter([
            $data['order']['delivery_street'],
            $data['order']['delivery_barangay'],
            $data['order']['delivery_city'],
            $data['order']['delivery_zip_code'],
        ])));
        $data['order']['subtotal'] = $data['order']['total_amount'] - $data['order']['delivery_fee'];

        $data['page_title'] = 'Order Details';
        $this->load->view('customer/layouts/header', $data);
        $this->load->view('customer/orders/details', $data);
        $this->load->view('customer/layouts/footer', $data);
    }

    public function cancel($id = null) {
        $customer_id = $this->session->userdata('user_id');
        $order = $this->db->where('order_id', $id)->where('customer_id', $customer_id)->get(ORDER_TABLE)->row_array();

        if (!$order || $order['order_status'] !== 'pending') {
            $this->session->set_flashdata('error', 'This order can no longer be cancelled');
            redirect('customer/orders/view/' . $id);
        }

        require_once APPPATH . 'services/StockService.php';
        require_once APPPATH . 'services/CommissionService.php';

        $items = $this->db->where('order_id', $id)->get(ORDER_DETAILS_TABLE)->result_array();
        foreach ($items as $item) {
            if (!empty($item['variant_id'])) {
                StockService::restoreStock($item['product_id'], $item['quantity'], 'order', $id, NULL, NULL, 'ANY', (int) $item['variant_id']);
            } elseif (!empty($item['variation_id'])) {
                StockService::restoreStock($item['product_id'], $item['quantity'], 'order', $id, NULL, NULL, (int) $item['variation_id']);
            } else {
                StockService::restoreStock($item['product_id'], $item['quantity'], 'order', $id);
            }
        }
        CommissionService::reverseCommission($id, 'Order cancelled by customer');

        $this->db->where('order_id', $id)->update(ORDER_TABLE, [
            'order_status' => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->session->set_flashdata('success', 'Order cancelled successfully');
        redirect('customer/orders/view/' . $id);
    }
}
