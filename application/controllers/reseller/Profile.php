<?php
class Profile extends Authenticated_Controller {
    public function __construct() {
        parent::__construct();
        $this->require_role(ROLE_RESELLER);
        $this->load->model(['reseller_model']);
    }

    public function index() {
        $data = $this->set_view_data();
        $data['page_title'] = 'Reseller Profile';

        $reseller = $this->db->select('r.*, u.email')
            ->from(RESELLER_TABLE . ' r')
            ->join(USER_ACCOUNT_TABLE . ' u', 'r.user_account_id = u.user_account_id')
            ->where('r.reseller_id', $this->user_id)
            ->get()->row_array();

        $account = $this->db->where('user_account_id', $reseller['user_account_id'])->get(USER_ACCOUNT_TABLE)->row_array();

        $data['user'] = [
            'full_name' => trim($reseller['first_name'] . ' ' . $reseller['last_name']),
            'email' => $reseller['email'],
            'phone' => $reseller['contact_number'],
            'created_at' => $reseller['created_at'],
            'last_login' => $account['last_login'] ?? NULL,
            'profile_image' => $reseller['profile_image'] ?? NULL,
        ];

        // A reseller_tbl row only exists once an application has been
        // approved (see admin/Reseller::approve_application()), so any
        // reseller reaching this page is implicitly approved.
        $data['reseller'] = $reseller + [
            'approval_status' => 'approved',
            'business_address' => trim(implode(', ', array_filter([$reseller['street'], $reseller['barangay'], $reseller['city']]))),
        ];

        $this->load->view('reseller/layouts/header', $data);
        $this->load->view('reseller/profile/index', $data);
        $this->load->view('reseller/layouts/footer', $data);
    }

    public function update() {
        if ($this->input->method() !== 'post') {
            redirect('reseller/profile');
        }

        $this->db->where('reseller_id', $this->user_id)->update(RESELLER_TABLE, [
            'first_name' => trim((string) $this->input->post('first_name', TRUE)),
            'middle_name' => trim((string) $this->input->post('middle_name', TRUE)),
            'last_name' => trim((string) $this->input->post('last_name', TRUE)),
            'contact_number' => $this->input->post('phone', TRUE),
            'business_name' => $this->input->post('business_name', TRUE),
            'gcash_number' => $this->input->post('gcash_number', TRUE),
            'gcash_name' => $this->input->post('gcash_name', TRUE),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->log_activity('profile_updated', 'reseller', $this->user_id);
        $this->session->set_flashdata('success', 'Profile updated successfully');
        redirect('reseller/profile');
    }

    /**
     * Reseller's own activity history — their own logged actions only
     * (withdrawals, listing changes, profile edits), framed as "history"
     * rather than a raw admin-style log.
     */
    public function activity_history() {
        $data = $this->set_view_data();
        $data['page_title'] = 'Activity History';

        $this->load->model('activity_log_model');

        $page = (int) ($this->input->get('page') ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $data['logs'] = $this->activity_log_model->get_user_logs('reseller', $this->user_id, $limit, $offset);
        $data['total'] = $this->activity_log_model->count_user_logs('reseller', $this->user_id);
        $data['pages'] = (int) ceil($data['total'] / $limit);
        $data['page'] = $page;

        $this->load->view('reseller/layouts/header', $data);
        $this->load->view('reseller/profile/activity_history', $data);
        $this->load->view('reseller/layouts/footer', $data);
    }

    public function change_password() {
        if ($this->input->method() !== 'post') {
            redirect('reseller/profile');
        }

        $current_password = $this->input->post('current_password', FALSE);
        $new_password = $this->input->post('new_password', FALSE);
        $confirm_password = $this->input->post('confirm_password', FALSE);

        $reseller = $this->db->where('reseller_id', $this->user_id)->get(RESELLER_TABLE)->row_array();
        $account = $this->db->where('user_account_id', $reseller['user_account_id'])->get(USER_ACCOUNT_TABLE)->row_array();

        if (!$account || !password_verify((string) $current_password, $account['password'])) {
            $this->session->set_flashdata('error', 'Current password is incorrect');
            redirect('reseller/profile');
        }
        if (strlen((string) $new_password) < 6) {
            $this->session->set_flashdata('error', 'New password must be at least 6 characters');
            redirect('reseller/profile');
        }
        if ($new_password !== $confirm_password) {
            $this->session->set_flashdata('error', 'New password and confirmation do not match');
            redirect('reseller/profile');
        }

        $this->db->where('user_account_id', $reseller['user_account_id'])->update(USER_ACCOUNT_TABLE, [
            'password' => password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->session->set_flashdata('success', 'Password changed successfully');
        redirect('reseller/profile');
    }

    public function update_photo() {
        if ($this->input->method() !== 'post' || empty($_FILES['photo']['name'])) {
            redirect('reseller/profile');
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
            $this->db->where('reseller_id', $this->user_id)->update(RESELLER_TABLE, [
                'profile_image' => $path,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $this->session->set_userdata('profile_image', $path);
            $this->session->set_flashdata('success', 'Profile photo updated');
        } else {
            $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
        }

        redirect('reseller/profile');
    }

    public function delete_photo() {
        $this->db->where('reseller_id', $this->user_id)->update(RESELLER_TABLE, ['profile_image' => NULL]);
        $this->session->set_userdata('profile_image', '');
        $this->session->set_flashdata('success', 'Profile photo removed');
        redirect('reseller/profile');
    }
}
