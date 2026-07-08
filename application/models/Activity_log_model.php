<?php
/**
 * Activity Log Model
 * Tracks all user activities across all roles
 */

class Activity_log_model extends CI_Model {
    
    protected $table = 'activity_logs';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Create activity log entry
     */
    public function create($data) {
        $log_data = array(
            'user_type'  => $data['user_type'],
            'user_id'    => $data['user_id'],
            'action'     => $data['action'],
            'entity_type'=> $data['entity_type'] ?? NULL,
            'entity_id'  => $data['entity_id'] ?? NULL,
            'details'    => $data['details'] ?? NULL,
            'ip_address' => $data['ip_address'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        );

        return $this->db->insert($this->table, $log_data);
    }

    /**
     * Convenience wrapper matching the positional-argument call style used
     * throughout the admin controllers: log_activity($user_id, $user_type,
     * $action, $details, $ip_address).
     */
    public function log_activity($user_id, $user_type, $action, $details = NULL, $ip_address = '') {
        return $this->create([
            'user_type' => $user_type,
            'user_id' => $user_id,
            'action' => $action,
            'details' => $details,
            'ip_address' => $ip_address,
        ]);
    }

    /**
     * Get logs for specific user
     */
    public function get_user_logs($user_type, $user_id, $limit = 50, $offset = 0) {
        $query = $this->db->where('user_type', $user_type)
                         ->where('user_id', $user_id)
                         ->order_by('created_at', 'DESC')
                         ->limit($limit, $offset)
                         ->get($this->table);
        
        return $query->result_array();
    }

    /**
     * Get all activity logs with pagination
     */
    public function get_all($limit = 50, $offset = 0) {
        $query = $this->db->order_by('created_at', 'DESC')
                         ->limit($limit, $offset)
                         ->get($this->table);
        
        return $query->result_array();
    }

    /**
     * Count total logs
     */
    public function count_all() {
        return $this->db->count_all($this->table);
    }

    /**
     * Count user logs
     */
    public function count_user_logs($user_type, $user_id) {
        $this->db->where('user_type', $user_type);
        $this->db->where('user_id', $user_id);
        return $this->db->count_all_results($this->table);
    }

    /**
     * Get logs for specific action
     */
    public function get_by_action($action, $limit = 50, $offset = 0) {
        $query = $this->db->where('action', $action)
                         ->order_by('created_at', 'DESC')
                         ->limit($limit, $offset)
                         ->get($this->table);
        
        return $query->result_array();
    }

    /**
     * Get logs for specific entity
     */
    public function get_by_entity($entity_type, $entity_id) {
        $query = $this->db->where('entity_type', $entity_type)
                         ->where('entity_id', $entity_id)
                         ->order_by('created_at', 'DESC')
                         ->get($this->table);
        
        return $query->result_array();
    }

    /**
     * Search logs
     */
    public function search($keyword, $limit = 50, $offset = 0) {
        $query = $this->db->group_start()
                         ->like('action', $keyword)
                         ->or_like('details', $keyword)
                         ->group_end()
                         ->order_by('created_at', 'DESC')
                         ->limit($limit, $offset)
                         ->get($this->table);
        
        return $query->result_array();
    }
}

/* End of file Activity_log_model.php */
/* Location: ./application/models/Activity_log_model.php */
