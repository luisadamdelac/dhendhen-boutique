<?php
/**
 * Reseller Model
 * Handles reseller-specific data from reseller_tbl
 */

class Reseller_model extends CI_Model {
    
    protected $table = 'reseller_tbl';

    public function __construct() {
        parent::__construct();
        $this->load->config('dropsell');
    }

    /**
     * Get reseller by reseller_id
     */
    public function get_by_id($reseller_id) {
        $query = $this->db->where('reseller_id', $reseller_id)
                         ->get($this->table);
        return $query->row_array();
    }

    /**
     * Get reseller by user_account_id
     */
    public function get_by_user_account_id($user_account_id) {
        $query = $this->db->where('user_account_id', $user_account_id)
                         ->get($this->table);
        return $query->row_array();
    }

    /**
     * Get all resellers with pagination
     */
    public function get_all($limit = NULL, $offset = NULL) {
        $query = $this->db->select('r.*, u.email, u.status, u.created_at')
                         ->from($this->table . ' r')
                         ->join('user_account_tbl u', 'r.user_account_id = u.user_account_id');
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        $query->order_by('r.created_at', 'DESC');
        return $query->get()->result_array();
    }

    /**
     * Count all resellers
     */
    public function count_all() {
        return $this->db->count_all($this->table);
    }

    /**
     * Get reseller with balance info
     */
    public function get_with_balance($reseller_id) {
        $query = $this->db->select('r.*')
                         ->from($this->table . ' r')
                         ->where('r.reseller_id', $reseller_id)
                         ->get();
        return $query->row_array();
    }

    /**
     * Create reseller profile
     */
    public function create($data) {
        $reseller_data = array(
            'user_account_id'         => $data['user_account_id'],
            'first_name'              => $data['first_name'],
            'middle_name'             => $data['middle_name'] ?? NULL,
            'last_name'               => $data['last_name'],
            'contact_number'          => $data['contact_number'],
            'street'                  => $data['street'] ?? NULL,
            'barangay'                => $data['barangay'] ?? NULL,
            'city'                    => $data['city'],
            'profile_image'           => $data['profile_image'] ?? NULL,
            'business_name'           => $data['business_name'] ?? NULL,
            'commission_balance'      => $data['commission_balance'] ?? 0.00,
            'total_commission_earned' => $data['total_commission_earned'] ?? 0.00,
            'total_withdrawn'         => $data['total_withdrawn'] ?? 0.00,
            'created_at'              => date('Y-m-d H:i:s'),
            'updated_at'              => date('Y-m-d H:i:s')
        );
        
        if ($this->db->insert($this->table, $reseller_data)) {
            return $this->db->insert_id();
        }
        
        return FALSE;
    }

    /**
     * Update reseller profile
     */
    public function update($reseller_id, $data) {
        $update_data = array(
            'first_name'    => $data['first_name'] ?? NULL,
            'middle_name'   => $data['middle_name'] ?? NULL,
            'last_name'     => $data['last_name'] ?? NULL,
            'contact_number'=> $data['contact_number'] ?? NULL,
            'street'        => $data['street'] ?? NULL,
            'barangay'      => $data['barangay'] ?? NULL,
            'city'          => $data['city'] ?? NULL,
            'profile_image' => $data['profile_image'] ?? NULL,
            'business_name' => $data['business_name'] ?? NULL,
            'updated_at'    => date('Y-m-d H:i:s')
        );
        
        // Remove NULL values
        $update_data = array_filter($update_data, function($v) { return $v !== NULL; });
        
        $this->db->where('reseller_id', $reseller_id);
        return $this->db->update($this->table, $update_data);
    }

    /**
     * Update commission balance
     */
    public function update_commission_balance($reseller_id, $amount) {
        $this->db->set('commission_balance', 'commission_balance + ' . (float)$amount, FALSE);
        $this->db->where('reseller_id', $reseller_id);
        return $this->db->update($this->table);
    }

    /**
     * Update withdrawn amount
     */
    public function update_withdrawn_amount($reseller_id, $amount) {
        $this->db->set('total_withdrawn', 'total_withdrawn + ' . (float)$amount, FALSE);
        $this->db->where('reseller_id', $reseller_id);
        return $this->db->update($this->table);
    }

    /**
     * Get reseller's full name
     */
    public function get_full_name($reseller_id) {
        $reseller = $this->get_by_id($reseller_id);
        
        if (!$reseller) {
            return '';
        }
        
        $name = trim($reseller['first_name']);
        if ($reseller['middle_name']) {
            $name .= ' ' . $reseller['middle_name'];
        }
        $name .= ' ' . $reseller['last_name'];
        
        return $name;
    }

    /**
     * Get reseller commission balance
     */
    public function get_commission_balance($reseller_id) {
        $reseller = $this->get_by_id($reseller_id);
        return $reseller ? (float)$reseller['commission_balance'] : 0.00;
    }

    /**
     * Search resellers
     */
    public function search($keyword, $limit = NULL, $offset = NULL) {
        $query = $this->db->select('r.*, u.email, u.status')
                         ->from($this->table . ' r')
                         ->join('user_account_tbl u', 'r.user_account_id = u.user_account_id')
                         ->group_start()
                         ->like('r.first_name', $keyword)
                         ->or_like('r.last_name', $keyword)
                         ->or_like('r.business_name', $keyword)
                         ->or_like('u.email', $keyword)
                         ->group_end();
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        $query->order_by('r.created_at', 'DESC');
        return $query->get()->result_array();
    }
}

/* End of file Reseller_model.php */
/* Location: ./application/models/Reseller_model.php */
