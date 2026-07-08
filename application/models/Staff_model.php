<?php
/**
 * Staff Model
 * Handles staff-specific data from staff_tbl
 */

class Staff_model extends CI_Model {
    
    protected $table = 'staff_tbl';

    public function __construct() {
        parent::__construct();
        $this->load->config('dropsell');
    }

    /**
     * Get staff by staff_id
     */
    public function get_by_id($staff_id) {
        $query = $this->db->where('staff_id', $staff_id)
                         ->get($this->table);
        return $query->row_array();
    }

    /**
     * Get staff by user_account_id
     */
    public function get_by_user_account_id($user_account_id) {
        $query = $this->db->where('user_account_id', $user_account_id)
                         ->get($this->table);
        return $query->row_array();
    }

    /**
     * Get all staff with pagination
     */
    public function get_all($limit = NULL, $offset = NULL) {
        $query = $this->db->select('s.*, u.email, u.status, u.created_at')
                         ->from($this->table . ' s')
                         ->join('user_account_tbl u', 's.user_account_id = u.user_account_id');
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        $query->order_by('s.created_at', 'DESC');
        return $query->get()->result_array();
    }

    /**
     * Count all staff
     */
    public function count_all() {
        return $this->db->count_all($this->table);
    }

    /**
     * Get staff with branch info
     */
    public function get_with_branch($staff_id) {
        $query = $this->db->select('s.*, b.branch_name, b.city')
                         ->from($this->table . ' s')
                         ->join('branches b', 's.branch_id = b.branch_id', 'left')
                         ->where('s.staff_id', $staff_id)
                         ->get();
        return $query->row_array();
    }

    /**
     * Create staff profile
     */
    public function create($data) {
        $staff_data = array(
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
        
        if ($this->db->insert($this->table, $staff_data)) {
            return $this->db->insert_id();
        }
        
        return FALSE;
    }

    /**
     * Update staff profile
     */
    public function update($staff_id, $data) {
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
        
        $this->db->where('staff_id', $staff_id);
        return $this->db->update($this->table, $update_data);
    }

    /**
     * Get staff's full name
     */
    public function get_full_name($staff_id) {
        $staff = $this->get_by_id($staff_id);
        
        if (!$staff) {
            return '';
        }
        
        $name = trim($staff['first_name']);
        if ($staff['middle_name']) {
            $name .= ' ' . $staff['middle_name'];
        }
        $name .= ' ' . $staff['last_name'];
        
        return $name;
    }
}

/* End of file Staff_model.php */
/* Location: ./application/models/Staff_model.php */
