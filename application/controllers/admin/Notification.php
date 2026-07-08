<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Notification extends Authenticated_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_role('admin');
        $this->load->model('Notification_model');
    }

    /**
     * Notification center
     */
    public function index() {
        $user_id = get_user_id();
        $page = $this->input->get('page') ?? 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $data['notifications'] = $this->Notification_model->get_by_user($user_id, 'admin', $limit, $offset);
        $data['total'] = $this->db->from('notifications')
            ->where('user_id', $user_id)
            ->where('user_type', 'admin')
            ->count_all_results();

        $data['unread_count'] = $this->Notification_model->get_unread_count($user_id, 'admin');
        $data['page'] = $page;
        $data['pages'] = ceil($data['total'] / $limit);

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/notification/index', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Mark notification as read
     */
    public function mark_as_read($notification_id = '') {
        if (empty($notification_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $notification = $this->Notification_model->get_by_id($notification_id);
        if (!$notification || $notification['user_id'] != get_user_id()) {
            echo json_encode(['success' => FALSE, 'message' => 'Notification not found']);
            return;
        }

        $this->Notification_model->mark_as_read($notification_id);
        echo json_encode(['success' => TRUE, 'message' => 'Notification marked as read']);
    }

    /**
     * Mark all as read
     */
    public function mark_all_as_read() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $user_id = get_user_id();
        $this->Notification_model->mark_all_as_read($user_id, 'admin');
        echo json_encode(['success' => TRUE, 'message' => 'All notifications marked as read']);
    }

    /**
     * Delete notification
     */
    public function delete($notification_id = '') {
        if (empty($notification_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $notification = $this->Notification_model->get_by_id($notification_id);
        if (!$notification || $notification['user_id'] != get_user_id()) {
            echo json_encode(['success' => FALSE, 'message' => 'Notification not found']);
            return;
        }

        $this->Notification_model->delete($notification_id);
        echo json_encode(['success' => TRUE, 'message' => 'Notification deleted']);
    }

    /**
     * Get unread count (AJAX)
     */
    public function unread_count() {
        $user_id = get_user_id();
        $count = $this->Notification_model->get_unread_count($user_id, 'admin');
        echo json_encode(['count' => $count]);
    }

    /**
     * Get unread notifications (AJAX)
     */
    public function unread() {
        $user_id = get_user_id();
        $notifications = $this->Notification_model->get_unread($user_id, 'admin');
        echo json_encode(['notifications' => $notifications]);
    }

    /**
     * Recent notifications for the header dropdowns (AJAX), optionally
     * filtered by type — the envelope icon only wants order-related ones.
     */
    public function recent($type = '') {
        $user_id = get_user_id();

        $query = $this->db->select('*')
            ->from('notifications')
            ->where('user_id', $user_id)
            ->where('user_type', 'admin');

        if (!empty($type)) {
            $query->where('type', $type);
        }

        $notifications = $query->order_by('created_at', 'DESC')->limit(8)->get()->result_array();

        echo json_encode(['success' => TRUE, 'notifications' => $notifications]);
    }
}
?>
