<?php
class Addresses extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->config('dropsell');
        $this->load->helper(['url', 'form', 'auth', 'address']);
        $this->load->database();
        load_scoped_session('customer');
        if (!ensure_logged_in()) {
            redirect('auth/login');
        }
        if ($this->session->userdata('user_type') !== ROLE_CUSTOMER) {
            deny_role_access();
        }
        $this->load->model('Activity_log_model');
    }

    /**
     * My Addresses — list with the default address shown first.
     */
    public function index() {
        $data['page_title'] = 'My Addresses';
        $customer_id = $this->session->userdata('user_id');

        $data['addresses'] = $this->db->select('*')
            ->from(CUSTOMER_ADDRESS_TABLE)
            ->where('customer_id', $customer_id)
            ->order_by('is_default', 'DESC')
            ->order_by('address_id', 'DESC')
            ->get()->result_array();

        $this->load->view('customer/layouts/header', $data);
        $this->load->view('customer/addresses/index', $data);
        $this->load->view('customer/layouts/footer', $data);
    }

    /**
     * Add a new address.
     */
    public function add() {
        $data['page_title'] = 'Add New Address';
        $data['return'] = $this->input->get('return', TRUE);

        if ($this->input->method() === 'post') {
            $this->_validate_and_save(NULL, $this->input->post('return', TRUE));
            return;
        }

        $this->load->view('customer/layouts/header', $data);
        $this->load->view('customer/addresses/form', $data);
        $this->load->view('customer/layouts/footer', $data);
    }

    /**
     * Edit an existing address.
     */
    public function edit($address_id = '') {
        $data['page_title'] = 'Edit Address';
        $customer_id = $this->session->userdata('user_id');

        if (empty($address_id)) {
            redirect('addresses');
        }

        $address = $this->db->where('address_id', $address_id)
            ->where('customer_id', $customer_id)
            ->get(CUSTOMER_ADDRESS_TABLE)->row_array();

        if (!$address) {
            $this->session->set_flashdata('error', 'Address not found');
            redirect('addresses');
        }

        if ($this->input->method() === 'post') {
            $this->_validate_and_save($address_id);
            return;
        }

        $data['address'] = $address;
        $this->load->view('customer/layouts/header', $data);
        $this->load->view('customer/addresses/form', $data);
        $this->load->view('customer/layouts/footer', $data);
    }

    /**
     * Mark an address as the default one.
     */
    public function set_default($address_id = '') {
        $customer_id = $this->session->userdata('user_id');

        if (empty($address_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $address = $this->db->where('address_id', $address_id)
            ->where('customer_id', $customer_id)
            ->get(CUSTOMER_ADDRESS_TABLE)->row_array();

        if (!$address) {
            $this->session->set_flashdata('error', 'Address not found');
            redirect('addresses');
        }

        $this->db->trans_start();
        $this->db->where('customer_id', $customer_id)->update(CUSTOMER_ADDRESS_TABLE, ['is_default' => 0]);
        $this->db->where('address_id', $address_id)->update(CUSTOMER_ADDRESS_TABLE, ['is_default' => 1]);
        $this->db->trans_complete();

        $this->session->set_flashdata('success', 'Default address updated');
        redirect('addresses');
    }

    /**
     * Delete a saved address. If the deleted address was the default and
     * other addresses remain, the most recently added one becomes default.
     */
    public function delete($address_id = '') {
        $customer_id = $this->session->userdata('user_id');

        if (empty($address_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $address = $this->db->where('address_id', $address_id)
            ->where('customer_id', $customer_id)
            ->get(CUSTOMER_ADDRESS_TABLE)->row_array();

        if (!$address) {
            $this->session->set_flashdata('error', 'Address not found');
            redirect('addresses');
        }

        $this->db->trans_start();
        $this->db->where('address_id', $address_id)->delete(CUSTOMER_ADDRESS_TABLE);

        if ($address['is_default']) {
            $next = $this->db->where('customer_id', $customer_id)
                ->order_by('address_id', 'DESC')
                ->limit(1)
                ->get(CUSTOMER_ADDRESS_TABLE)->row_array();

            if ($next) {
                $this->db->where('address_id', $next['address_id'])->update(CUSTOMER_ADDRESS_TABLE, ['is_default' => 1]);
            }
        }

        $this->db->trans_complete();

        $this->Activity_log_model->log_activity($customer_id, 'customer', 'address_deleted', trim(implode(', ', array_filter([$address['municipality'] ?? '', $address['barangay'] ?? '']))), $this->input->ip_address());

        $this->session->set_flashdata('success', 'Address deleted successfully');
        redirect('addresses');
    }

    /**
     * Validate and save address data (used by both add() and edit()).
     * $return is an optional whitelisted page to send the customer back to
     * once saved (e.g. 'checkout', when they added an address mid-checkout).
     */
    private function _validate_and_save($address_id = NULL, $return = NULL) {
        $customer_id = $this->session->userdata('user_id');
        $return_url = $return === 'checkout' ? 'checkout' : 'addresses';
        $is_ajax = $this->input->is_ajax_request();

        $this->load->library('form_validation');
        $this->form_validation->set_rules('label', 'Label', 'trim|max_length[50]');
        $this->form_validation->set_rules('street', 'Street', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('municipality', 'Municipality', 'required|trim|max_length[50]');
        $this->form_validation->set_rules('barangay', 'Barangay', 'required|trim|max_length[50]');

        if ($this->form_validation->run() === FALSE) {
            if ($is_ajax) {
                echo json_encode(['success' => FALSE, 'message' => strip_tags(validation_errors(' ', ' '))]);
                return;
            }
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            redirect($address_id ? "addresses/edit/{$address_id}" : 'addresses/add' . ($return ? '?return=' . $return : ''));
        }

        $municipality = $this->input->post('municipality', TRUE);
        $barangay = $this->input->post('barangay', TRUE);

        if (!is_valid_oriental_mindoro_address($municipality, $barangay)) {
            $message = 'Please select a valid Municipality and Barangay within Oriental Mindoro.';
            if ($is_ajax) {
                echo json_encode(['success' => FALSE, 'message' => $message]);
                return;
            }
            $this->session->set_flashdata('error', $message);
            redirect($address_id ? "addresses/edit/{$address_id}" : 'addresses/add' . ($return ? '?return=' . $return : ''));
        }

        $address_data = [
            'label' => $this->input->post('label', TRUE) ?: 'Home',
            'street' => $this->input->post('street', TRUE),
            'barangay' => $barangay,
            'municipality' => $municipality,
            'province' => 'Oriental Mindoro',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($address_id) {
            $this->db->where('address_id', $address_id)
                ->where('customer_id', $customer_id)
                ->update(CUSTOMER_ADDRESS_TABLE, $address_data);
            $message = 'Address updated successfully';
            $this->Activity_log_model->log_activity($customer_id, 'customer', 'address_updated', $municipality . ', ' . $barangay, $this->input->ip_address());
        } else {
            $address_data['customer_id'] = $customer_id;
            $address_data['created_at'] = date('Y-m-d H:i:s');

            // First saved address is automatically the default.
            $existing_count = $this->db->where('customer_id', $customer_id)->count_all_results(CUSTOMER_ADDRESS_TABLE);
            $address_data['is_default'] = $existing_count === 0 ? 1 : 0;

            $this->db->insert(CUSTOMER_ADDRESS_TABLE, $address_data);
            $address_id = $this->db->insert_id();
            $message = 'Address added successfully';
            $this->Activity_log_model->log_activity($customer_id, 'customer', 'address_added', $municipality . ', ' . $barangay, $this->input->ip_address());
        }

        if ($is_ajax) {
            $address_data['address_id'] = $address_id;
            echo json_encode(['success' => TRUE, 'message' => $message, 'address' => $address_data]);
            return;
        }

        $this->session->set_flashdata('success', $message);
        redirect($return_url);
    }
}
