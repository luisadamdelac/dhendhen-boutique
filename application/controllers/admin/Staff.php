<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Staff extends Authenticated_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_role('admin');
        $this->load->model(['Staff_model', 'Activity_log_model']);
        $this->load->library('form_validation');
    }

    /**
     * Staff list view
     */
    public function index() {
        $data['page_title'] = 'Staff';
        $data['current_page'] = 'staff';

        $data['staffs'] = $this->db
            ->select('st.*, u.email, u.status')
            ->from(STAFF_TABLE . ' st')
            ->join(USER_ACCOUNT_TABLE . ' u', 'st.user_account_id = u.user_account_id')
            ->order_by('st.staff_id', 'DESC')
            ->get()->result_array();

        $data['total'] = count($data['staffs']);

        $data['stat_total_staff']  = $this->db->count_all(STAFF_TABLE);
        $data['stat_active_staff'] = $this->db
            ->from(STAFF_TABLE . ' st')
            ->join(USER_ACCOUNT_TABLE . ' u', 'st.user_account_id = u.user_account_id')
            ->where('u.status', 'active')
            ->count_all_results();
        $data['stat_inactive_staff'] = $data['stat_total_staff'] - $data['stat_active_staff'];

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/staff/list', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Add staff page
     */
    public function add() {
        $data['page_title'] = 'Add Staff';
        $data['current_page'] = 'staff';
        if ($this->input->method() === 'post') {
            $this->_validate_and_save_staff();
            return;
        }

        $data['branches'] = $this->db->select('*')->from(BRANCHES_TABLE)->get()->result_array();
        $data['old_input'] = $this->session->flashdata('old_input') ?: [];
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/staff/add', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Edit staff page
     */
    public function edit($staff_id = '') {
        $data['page_title'] = 'Edit Staff';
        $data['current_page'] = 'staff';
        if (empty($staff_id)) {
            redirect('admin/staff');
        }

        $staff = $this->db->select('st.*, u.email')
            ->from(STAFF_TABLE . ' st')
            ->join(USER_ACCOUNT_TABLE . ' u', 'st.user_account_id = u.user_account_id')
            ->where('st.staff_id', $staff_id)
            ->get()->row_array();

        if (!$staff) {
            $this->session->set_flashdata('error', 'Staff not found');
            redirect('admin/staff');
        }

        if ($this->input->method() === 'post') {
            $this->_validate_and_save_staff($staff_id);
            return;
        }

        // On a failed validation bounce-back, _validate_and_save_staff() flashes
        // the submitted values as 'old_input' — merge them over the DB row so
        // the admin's in-progress edits aren't wiped by this fresh reload.
        $old_input = $this->session->flashdata('old_input');
        $data['staff'] = $old_input ? array_merge($staff, $old_input) : $staff;
        $data['branches'] = $this->db->select('*')->from(BRANCHES_TABLE)->get()->result_array();
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/staff/edit', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * View staff profile
     */
    public function view($staff_id = '') {
        $data['page_title'] = 'Staff Details';
        $data['current_page'] = 'staff';
        if (empty($staff_id)) {
            redirect('admin/staff');
        }

        $staff = $this->db->select('st.*, u.email, u.status, u.created_at')
            ->from(STAFF_TABLE . ' st')
            ->join(USER_ACCOUNT_TABLE . ' u', 'st.user_account_id = u.user_account_id')
            ->where('st.staff_id', $staff_id)
            ->get()->row_array();

        if (!$staff) {
            $this->session->set_flashdata('error', 'Staff not found');
            redirect('admin/staff');
        }

        $data['staff'] = $staff;
        $data['activity'] = $this->Activity_log_model->get_user_logs('staff', $staff_id, 10);

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/staff/view', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Admin-managed profile photo — mirrors staff/Profile::update_photo(),
     * but scoped to whichever staff_id admin is looking at rather than
     * $this->user_id, so admin can set a staff member's photo on their
     * behalf (e.g. before that staff has ever logged in to upload one).
     */
    public function upload_photo($staff_id = '') {
        if (empty($staff_id) || $this->input->method() !== 'post' || empty($_FILES['photo']['name'])) {
            redirect('admin/staff/view/' . $staff_id);
        }

        $staff = $this->db->where('staff_id', $staff_id)->get(STAFF_TABLE)->row_array();
        if (!$staff) {
            show_404();
        }

        $upload_dir = FCPATH . 'public/uploads/avatars/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, TRUE);
        }

        $this->load->library('upload', [
            'upload_path' => $upload_dir,
            'allowed_types' => 'jpg|jpeg|png|gif|webp',
            'max_size' => 2048,
            'encrypt_name' => TRUE,
        ]);

        if ($this->upload->do_upload('photo')) {
            $path = 'public/uploads/avatars/' . $this->upload->data('file_name');
            $this->db->where('staff_id', $staff_id)->update(STAFF_TABLE, [
                'profile_image' => $path,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $this->session->set_flashdata('success', 'Staff photo updated');
        } else {
            $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
        }

        redirect('admin/staff/view/' . $staff_id);
    }

    public function remove_photo($staff_id = '') {
        if (empty($staff_id) || $this->input->method() !== 'post') {
            show_404();
        }
        $this->db->where('staff_id', $staff_id)->update(STAFF_TABLE, ['profile_image' => NULL]);
        $this->session->set_flashdata('success', 'Staff photo removed');
        redirect('admin/staff/view/' . $staff_id);
    }

    /**
     * Delete staff
     */
    public function delete($staff_id = '') {
        if (empty($staff_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $staff = $this->db->select('*')->from(STAFF_TABLE)->where('staff_id', $staff_id)->get()->row_array();
        if (!$staff) {
            echo json_encode(['success' => FALSE, 'message' => 'Staff not found']);
            return;
        }

        // Delete user account and staff record
        $this->db->trans_start();
        $this->db->delete(STAFF_TABLE, ['staff_id' => $staff_id]);
        $this->db->delete(USER_ACCOUNT_TABLE, ['user_account_id' => $staff['user_account_id']]);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => FALSE, 'message' => 'Failed to delete staff']);
        } else {
            $this->Activity_log_model->log_activity(
                get_user_id(),
                'admin',
                'delete_staff',
                'Deleted staff: ' . trim($staff['first_name'] . ' ' . $staff['last_name']),
                get_client_ip()
            );
            echo json_encode(['success' => TRUE, 'message' => 'Staff deleted successfully']);
        }
    }

    /**
     * Activate / deactivate staff login access
     */
    public function activate($staff_id = '') {
        return $this->_set_staff_status($staff_id, 'active');
    }

    public function deactivate($staff_id = '') {
        return $this->_set_staff_status($staff_id, 'inactive');
    }

    private function _set_staff_status($staff_id, $status) {
        if (empty($staff_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $staff = $this->db->select('*')->from(STAFF_TABLE)->where('staff_id', $staff_id)->get()->row_array();
        if (!$staff) {
            echo json_encode(['success' => FALSE, 'message' => 'Staff not found']);
            return;
        }

        $this->db->where('user_account_id', $staff['user_account_id'])->update(USER_ACCOUNT_TABLE, ['status' => $status]);
        $this->Activity_log_model->log_activity(get_user_id(), 'admin', $status . '_staff', 'Set staff ' . $staff_id . ' to ' . $status, get_client_ip());
        echo json_encode(['success' => TRUE, 'message' => 'Staff ' . ($status === 'active' ? 'activated' : 'deactivated') . ' successfully']);
    }

    /**
     * Validate and save staff data
     */
    private function _validate_and_save_staff($staff_id = NULL) {
        $this->load->helper('address');
        $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
        $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
        $this->form_validation->set_rules('middle_name', 'Middle Name', 'trim');
        $this->form_validation->set_rules('contact_number', 'Contact Number', 'required|trim|max_length[11]');
        $this->form_validation->set_rules('street', 'Street', 'required|trim');
        $this->form_validation->set_rules('city', 'City', 'trim');
        $this->form_validation->set_rules('barangay', 'Barangay', 'trim');
        $this->form_validation->set_rules('branch_id', 'Assigned Branch', 'required|trim');
        if (!$staff_id) {
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim|is_unique[user_account_tbl.email]');
            $this->form_validation->set_rules('password', 'Password', 'trim');
        }

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            $this->session->set_flashdata('old_input', $this->input->post(NULL, TRUE));
            if ($staff_id) {
                redirect("admin/staff/edit/{$staff_id}");
            } else {
                redirect('admin/staff/add');
            }
        }

        $city = $this->input->post('city', TRUE);
        $barangay = $this->input->post('barangay', TRUE);

        // City/Barangay are optional, but if either is set they must be a
        // real Oriental Mindoro City/Municipality + Barangay combination.
        if (($city || $barangay) && !is_valid_oriental_mindoro_address($city, $barangay)) {
            $this->session->set_flashdata('error', 'Please select a valid City/Municipality and Barangay within Oriental Mindoro.');
            $this->session->set_flashdata('old_input', $this->input->post(NULL, TRUE));
            redirect($staff_id ? "admin/staff/edit/{$staff_id}" : 'admin/staff/add');
        }

        $staff_data = [
            'first_name' => $this->input->post('first_name', TRUE),
            'last_name' => $this->input->post('last_name', TRUE),
            'middle_name' => $this->input->post('middle_name', TRUE) ?: NULL,
            'contact_number' => $this->input->post('contact_number', TRUE),
            'street' => $this->input->post('street', TRUE),
            'city' => $city ?: NULL,
            'barangay' => $barangay ?: NULL,
            'branch_id' => $this->input->post('branch_id', TRUE) ?: NULL,
        ];

        if ($staff_id) {
            $staff_data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->update(STAFF_TABLE, $staff_data, ['staff_id' => $staff_id]);
            $this->session->set_flashdata('success', 'Staff updated successfully');
        } else {
            $password = $this->input->post('password', TRUE);
            if (empty($password)) {
                $password = $staff_data['last_name'] . '123';
            }

            $user_data = [
                'email' => $this->input->post('email', TRUE),
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'status' => 'active',
                'is_verified' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $this->db->insert(USER_ACCOUNT_TABLE, $user_data);
            $user_account_id = $this->db->insert_id();

            $staff_data['user_account_id'] = $user_account_id;
            $staff_data['created_at'] = date('Y-m-d H:i:s');
            $staff_data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->insert(STAFF_TABLE, $staff_data);

            $this->Activity_log_model->log_activity(
                get_user_id(),
                'admin',
                'create_staff',
                'Created staff: ' . $staff_data['first_name'] . ' ' . $staff_data['last_name'],
                get_client_ip()
            );

            $this->session->set_flashdata('success', 'Staff created successfully.');
        }

        redirect('admin/staff');
    }
}
?>
