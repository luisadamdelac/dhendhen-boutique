<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Commission extends Authenticated_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_role('admin');
        $this->load->model(['Commission_model', 'Reseller_model']);
    }

    /**
     * Commission transactions list
     */
    public function index() {
        $data['page_title'] = 'Commission Transactions';
        $data['transactions'] = $this->db->select('ct.*, r.first_name, r.last_name, r.business_name')
            ->from(COMMISSIONS_TABLE . ' ct')
            ->join(RESELLER_TABLE . ' r', 'ct.reseller_id = r.reseller_id')
            ->order_by('ct.created_at', 'DESC')
            ->get()->result_array();

        $data['total'] = count($data['transactions']);
        $data['resellers'] = $this->db->select('*')->from(RESELLER_TABLE)->get()->result_array();
        $data['stats'] = $this->Commission_model->get_statistics();

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/commission/list', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Commission statistics
     */
    public function statistics() {
        $data['overall_stats'] = $this->Commission_model->get_statistics();
        $data['by_status'] = [];

        foreach (['pending', 'released', 'withdrawn'] as $status) {
            $result = $this->db->select('COUNT(*) as count, SUM(amount) as total')
                ->from(COMMISSIONS_TABLE)
                ->where('status', $status)
                ->get()->row_array();
            $data['by_status'][$status] = $result;
        }

        $data['top_earners'] = $this->db->select('r.reseller_id, r.first_name, r.last_name, r.business_name, r.commission_balance, COUNT(ct.commission_id) as total_transactions, SUM(ct.amount) as total_earned')
            ->from(RESELLER_TABLE . ' r')
            ->join(COMMISSIONS_TABLE . ' ct', 'r.reseller_id = ct.reseller_id', 'left')
            ->group_by('r.reseller_id')
            ->order_by('total_earned', 'DESC')
            ->limit(10)
            ->get()->result_array();

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/commission/statistics', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * View transaction details
     */
    public function view($commission_id = '') {
        if (empty($commission_id)) {
            redirect('admin/commission');
        }

        $transaction = $this->Commission_model->get_by_id($commission_id);
        if (!$transaction) {
            $this->session->set_flashdata('error', 'Transaction not found');
            redirect('admin/commission');
        }

        $data['transaction'] = $transaction;
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/commission/view', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Reseller commission summary
     */
    public function reseller_summary($reseller_id = '') {
        if (empty($reseller_id)) {
            redirect('admin/commission');
        }

        $reseller = $this->db->select('*')->from(RESELLER_TABLE)->where('reseller_id', $reseller_id)->get()->row_array();
        if (!$reseller) {
            $this->session->set_flashdata('error', 'Reseller not found');
            redirect('admin/commission');
        }

        $data['reseller'] = $reseller;
        $data['total_earned'] = $this->Commission_model->get_total_earned($reseller_id);
        $data['pending_commission'] = $this->Commission_model->get_pending($reseller_id);
        $data['recent_transactions'] = $this->Commission_model->get_by_reseller($reseller_id, 20, 0);

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/commission/reseller_summary', $data);
        $this->load->view('admin/layout/footer', $data);
    }
}
?>
