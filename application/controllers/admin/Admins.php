<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admins extends Authenticated_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_role('admin');
        $this->load->model(['Admin_model', 'Activity_log_model']);
        $this->load->library('form_validation');
    }

    /**
     * Admin list view
     */
    public function index() {
        $data['page_title'] = 'Admins';
        $data['current_page'] = 'admins';
        $page = $this->input->get('page') ?? 1;
        $search = $this->input->get('search') ?? '';
        $limit = PAGINATION_LIMIT;
        $offset = ($page - 1) * $limit;

        if (!empty($search)) {
            $data['admins'] = $this->db
                ->select('a.*, u.email, u.status')
                ->from(ADMIN_TABLE . ' a')
                ->join(USER_ACCOUNT_TABLE . ' u', 'a.user_account_id = u.user_account_id')
                ->group_start()
                    ->or_like('u.email', $search, 'both')
                    ->or_like('a.first_name', $search, 'both')
                    ->or_like('a.last_name', $search, 'both')
                ->group_end()
                ->order_by('a.admin_id', 'DESC')
                ->limit($limit, $offset)
                ->get()->result_array();
        } else {
            $data['admins'] = $this->db
                ->select('a.*, u.email, u.status')
                ->from(ADMIN_TABLE . ' a')
                ->join(USER_ACCOUNT_TABLE . ' u', 'a.user_account_id = u.user_account_id')
                ->order_by('a.admin_id', 'DESC')
                ->limit($limit, $offset)
                ->get()->result_array();
        }

        if (!empty($search)) {
            $total_query = $this->db
                ->select('COUNT(*) as count')
                ->from(ADMIN_TABLE . ' a')
                ->join(USER_ACCOUNT_TABLE . ' u', 'a.user_account_id = u.user_account_id')
                ->group_start()
                    ->or_like('u.email', $search, 'both')
                    ->or_like('a.first_name', $search, 'both')
                    ->or_like('a.last_name', $search, 'both')
                ->group_end()
                ->get();
            $data['total'] = $total_query->row()->count;
        } else {
            $data['total'] = $this->db
                ->select('COUNT(*) as count')
                ->from(ADMIN_TABLE . ' a')
                ->join(USER_ACCOUNT_TABLE . ' u', 'a.user_account_id = u.user_account_id')
                ->get()->row()->count;
        }

        $data['search'] = $search;
        $data['page'] = $page;
        $data['pages'] = ceil($data['total'] / $limit);

        $data['stat_total_admins']  = $this->db->count_all(ADMIN_TABLE);
        $data['stat_active_admins'] = $this->db
            ->from(ADMIN_TABLE . ' a')
            ->join(USER_ACCOUNT_TABLE . ' u', 'a.user_account_id = u.user_account_id')
            ->where('u.status', 'active')
            ->count_all_results();
        $data['stat_inactive_admins'] = $data['stat_total_admins'] - $data['stat_active_admins'];

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/admins/list', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Add admin page
     */
    public function add() {
        $data['page_title'] = 'Add Admin';
        $data['current_page'] = 'admins';
        if ($this->input->method() === 'post') {
            $this->_validate_and_save_admin();
            return;
        }

        $data['branches'] = $this->db->select('*')->from(BRANCHES_TABLE)->get()->result_array();
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/admins/add', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Edit admin page
     */
    public function edit($admin_id = '') {
        $data['page_title'] = 'Edit Admin';
        $data['current_page'] = 'admins';
        if (empty($admin_id)) {
            redirect('admin/admins');
        }

        $admin = $this->db->select('a.*, u.email')
            ->from(ADMIN_TABLE . ' a')
            ->join(USER_ACCOUNT_TABLE . ' u', 'a.user_account_id = u.user_account_id')
            ->where('a.admin_id', $admin_id)
            ->get()->row_array();

        if (!$admin) {
            $this->session->set_flashdata('error', 'Admin not found');
            redirect('admin/admins');
        }

        if ($this->input->method() === 'post') {
            $this->_validate_and_save_admin($admin_id);
            return;
        }

        $data['admin'] = $admin;
        $data['branches'] = $this->db->select('*')->from(BRANCHES_TABLE)->get()->result_array();
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/admins/edit', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * View admin profile
     */
    public function view($admin_id = '') {
        $data['page_title'] = 'Admin Details';
        $data['current_page'] = 'admins';
        if (empty($admin_id)) {
            redirect('admin/admins');
        }

        $admin = $this->db->select('a.*, u.email, u.status, u.created_at')
            ->from(ADMIN_TABLE . ' a')
            ->join(USER_ACCOUNT_TABLE . ' u', 'a.user_account_id = u.user_account_id')
            ->where('a.admin_id', $admin_id)
            ->get()->row_array();

        if (!$admin) {
            $this->session->set_flashdata('error', 'Admin not found');
            redirect('admin/admins');
        }

        $data['admin'] = $admin;
        $data['activity'] = $this->Activity_log_model->get_user_logs('admin', $admin_id, 10);

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/admins/view', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Delete admin
     */
    public function delete($admin_id = '') {
        if (empty($admin_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $admin = $this->db->select('*')->from(ADMIN_TABLE)->where('admin_id', $admin_id)->get()->row_array();
        if (!$admin) {
            echo json_encode(['success' => FALSE, 'message' => 'Admin not found']);
            return;
        }

        if ((int) $admin['user_account_id'] === (int) $this->user_account_id) {
            echo json_encode(['success' => FALSE, 'message' => 'You cannot delete your own account']);
            return;
        }

        $this->db->trans_start();
        $this->db->delete(ADMIN_TABLE, ['admin_id' => $admin_id]);
        $this->db->delete(USER_ACCOUNT_TABLE, ['user_account_id' => $admin['user_account_id']]);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => FALSE, 'message' => 'Failed to delete admin']);
        } else {
            $this->Activity_log_model->log_activity(
                $this->user_id,
                'admin',
                'delete_admin',
                'Deleted admin: ' . trim($admin['first_name'] . ' ' . $admin['last_name']),
                $this->input->ip_address()
            );
            echo json_encode(['success' => TRUE, 'message' => 'Admin deleted successfully']);
        }
    }

    /**
     * Activate / deactivate admin login access
     */
    public function activate($admin_id = '') {
        return $this->_set_admin_status($admin_id, 'active');
    }

    public function deactivate($admin_id = '') {
        return $this->_set_admin_status($admin_id, 'inactive');
    }

    private function _set_admin_status($admin_id, $status) {
        if (empty($admin_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $admin = $this->db->select('*')->from(ADMIN_TABLE)->where('admin_id', $admin_id)->get()->row_array();
        if (!$admin) {
            echo json_encode(['success' => FALSE, 'message' => 'Admin not found']);
            return;
        }

        if ($status === 'inactive' && (int) $admin['user_account_id'] === (int) $this->user_account_id) {
            echo json_encode(['success' => FALSE, 'message' => 'You cannot deactivate your own account']);
            return;
        }

        $this->db->where('user_account_id', $admin['user_account_id'])->update(USER_ACCOUNT_TABLE, ['status' => $status]);
        $this->Activity_log_model->log_activity($this->user_id, 'admin', $status . '_admin', 'Set admin ' . $admin_id . ' to ' . $status, $this->input->ip_address());
        echo json_encode(['success' => TRUE, 'message' => 'Admin ' . ($status === 'active' ? 'activated' : 'deactivated') . ' successfully']);
    }

    /**
     * Validate and save admin data
     */
    private function _validate_and_save_admin($admin_id = NULL) {
        $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
        $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
        $this->form_validation->set_rules('middle_name', 'Middle Name', 'trim');
        $this->form_validation->set_rules('contact_number', 'Contact Number', 'required|trim|max_length[11]');
        $this->form_validation->set_rules('street', 'Street', 'trim');
        $this->form_validation->set_rules('barangay', 'Barangay', 'trim');
        $this->form_validation->set_rules('city', 'City', 'required|trim');
        $this->form_validation->set_rules('municipality', 'Municipality', 'trim');
        if (!$admin_id) {
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim|is_unique[user_account_tbl.email]');
        }

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            if ($admin_id) {
                redirect("admin/admins/edit/{$admin_id}");
            } else {
                redirect('admin/admins/add');
            }
        }

        $admin_data = [
            'first_name' => $this->input->post('first_name', TRUE),
            'last_name' => $this->input->post('last_name', TRUE),
            'middle_name' => $this->input->post('middle_name', TRUE) ?: NULL,
            'contact_number' => $this->input->post('contact_number', TRUE),
            'street' => $this->input->post('street', TRUE),
            'barangay' => $this->input->post('barangay', TRUE),
            'city' => $this->input->post('city', TRUE),
            'municipality' => $this->input->post('municipality', TRUE) ?: NULL,
            'branch_id' => $this->input->post('branch_id', TRUE) ?: NULL,
        ];

        if ($admin_id) {
            $admin_data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->update(ADMIN_TABLE, $admin_data, ['admin_id' => $admin_id]);
            $this->session->set_flashdata('success', 'Admin updated successfully');
        } else {
            $user_data = [
                'email' => $this->input->post('email', TRUE),
                'password' => password_hash('password123', PASSWORD_BCRYPT),
                'status' => 'active',
                'is_verified' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $this->db->insert(USER_ACCOUNT_TABLE, $user_data);
            $user_account_id = $this->db->insert_id();

            $admin_data['user_account_id'] = $user_account_id;
            $admin_data['created_at'] = date('Y-m-d H:i:s');
            $admin_data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->insert(ADMIN_TABLE, $admin_data);

            $this->Activity_log_model->log_activity(
                $this->user_id,
                'admin',
                'create_admin',
                'Created admin: ' . $admin_data['first_name'] . ' ' . $admin_data['last_name'],
                $this->input->ip_address()
            );

            $this->session->set_flashdata('success', 'Admin created successfully with default password: password123');
        }

        redirect('admin/admins');
    }
}
