<?php
/**
 * API Controller
 * Handles all API endpoints for AJAX requests
 */

class Api extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->config('dropsell');
        $this->load->helper(['url', 'session']);
        $this->load->model('notification_model');

        // Root-level controller (no admin/staff/reseller/customer directory
        // of its own) called from multiple roles' pages via AJAX — each
        // caller must say which role's session to read via ?scope=.
        $requested_scope = $this->input->get('scope', TRUE);
        $valid_roles = array(ROLE_ADMIN, ROLE_STAFF, ROLE_RESELLER, ROLE_CUSTOMER);
        load_scoped_session(in_array($requested_scope, $valid_roles, true) ? $requested_scope : null);

        // Set JSON header
        header('Content-Type: application/json');
    }

    /**
     * Get unread notifications count
     */
    public function notifications() {
        // Check if user is logged in
        if (!$this->session->userdata('user_account_id')) {
            echo json_encode([
                'success' => FALSE,
                'message' => 'Not authenticated',
                'count' => 0
            ]);
            return;
        }

        // Get user info from session
        $user_id = $this->session->userdata('user_id');
        $user_type = $this->session->userdata('user_type');

        // Get unread notifications count
        $count = $this->notification_model->get_unread_count($user_id, $user_type);

        // Return JSON response
        echo json_encode([
            'success' => TRUE,
            'count' => (int)$count,
            'message' => 'Notifications retrieved successfully'
        ]);
    }

    /**
     * Mark notification as read
     */
    public function mark_notification_read() {
        if (!$this->session->userdata('user_account_id')) {
            echo json_encode([
                'success' => FALSE,
                'message' => 'Not authenticated'
            ]);
            return;
        }

        $notification_id = $this->input->post('notification_id');

        if (!$notification_id) {
            echo json_encode([
                'success' => FALSE,
                'message' => 'Notification ID is required'
            ]);
            return;
        }

        // Update notification as read
        $updated = $this->db->update('notifications', 
            ['is_read' => 1], 
            ['notification_id' => $notification_id]
        );

        if ($updated) {
            echo json_encode([
                'success' => TRUE,
                'message' => 'Notification marked as read'
            ]);
        } else {
            echo json_encode([
                'success' => FALSE,
                'message' => 'Failed to update notification'
            ]);
        }
    }

    /**
     * Get all notifications for user
     */
    public function get_all_notifications() {
        if (!$this->session->userdata('user_account_id')) {
            echo json_encode([
                'success' => FALSE,
                'message' => 'Not authenticated',
                'notifications' => []
            ]);
            return;
        }

        $user_id = $this->session->userdata('user_id');
        $user_type = $this->session->userdata('user_type');

        // Get notifications
        $notifications = $this->db->where('user_id', $user_id)
                                  ->where('user_type', $user_type)
                                  ->order_by('created_at', 'DESC')
                                  ->limit(10)
                                  ->get('notifications')
                                  ->result_array();

        echo json_encode([
            'success' => TRUE,
            'notifications' => $notifications,
            'total' => count($notifications)
        ]);
    }
}

/* End of file Api.php */
/* Location: ./application/controllers/Api.php */
