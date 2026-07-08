<?php
class Landing extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
    }

    public function index() {
        $data['page_title'] = 'Welcome';
        $this->load->view('landing/landing_page', $data);
    }

    public function landing_page() {
        $this->index();
    }
}
