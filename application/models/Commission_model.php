<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Commission_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get all commission transactions with reseller details
     */
    public function get_all($limit = 50, $offset = 0) {
        return $this->db->select('ct.*, r.first_name, r.last_name, r.business_name, r.commission_balance')
            ->from(COMMISSIONS_TABLE . ' ct')
            ->join(RESELLER_TABLE . ' r', 'ct.reseller_id = r.reseller_id')
            ->order_by('ct.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()->result_array();
    }

    /**
     * Get commission transactions by reseller
     */
    public function get_by_reseller($reseller_id, $limit = 50, $offset = 0) {
        return $this->db->select('*')
            ->from(COMMISSIONS_TABLE)
            ->where('reseller_id', $reseller_id)
            ->order_by('created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()->result_array();
    }

    /**
     * Get commission by commission ID
     */
    public function get_by_id($commission_id) {
        return $this->db->select('ct.*, r.first_name, r.last_name, r.business_name, u.email')
            ->from(COMMISSIONS_TABLE . ' ct')
            ->join(RESELLER_TABLE . ' r', 'ct.reseller_id = r.reseller_id')
            ->join(USER_ACCOUNT_TABLE . ' u', 'r.user_account_id = u.user_account_id')
            ->where('ct.commission_id', $commission_id)
            ->get()->row_array();
    }

    /**
     * Get commission statistics
     */
    public function get_statistics() {
        return $this->db->select('COUNT(*) as total_transactions, SUM(amount) as total_earned, AVG(amount) as avg_commission')
            ->from(COMMISSIONS_TABLE)
            ->get()->row_array();
    }

    /**
     * Get commission by status
     */
    public function get_by_status($status, $limit = 50, $offset = 0) {
        return $this->db->select('ct.*, r.first_name, r.last_name, r.business_name')
            ->from(COMMISSIONS_TABLE . ' ct')
            ->join(RESELLER_TABLE . ' r', 'ct.reseller_id = r.reseller_id')
            ->where('ct.status', $status)
            ->order_by('ct.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()->result_array();
    }

    /**
     * Get commission by date range
     */
    public function get_by_date_range($start_date, $end_date, $limit = 50, $offset = 0) {
        return $this->db->select('ct.*, r.first_name, r.last_name, r.business_name')
            ->from(COMMISSIONS_TABLE . ' ct')
            ->join(RESELLER_TABLE . ' r', 'ct.reseller_id = r.reseller_id')
            ->where('DATE(ct.created_at) >=', date('Y-m-d', strtotime($start_date)))
            ->where('DATE(ct.created_at) <=', date('Y-m-d', strtotime($end_date)))
            ->order_by('ct.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()->result_array();
    }

    /**
     * Insert commission transaction
     */
    public function insert($data) {
        if ($this->db->insert(COMMISSIONS_TABLE, $data)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    /**
     * Update commission transaction
     */
    public function update($commission_id, $data) {
        return $this->db->update(COMMISSIONS_TABLE, $data, ['commission_id' => $commission_id]);
    }

    /**
     * Get commission count
     */
    public function count_all() {
        return $this->db->from(COMMISSIONS_TABLE)->count_all_results();
    }

    /**
     * Get total commission earned
     */
    public function get_total_earned($reseller_id = NULL) {
        $query = $this->db->select('SUM(amount) as total')->from(COMMISSIONS_TABLE);

        if ($reseller_id) {
            $query->where('reseller_id', $reseller_id);
        }

        $result = $query->get()->row_array();
        return $result['total'] ?? 0;
    }

    /**
     * Get pending commission
     */
    public function get_pending($reseller_id = NULL) {
        $query = $this->db->select('SUM(amount) as total')
            ->from(COMMISSIONS_TABLE)
            ->where('status', 'pending');

        if ($reseller_id) {
            $query->where('reseller_id', $reseller_id);
        }

        $result = $query->get()->row_array();
        return $result['total'] ?? 0;
    }
}
