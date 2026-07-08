<?php
/**
 * Profile Redirect Controller
 * Handles generic /profile URL and redirects to role-specific profile
 */

class ProfileRedirect extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->config('dropsell');
        $this->load->helper('url');
    }

    /**
     * Which role's session cookie (if any) is present on this request.
     * Each role now has its own isolated session, so there's no single
     * "current user" the way there used to be with one shared session —
     * this just picks the first one found (priority order below).
     */
    private function _active_role() {
        foreach (array(ROLE_ADMIN, ROLE_STAFF, ROLE_RESELLER, ROLE_CUSTOMER) as $role) {
            if ($this->input->cookie('ci_session_' . $role, TRUE)) {
                return $role;
            }
        }
        return NULL;
    }

    /**
     * Default action - redirect to appropriate profile based on user role
     */
    public function index() {
        $role = $this->_active_role();

        switch ($role) {
            case ROLE_ADMIN:
                redirect('admin/profile');
                break;
            case ROLE_STAFF:
                redirect('staff/profile');
                break;
            case ROLE_RESELLER:
                redirect('reseller/profile');
                break;
            case ROLE_CUSTOMER:
                redirect('customer/account');
                break;
            default:
                redirect('auth/login');
        }
    }

    /**
     * Handle profile actions with parameter
     */
    public function action($action = NULL) {
        $role = $this->_active_role();

        switch ($role) {
            case ROLE_ADMIN:
                redirect('admin/profile/' . $action);
                break;
            case ROLE_STAFF:
                redirect('staff/profile/' . $action);
                break;
            case ROLE_RESELLER:
                redirect('reseller/profile/' . $action);
                break;
            case ROLE_CUSTOMER:
                redirect('customer/account/' . $action);
                break;
            default:
                redirect('auth/login');
        }
    }
}

/* End of file ProfileRedirect.php */
/* Location: ./application/controllers/core/ProfileRedirect.php */
