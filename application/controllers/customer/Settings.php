<?php
class Settings extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper(['url', 'auth']);
        $this->load->database();
        load_scoped_session('customer');
        if (!ensure_logged_in()) {
            redirect('auth/login');
        }
        $this->load->config('dropsell');
        if ($this->session->userdata('user_type') !== ROLE_CUSTOMER) {
            deny_role_access();
        }
    }

    public function index() {
        $data['page_title'] = 'Settings';
        $this->load->view('customer/layouts/header', $data);
        $this->load->view('customer/settings/index', $data);
        $this->load->view('customer/layouts/footer', $data);
    }
}
