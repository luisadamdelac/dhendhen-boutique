<?php
class Profile extends Authenticated_Controller {
    public function __construct() {
        parent::__construct();
        $this->require_role(ROLE_STAFF);
    }

    public function index() {
        $data = $this->set_view_data();
        $data['page_title'] = 'Staff Profile';
        $data['profile'] = $this->db->select('s.*, u.email')->from(STAFF_TABLE . ' s')->join(USER_ACCOUNT_TABLE . ' u', 's.user_account_id = u.user_account_id')->where('s.staff_id', $this->user_id)->get()->row_array();
        $this->load->view('staff/layouts/header', $data);
        $this->load->view('staff/profile/index', $data);
        $this->load->view('staff/layouts/footer', $data);
    }

    public function update() {
        if ($this->input->method() !== 'post') {
            redirect('staff/profile');
        }

        $this->db->where('staff_id', $this->user_id)->update(STAFF_TABLE, [
            'first_name' => $this->input->post('first_name', TRUE),
            'last_name' => $this->input->post('last_name', TRUE),
            'contact_number' => $this->input->post('contact_number', TRUE),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->log_activity('profile_updated', 'staff', $this->user_id);

        $this->session->set_flashdata('success', 'Profile updated successfully');
        redirect('staff/profile');
    }

    /**
     * Staff's own activity log — limited to actions this staff member took
     * (order status updates, stock adjustments, profile changes), not the
     * full system-wide log admins see.
     */
    public function activity_log() {
        $data = $this->set_view_data();
        $data['page_title'] = 'Activity Log';

        $this->load->model('activity_log_model');

        $page = (int) ($this->input->get('page') ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $data['logs'] = $this->activity_log_model->get_user_logs('staff', $this->user_id, $limit, $offset);
        $data['total'] = $this->activity_log_model->count_user_logs('staff', $this->user_id);
        $data['pages'] = (int) ceil($data['total'] / $limit);
        $data['page'] = $page;

        $this->load->view('staff/layouts/header', $data);
        $this->load->view('staff/profile/activity_log', $data);
        $this->load->view('staff/layouts/footer', $data);
    }

    public function change_password() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $this->load->library('form_validation');
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

        $staff = $this->db->select('u.*')
            ->from(STAFF_TABLE . ' s')
            ->join(USER_ACCOUNT_TABLE . ' u', 's.user_account_id = u.user_account_id')
            ->where('s.staff_id', $this->user_id)
            ->get()->row_array();

        if (!$staff || !password_verify($this->input->post('current_password'), $staff['password'])) {
            echo json_encode(['success' => FALSE, 'message' => 'Current password is incorrect']);
            return;
        }

        $this->db->update(USER_ACCOUNT_TABLE, [
            'password' => password_hash($this->input->post('new_password'), PASSWORD_BCRYPT),
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['user_account_id' => $staff['user_account_id']]);

        echo json_encode(['success' => TRUE, 'message' => 'Password changed successfully']);
    }

    public function update_photo() {
        if ($this->input->method() !== 'post' || empty($_FILES['photo']['name'])) {
            redirect('staff/profile');
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
            $this->db->where('staff_id', $this->user_id)->update(STAFF_TABLE, [
                'profile_image' => $path,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $this->session->set_userdata('profile_image', $path);
            $this->session->set_flashdata('success', 'Profile photo updated');
        } else {
            $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
        }

        redirect('staff/profile');
    }

    public function delete_photo() {
        $this->db->where('staff_id', $this->user_id)->update(STAFF_TABLE, ['profile_image' => NULL]);
        $this->session->set_userdata('profile_image', '');
        $this->session->set_flashdata('success', 'Profile photo removed');
        redirect('staff/profile');
    }
}
