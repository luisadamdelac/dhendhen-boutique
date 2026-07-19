<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends Authenticated_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_role('admin');
        $this->load->model('Admin_model');
        $this->load->library('form_validation');
    }

    /**
     * View profile
     */
    public function index() {
        $admin_id = get_user_id();
        $admin = $this->db->select('a.*, u.email, u.status, u.created_at')
            ->from(ADMIN_TABLE . ' a')
            ->join(USER_ACCOUNT_TABLE . ' u', 'a.user_account_id = u.user_account_id')
            ->where('a.admin_id', $admin_id)
            ->get()->row_array();

        if (!$admin) {
            $this->session->set_flashdata('error', 'Admin profile not found');
            redirect('admin/dashboard');
        }

        $data['user'] = [
            'full_name' => trim(($admin['first_name'] ?? '') . ' ' . (!empty($admin['middle_name']) ? $admin['middle_name'] . ' ' : '') . ($admin['last_name'] ?? '')),
            'first_name' => $admin['first_name'] ?? '',
            'middle_name' => $admin['middle_name'] ?? '',
            'last_name' => $admin['last_name'] ?? '',
            'email' => $admin['email'],
            'phone' => $admin['contact_number'],
            'role' => 'admin',
            'status' => $admin['status'],
            'created_at' => $admin['created_at'],
            'profile_image' => $admin['profile_image'],
        ];
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/profile/index', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Update profile (and optionally password) from the single profile form
     */
    public function update() {
        if ($this->input->method() !== 'post') {
            redirect('admin/profile');
        }

        $admin_id = get_user_id();

        $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
        $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
        $this->form_validation->set_rules('middle_name', 'Middle Name', 'trim');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            redirect('admin/profile');
        }

        $admin = $this->db->where('admin_id', $admin_id)->get(ADMIN_TABLE)->row_array();

        $new_password = $this->input->post('new_password', FALSE);
        if (!empty($new_password)) {
            $current_password = $this->input->post('current_password', FALSE);
            $account = $this->db->where('user_account_id', $admin['user_account_id'])->get(USER_ACCOUNT_TABLE)->row_array();

            if (!$account || !password_verify((string) $current_password, $account['password'])) {
                $this->session->set_flashdata('error', 'Current password is incorrect');
                redirect('admin/profile');
            }
            if ($new_password !== $this->input->post('confirm_password', FALSE)) {
                $this->session->set_flashdata('error', 'New password and confirmation do not match');
                redirect('admin/profile');
            }
            if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%^&*]).{12,}$/', (string) $new_password)) {
                $this->session->set_flashdata('error', 'Password must be at least 12 characters and include an uppercase letter, lowercase letter, number, and special character (!@#$%^&*).');
                redirect('admin/profile');
            }

            $this->db->where('user_account_id', $admin['user_account_id'])->update(USER_ACCOUNT_TABLE, [
                'password' => password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $first_name = trim((string) $this->input->post('first_name', TRUE));
        $middle_name = trim((string) $this->input->post('middle_name', TRUE));
        $last_name = trim((string) $this->input->post('last_name', TRUE));

        $this->db->where('admin_id', $admin_id)->update(ADMIN_TABLE, [
            'first_name' => $first_name,
            'middle_name' => $middle_name ?: NULL,
            'last_name' => $last_name,
            'contact_number' => $this->input->post('phone', TRUE) ?: $admin['contact_number'],
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->where('user_account_id', $admin['user_account_id'])->update(USER_ACCOUNT_TABLE, [
            'email' => $this->input->post('email', TRUE),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Refresh session values so displayed name/email update immediately
        $full_name = trim($first_name . ' ' . (!empty($middle_name) ? $middle_name . ' ' : '') . $last_name);
        $this->session->set_userdata([
            'first_name' => $first_name,
            'middle_name' => $middle_name ?: '',
            'last_name' => $last_name,
            'full_name' => $full_name,
            'email' => $this->input->post('email', TRUE),
        ]);
        $this->session->set_flashdata('success', 'Profile updated successfully');
        redirect('admin/profile');
    }

    /**
     * Change password
     */
    public function change_password() {
        $admin_id = get_user_id();

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('current_password', 'Current Password', 'required');
            $this->form_validation->set_rules('new_password', 'New Password',
                'required|min_length[12]|regex_match[/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%^&*]).{12,}$/]',
                ['regex_match' => 'Password must be at least 12 characters and include an uppercase letter, lowercase letter, number, and special character (!@#$%^&*).']
            );
            $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[new_password]');

            if ($this->form_validation->run() === FALSE) {
                echo json_encode(['success' => FALSE, 'message' => validation_errors(' ', ' ')]);
                return;
            }

            $admin = $this->db->select('u.*')
                ->from(ADMIN_TABLE . ' a')
                ->join(USER_ACCOUNT_TABLE . ' u', 'a.user_account_id = u.user_account_id')
                ->where('a.admin_id', $admin_id)
                ->get()->row_array();

            if (!password_verify($this->input->post('current_password'), $admin['password'])) {
                echo json_encode(['success' => FALSE, 'message' => 'Current password is incorrect']);
                return;
            }

            $new_password_hash = password_hash($this->input->post('new_password'), PASSWORD_BCRYPT);
            $this->db->update(USER_ACCOUNT_TABLE, ['password' => $new_password_hash], ['user_account_id' => $admin['user_account_id']]);

            echo json_encode(['success' => TRUE, 'message' => 'Password changed successfully']);
        }
    }

    public function update_photo() {
        if ($this->input->method() !== 'post' || empty($_FILES['photo']['name'])) {
            redirect('admin/profile');
        }

        $admin_id = get_user_id();
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
            $this->db->where('admin_id', $admin_id)->update(ADMIN_TABLE, [
                'profile_image' => $path,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $this->session->set_userdata('profile_image', $path);
            $this->session->set_flashdata('success', 'Profile photo updated');
        } else {
            $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
        }

        redirect('admin/profile');
    }

    public function delete_photo() {
        $this->db->where('admin_id', get_user_id())->update(ADMIN_TABLE, ['profile_image' => NULL]);
        $this->session->set_userdata('profile_image', '');
        $this->session->set_flashdata('success', 'Profile photo removed');
        redirect('admin/profile');
    }
}
?>
