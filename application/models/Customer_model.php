<?php
/**
 * Customer Model
 * Handles customer-specific data from customer_tbl
 */

class Customer_model extends CI_Model {
    
    protected $table = 'customer_tbl';

    public function __construct() {
        parent::__construct();
        $this->load->config('dropsell');
    }

    /**
     * Get customer by customer_id
     */
    public function get_by_id($customer_id) {
        $query = $this->db->where('customer_id', $customer_id)
                         ->get($this->table);
        return $query->row_array();
    }

    /**
     * Get customer by user_account_id
     */
    public function get_by_user_account_id($user_account_id) {
        $query = $this->db->where('user_account_id', $user_account_id)
                         ->get($this->table);
        return $query->row_array();
    }

    /**
     * Get all customers with pagination
     */
    public function get_all($limit = NULL, $offset = NULL) {
        $query = $this->db->select('c.*, u.email, u.status, u.created_at')
                         ->from($this->table . ' c')
                         ->join('user_account_tbl u', 'c.user_account_id = u.user_account_id');
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        $query->order_by('c.created_at', 'DESC');
        return $query->get()->result_array();
    }

    /**
     * Count all customers
     */
    public function count_all() {
        return $this->db->count_all($this->table);
    }

    /**
     * Create customer profile
     */
    public function create($data) {
        $customer_data = array(
            'user_account_id' => $data['user_account_id'],
            'first_name'      => $data['first_name'],
            'middle_name'     => $data['middle_name'] ?? NULL,
            'last_name'       => $data['last_name'],
            'contact_number'  => $data['contact_number'],
            'street'          => $data['street'] ?? NULL,
            'barangay'        => $data['barangay'] ?? NULL,
            'city'            => $data['city'],
            'profile_image'   => $data['profile_image'] ?? NULL,
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s')
        );
        
        if ($this->db->insert($this->table, $customer_data)) {
            return $this->db->insert_id();
        }
        
        return FALSE;
    }

    /**
     * Update customer profile
     */
    public function update($customer_id, $data) {
        $update_data = array(
            'first_name'    => $data['first_name'] ?? NULL,
            'middle_name'   => $data['middle_name'] ?? NULL,
            'last_name'     => $data['last_name'] ?? NULL,
            'contact_number'=> $data['contact_number'] ?? NULL,
            'street'        => $data['street'] ?? NULL,
            'barangay'      => $data['barangay'] ?? NULL,
            'city'          => $data['city'] ?? NULL,
            'profile_image' => $data['profile_image'] ?? NULL,
            'updated_at'    => date('Y-m-d H:i:s')
        );
        
        // Remove NULL values
        $update_data = array_filter($update_data, function($v) { return $v !== NULL; });
        
        $this->db->where('customer_id', $customer_id);
        return $this->db->update($this->table, $update_data);
    }

    /**
     * Get customer's full name
     */
    public function get_full_name($customer_id) {
        $customer = $this->get_by_id($customer_id);
        
        if (!$customer) {
            return '';
        }
        
        $name = trim($customer['first_name']);
        if ($customer['middle_name']) {
            $name .= ' ' . $customer['middle_name'];
        }
        $name .= ' ' . $customer['last_name'];
        
        return $name;
    }

    /**
     * Search customers
     */
    public function search($keyword, $limit = NULL, $offset = NULL) {
        $query = $this->db->select('c.*, u.email, u.status')
                         ->from($this->table . ' c')
                         ->join('user_account_tbl u', 'c.user_account_id = u.user_account_id')
                         ->group_start()
                         ->like('c.first_name', $keyword)
                         ->or_like('c.last_name', $keyword)
                         ->or_like('u.email', $keyword)
                         ->or_like('c.contact_number', $keyword)
                         ->group_end();
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        $query->order_by('c.created_at', 'DESC');
        return $query->get()->result_array();
    }
}

/* End of file Customer_model.php */
/* Location: ./application/models/Customer_model.php */
