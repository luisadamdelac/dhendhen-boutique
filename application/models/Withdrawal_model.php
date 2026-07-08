<?php
/**
 * Withdrawal Model
 * Handles withdrawal requests from withdrawal_tbl
 */

class Withdrawal_model extends CI_Model {
    
    protected $table = 'withdrawal_tbl';

    public function __construct() {
        parent::__construct();
        $this->load->config('dropsell');
    }

    /**
     * Get withdrawal by withdrawal_id
     */
    public function get_by_id($withdrawal_id) {
        $query = $this->db->where('withdrawal_id', $withdrawal_id)
                         ->get($this->table);
        return $query->row_array();
    }

    /**
     * Get all withdrawals
     */
    public function get_all($limit = NULL, $offset = NULL) {
        $query = $this->db->select('w.*, r.business_name, r.first_name, r.last_name')
                         ->from($this->table . ' w')
                         ->join('reseller_tbl r', 'w.reseller_id = r.reseller_id');
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        $query->order_by('w.created_at', 'DESC');
        return $query->get()->result_array();
    }

    /**
     * Get withdrawals by status
     */
    public function get_by_status($status, $limit = NULL, $offset = NULL) {
        $query = $this->db->where('status', $status);
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        $query->order_by('created_at', 'DESC');
        return $query->get($this->table)->result_array();
    }

    /**
     * Get withdrawals by reseller
     */
    public function get_by_reseller($reseller_id, $limit = NULL, $offset = NULL) {
        $query = $this->db->where('reseller_id', $reseller_id);
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        $query->order_by('created_at', 'DESC');
        return $query->get($this->table)->result_array();
    }

    /**
     * Count by status
     */
    public function count_by_status($status) {
        $this->db->where('status', $status);
        return $this->db->count_all_results($this->table);
    }

    /**
     * Count pending withdrawals
     */
    public function count_pending() {
        return $this->count_by_status('pending');
    }

    /**
     * Update withdrawal status
     */
    public function update_status($withdrawal_id, $status, $remarks = NULL) {
        $data = array(
            'status'           => $status,
            'rejection_reason' => $remarks,
            'updated_at'       => date('Y-m-d H:i:s')
        );

        $this->db->where('withdrawal_id', $withdrawal_id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Approve withdrawal
     */
    public function approve($withdrawal_id, $admin_id, $scheduled_date = NULL) {
        $data = array(
            'status'       => 'approved',
            'admin_id'     => $admin_id,
            'approved_by'  => $admin_id,
            'approved_at'  => date('Y-m-d H:i:s'),
            'scheduled_date'=> $scheduled_date,
            'updated_at'   => date('Y-m-d H:i:s')
        );

        $this->db->where('withdrawal_id', $withdrawal_id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Reject withdrawal
     */
    public function reject($withdrawal_id, $admin_id, $remarks = NULL) {
        $data = array(
            'status'           => 'rejected',
            'admin_id'         => $admin_id,
            'rejection_reason' => $remarks,
            'updated_at'       => date('Y-m-d H:i:s')
        );

        $this->db->where('withdrawal_id', $withdrawal_id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Mark as completed
     */
    public function mark_completed($withdrawal_id, $payment_reference) {
        $data = array(
            'status'            => 'completed',
            'payment_reference' => $payment_reference,
            'updated_at'        => date('Y-m-d H:i:s')
        );

        $this->db->where('withdrawal_id', $withdrawal_id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Create withdrawal request
     */
    public function create($data) {
        $withdrawal_data = array(
            'withdrawal_number' => $data['withdrawal_number'],
            'reseller_id'       => $data['reseller_id'],
            'gcash_number'      => $data['gcash_number'],
            'gcash_name'        => $data['gcash_name'],
            'amount'            => (float)$data['amount'],
            'otp_code'          => $data['otp_code'] ?? NULL,
            'otp_verified'      => $data['otp_verified'] ?? 0,
            'status'            => 'pending',
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s')
        );

        if ($this->db->insert($this->table, $withdrawal_data)) {
            return $this->db->insert_id();
        }

        return FALSE;
    }

    /**
     * Get total pending withdrawals amount
     */
    public function get_pending_total() {
        $result = $this->db->select_sum('amount')
                          ->where('status', 'pending')
                          ->get($this->table)
                          ->row();
        
        return $result->amount ?? 0.00;
    }
}

/* End of file Withdrawal_model.php */
/* Location: ./application/models/Withdrawal_model.php */
