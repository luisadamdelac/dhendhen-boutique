<?php
/**
 * Authenticated Base Controller
 * All role-specific controllers should extend this class
 * Provides automatic authentication checks and role validation
 */

class Authenticated_Controller extends CI_Controller {

    protected $user_type;
    protected $user_id;
    protected $user_account_id;
    protected $user_email;
    protected $user_full_name;

    public function __construct() {
        parent::__construct();

        $this->load->config('dropsell');
        $this->load->helper('session');
        load_scoped_session(); // isolate this role's session from the other 3
        $this->load->model('activity_log_model');

        // Check if user is logged in
        $this->_require_login();

        // Load session data into properties
        $this->user_type = $this->session->userdata('user_type');
        $this->user_id = $this->session->userdata('user_id');
        $this->user_account_id = $this->session->userdata('user_account_id');
        $this->user_email = $this->session->userdata('email');
        $this->user_full_name = $this->session->userdata('full_name');

        // Make current page available to all views, including header/sidebar.
        // Provide safe defaults so the shared admin layout can render even when
        // a controller does not set every view variable explicitly.
        $this->load->vars($this->get_default_view_data());
    }

    /**
     * Private: Require login
     */
    private function _require_login() {
        $this->load->helper('auth');
        if (!ensure_logged_in()) {
            $this->session->set_userdata('redirect_url', current_url());
            redirect('auth/login');
        }
    }

    /**
     * Require specific role
     */
    protected function require_role($required_role) {
        if ($this->user_type !== $required_role) {
            $this->load->helper('auth');
            deny_role_access();
        }
    }

    /**
     * Require any of multiple roles
     */
    protected function require_any_role($required_roles = array()) {
        if (!in_array($this->user_type, $required_roles)) {
            $this->load->helper('auth');
            deny_role_access();
        }
    }

    /**
     * Log activity
     */
    protected function log_activity($action, $entity_type = NULL, $entity_id = NULL, $details = NULL) {
        $log_data = array(
            'user_type'  => $this->user_type,
            'user_id'    => $this->user_id,
            'action'     => $action,
            'entity_type'=> $entity_type,
            'entity_id'  => $entity_id,
            'details'    => $details,
            'ip_address' => $this->input->ip_address()
        );

        $this->activity_log_model->create($log_data);
    }

    /**
     * Set view data
     */
    protected function get_current_page() {
        $page = $this->uri->segment(2);

        if (empty($page) && $this->uri->segment(1) === 'admin') {
            return 'dashboard';
        }

        return $page ?: '';
    }

    protected function get_default_view_data($extra = array()) {
        $defaults = array(
            'user_type' => $this->user_type,
            'user_id' => $this->user_id,
            'user_email' => $this->user_email,
            'user_full_name' => $this->user_full_name,
            'page_title' => '',
            'current_page' => $this->get_current_page(),
            'low_stock_products' => [],
            'pending_orders_notifications' => 0,
            'pending_resellers' => 0,
            'pending_withdrawals' => 0,
        );

        // These sidebar/tab badges are only meaningful for admins, and this
        // runs on every admin page load (constructor + set_view_data), so
        // computing them here — instead of in every individual controller —
        // is what keeps the badges from going stale as pages are added.
        if ($this->user_type === 'admin') {
            $defaults['pending_resellers'] = (int) $this->db
                ->where('status', 'pending')
                ->count_all_results(RESELLER_TABLE);

            // Only count withdrawals the admin can actually act on right now
            // (reseller has verified their OTP) — otherwise the badge would
            // flag requests that are still waiting on the reseller, which
            // would look like a bug ("why is there a badge with nothing to approve").
            $defaults['pending_withdrawals'] = (int) $this->db
                ->where('status', 'pending')
                ->where('otp_verified', 1)
                ->count_all_results(WITHDRAWAL_TABLE);
        }

        return array_merge($defaults, $extra);
    }

    protected function set_view_data($extra = array()) {
        $data = $this->get_default_view_data($extra);
        $this->load->vars($data);
        return $data;
    }
}

/* End of file Authenticated_Controller.php */
/* Location: ./application/core/Authenticated_Controller.php */
