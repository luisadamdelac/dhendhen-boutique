<?php
class Notifications extends CI_Controller {
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
        $this->load->model('Notification_model');
    }

    /**
     * Notification center — mirrors reseller/staff/admin's own Notifications
     * controller, just scoped to user_type 'customer'.
     */
    public function index() {
        $data['page_title'] = 'Notifications';
        $customer_id = $this->session->userdata('user_id');

        $page = max(1, (int) ($this->input->get('page') ?? 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $data['notifications'] = $this->Notification_model->get_by_user($customer_id, 'customer', $limit, $offset);
        $data['total'] = $this->db->from('notifications')
            ->where('user_id', $customer_id)
            ->where('user_type', 'customer')
            ->count_all_results();

        $data['unread_count'] = $this->Notification_model->get_unread_count($customer_id, 'customer');
        $data['page'] = $page;
        $data['pages'] = (int) ceil($data['total'] / $limit);

        $this->load->view('customer/layouts/header', $data);
        $this->load->view('customer/notifications/index', $data);
        $this->load->view('customer/layouts/footer', $data);
    }

    public function mark_as_read($notification_id = '') {
        if (empty($notification_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $customer_id = $this->session->userdata('user_id');
        $notification = $this->Notification_model->get_by_id($notification_id);
        if (!$notification || $notification['user_id'] != $customer_id || $notification['user_type'] !== 'customer') {
            echo json_encode(['success' => FALSE, 'message' => 'Notification not found']);
            return;
        }

        $this->Notification_model->mark_as_read($notification_id);
        echo json_encode(['success' => TRUE, 'message' => 'Notification marked as read']);
    }

    public function mark_all_as_read() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $this->Notification_model->mark_all_as_read($this->session->userdata('user_id'), 'customer');
        echo json_encode(['success' => TRUE, 'message' => 'All notifications marked as read']);
    }

    public function delete($notification_id = '') {
        if (empty($notification_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $customer_id = $this->session->userdata('user_id');
        $notification = $this->Notification_model->get_by_id($notification_id);
        if (!$notification || $notification['user_id'] != $customer_id || $notification['user_type'] !== 'customer') {
            echo json_encode(['success' => FALSE, 'message' => 'Notification not found']);
            return;
        }

        $this->Notification_model->delete($notification_id);
        echo json_encode(['success' => TRUE, 'message' => 'Notification deleted']);
    }

    /** JSON: unread count, for the header bell badge. */
    public function unread_count() {
        $count = $this->Notification_model->get_unread_count($this->session->userdata('user_id'), 'customer');
        echo json_encode(['count' => $count]);
    }
}
