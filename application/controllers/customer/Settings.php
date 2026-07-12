<?php
class Settings extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper(['url', 'auth']);
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
        $data['page_title'] = 'Settings';
        $this->load->view('customer/layouts/header', $data);
        $this->load->view('customer/settings/index', $data);
        $this->load->view('customer/layouts/footer', $data);
    }

    /**
     * Activity History — a customer-friendly timeline merging their order
     * events (from order_tbl) with account events (from activity_logs),
     * sorted newest first. Framed as "history" rather than a raw log.
     */
    public function activity_history() {
        $data['page_title'] = 'Activity History';
        $customer_id = $this->session->userdata('user_id');

        $this->load->model('Activity_log_model');

        $orders = $this->db->select('order_id, order_number, order_status, total_amount, created_at')
            ->from(ORDER_TABLE)
            ->where('customer_id', $customer_id)
            ->order_by('created_at', 'DESC')
            ->limit(50)
            ->get()->result_array();

        $account_logs = $this->Activity_log_model->get_user_logs('customer', $customer_id, 50, 0);

        $events = [];
        foreach ($orders as $order) {
            $events[] = [
                'type' => 'order',
                'date' => $order['created_at'],
                'label' => 'Order #' . $order['order_number'] . ' — ' . ucfirst($order['order_status']),
                'detail' => '₱' . number_format($order['total_amount'], 2),
                'link' => site_url('orders/view/' . $order['order_id']),
            ];
        }
        foreach ($account_logs as $log) {
            $events[] = [
                'type' => 'account',
                'date' => $log['created_at'],
                'label' => ucwords(str_replace('_', ' ', $log['action'])),
                'detail' => $log['details'] ?? '',
                'link' => NULL,
            ];
        }

        usort($events, fn($a, $b) => strtotime($b['date']) <=> strtotime($a['date']));
        $data['events'] = $events;

        $this->load->view('customer/layouts/header', $data);
        $this->load->view('customer/settings/activity_history', $data);
        $this->load->view('customer/layouts/footer', $data);
    }
}
