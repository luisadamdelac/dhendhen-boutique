<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Notifications extends Authenticated_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_role(ROLE_RESELLER);
        $this->load->model('Notification_model');
    }

    /**
     * Notification center
     */
    public function index() {
        $data = $this->set_view_data();
        $data['page_title'] = 'Notifications';

        $page = $this->input->get('page') ?? 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $data['notifications'] = $this->Notification_model->get_by_user($this->user_id, 'reseller', $limit, $offset);
        $data['total'] = $this->db->from('notifications')
            ->where('user_id', $this->user_id)
            ->where('user_type', 'reseller')
            ->count_all_results();

        $data['unread_count'] = $this->Notification_model->get_unread_count($this->user_id, 'reseller');
        $data['page'] = $page;
        $data['pages'] = ceil($data['total'] / $limit);

        $this->load->view('reseller/layouts/header', $data);
        $this->load->view('reseller/notifications/index', $data);
        $this->load->view('reseller/layouts/footer', $data);
    }

    /**
     * Mark notification as read
     */
    public function mark_as_read($notification_id = '') {
        if (empty($notification_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $notification = $this->Notification_model->get_by_id($notification_id);
        if (!$notification || $notification['user_id'] != $this->user_id || $notification['user_type'] !== 'reseller') {
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

        $this->Notification_model->mark_all_as_read($this->user_id, 'reseller');
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
        if (!$notification || $notification['user_id'] != $this->user_id || $notification['user_type'] !== 'reseller') {
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
        $count = $this->Notification_model->get_unread_count($this->user_id, 'reseller');
        echo json_encode(['count' => $count]);
    }
}
