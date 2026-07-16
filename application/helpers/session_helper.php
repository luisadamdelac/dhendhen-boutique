<?php
/**
 * Session Helper - Role-based Session Management
 * Provides functions for checking user roles and permissions
 */

// Check if already loaded
if (!function_exists('load_scoped_session')) {

    /**
     * Load the CI session library scoped to the current role's own cookie,
     * so admin/staff/reseller/customer each get an independently isolated
     * session — logging into one role in a browser can never overwrite or
     * be overwritten by another role's session in the same browser.
     *
     * Scope is auto-detected from the resolved controller's directory
     * (admin/, staff/, reseller/, customer/) via the router, NOT the raw
     * URL — several customer routes are aliased without a "customer/"
     * prefix (e.g. /cart, /shop, /account), so the URL segment alone is
     * not reliable. Pass $scope explicitly for root-level controllers that
     * don't live in one of those directories (Auth, Review, Api, ...).
     *
     * Safe to call more than once per request: CI3's loader already
     * no-ops a second load->library('session', ...) call once CI_Session
     * exists, so only the first call in a request actually takes effect.
     */
    function load_scoped_session($scope = null) {
        $CI =& get_instance();
        $CI->load->config('dropsell'); // defines ROLE_ADMIN etc.; safe to call repeatedly

        if (!$scope) {
            $scope = rtrim($CI->router->fetch_directory(), '/');
        }

        $valid_roles = array(ROLE_ADMIN, ROLE_STAFF, ROLE_RESELLER, ROLE_CUSTOMER);
        $cookie_name = in_array($scope, $valid_roles, true) ? 'ci_session_' . $scope : 'ci_session';

        // NOTE: CI3's Session library reads this param as 'cookie_name', not
        // 'sess_cookie_name' (that key only applies to $config['sess_cookie_name']
        // in application/config/config.php). Passing the wrong key here silently
        // falls back to the generic 'ci_session' cookie for every role, which
        // breaks the whole point of per-role session isolation: every
        // Authenticated_Controller-based request would open the (empty) generic
        // session instead of the role's own, and immediately look logged out.
        $CI->load->library('session', array('cookie_name' => $cookie_name));
    }
}

if (!function_exists('is_logged_in')) {

    /**
     * Check if user is logged in
     */
    function is_logged_in() {
        $CI = &get_instance();
        return (bool) $CI->session->userdata('user_account_id');
    }

    /**
     * Get current user type (role)
     * Returns: admin, staff, reseller, customer, or FALSE
     */
    function get_user_type() {
        $CI = &get_instance();
        return $CI->session->userdata('user_type') ?: FALSE;
    }

    /**
     * Get current user ID (role-specific ID)
     * Returns: admin_id, staff_id, reseller_id, or customer_id
     */
    function get_user_id() {
        $CI = &get_instance();
        return $CI->session->userdata('user_id') ?: FALSE;
    }

    /**
     * Get current user account ID
     * Returns: user_account_id from user_account_tbl
     */
    function get_user_account_id() {
        $CI = &get_instance();
        return $CI->session->userdata('user_account_id') ?: FALSE;
    }

    /**
     * Get current user's email
     */
    function get_user_email() {
        $CI = &get_instance();
        return $CI->session->userdata('email') ?: '';
    }

    /**
     * Get current user's full name
     */
    function get_user_full_name() {
        $CI = &get_instance();
        return $CI->session->userdata('full_name') ?: '';
    }

    /**
     * Get current user's first name
     */
    function get_user_first_name() {
        $CI = &get_instance();
        return $CI->session->userdata('first_name') ?: '';
    }

    /**
     * Get current user's profile image — read fresh from the database
     * rather than trusting the cached session value. A photo can also be
     * set on this user's behalf by someone else (e.g. an admin uploading a
     * staff member's photo from Admin > Staff), and there's no way to push
     * that update into an already-open browser session — only a live DB
     * read stays accurate for both that case and the user's own uploads.
     */
    function get_user_profile_image() {
        $CI = &get_instance();

        $userType = $CI->session->userdata('user_type');
        $userId = $CI->session->userdata('user_id');
        if (empty($userType) || empty($userId)) {
            return '';
        }

        $tables = array(
            ROLE_ADMIN    => array('table' => ADMIN_TABLE, 'pk' => 'admin_id'),
            ROLE_STAFF    => array('table' => STAFF_TABLE, 'pk' => 'staff_id'),
            ROLE_RESELLER => array('table' => RESELLER_TABLE, 'pk' => 'reseller_id'),
            ROLE_CUSTOMER => array('table' => CUSTOMER_TABLE, 'pk' => 'customer_id'),
        );
        if (!isset($tables[$userType])) {
            return $CI->session->userdata('profile_image') ?: '';
        }

        $CI->load->database();
        $row = $CI->db->select('profile_image')
            ->where($tables[$userType]['pk'], $userId)
            ->get($tables[$userType]['table'])
            ->row_array();

        return !empty($row['profile_image']) ? $row['profile_image'] : '';
    }

    /**
     * Default avatar path used everywhere a profile_image isn't set.
     */
    function default_avatar_url() {
        $path = 'public/images/default-avatar.svg';
        $version = @filemtime(FCPATH . $path) ?: time();
        return $path . '?v=' . $version;
    }

    /**
     * Check if user is admin
     */
    function is_admin() {
        $CI = &get_instance();
        return $CI->session->userdata('user_type') === 'admin';
    }

    /**
     * Check if user is staff
     */
    function is_staff() {
        $CI = &get_instance();
        return $CI->session->userdata('user_type') === 'staff';
    }

    /**
     * Check if user is reseller
     */
    function is_reseller() {
        $CI = &get_instance();
        return $CI->session->userdata('user_type') === 'reseller';
    }

    /**
     * Check if user is customer
     */
    function is_customer() {
        $CI = &get_instance();
        return $CI->session->userdata('user_type') === 'customer';
    }

    /**
     * Redirect to login if not logged in
     */
    function require_login($redirect_url = NULL) {
        $CI = &get_instance();
        
        if (!is_logged_in()) {
            if ($redirect_url) {
                $CI->session->set_userdata('redirect_url', $redirect_url);
            }
            redirect('auth/login');
        }
    }

    /**
     * Require specific role
     */
    function require_role($required_role, $redirect_to = 'auth/login') {
        $CI = &get_instance();
        
        if (!is_logged_in()) {
            redirect($redirect_to);
        }

        $user_type = $CI->session->userdata('user_type');
        
        if ($user_type !== $required_role) {
            redirect($redirect_to);
        }
    }

    /**
     * Require any of the specified roles
     */
    function require_any_role($required_roles = array(), $redirect_to = 'auth/login') {
        $CI = &get_instance();
        
        if (!is_logged_in()) {
            redirect($redirect_to);
        }

        $user_type = $CI->session->userdata('user_type');
        
        if (!in_array($user_type, $required_roles)) {
            redirect($redirect_to);
        }
    }

    /**
     * Get the current request's IP address
     */
    function get_client_ip() {
        $CI = &get_instance();
        return $CI->input->ip_address();
    }

    /**
     * Get user login timestamp
     */
    function get_login_time() {
        $CI = &get_instance();
        return $CI->session->userdata('logged_in_at') ?: 0;
    }

    /**
     * Check if session has expired (based on timeout)
     */
    function is_session_expired() {
        $CI = &get_instance();
        $CI->load->config('dropsell');
        
        $login_time = $CI->session->userdata('logged_in_at');
        $timeout = $CI->config->item('session_timeout');
        
        if (!$login_time) {
            return TRUE;
        }
        
        return (time() - $login_time) > $timeout;
    }

    /**
     * Renew session timestamp
     */
    function renew_session() {
        $CI = &get_instance();
        $CI->session->set_userdata('logged_in_at', time());
    }

    /**
     * Get user session array
     */
    function get_user_session() {
        $CI = &get_instance();
        return array(
            'user_type'       => $CI->session->userdata('user_type'),
            'user_id'         => $CI->session->userdata('user_id'),
            'user_account_id' => $CI->session->userdata('user_account_id'),
            'email'           => $CI->session->userdata('email'),
            'first_name'      => $CI->session->userdata('first_name'),
            'last_name'       => $CI->session->userdata('last_name'),
            'full_name'       => $CI->session->userdata('full_name'),
            'profile_image'   => $CI->session->userdata('profile_image'),
            'logged_in_at'    => $CI->session->userdata('logged_in_at')
        );
    }
}

/* End of file session_helper.php */
/* Location: ./application/helpers/session_helper.php */
