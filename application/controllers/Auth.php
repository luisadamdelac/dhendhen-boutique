<?php
/**
 * Authentication Controller
 * Unified authentication system for all 4 roles: Admin, Staff, Reseller, Customer
 * 
 * Uses user_account_tbl as primary authentication table
 * Routes to role-specific dashboards based on detected role
 */

class Auth extends CI_Controller {

    /** Max wrong guesses against a password-reset OTP before it's cancelled — see do_verify_otp(). */
    const RESET_OTP_MAX_ATTEMPTS = 5;

    public function __construct() {
        parent::__construct();
        
        // Add headers to prevent tracking prevention from blocking storage
        header('Cross-Origin-Opener-Policy: same-origin-allow-popups');
        header('Cross-Origin-Embedder-Policy: require-corp');
        
        $this->load->database();
        // NOTE: session is intentionally NOT loaded here. Every role has its
        // own isolated session cookie (ci_session_admin, ci_session_staff, ...)
        // so each action below loads exactly the session it needs — the
        // generic pre-login one, or (logout only) a specific role's — via
        // load_scoped_session(). See session_helper.php.
        // Note: $this->security is already available on every controller —
        // CI_Input boots CI_Security from system/core/Security.php and
        // CI_Controller copies it onto $this automatically. Explicitly calling
        // $this->load->library('security') fails because CI3 never ships a
        // system/libraries/Security.php for the stock loader to find.
        $this->load->helper('url');
        $this->load->config('dropsell');
        $this->load->model('user_model');
        $this->load->model('admin_model');
        $this->load->model('staff_model');
        $this->load->model('reseller_model');
        $this->load->model('customer_model');
        $this->load->helper(['form', 'csrf']);
        
        // Load services
        require_once APPPATH . 'services/ActivityLogger.php';
        require_once APPPATH . 'services/EmailQueueService.php';
    }

    /**
     * Default action - redirect to login
     */
    public function index() {
        redirect('auth/login');
    }

    /**
     * Display login form
     */
    public function login() {
        // Each role keeps its own isolated session cookie (ci_session_admin,
        // ci_session_staff, ...), so a browser can be logged into several
        // roles at once — this form must stay reachable even while another
        // role is already active, otherwise there'd be no way to log into a
        // second/third/fourth role in the same browser.
        load_scoped_session();

        // Load login view
        $data['page_title'] = 'DropSell Login';
        $data['error'] = $this->session->flashdata('error');
        $data['success'] = $this->session->flashdata('success') ?: ($this->input->get('logged_out', TRUE) ? 'You have been logged out successfully' : '');

        $this->load->view('auth/login', $data);
    }

    /**
     * Handle login form submission
     */
    public function do_login() {
        load_scoped_session(); // generic pre-login session: CSRF token + flash on failure

        // Only accept POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/login');
        }

        // CSRF token validation
        if (!csrf_token_valid()) {
            $this->session->set_flashdata('error', 'Security token mismatch. Please try again.');
            redirect('auth/login');
        }

        // Check brute force protection
        $email = $this->input->post('email', TRUE);
        $ip = $this->input->ip_address();
        
        if (!$this->_check_brute_force_limit($email, $ip)) {
            $lockout_minutes = $this->config->item('login_lockout_minutes') ?? 15;
            $this->session->set_flashdata('error', 'Too many failed login attempts for this account. Please try again in ' . $lockout_minutes . ' minutes.');
            redirect('auth/login');
        }

        // Validate form
        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            redirect('auth/login');
        }

        $password = $this->input->post('password', FALSE);

        // Verify login credentials
        $user = $this->user_model->verify_login($email, $password);

        if (!$user) {
            // Log failed login attempt
            ActivityLogger::loginFailure($email, 'Invalid credentials');
            log_message('error', 'Failed login attempt for email: ' . $email . ' from IP: ' . $ip);
            
            // Record failed attempt for rate limiting
            $this->db->insert('login_attempt_tbl', array(
                'email' => $email,
                'ip_address' => $ip,
                'success' => 0,
                'reason' => 'Invalid credentials',
                'created_at' => date('Y-m-d H:i:s')
            ));
            
            $this->session->set_flashdata('error', 'Invalid email or password');
            redirect('auth/login');
        }

        // Get user role from database
        $role = $this->user_model->get_user_role($user['user_account_id']);

        if (!$role) {
            log_message('error', 'User role not found for user_account_id: ' . $user['user_account_id'] . ' email: ' . $email);
            
            // More helpful error message
            $error_msg = 'Your user account exists but has no role assigned. This typically means your account profile was not created in one of the role tables (admin, staff, reseller, customer). ';
            $error_msg .= 'Please contact support with your email (' . htmlspecialchars($email) . ') and mention this error code: ERR_ROLE_NOT_FOUND_' . $user['user_account_id'];
            
            $this->session->set_flashdata('error', $error_msg);
            redirect('auth/login');
        }

        // Validate role is allowed
        $allowed_roles = $this->config->item('allowed_roles');
        if (!in_array($role, $allowed_roles)) {
            log_message('error', 'Invalid role detected: ' . $role . ' for user_account_id: ' . $user['user_account_id']);
            $this->session->set_flashdata('error', 'Invalid user role. Please contact support.');
            redirect('auth/login');
        }

        // Get role-specific ID
        $role_id = $this->user_model->get_role_id($user['user_account_id'], $role);

        if (!$role_id) {
            log_message('error', 'Role-specific ID not found for role: ' . $role . ' user_account_id: ' . $user['user_account_id']);
            $this->session->set_flashdata('error', 'User profile not found. Please contact support.');
            redirect('auth/login');
        }

        // Get role-specific profile data
        $profile = $this->user_model->get_user_profile($user['user_account_id'], $role);

        if (!$profile) {
            log_message('error', 'Profile data not found for role: ' . $role . ' user_account_id: ' . $user['user_account_id']);
            $this->session->set_flashdata('error', 'Unable to load user profile. Please contact support.');
            redirect('auth/login');
        }

        // Check role-specific status (e.g., reseller pending approval)
        if ($role === ROLE_RESELLER && $profile['status'] === 'pending') {
            $this->session->set_flashdata('error', 'Your reseller application is pending approval. You will be notified via email once approved.');
            redirect('auth/login');
        }

        if ($role === ROLE_RESELLER && $profile['status'] === 'rejected') {
            $this->session->set_flashdata('error', 'Your reseller application has been rejected. Please contact support for more information.');
            redirect('auth/login');
        }

        // Switch from the generic pre-login session to this role's own
        // isolated session, so this browser can be logged into admin/staff/
        // reseller/customer at once without any of them overwriting another.
        $this->_switch_to_role_session($role);

        // Set session data
        $session_data = array(
            'user_type'       => $role,
            'user_id'         => $role_id,  // admin_id, staff_id, reseller_id, customer_id
            'user_account_id' => $user['user_account_id'],
            'email'           => $user['email'],
            'first_name'      => $profile['first_name'] ?? '',
            'last_name'       => $profile['last_name'] ?? '',
            'full_name'       => trim(($profile['first_name'] ?? '') . ' ' . ($profile['middle_name'] ?? '') . ' ' . ($profile['last_name'] ?? '')),
            'profile_image'   => $profile['profile_image'] ?? '',
            'logged_in_at'    => time(),
            'is_logged_in'    => TRUE
        );

        foreach ($session_data as $key => $value) {
            $_SESSION[$key] = $value;
        }

        if ($this->input->post('remember')) {
            $this->_remember_login($user['user_account_id']);
        }

        // Log successful login
        ActivityLogger::loginSuccess($role, $role_id, $email);
        $this->db->insert('login_attempt_tbl', array(
            'email' => $email,
            'ip_address' => $ip,
            'success' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ));
        
        log_message('info', 'Successful login for user: ' . $email . ' with role: ' . $role);

        // Redirect to role-specific dashboard
        $this->_redirect_to_dashboard();
    }

    /**
     * Forgot password - display form
     */
    public function forgot_password() {
        load_scoped_session();

        $data['page_title'] = 'Forgot Password - DropSell';
        $data['error'] = $this->session->flashdata('error');
        $data['success'] = $this->session->flashdata('success');
        $data['prefill_email'] = $this->input->get('email', TRUE);

        $this->load->view('auth/forgot_password', $data);
    }

    /**
     * Send forgot password email
     */
    public function send_reset_link() {
        load_scoped_session();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/forgot_password');
        }

        if (!csrf_token_valid()) {
            $this->session->set_flashdata('error', 'Security token mismatch.');
            redirect('auth/forgot_password');
        }

        $email = $this->input->post('email', TRUE);

        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            redirect('auth/forgot_password');
        }

        $user = $this->user_model->get_by_email($email);

        if (!$user || $user['status'] !== 'active') {
            // Always show same message (anti-enumeration) — no OTP was
            // actually generated, so send them back rather than on to a
            // verify-OTP page that could never succeed.
            $this->session->set_flashdata('success', 'If an account exists with this email, a verification code has been sent.');
            redirect('auth/forgot_password');
        }

        // Generate reset token (used later, after OTP verification, to reach
        // the existing token-link reset_password page/do_reset() unchanged)
        // and a 6-digit OTP (what the user actually enters).
        $token = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $token);
        $otp_code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Store in database
        $reset_data = array(
            'user_account_id' => $user['user_account_id'],
            'email' => $email,
            'token' => $token,
            'token_hash' => $token_hash,
            'otp_code' => $otp_code,
            'expires_at' => $expires_at,
            'is_used' => 0
        );

        $this->db->insert('password_reset_tbl', $reset_data);

        // Queue email
        EmailQueueService::queue($email, $user['email'] ?? 'User',
            'Your DropSell Verification Code',
            EmailQueueService::wrapEmailTemplate('Password Reset Code',
                '<p>Hi,</p>' .
                '<p>We received a request to reset your DropSell password. Enter this verification code to continue:</p>' .
                '<div style="text-align:center;margin:20px 0;">' .
                '<span style="display:inline-block;padding:14px 28px;background:#faf7fb;border:2px dashed #d6006d;border-radius:10px;font-size:28px;font-weight:700;letter-spacing:6px;color:#d6006d;">' . $otp_code . '</span>' .
                '</div>' .
                '<p style="color:#6b7280;font-size:13px;">This code expires in 15 minutes. If you did not request this, you can safely ignore this email.</p>'
            )
        );

        redirect('auth/verify_otp?email=' . urlencode($email));
    }

    /**
     * Verify the OTP emailed by send_reset_link() — display form
     */
    public function verify_otp() {
        load_scoped_session();

        $data['page_title'] = 'Verify Code - DropSell';
        $data['email'] = $this->input->get('email', TRUE) ?: '';
        $data['error'] = $this->session->flashdata('error');
        $data['success'] = $this->session->flashdata('success');

        $this->load->view('auth/verify_otp', $data);
    }

    /**
     * Handle OTP verification submission — on success, hands off to the
     * existing token-link reset page so reset()/do_reset() stay unchanged.
     */
    public function do_verify_otp() {
        load_scoped_session();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/forgot_password');
        }

        if (!csrf_token_valid()) {
            $this->session->set_flashdata('error', 'Security token mismatch.');
            redirect('auth/forgot_password');
        }

        $email = $this->input->post('email', TRUE);
        $otp_code = trim((string) $this->input->post('otp_code', TRUE));

        if (empty($email) || empty($otp_code)) {
            $this->session->set_flashdata('error', 'Please enter the verification code.');
            redirect('auth/verify_otp?email=' . urlencode($email));
        }

        // The active (unused, unexpired) reset request for this email,
        // whether or not the submitted code actually matches it — needed to
        // track and lock out wrong guesses. Without this, the 6-digit code
        // was brute-forceable with unlimited attempts for its whole 15-minute
        // window; mirrors the same otp_attempts lockout already used for
        // reseller withdrawal OTPs (Withdrawals::verify_withdrawal_otp()).
        $active_request = $this->db->where('email', $email)
                                   ->where('is_used', 0)
                                   ->where('expires_at >', date('Y-m-d H:i:s'))
                                   ->order_by('reset_id', 'DESC')
                                   ->get('password_reset_tbl')
                                   ->row_array();

        if ($active_request && (int) $active_request['otp_attempts'] >= self::RESET_OTP_MAX_ATTEMPTS) {
            $this->db->where('reset_id', $active_request['reset_id'])->update('password_reset_tbl', ['is_used' => 1]);
            $this->session->set_flashdata('error', 'Too many incorrect attempts. Please request a new verification code.');
            redirect('auth/forgot_password');
        }

        if (!$active_request || !hash_equals($active_request['otp_code'], $otp_code)) {
            if ($active_request) {
                $this->db->where('reset_id', $active_request['reset_id'])
                         ->update('password_reset_tbl', ['otp_attempts' => (int) $active_request['otp_attempts'] + 1]);
            }
            $this->session->set_flashdata('error', 'Invalid or expired verification code.');
            redirect('auth/verify_otp?email=' . urlencode($email));
        }

        redirect('auth/reset/' . $active_request['token']);
    }

    /**
     * Reset password form
     */
    public function reset($token = '') {
        load_scoped_session();

        if (empty($token)) {
            $this->session->set_flashdata('error', 'Invalid reset token.');
            redirect('auth/login');
        }

        // Validate token
        $token_hash = hash('sha256', $token);
        $reset_record = $this->db->where('token_hash', $token_hash)
                                 ->where('is_used', 0)
                                 ->where('expires_at >', date('Y-m-d H:i:s'))
                                 ->get('password_reset_tbl')
                                 ->row_array();

        if (!$reset_record) {
            $this->session->set_flashdata('error', 'Invalid or expired reset token.');
            redirect('auth/forgot_password');
        }

        $data['page_title'] = 'Reset Password - DropSell';
        $data['token'] = $token;
        $data['error'] = $this->session->flashdata('error');
        $data['success'] = $this->session->flashdata('success');
        
        $this->load->view('auth/reset_password', $data);
    }

    /**
     * Handle password reset
     */
    public function do_reset() {
        load_scoped_session();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/forgot_password');
        }

        if (!csrf_token_valid()) {
            $this->session->set_flashdata('error', 'Security token mismatch.');
            redirect('auth/forgot_password');
        }

        $token = $this->input->post('token', TRUE);
        $password = $this->input->post('password', FALSE);
        $password_confirm = $this->input->post('password_confirm', FALSE);

        $this->load->library('form_validation');
        $this->form_validation->set_rules('password', 'Password',
            'required|min_length[12]|regex_match[/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%^&*]).{12,}$/]',
            ['regex_match' => 'Password must be at least 12 characters and include an uppercase letter, lowercase letter, number, and special character (!@#$%^&*).']
        );
        $this->form_validation->set_rules('password_confirm', 'Confirm Password', 'required|matches[password]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            redirect('auth/reset/' . $token);
        }

        // Validate token
        $token_hash = hash('sha256', $token);
        $reset_record = $this->db->where('token_hash', $token_hash)
                                 ->where('is_used', 0)
                                 ->where('expires_at >', date('Y-m-d H:i:s'))
                                 ->get('password_reset_tbl')
                                 ->row_array();

        if (!$reset_record) {
            $this->session->set_flashdata('error', 'Invalid or expired reset token.');
            redirect('auth/forgot_password');
        }

        try {
            $this->db->trans_start();

            // Update password
            $this->db->where('user_account_id', $reset_record['user_account_id'])
                     ->update('user_account_tbl', array(
                         'password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
                         'updated_at' => date('Y-m-d H:i:s')
                     ));

            // Mark token as used
            $this->db->where('reset_id', $reset_record['reset_id'])
                     ->update('password_reset_tbl', array('is_used' => 1));

            // Revoke every "remember me" token for this account — otherwise
            // a stolen/leaked remember_token cookie on another device still
            // passes ensure_logged_in() after this reset, defeating the
            // whole point of resetting the password to lock a compromised
            // account back down.
            $this->db->where('user_account_id', $reset_record['user_account_id'])
                     ->where('type', 'remember_me')
                     ->where('revoked', 0)
                     ->update('auth_tokens', array('revoked' => 1));

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            ActivityLogger::passwordChanged('', $reset_record['user_account_id']);
            log_message('info', 'Password reset for user: ' . $reset_record['email']);

            $this->session->set_flashdata('success', 'Password updated successfully. Please log in with your new password.');
            redirect('auth/login');

        } catch (Exception $e) {
            log_message('error', 'Password reset error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Failed to update password. Please try again.');
            redirect('auth/reset/' . $token);
        }
    }

    /**
     * Handle logout. Expects the role being logged out of as the 3rd URI
     * segment (auth/logout/admin, auth/logout/staff, ...) so it destroys
     * THAT role's own session cookie — not some other role's — since each
     * role now has its own isolated session in this browser. Every logout
     * link in the app's layouts passes its own role explicitly.
     */
    public function logout() {
        $role = $this->uri->segment(3);
        $valid_roles = array(ROLE_ADMIN, ROLE_STAFF, ROLE_RESELLER, ROLE_CUSTOMER);
        load_scoped_session(in_array($role, $valid_roles, true) ? $role : null);

        // Log logout activity
        if ($this->session->userdata('user_account_id')) {
            $this->_log_activity(
                $this->session->userdata('user_type'),
                $this->session->userdata('user_id'),
                'logout',
                'User logged out'
            );
        }

        $this->_forget_login();

        // Destroy this role's session
        $this->session->sess_destroy();

        // The flash message would need to land in the generic pre-login
        // session, but CI3 won't load a second Session instance in one
        // request — use a query flag instead; login() reads it.
        redirect('auth/login?logged_out=1');
    }

    /**
     * Private: Issue a long-lived "remember me" token for this account and
     * store it as remember_token=user_account_id:token in an httponly cookie.
     */
    private function _remember_login($user_account_id) {
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));

        $this->db->insert('auth_tokens', array(
            'user_account_id' => $user_account_id,
            'type' => 'remember_me',
            'token_hash' => hash('sha256', $token),
            'expires_at' => $expires_at,
            'revoked' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ));

        $this->input->set_cookie(array(
            'name' => 'remember_token',
            'value' => $user_account_id . ':' . $token,
            'expire' => 30 * 24 * 60 * 60,
            'httponly' => TRUE
        ));
    }

    /**
     * Private: Revoke the current remember-me token (if any) and clear the cookie.
     */
    private function _forget_login() {
        $cookie = $this->input->cookie('remember_token', TRUE);
        if (!empty($cookie) && strpos($cookie, ':') !== FALSE) {
            list($user_account_id, $token) = explode(':', $cookie, 2);
            $this->db->where('user_account_id', (int) $user_account_id)
                     ->where('token_hash', hash('sha256', $token))
                     ->update('auth_tokens', array('revoked' => 1));
        }

        $this->input->set_cookie(array(
            'name' => 'remember_token',
            'value' => '',
            'expire' => -3600
        ));
    }

    /**
     * Get current user full data
     */
    public function get_current_user() {
        load_scoped_session();

        if (!$this->session->userdata('user_account_id')) {
            return FALSE;
        }

        return $this->user_model->get_complete_user($this->session->userdata('user_account_id'));
    }

    /**
     * Display reseller registration form
     */
    public function register_reseller() {
        load_scoped_session();

        $data['page_title'] = 'Register as Reseller - DropSell';
        $data['error']   = $this->session->flashdata('error');
        $data['success'] = $this->session->flashdata('success');

        $this->load->view('auth/register_reseller', $data);
    }

    /**
     * Handle reseller registration form submission
     */
    public function do_register_reseller() {
        load_scoped_session();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/register_reseller');
        }

        $data['page_title'] = 'Register as Reseller - DropSell';
        $data['success'] = NULL;

        if (!csrf_token_valid()) {
            $data['error'] = 'Security token mismatch. Please try again.';
            $data['error_fields'] = [];
            $this->load->view('auth/register_reseller', $data);
            return;
        }

        $this->load->helper('address');
        $this->load->library('form_validation');

        $this->form_validation->set_rules('first_name',    'First Name',    'required|trim');
        $this->form_validation->set_rules('last_name',     'Last Name',     'required|trim');
        $this->form_validation->set_rules('middle_name',   'Middle Name',   'trim');
        $this->form_validation->set_rules('business_name', 'Business Name', 'required|trim|max_length[50]');
        $this->form_validation->set_rules('email',         'Email',         'required|valid_email|trim');
        $this->form_validation->set_rules('contact_number','Contact Number','required|trim|max_length[11]');
        $this->form_validation->set_rules('street',        'Street',        'required|trim|max_length[100]');
        $this->form_validation->set_rules('municipality',  'Municipality',  'required|trim|max_length[50]');
        $this->form_validation->set_rules('barangay',      'Barangay',      'required|trim|max_length[50]');
        $this->form_validation->set_rules('password', 'Password',
            'required|min_length[12]|regex_match[/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%^&*]).{12,}$/]',
            ['regex_match' => 'Password must be at least 12 characters and include an uppercase letter, lowercase letter, number, and special character (!@#$%^&*).']
        );
        $this->form_validation->set_rules('password_confirm', 'Confirm Password', 'required|matches[password]');
        $this->form_validation->set_rules('terms',         'Terms',         'required');

        if ($this->form_validation->run() === FALSE) {
            $data['error'] = validation_errors(' ', ' ');
            $data['error_fields'] = array_keys($this->form_validation->error_array());
            $this->load->view('auth/register_reseller', $data);
            return;
        }

        $first_name    = $this->input->post('first_name',    TRUE);
        $last_name     = $this->input->post('last_name',     TRUE);
        $middle_name   = $this->input->post('middle_name',   TRUE);
        $business_name = $this->input->post('business_name', TRUE);
        $email         = $this->input->post('email',         TRUE);
        $contact_number= $this->input->post('contact_number',TRUE);
        $street        = $this->input->post('street',        TRUE);
        $municipality  = $this->input->post('municipality',  TRUE);
        $barangay      = $this->input->post('barangay',      TRUE);
        $password      = $this->input->post('password',      FALSE);

        if (!is_valid_oriental_mindoro_address($municipality, $barangay)) {
            $data['error'] = 'Please select a valid Municipality and Barangay within Oriental Mindoro.';
            $data['error_fields'] = ['municipality', 'barangay'];
            $this->load->view('auth/register_reseller', $data);
            return;
        }

        $existing = $this->db->where('email', $email)->get('user_account_tbl')->row();
        if ($existing) {
            $data['error'] = 'This email is already registered. Please use a different email or log in.';
            $data['error_fields'] = ['email'];
            $this->load->view('auth/register_reseller', $data);
            return;
        }

        try {
            $this->db->trans_start();

            $account_id = $this->user_model->create_account(array(
                'email'      => $email,
                'password'   => password_hash($password, PASSWORD_BCRYPT),
                'status'     => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));

            $this->db->insert('reseller_tbl', array(
                'user_account_id'         => $account_id,
                'status'                  => 'pending',
                'first_name'              => $first_name,
                'middle_name'             => $middle_name ?: NULL,
                'last_name'               => $last_name,
                'contact_number'          => $contact_number,
                'street'                  => $street,
                'barangay'                => $barangay,
                'city'                    => $municipality,
                'business_name'           => $business_name,
                'commission_balance'      => 0.00,
                'total_commission_earned' => 0.00,
                'total_withdrawn'         => 0.00,
                'created_at'              => date('Y-m-d H:i:s'),
                'updated_at'              => date('Y-m-d H:i:s'),
            ));

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            log_message('info', 'New reseller registration (pending): ' . $email);

            $this->session->set_flashdata('success', 'Your reseller application has been submitted! You will be notified through email once your application is approved or rejected.');
            redirect('auth/login');

        } catch (Exception $e) {
            log_message('error', 'Reseller registration error: ' . $e->getMessage());
            $data['error'] = 'Registration failed. Please try again.';
            $data['error_fields'] = [];
            $this->load->view('auth/register_reseller', $data);
        }
    }

    /**
     * Display registration form
     */
    public function register() {
        load_scoped_session();

        // Get user type from URL or default to customer
        $user_type = $this->input->get('role', TRUE) ?? 'customer';
        
        // Load registration view
        $data['page_title'] = 'Register - DropSell';
        $data['user_type'] = $user_type;
        $data['error'] = $this->session->flashdata('error');
        $data['success'] = $this->session->flashdata('success');
        $data['old'] = [];

        $this->load->view('auth/register', $data);
    }

    /**
     * Handle registration form submission
     */
    public function do_register() {
        load_scoped_session();

        // Only accept POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/register');
        }

        $data['page_title'] = 'Register - DropSell';
        $data['user_type'] = $this->input->get('role', TRUE) ?? 'customer';
        $data['success'] = NULL;
        // Re-echoed into the form on any validation failure below so the
        // customer doesn't have to retype everything (password excluded).
        $data['old'] = $this->input->post();
        unset($data['old']['password'], $data['old']['password_confirm']);

        if (!csrf_token_valid()) {
            $data['error'] = 'Security token mismatch. Please try again.';
            $data['error_fields'] = [];
            $this->load->view('auth/register', $data);
            return;
        }

        // Validate form
        // Note: registration only ever creates a customer profile. Becoming a
        // reseller is a separate, admin-reviewed application (see
        // customer/Account::apply_reseller() and admin/Reseller::approve_application()),
        // since reseller_tbl has no status column of its own — a row only
        // exists there once an application has been approved.
        $this->load->helper('address');
        $this->load->library('form_validation');
        $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
        $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
        $this->form_validation->set_rules('middle_name', 'Middle Name', 'trim');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('contact_number', 'Contact Number', 'required|trim|max_length[11]');
        $this->form_validation->set_rules('street', 'Street', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('municipality', 'Municipality', 'required|trim|max_length[50]');
        $this->form_validation->set_rules('barangay', 'Barangay', 'required|trim|max_length[50]');
        $this->form_validation->set_rules('password', 'Password',
            'required|min_length[12]|regex_match[/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%^&*]).{12,}$/]',
            ['regex_match' => 'Password must be at least 12 characters and include an uppercase letter, lowercase letter, number, and special character (!@#$%^&*).']
        );
        $this->form_validation->set_rules('password_confirm', 'Confirm Password', 'required|matches[password]');
        $this->form_validation->set_rules('terms', 'Terms', 'required');

        if ($this->form_validation->run() === FALSE) {
            $data['error'] = validation_errors(' ', ' ');
            $data['error_fields'] = array_keys($this->form_validation->error_array());
            $this->load->view('auth/register', $data);
            return;
        }

        // Get form data
        $first_name = $this->input->post('first_name', TRUE);
        $last_name = $this->input->post('last_name', TRUE);
        $middle_name = $this->input->post('middle_name', TRUE);
        $email = $this->input->post('email', TRUE);
        $contact_number = $this->input->post('contact_number', TRUE);
        $street = $this->input->post('street', TRUE);
        $municipality = $this->input->post('municipality', TRUE);
        $barangay = $this->input->post('barangay', TRUE);
        $password = $this->input->post('password', FALSE);

        // The province is locked to Oriental Mindoro, and the municipality/
        // barangay dropdowns are scoped to it — reject anything that doesn't
        // match a real combination (e.g. a tampered POST request).
        if (!is_valid_oriental_mindoro_address($municipality, $barangay)) {
            $data['error'] = 'Please select a valid Municipality and Barangay within Oriental Mindoro.';
            $data['error_fields'] = ['municipality', 'barangay'];
            $this->load->view('auth/register', $data);
            return;
        }

        // Check if email already exists
        $existing = $this->db->where('email', $email)->get('user_account_tbl')->row();
        if ($existing) {
            $data['error'] = 'Email already registered';
            $data['error_fields'] = ['email'];
            $this->load->view('auth/register', $data);
            return;
        }

        // Create user account
        $account_data = array(
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'created_at' => date('Y-m-d H:i:s'),
            'status' => 'active'
        );

        $account_id = $this->user_model->create_account($account_data);

        if (!$account_id) {
            $data['error'] = 'Failed to create account. Please try again.';
            $data['error_fields'] = [];
            $this->load->view('auth/register', $data);
            return;
        }

        $profile_data = array(
            'user_account_id' => $account_id,
            'first_name' => $first_name,
            'middle_name' => $middle_name ?: NULL,
            'last_name' => $last_name,
            'contact_number' => $contact_number,
            'street' => $street,
            'barangay' => $barangay,
            'municipality' => $municipality,
            'province' => 'Oriental Mindoro',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        $this->db->insert('customer_tbl', $profile_data);
        $customer_id = $this->db->insert_id();

        // Seed the customer's address book (Settings > My Addresses) with the
        // address they just registered with, marked as their default.
        $this->db->insert('customer_address_tbl', array(
            'customer_id' => $customer_id,
            'label' => 'Home',
            'street' => $street,
            'barangay' => $barangay,
            'municipality' => $municipality,
            'province' => 'Oriental Mindoro',
            'is_default' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ));

        // Set success message and redirect to login
        $this->session->set_flashdata('success', 'Account created successfully! Please log in.');
        redirect('auth/login');
    }

    /**
     * Private: Check brute force limits
     * Strictly per-account: only the attempted email's own failed attempts
     * count against it. A shared/NAT'd IP with unrelated accounts failing
     * logins must never lock out an account that hasn't failed itself.
     */
    private function _check_brute_force_limit($email, $ip) {
        $lockout_minutes = $this->config->item('login_lockout_minutes') ?? 15;
        $window_start = date('Y-m-d H:i:s', strtotime('-' . $lockout_minutes . ' minutes'));

        $email_attempts = $this->db->where('email', $email)
                                   ->where('success', 0)
                                   ->where('created_at >', $window_start)
                                   ->count_all_results('login_attempt_tbl');

        $max_attempts = $this->config->item('max_login_attempts') ?? 5;

        return $email_attempts < $max_attempts;
    }

    /**
     * Private: Redirect to appropriate dashboard based on user role
     */
    private function _redirect_to_dashboard() {
        $this->load->helper('auth');
        redirect(role_dashboard_url($this->session->userdata('user_type')));
    }


    /**
     * Private: Close the generic pre-login PHP session and reopen a fresh,
     * regenerated one under this role's own cookie name, so admin/staff/
     * reseller/customer logins never share or clobber one another's session
     * in the same browser. CI3 won't instantiate a second Session library
     * object in one request, so this uses native session functions
     * directly; do_login() populates $_SESSION by hand immediately after.
     */
    private function _switch_to_role_session($role) {
        session_write_close();

        $params = session_get_cookie_params();
        session_name('ci_session_' . $role);
        session_set_cookie_params(
            $params['lifetime'],
            $params['path'],
            $params['domain'],
            $params['secure'],
            TRUE // httponly
        );
        session_start();
        session_regenerate_id(TRUE);
    }

    /**
     * Private: Log user activity
     */
    private function _log_activity($user_type, $user_id, $action, $details = NULL) {
        $this->load->model('activity_log_model');
        
        $log_data = array(
            'user_type'  => $user_type,
            'user_id'    => $user_id,
            'action'     => $action,
            'details'    => $details,
            'ip_address' => $this->input->ip_address(),
            'created_at' => date('Y-m-d H:i:s')
        );

        $this->activity_log_model->create($log_data);
    }
}

/* End of file Auth.php */
/* Location: ./application/controllers/Auth.php */
