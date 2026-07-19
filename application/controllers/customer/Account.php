<?php
class Account extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper(['url', 'form', 'auth']);
        $this->load->database();
        load_scoped_session('customer');
        if (!ensure_logged_in()) {
            redirect('auth/login');
        }
        $this->load->config('dropsell');
        if ($this->session->userdata('user_type') !== ROLE_CUSTOMER) {
            deny_role_access();
        }
        $this->load->model('Activity_log_model');
    }

    public function index() {
        $data['page_title'] = 'My Account';
        $user_account_id = $this->session->userdata('user_account_id');
        $customer_id = $this->session->userdata('user_id');

        $account = $this->db->select('*')->from(USER_ACCOUNT_TABLE)->where('user_account_id', $user_account_id)->get()->row_array();
        $customer = $this->db->select('*')->from(CUSTOMER_TABLE)->where('customer_id', $customer_id)->get()->row_array();

        $data['user'] = array_merge($account ?: [], [
            'full_name' => trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? '')),
            'phone' => $customer['contact_number'] ?? '',
            'profile_image' => $customer['profile_image'] ?? NULL,
        ]);
        $data['stats'] = [
            'total_orders' => $this->db->from(ORDER_TABLE)->where('customer_id', $customer_id)->count_all_results(),
            'pending_orders' => $this->db->from(ORDER_TABLE)->where('customer_id', $customer_id)->where('order_status', 'pending')->count_all_results(),
            'completed_orders' => $this->db->from(ORDER_TABLE)->where('customer_id', $customer_id)->where('order_status', 'delivered')->count_all_results(),
        ];

        $existing_application = $this->db->select('*')->from(RESELLER_APPLICATION_TABLE)
            ->where('customer_id', $customer_id)->order_by('application_id', 'DESC')->get()->row_array();
        $data['reseller_application'] = $existing_application;

        // Already an active reseller? They can't apply again — show a
        // shortcut to the reseller dashboard instead of the apply form.
        $data['active_reseller'] = $this->db->select('reseller_id')->from(RESELLER_TABLE)
            ->where('user_account_id', $user_account_id)->where('status', 'active')->get()->row_array();

        $this->load->model('settings_model');
        $data['minimum_spend'] = (float) $this->settings_model->get_minimum_spend();
        // Eligibility is based on a single delivered order reaching the
        // minimum — small purchases don't add up over time toward it.
        $data['best_single_order'] = (float) ($this->db->select_max('total_amount')
            ->from(ORDER_TABLE)
            ->where('customer_id', $customer_id)
            ->where('order_status', 'delivered')
            ->get()->row()->total_amount ?? 0);

        $this->load->view('customer/layouts/header', $data);
        $this->load->view('customer/account/index', $data);
        $this->load->view('customer/layouts/footer', $data);
    }

    public function update_profile() {
        if ($this->input->method() !== 'post') {
            redirect('account');
        }

        $full_name = trim((string) $this->input->post('full_name', true));
        $email = $this->input->post('email', true);
        $phone = $this->input->post('phone', true);
        $name_parts = preg_split('/\s+/', $full_name, 2);
        $first_name = $name_parts[0] ?? '';
        $last_name = $name_parts[1] ?? $first_name;

        $user_account_id = $this->session->userdata('user_account_id');
        $customer_id = $this->session->userdata('user_id');

        $this->db->update(USER_ACCOUNT_TABLE, [
            'email' => $email,
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['user_account_id' => $user_account_id]);

        $this->db->update(CUSTOMER_TABLE, [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'contact_number' => $phone,
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['customer_id' => $customer_id]);

        $this->session->set_userdata([
            'email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'full_name' => $full_name,
        ]);

        $this->Activity_log_model->log_activity($customer_id, 'customer', 'profile_updated', NULL, $this->input->ip_address());

        $this->session->set_flashdata('success', 'Profile updated successfully');
        redirect('account');
    }

    public function change_password() {
        if ($this->input->method() !== 'post') {
            redirect('account');
        }

        $current = $this->input->post('current_password', false);
        $new = $this->input->post('new_password', false);
        $confirm = $this->input->post('confirm_password', false);

        if ($new !== $confirm) {
            $this->session->set_flashdata('error', 'New password and confirmation do not match.');
            redirect('account');
        }

        if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%^&*]).{12,}$/', (string) $new)) {
            $this->session->set_flashdata('error', 'Password must be at least 12 characters and include an uppercase letter, lowercase letter, number, and special character (!@#$%^&*).');
            redirect('account');
        }

        $user_account_id = $this->session->userdata('user_account_id');
        $account = $this->db->where('user_account_id', $user_account_id)->get(USER_ACCOUNT_TABLE)->row_array();

        if (!$account || !password_verify($current, $account['password'])) {
            $this->session->set_flashdata('error', 'Current password is incorrect.');
            redirect('account');
        }

        $this->db->update(USER_ACCOUNT_TABLE, [
            'password' => password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]),
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['user_account_id' => $user_account_id]);

        $this->Activity_log_model->log_activity($this->session->userdata('user_id'), 'customer', 'password_changed', NULL, $this->input->ip_address());

        $this->session->set_flashdata('success', 'Password changed successfully');
        redirect('account');
    }

    public function update_photo() {
        if ($this->input->method() !== 'post' || empty($_FILES['photo']['name'])) {
            redirect('account');
        }

        $customer_id = $this->session->userdata('user_id');
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
            $this->db->update(CUSTOMER_TABLE, [
                'profile_image' => $path,
                'updated_at' => date('Y-m-d H:i:s'),
            ], ['customer_id' => $customer_id]);
            $this->session->set_userdata('profile_image', $path);
            $this->session->set_flashdata('success', 'Profile photo updated');
        } else {
            $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
        }

        redirect('account');
    }

    public function delete_photo() {
        $customer_id = $this->session->userdata('user_id');
        $this->db->update(CUSTOMER_TABLE, ['profile_image' => NULL], ['customer_id' => $customer_id]);
        $this->session->set_userdata('profile_image', '');
        $this->session->set_flashdata('success', 'Profile photo removed');
        redirect('account');
    }

    /**
     * Submit an application to become a reseller.
     * Approval is handled separately by admin/Reseller::approve_application().
     */
    public function apply_reseller() {
        if ($this->input->method() !== 'post') {
            redirect('account');
        }

        $customer_id = $this->session->userdata('user_id');

        $existing = $this->db->select('application_id')->from(RESELLER_APPLICATION_TABLE)
            ->where('customer_id', $customer_id)->where('status', 'pending')->get()->row_array();
        if ($existing) {
            $this->session->set_flashdata('error', 'You already have a pending reseller application.');
            redirect('account');
        }

        $this->load->model('settings_model');
        $minimum_spend = (float) $this->settings_model->get_minimum_spend();
        // Eligibility requires a SINGLE delivered order reaching the
        // minimum — small purchases don't accumulate toward it.
        $best_single_order = (float) ($this->db->select_max('total_amount')
            ->from(ORDER_TABLE)
            ->where('customer_id', $customer_id)
            ->where('order_status', 'delivered')
            ->get()->row()->total_amount ?? 0);

        if ($best_single_order < $minimum_spend) {
            $this->session->set_flashdata('error', 'You need a single completed order of at least ₱' . number_format($minimum_spend, 2) .
                ' to apply as a reseller (your largest order so far is ₱' . number_format($best_single_order, 2) . ').');
            redirect('account');
        }

        $this->load->library('form_validation');
        $this->form_validation->set_rules('business_name', 'Business Name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('business_city', 'City', 'required|trim|max_length[50]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            redirect('account');
        }

        $this->db->insert(RESELLER_APPLICATION_TABLE, [
            'customer_id' => $customer_id,
            'business_name' => $this->input->post('business_name', true),
            'business_street' => $this->input->post('business_street', true),
            'business_barangay' => $this->input->post('business_barangay', true),
            'business_city' => $this->input->post('business_city', true),
            'business_zip_code' => $this->input->post('business_zip_code', true),
            'minimum_spend' => $minimum_spend,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->Activity_log_model->log_activity($customer_id, 'customer', 'reseller_application_submitted', 'Business: ' . $this->input->post('business_name', true), $this->input->ip_address());

        $this->session->set_flashdata('success', 'Your reseller application has been submitted for review. You will be notified through email once your application is approved or rejected.');
        redirect('account');
    }
}
