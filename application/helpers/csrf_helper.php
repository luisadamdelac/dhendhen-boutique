<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Lightweight session-backed CSRF token for forms outside the CI_Security
 * subclass mechanism (which this app's application/core/Authenticated_Controller.php
 * autoload already occupies via $config['subclass_prefix']).
 */
if (!function_exists('csrf_field')) {
    function csrf_field() {
        $CI =& get_instance();
        $token = $CI->session->userdata('csrf_token');
        if (!$token) {
            $token = bin2hex(random_bytes(16));
            $CI->session->set_userdata('csrf_token', $token);
        }
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}

if (!function_exists('csrf_token_valid')) {
    function csrf_token_valid() {
        $CI =& get_instance();
        $posted = $CI->input->post('csrf_token', TRUE);
        $session_token = $CI->session->userdata('csrf_token');

        $valid = $posted && $session_token && is_string($posted) && hash_equals($session_token, $posted);

        // Regenerate after every attempt so a token can't be replayed.
        $CI->session->unset_userdata('csrf_token');

        return $valid;
    }
}
