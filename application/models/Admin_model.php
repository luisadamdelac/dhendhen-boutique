<?php
/**
 * Admin Model
 * Handles admin-specific data from admin_tbl
 */

class Admin_model extends CI_Model {
    
    protected $table = 'admin_tbl';

    public function __construct() {
        parent::__construct();
        $this->load->config('dropsell');
    }

    /**
     * Get admin by admin_id
     */
    public function get_by_id($admin_id) {
        $query = $this->db->where('admin_id', $admin_id)
                         ->get($this->table);
        return $query->row_array();
    }

    /**
     * Get admin by user_account_id
     */
    public function get_by_user_account_id($user_account_id) {
        $query = $this->db->where('user_account_id', $user_account_id)
                         ->get($this->table);
        return $query->row_array();
    }

    /**
     * Get all admins with pagination
     */
    public function get_all($limit = NULL, $offset = NULL) {
        $query = $this->db->select('a.*, u.email, u.status, u.created_at')
                         ->from($this->table . ' a')
                         ->join('user_account_tbl u', 'a.user_account_id = u.user_account_id');
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        $query->order_by('a.created_at', 'DESC');
        return $query->get()->result_array();
    }

    /**
     * Count all admins
     */
    public function count_all() {
        return $this->db->count_all($this->table);
    }

    /**
     * Get admin with branch info
     */
    public function get_with_branch($admin_id) {
        $query = $this->db->select('a.*, b.branch_name, b.city')
                         ->from($this->table . ' a')
                         ->join('branches b', 'a.branch_id = b.branch_id', 'left')
                         ->where('a.admin_id', $admin_id)
                         ->get();
        return $query->row_array();
    }

    /**
     * Create admin profile
     */
    public function create($data) {
        $admin_data = array(
            'user_account_id' => $data['user_account_id'],
            'first_name'      => $data['first_name'],
            'middle_name'     => $data['middle_name'] ?? NULL,
            'last_name'       => $data['last_name'],
            'contact_number'  => $data['contact_number'],
            'street'          => $data['street'] ?? NULL,
            'barangay'        => $data['barangay'] ?? NULL,
            'city'            => $data['city'],
            'profile_image'   => $data['profile_image'] ?? NULL,
            'branch_id'       => $data['branch_id'] ?? NULL,
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s')
        );
        
        if ($this->db->insert($this->table, $admin_data)) {
            return $this->db->insert_id();
        }
        
        return FALSE;
    }

    /**
     * Update admin profile
     */
    public function update($admin_id, $data) {
        $update_data = array(
            'first_name'    => $data['first_name'] ?? NULL,
            'middle_name'   => $data['middle_name'] ?? NULL,
            'last_name'     => $data['last_name'] ?? NULL,
            'contact_number'=> $data['contact_number'] ?? NULL,
            'street'        => $data['street'] ?? NULL,
            'barangay'      => $data['barangay'] ?? NULL,
            'city'          => $data['city'] ?? NULL,
            'profile_image' => $data['profile_image'] ?? NULL,
            'branch_id'     => $data['branch_id'] ?? NULL,
            'updated_at'    => date('Y-m-d H:i:s')
        );
        
        // Remove NULL values
        $update_data = array_filter($update_data, function($v) { return $v !== NULL; });
        
        $this->db->where('admin_id', $admin_id);
        return $this->db->update($this->table, $update_data);
    }

    /**
     * Search admins
     */
    public function search($keyword, $limit = NULL, $offset = NULL) {
        $query = $this->db->select('a.*, u.email, u.status')
                         ->from($this->table . ' a')
                         ->join('user_account_tbl u', 'a.user_account_id = u.user_account_id')
                         ->group_start()
                         ->like('a.first_name', $keyword)
                         ->or_like('a.last_name', $keyword)
                         ->or_like('u.email', $keyword)
                         ->or_like('a.contact_number', $keyword)
                         ->group_end();
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        $query->order_by('a.created_at', 'DESC');
        return $query->get()->result_array();
    }

    /**
     * Get admin's full name
     */
    public function get_full_name($admin_id) {
        $admin = $this->get_by_id($admin_id);
        
        if (!$admin) {
            return '';
        }
        
        $name = trim($admin['first_name']);
        if ($admin['middle_name']) {
            $name .= ' ' . $admin['middle_name'];
        }
        $name .= ' ' . $admin['last_name'];
        
        return $name;
    }
}

/* End of file Admin_model.php */
/* Location: ./application/models/Admin_model.php */
