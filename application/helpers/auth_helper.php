<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Shared login-session check used by every customer/admin/staff/reseller
 * controller. Falls back to the "remember me" token (auth_tbl) when there's
 * no active session, so a valid remember_token cookie silently re-logs the
 * visitor in instead of bouncing them to the login form.
 */
if (!function_exists('ensure_logged_in')) {
    function ensure_logged_in() {
        $CI =& get_instance();
        $CI->load->database();

        if ($CI->session->userdata('user_account_id')) {
            return TRUE;
        }

        $cookie = $CI->input->cookie('remember_token', TRUE);
        if (empty($cookie) || strpos($cookie, ':') === FALSE) {
            return FALSE;
        }

        [$user_account_id, $token] = explode(':', $cookie, 2);
        $user_account_id = (int) $user_account_id;
        $token_hash = hash('sha256', $token);

        $row = $CI->db->where('user_account_id', $user_account_id)
            ->where('type', 'remember_me')
            ->where('token_hash', $token_hash)
            ->where('revoked', 0)
            ->group_start()
                ->where('expires_at', NULL)
                ->or_where('expires_at >', date('Y-m-d H:i:s'))
            ->group_end()
            ->get('auth_tokens')->row_array();

        if (!$row) {
            return FALSE;
        }

        $CI->load->model('user_model');
        $account = $CI->user_model->get_by_id($user_account_id);
        $role = $CI->user_model->get_user_role($user_account_id);

        if (!$account || $account['status'] !== 'active' || !$role) {
            return FALSE;
        }

        $role_id = $CI->user_model->get_role_id($user_account_id, $role);
        $profile = $CI->user_model->get_user_profile($user_account_id, $role);

        if (!$role_id || !$profile) {
            return FALSE;
        }

        $CI->session->sess_regenerate();
        $CI->session->set_userdata([
            'user_type' => $role,
            'user_id' => $role_id,
            'user_account_id' => $account['user_account_id'],
            'email' => $account['email'],
            'first_name' => $profile['first_name'] ?? '',
            'last_name' => $profile['last_name'] ?? '',
            'full_name' => trim(($profile['first_name'] ?? '') . ' ' . ($profile['middle_name'] ?? '') . ' ' . ($profile['last_name'] ?? '')),
            'profile_image' => $profile['profile_image'] ?? '',
            'logged_in_at' => time(),
            'is_logged_in' => TRUE,
        ]);

        return TRUE;
    }
}

/**
 * Maps a role to its dashboard route. Single source of truth so Auth::_redirect_to_dashboard(),
 * ProfileRedirect, and every role-guard check land on the same URL for a given role.
 */
if (!function_exists('role_dashboard_url')) {
    function role_dashboard_url($role) {
        switch ($role) {
            case ROLE_ADMIN:
                return 'admin/dashboard';
            case ROLE_STAFF:
                return 'staff/dashboard';
            case ROLE_RESELLER:
                return 'reseller/dashboard';
            case ROLE_CUSTOMER:
                return 'customer/shop';
            default:
                return 'auth/login';
        }
    }
}

/**
 * A logged-in user hit a page that belongs to a different role (e.g. a customer
 * session opened an /admin/* URL). Instead of dead-ending on a blank 403 error
 * page, send them back to the dashboard that matches their own session so the
 * workflow stays coherent - this is what every role-guard check should call.
 */
if (!function_exists('deny_role_access')) {
    function deny_role_access($message = 'Access denied: that page is not available for your account.') {
        $CI =& get_instance();
        $CI->session->set_flashdata('error', $message);
        redirect(role_dashboard_url($CI->session->userdata('user_type')));
    }
}
