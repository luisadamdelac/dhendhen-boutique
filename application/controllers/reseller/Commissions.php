<?php
class Commissions extends Authenticated_Controller {
    public function __construct() {
        parent::__construct();
        $this->require_role(ROLE_RESELLER);
        $this->load->model(['commission_model', 'activity_log_model']);
    }

    public function index() {
        $data = $this->set_view_data();
        $data['page_title'] = 'Reseller Commissions';
        $data['commissions'] = $this->db->select('*')->from(COMMISSIONS_TABLE)->where('reseller_id', $this->user_id)->order_by('commission_id', 'DESC')->get()->result_array();
        $data['wallet'] = [
            'pending_commission'  => $this->db->select('SUM(amount) as total')->from(COMMISSIONS_TABLE)->where('reseller_id', $this->user_id)->where('status', 'pending')->get()->row()->total ?: 0,
            'approved_commission' => $this->db->select('SUM(amount) as total')->from(COMMISSIONS_TABLE)->where('reseller_id', $this->user_id)->where('status', 'released')->get()->row()->total ?: 0,
            'paid_commission'     => $this->db->select('SUM(amount) as total')->from(COMMISSIONS_TABLE)->where('reseller_id', $this->user_id)->where('status', 'withdrawn')->get()->row()->total ?: 0,
            'pending_refunds'     => $this->db->from(REFUND_REQUEST_TABLE)->where('reseller_id', $this->user_id)->where('status', 'pending')->count_all_results(),
        ];
        $this->load->view('reseller/layouts/header', $data);
        $this->load->view('reseller/commissions/index', $data);
        $this->load->view('reseller/layouts/footer', $data);
    }
}
