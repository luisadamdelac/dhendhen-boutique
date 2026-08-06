<?php
class Refunds extends CI_Controller {
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
        require_once APPPATH . 'services/ImageService.php';
    }

    public function index() {
        $data['page_title'] = 'My Refund Requests';
        $customer_id = $this->session->userdata('user_id');

        $data['refunds'] = $this->db->select('rr.*, o.order_number')
            ->from(REFUND_REQUEST_TABLE . ' rr')
            ->join(ORDER_TABLE . ' o', 'rr.order_id = o.order_id', 'left')
            ->where('rr.customer_id', $customer_id)
            ->order_by('rr.refund_id', 'DESC')
            ->get()->result_array();

        // Delivered orders that don't already have a refund request.
        $already_requested = array_column($data['refunds'], 'order_id');
        $eligible_query = $this->db->select('order_id, order_number, total_amount')
            ->from(ORDER_TABLE)
            ->where('customer_id', $customer_id)
            ->where('order_status', 'delivered');
        if (!empty($already_requested)) {
            $eligible_query->where_not_in('order_id', $already_requested);
        }
        $data['eligible_orders'] = $eligible_query->order_by('order_id', 'DESC')->get()->result_array();

        $this->load->view('customer/layouts/header', $data);
        $this->load->view('customer/refunds/index', $data);
        $this->load->view('customer/layouts/footer', $data);
    }

    /**
     * Submit a refund request for a delivered order. Only one request per
     * order is allowed; approval/restocking is handled by admin/Refund.
     */
    public function submit() {
        if ($this->input->method() !== 'post') {
            redirect('refund');
        }

        $customer_id = $this->session->userdata('user_id');
        $order_id = (int) $this->input->post('order_id', TRUE);

        $order = $this->db->where('order_id', $order_id)
            ->where('customer_id', $customer_id)
            ->where('order_status', 'delivered')
            ->get(ORDER_TABLE)->row_array();

        if (!$order) {
            $this->session->set_flashdata('error', 'This order is not eligible for a refund.');
            redirect('refund');
        }

        $existing = $this->db->where('order_id', $order_id)->get(REFUND_REQUEST_TABLE)->row_array();
        if ($existing) {
            $this->session->set_flashdata('error', 'A refund request already exists for this order.');
            redirect('refund');
        }

        $this->load->library('form_validation');
        $this->form_validation->set_rules('reason', 'Reason', 'required|trim|max_length[500]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            redirect('refund');
        }

        // --- Evidence upload (required) ---
        if (empty($_FILES['evidence']['name'])) {
            $this->session->set_flashdata('error', 'Please upload a photo of the item as evidence for your refund request.');
            redirect('refund');
        }

        $upload_path = FCPATH . 'uploads/refunds/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, TRUE);
        }

        $this->load->library('upload', [
            'upload_path'   => $upload_path,
            'allowed_types' => 'jpg|jpeg|png|webp',
            'max_size'      => 5120,
            'encrypt_name'  => TRUE,
        ]);

        if (!$this->upload->do_upload('evidence')) {
            $this->session->set_flashdata('error', 'Evidence upload failed: ' . $this->upload->display_errors('', ''));
            redirect('refund');
        }

        $upload_data = $this->upload->data();
        $file_name   = ImageService::convertToWebp($upload_path, $upload_data['file_name']) ?? $upload_data['file_name'];
        $image_path  = 'uploads/refunds/' . $file_name;

        $this->db->insert(REFUND_REQUEST_TABLE, [
            'refund_number' => 'RF' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
            'order_id' => $order_id,
            'customer_id' => $customer_id,
            'reseller_id' => $order['reseller_id'],
            'reason' => $this->input->post('reason', TRUE),
            'images' => $image_path,
            'amount' => $order['total_amount'],
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        require_once APPPATH . 'services/NotificationService.php';
        NotificationService::refundSubmitted($this->db->insert_id(), $customer_id, $order_id);

        $this->session->set_flashdata('success', 'Your refund request has been submitted for review.');
        redirect('refund');
    }
}
