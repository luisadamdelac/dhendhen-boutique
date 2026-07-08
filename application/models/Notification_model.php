<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_model extends CI_Model {

    protected $table = 'notifications';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get all notifications
     */
    public function get_all($limit = 50, $offset = 0) {
        return $this->db->select('*')
            ->from($this->table)
            ->order_by('created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()->result_array();
    }

    /**
     * Get unread notifications
     */
    public function get_unread($user_id, $user_type) {
        return $this->db->select('*')
            ->from($this->table)
            ->where('user_id', $user_id)
            ->where('user_type', $user_type)
            ->where('is_read', 0)
            ->order_by('created_at', 'DESC')
            ->get()->result_array();
    }

    /**
     * Get unread count
     */
    public function get_unread_count($user_id, $user_type) {
        return $this->db->from($this->table)
            ->where('user_id', $user_id)
            ->where('user_type', $user_type)
            ->where('is_read', 0)
            ->count_all_results();
    }

    /**
     * Get unread count by notification type
     */
    public function get_unread_count_by_type($user_id, $user_type, $notification_type) {
        return $this->db->from($this->table)
            ->where('user_id', $user_id)
            ->where('user_type', $user_type)
            ->where('type', $notification_type)
            ->where('is_read', 0)
            ->count_all_results();
    }

    /**
     * Get notifications by user
     */
    public function get_by_user($user_id, $user_type, $limit = 50, $offset = 0) {
        return $this->db->select('*')
            ->from($this->table)
            ->where('user_id', $user_id)
            ->where('user_type', $user_type)
            ->order_by('created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()->result_array();
    }

    /**
     * Get notification by ID
     */
    public function get_by_id($notification_id) {
        return $this->db->select('*')
            ->from($this->table)
            ->where('notification_id', $notification_id)
            ->get()->row_array();
    }

    /**
     * Insert notification
     */
    public function insert($data) {
        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    /**
     * Mark notification as read
     */
    public function mark_as_read($notification_id) {
        return $this->db->update($this->table, ['is_read' => 1], ['notification_id' => $notification_id]);
    }

    /**
     * Mark all as read for user
     */
    public function mark_all_as_read($user_id, $user_type) {
        return $this->db->update($this->table, ['is_read' => 1], ['user_id' => $user_id, 'user_type' => $user_type]);
    }

    /**
     * Delete notification
     */
    public function delete($notification_id) {
        return $this->db->delete($this->table, ['notification_id' => $notification_id]);
    }

    /**
     * Delete all notifications for user
     */
    public function delete_by_user($user_id, $user_type) {
        return $this->db->delete($this->table, ['user_id' => $user_id, 'user_type' => $user_type]);
    }

    /**
     * Get notification by type
     */
    public function get_by_type($notification_type, $limit = 50, $offset = 0) {
        return $this->db->select('*')
            ->from($this->table)
            ->where('type', $notification_type)
            ->order_by('created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()->result_array();
    }
}
?>
