<?php
/**
 * Refund Model
 * Handles refund requests from refund_request_tbl
 */

class Refund_model extends CI_Model {
    
    protected $table = 'refund_request_tbl';

    public function __construct() {
        parent::__construct();
        $this->load->config('dropsell');
    }

    /**
     * Get refund by refund_id
     */
    public function get_by_id($refund_id) {
        $query = $this->db->where('refund_id', $refund_id)
                         ->get($this->table);
        return $query->row_array();
    }

    /**
     * Get all refunds
     */
    public function get_all($limit = NULL, $offset = NULL) {
        $query = $this->db->select('r.*, c.first_name as customer_first, c.last_name as customer_last, o.order_number')
                         ->from($this->table . ' r')
                         ->join('customer_tbl c', 'r.customer_id = c.customer_id')
                         ->join('order_tbl o', 'r.order_id = o.order_id');
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        $query->order_by('r.created_at', 'DESC');
        return $query->get()->result_array();
    }

    /**
     * Get refunds by status
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
     * Get refunds by customer
     */
    public function get_by_customer($customer_id, $limit = NULL, $offset = NULL) {
        $query = $this->db->where('customer_id', $customer_id);
        
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
     * Count pending refunds
     */
    public function count_pending() {
        return $this->count_by_status('pending');
    }

    /**
     * Update refund status
     */
    public function update_status($refund_id, $status, $remarks = NULL) {
        $data = array(
            'status'         => $status,
            'admin_remarks'  => $remarks,
            'reviewed_at'    => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s')
        );

        $this->db->where('refund_id', $refund_id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Approve refund
     */
    public function approve($refund_id, $admin_id, $remarks = NULL) {
        $data = array(
            'status'      => 'approved',
            'admin_id'    => $admin_id,
            'reviewed_by' => $admin_id,
            'admin_remarks'=> $remarks,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s')
        );

        $this->db->where('refund_id', $refund_id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Reject refund
     */
    public function reject($refund_id, $admin_id, $remarks = NULL) {
        $data = array(
            'status'      => 'rejected',
            'admin_id'    => $admin_id,
            'reviewed_by' => $admin_id,
            'admin_remarks'=> $remarks,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s')
        );

        $this->db->where('refund_id', $refund_id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Create refund request
     */
    public function create($data) {
        $refund_data = array(
            'refund_number' => $data['refund_number'],
            'order_id'      => $data['order_id'],
            'customer_id'   => $data['customer_id'],
            'reseller_id'   => $data['reseller_id'],
            'reason'        => $data['reason'],
            'amount'        => (float)$data['amount'],
            'images'        => $data['images'] ?? NULL,
            'status'        => 'pending',
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s')
        );

        if ($this->db->insert($this->table, $refund_data)) {
            return $this->db->insert_id();
        }

        return FALSE;
    }
}

/* End of file Refund_model.php */
/* Location: ./application/models/Refund_model.php */
