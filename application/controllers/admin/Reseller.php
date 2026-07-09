<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Reseller extends Authenticated_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_role('admin');
        $this->load->model(['Reseller_model', 'ResellerApplication_model', 'Activity_log_model']);
        $this->load->library('form_validation');
        require_once APPPATH . 'services/EmailQueueService.php';
    }

    /**
     * Reseller list view
     */
    public function index() {
        $data['page_title'] = 'Resellers';
        $data['current_page'] = 'resellers';

        // The DataTable on the list view now handles search, sorting,
        // pagination, and filtering client-side, so the full reseller set
        // is fetched here in one go rather than one server-paginated slice.
        // u.status is aliased to avoid colliding with (and silently
        // overwriting) r.status — the reseller's own pending/active/rejected
        // state — since r.* and u.status both produce a 'status' key.
        $data['resellers'] = $this->db->select('r.*, u.email, u.status as account_status')
            ->from(RESELLER_TABLE . ' r')
            ->join(USER_ACCOUNT_TABLE . ' u', 'r.user_account_id = u.user_account_id')
            ->order_by('r.reseller_id', 'DESC')
            ->get()->result_array();

        $data['total'] = count($data['resellers']);

        // Only query status column if it actually exists in reseller_tbl
        $fields = $this->db->list_fields(RESELLER_TABLE);
        $data['pending_count'] = in_array('status', $fields)
            ? $this->db->where('status', 'pending')->count_all_results(RESELLER_TABLE)
            : 0;

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/reseller/list', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Reseller applications
     */
    public function applications() {
        $data['page_title'] = 'Reseller Upgrade Requests';
        $data['current_page'] = 'resellers';
        $page = $this->input->get('page') ?? 1;
        $status = $this->input->get('status') ?? 'pending';
        $limit = PAGINATION_LIMIT;
        $offset = ($page - 1) * $limit;

        $this->load->model('settings_model');
        $minimum_spend = (float) $this->settings_model->get_minimum_spend();

        $applications = $this->ResellerApplication_model->get_by_status($status, $limit, $offset);
        $this->_attach_eligibility_stats($applications, $minimum_spend);

        $data['applications'] = $applications;
        $data['minimum_spend'] = $minimum_spend;
        $data['total'] = $this->ResellerApplication_model->count_by_status($status);
        $data['status'] = $status;
        $data['page'] = $page;
        $data['pages'] = ceil($data['total'] / $limit);

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/reseller/applications', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Review a single application in full detail before approving/rejecting.
     */
    public function view_application($application_id = '') {
        $data['page_title'] = 'Review Upgrade Request';
        $data['current_page'] = 'resellers';
        if (empty($application_id)) {
            redirect('admin/reseller/applications');
        }

        $application = $this->ResellerApplication_model->get_by_id($application_id);
        if (!$application) {
            $this->session->set_flashdata('error', 'Application not found');
            redirect('admin/reseller/applications');
        }

        $this->load->model('settings_model');
        $minimum_spend = (float) $this->settings_model->get_minimum_spend();
        $applications = [$application];
        $this->_attach_eligibility_stats($applications, $minimum_spend);

        $data['application'] = $applications[0];
        $data['minimum_spend'] = $minimum_spend;

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/reseller/view_application', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Attach each application's qualified spend, completed order count, and
     * last delivered order date — computed from delivered orders only,
     * matching the same eligibility rule used at
     * customer/Account::apply_reseller().
     */
    private function _attach_eligibility_stats(array &$applications, float $minimum_spend): void {
        if (empty($applications)) {
            return;
        }
        $customer_ids = array_unique(array_column($applications, 'customer_id'));

        $stats = $this->db
            ->select('customer_id,
                SUM(total_amount) as qualified_spend,
                COUNT(*) as completed_orders,
                MAX(created_at) as last_delivered_at', FALSE)
            ->from(ORDER_TABLE)
            ->where('order_status', 'delivered')
            ->where_in('customer_id', $customer_ids)
            ->group_by('customer_id')
            ->get()->result_array();

        $by_customer = [];
        foreach ($stats as $row) {
            $by_customer[$row['customer_id']] = $row;
        }

        foreach ($applications as &$app) {
            $row = $by_customer[$app['customer_id']] ?? ['qualified_spend' => 0, 'completed_orders' => 0, 'last_delivered_at' => NULL];
            $app['qualified_spend'] = (float) $row['qualified_spend'];
            $app['completed_orders'] = (int) $row['completed_orders'];
            $app['last_delivered_at'] = $row['last_delivered_at'];
            $app['is_eligible'] = $app['qualified_spend'] >= $minimum_spend;
        }
        unset($app);
    }

    /**
     * View reseller profile
     */
    public function view($reseller_id = '') {
        $data['page_title'] = 'Reseller Profile';
        $data['current_page'] = 'resellers';
        if (empty($reseller_id)) {
            redirect('admin/reseller');
        }

        // u.status is aliased to avoid colliding with (and silently
        // overwriting) r.status — see the same fix in index().
        $reseller = $this->db->select('r.*, u.email, u.status as account_status, u.created_at')
            ->from(RESELLER_TABLE . ' r')
            ->join(USER_ACCOUNT_TABLE . ' u', 'r.user_account_id = u.user_account_id')
            ->where('r.reseller_id', $reseller_id)
            ->get()->row_array();

        if (!$reseller) {
            $this->session->set_flashdata('error', 'Reseller not found');
            redirect('admin/reseller');
        }

        $data['reseller'] = $reseller;
        $data['orders'] = $this->db->select('*')
            ->from(ORDER_TABLE)
            ->where('reseller_id', $reseller_id)
            ->order_by('order_id', 'DESC')
            ->limit(10)->get()->result_array();

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/reseller/view', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Edit reseller
     */
    public function edit($reseller_id = '') {
        $data['page_title'] = 'Edit Reseller';
        $data['current_page'] = 'resellers';
        if (empty($reseller_id)) {
            redirect('admin/reseller');
        }

        $reseller = $this->db->select('r.*, u.email, u.status')
            ->from(RESELLER_TABLE . ' r')
            ->join(USER_ACCOUNT_TABLE . ' u', 'r.user_account_id = u.user_account_id')
            ->where('r.reseller_id', $reseller_id)
            ->get()->row_array();

        if (!$reseller) {
            $this->session->set_flashdata('error', 'Reseller not found');
            redirect('admin/reseller');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
            $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
            $this->form_validation->set_rules('contact_number', 'Contact Number', 'required|trim|max_length[11]');
            $this->form_validation->set_rules('city', 'City', 'required|trim');
            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors(' ', ' '));
                redirect("admin/reseller/edit/{$reseller_id}");
            }

            $update_data = [
                'first_name' => $this->input->post('first_name'),
                'last_name' => $this->input->post('last_name'),
                'contact_number' => $this->input->post('contact_number'),
                'street' => $this->input->post('street') ?? '',
                'barangay' => $this->input->post('barangay') ?? '',
                'city' => $this->input->post('city'),
                'business_name' => $this->input->post('business_name') ?? '',
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $this->db->update(RESELLER_TABLE, $update_data, ['reseller_id' => $reseller_id]);
            $this->Activity_log_model->log_activity(get_user_id(), 'admin', 'update_reseller', 'Updated reseller: ' . $reseller_id, get_client_ip());
            $this->session->set_flashdata('success', 'Reseller updated successfully');
            redirect("admin/reseller/view/{$reseller_id}");
        }

        $data['reseller'] = $reseller;
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/reseller/edit', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Approve application
     */
    public function approve_application($application_id = '') {
        if (empty($application_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $application = $this->ResellerApplication_model->get_by_id($application_id);
        if (!$application) {
            echo json_encode(['success' => FALSE, 'message' => 'Application not found']);
            return;
        }

        // reseller_tbl.user_account_id has no unique constraint, and a
        // customer's old session stays valid even after they're approved
        // (role isolation doesn't re-check the DB mid-session) — so without
        // this guard, re-submitting + re-approving would silently create a
        // second reseller_tbl row for the same account.
        $already_reseller = $this->db->where('user_account_id', $application['user_account_id'])
            ->get(RESELLER_TABLE)->row_array();
        if ($already_reseller) {
            echo json_encode(['success' => FALSE, 'message' => 'This account is already a reseller.']);
            return;
        }

        $this->db->trans_start();

        // Create reseller account, seeded from the applicant's customer profile
        $reseller_data = [
            'user_account_id' => $application['user_account_id'],
            'first_name' => $application['first_name'],
            'last_name' => $application['last_name'],
            'contact_number' => $application['contact_number'],
            'street' => $application['business_street'],
            'barangay' => $application['business_barangay'],
            'city' => $application['business_city'],
            'business_name' => $application['business_name'],
            'commission_balance' => 0,
            'total_commission_earned' => 0,
            'total_withdrawn' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert(RESELLER_TABLE, $reseller_data);

        // Mark application as approved
        $this->ResellerApplication_model->approve($application_id, $this->user_id, $this->input->post('admin_remarks') ?? '');

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => FALSE, 'message' => 'Failed to approve application']);
        } else {
            $this->Activity_log_model->log_activity(get_user_id(), 'admin', 'approve_reseller_app', 'Approved reseller application: ' . $application_id, get_client_ip());
            EmailQueueService::queueResellerApplicationApproved(
                $application['email'],
                trim($application['first_name'] . ' ' . $application['last_name']),
                $application['business_name']
            );
            echo json_encode(['success' => TRUE, 'message' => 'Reseller application approved successfully']);
        }
    }

    /**
     * Reject application
     */
    public function reject_application($application_id = '') {
        if (empty($application_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $application = $this->ResellerApplication_model->get_by_id($application_id);
        if (!$application) {
            echo json_encode(['success' => FALSE, 'message' => 'Application not found']);
            return;
        }

        $admin_remarks = $this->input->post('admin_remarks') ?? '';
        $this->ResellerApplication_model->reject($application_id, $this->user_id, $admin_remarks);
        $this->Activity_log_model->log_activity(get_user_id(), 'admin', 'reject_reseller_app', 'Rejected reseller application: ' . $application_id, get_client_ip());

        EmailQueueService::queueResellerApplicationRejected(
            $application['email'],
            trim($application['first_name'] . ' ' . $application['last_name']),
            $admin_remarks
        );

        echo json_encode(['success' => TRUE, 'message' => 'Reseller application rejected successfully']);
    }

    /**
     * Pending direct reseller registrations
     */
    public function pending_registrations() {
        $data['page_title']    = 'Pending Reseller Registrations';
        $data['current_page']  = 'resellers';
        $status = $this->input->get('status') ?? 'pending';
        $data['status'] = $status;

        $data['pending'] = $this->db
            ->select('r.*, u.email, u.created_at as registered_at')
            ->from(RESELLER_TABLE . ' r')
            ->join(USER_ACCOUNT_TABLE . ' u', 'r.user_account_id = u.user_account_id')
            ->where('r.status', $status)
            ->order_by('r.reseller_id', 'DESC')
            ->get()->result_array();

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/reseller/pending_registrations', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Approve a directly registered reseller
     */
    public function approve_registration($reseller_id = '') {
        if (empty($reseller_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $reseller = $this->db->select('r.*, u.email')
            ->from(RESELLER_TABLE . ' r')
            ->join(USER_ACCOUNT_TABLE . ' u', 'r.user_account_id = u.user_account_id')
            ->where('r.reseller_id', $reseller_id)
            ->get()->row_array();
        // Approving from 'rejected' is allowed (re-approval); only an
        // already-active reseller is blocked.
        if (!$reseller || $reseller['status'] === 'active') {
            echo json_encode(['success' => FALSE, 'message' => 'Registration not found or already active']);
            return;
        }

        $this->db->update(RESELLER_TABLE, ['status' => 'active', 'admin_remarks' => NULL, 'updated_at' => date('Y-m-d H:i:s')], ['reseller_id' => $reseller_id]);
        $this->Activity_log_model->log_activity(get_user_id(), 'admin', 'approve_reseller_registration', 'Approved reseller registration: ' . $reseller_id, get_client_ip());

        EmailQueueService::queueResellerApplicationApproved(
            $reseller['email'],
            trim($reseller['first_name'] . ' ' . $reseller['last_name']),
            $reseller['business_name']
        );

        echo json_encode(['success' => TRUE, 'message' => 'Reseller registration approved successfully']);
    }

    /**
     * Reject a directly registered reseller
     */
    public function reject_registration($reseller_id = '') {
        if (empty($reseller_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $reseller = $this->db->select('r.*, u.email')
            ->from(RESELLER_TABLE . ' r')
            ->join(USER_ACCOUNT_TABLE . ' u', 'r.user_account_id = u.user_account_id')
            ->where('r.reseller_id', $reseller_id)
            ->get()->row_array();
        if (!$reseller || $reseller['status'] !== 'pending') {
            echo json_encode(['success' => FALSE, 'message' => 'Registration not found or already processed']);
            return;
        }

        $admin_remarks = $this->input->post('admin_remarks') ?? '';
        $this->db->update(RESELLER_TABLE, ['status' => 'rejected', 'admin_remarks' => $admin_remarks, 'updated_at' => date('Y-m-d H:i:s')], ['reseller_id' => $reseller_id]);
        $this->Activity_log_model->log_activity(get_user_id(), 'admin', 'reject_reseller_registration', 'Rejected reseller registration: ' . $reseller_id, get_client_ip());

        EmailQueueService::queueResellerApplicationRejected(
            $reseller['email'],
            trim($reseller['first_name'] . ' ' . $reseller['last_name']),
            $admin_remarks
        );

        echo json_encode(['success' => TRUE, 'message' => 'Reseller registration rejected']);
    }

    /**
     * Suspend reseller
     */
    public function suspend($reseller_id = '') {
        if (empty($reseller_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $reseller = $this->db->select('*')->from(RESELLER_TABLE)->where('reseller_id', $reseller_id)->get()->row_array();
        if (!$reseller) {
            echo json_encode(['success' => FALSE, 'message' => 'Reseller not found']);
            return;
        }

        $this->db->update(USER_ACCOUNT_TABLE, ['status' => 'inactive'], ['user_account_id' => $reseller['user_account_id']]);
        $this->Activity_log_model->log_activity(get_user_id(), 'admin', 'suspend_reseller', 'Suspended reseller: ' . $reseller_id, get_client_ip());
        echo json_encode(['success' => TRUE, 'message' => 'Reseller suspended successfully']);
    }

    /**
     * Activate reseller
     */
    public function activate($reseller_id = '') {
        if (empty($reseller_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $reseller = $this->db->select('*')->from(RESELLER_TABLE)->where('reseller_id', $reseller_id)->get()->row_array();
        if (!$reseller) {
            echo json_encode(['success' => FALSE, 'message' => 'Reseller not found']);
            return;
        }

        $this->db->update(USER_ACCOUNT_TABLE, ['status' => 'active'], ['user_account_id' => $reseller['user_account_id']]);
        $this->Activity_log_model->log_activity(get_user_id(), 'admin', 'activate_reseller', 'Activated reseller: ' . $reseller_id, get_client_ip());
        echo json_encode(['success' => TRUE, 'message' => 'Reseller activated successfully']);
    }
}
?>
