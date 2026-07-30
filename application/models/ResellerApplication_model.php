<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ResellerApplication_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    private function base_query() {
        return $this->db->select('ra.*, c.first_name, c.last_name, c.contact_number, c.profile_image, c.user_account_id, u.email')
            ->from(RESELLER_APPLICATION_TABLE . ' ra')
            ->join(CUSTOMER_TABLE . ' c', 'ra.customer_id = c.customer_id')
            ->join(USER_ACCOUNT_TABLE . ' u', 'c.user_account_id = u.user_account_id');
    }

    /**
     * Get all applications with customer details
     */
    public function get_all($limit = 50, $offset = 0) {
        return $this->base_query()
            ->order_by('ra.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()->result_array();
    }

    /**
     * Get application by ID
     */
    public function get_by_id($application_id) {
        return $this->base_query()
            ->where('ra.application_id', $application_id)
            ->get()->row_array();
    }

    /**
     * Get pending applications
     */
    public function get_pending($limit = 50, $offset = 0) {
        return $this->base_query()
            ->where('ra.status', 'pending')
            ->order_by('ra.created_at', 'ASC')
            ->limit($limit, $offset)
            ->get()->result_array();
    }

    /**
     * Get pending count
     */
    public function get_pending_count() {
        return $this->db->from(RESELLER_APPLICATION_TABLE)
            ->where('status', 'pending')
            ->count_all_results();
    }

    /**
     * Get applications by status
     */
    public function get_by_status($status, $limit = 50, $offset = 0) {
        return $this->base_query()
            ->where('ra.status', $status)
            ->order_by('ra.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()->result_array();
    }

    /**
     * Insert application
     */
    public function insert($data) {
        if ($this->db->insert(RESELLER_APPLICATION_TABLE, $data)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    /**
     * Update application
     */
    public function update($application_id, $data) {
        return $this->db->update(RESELLER_APPLICATION_TABLE, $data, ['application_id' => $application_id]);
    }

    /**
     * Approve application
     */
    public function approve($application_id, $admin_id, $admin_remarks = '') {
        return $this->update($application_id, [
            'status' => 'approved',
            'admin_remarks' => $admin_remarks,
            'reviewed_by' => $admin_id,
            'admin_id' => $admin_id,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Reject application
     */
    public function reject($application_id, $admin_id, $admin_remarks = '') {
        return $this->update($application_id, [
            'status' => 'rejected',
            'admin_remarks' => $admin_remarks,
            'reviewed_by' => $admin_id,
            'admin_id' => $admin_id,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get count by status
     */
    public function count_by_status($status) {
        return $this->db->from(RESELLER_APPLICATION_TABLE)
            ->where('status', $status)
            ->count_all_results();
    }
}
