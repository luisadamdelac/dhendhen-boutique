<?php
class Orders extends Authenticated_Controller {
    public function __construct() {
        parent::__construct();
        $this->require_role(ROLE_RESELLER);
        $this->load->model(['order_model', 'activity_log_model']);
    }

    public function index() {
        $data = $this->set_view_data();
        $data['page_title'] = 'Reseller Orders';
        $data['orders'] = $this->db->select('o.*, c.first_name, c.last_name, ct.amount as commission_amount')
            ->from(ORDER_TABLE . ' o')
            ->join(CUSTOMER_TABLE . ' c', 'o.customer_id = c.customer_id', 'left')
            ->join(COMMISSIONS_TABLE . ' ct', 'ct.order_id = o.order_id', 'left')
            ->where('o.reseller_id', $this->user_id)
            ->order_by('o.order_id', 'DESC')
            ->get()->result_array();

        foreach ($data['orders'] as &$order) {
            $order['customer_name'] = trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? ''));
        }
        unset($order);
        $this->load->view('reseller/layouts/header', $data);
        $this->load->view('reseller/orders/index', $data);
        $this->load->view('reseller/layouts/footer', $data);
    }

    public function view($order_id = NULL) {
        $order = $this->db->select('o.*, c.first_name, c.last_name, c.contact_number, u.email')
            ->from(ORDER_TABLE . ' o')
            ->join(CUSTOMER_TABLE . ' c', 'o.customer_id = c.customer_id', 'left')
            ->join(USER_ACCOUNT_TABLE . ' u', 'c.user_account_id = u.user_account_id', 'left')
            ->where('o.order_id', $order_id)
            ->where('o.reseller_id', $this->user_id)
            ->get()->row_array();

        if (!$order) {
            show_404();
        }

        $order['customer_name'] = trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? ''));
        $order['customer_email'] = $order['email'] ?? '';
        $order['customer_phone'] = $order['contact_number'] ?? '';
        $order['delivery_address'] = trim(implode(', ', array_filter([
            $order['delivery_street'], $order['delivery_barangay'], $order['delivery_city'], $order['delivery_zip_code'],
        ])));

        $payment = $this->db->where('order_id', $order_id)->get(PAYMENTS_TABLE)->row_array();
        $order['payment_method'] = $payment['payment_method'] ?? 'GCash';
        $order['payment_status'] = $payment['status'] ?? 'pending';

        $commission = $this->db->where('order_id', $order_id)->get(COMMISSIONS_TABLE)->row_array();
        $order['commission_amount'] = $commission['amount'] ?? 0;

        $order['items'] = $this->db->select('od.*, od.unit_price as price, p.product_name, pi.image_path as product_image')
            ->from(ORDER_DETAILS_TABLE . ' od')
            ->join(PRODUCT_TABLE . ' p', 'od.product_id = p.product_id', 'left')
            ->join(PRODUCT_IMAGE_TABLE . ' pi', 'pi.product_id = p.product_id AND pi.is_primary = 1', 'left')
            ->where('od.order_id', $order_id)
            ->get()->result_array();

        $data = $this->set_view_data();
        $data['page_title'] = 'Order Details';
        $data['order'] = $order;

        $this->load->view('reseller/layouts/header', $data);
        $this->load->view('reseller/orders/view', $data);
        $this->load->view('reseller/layouts/footer', $data);
    }
}
