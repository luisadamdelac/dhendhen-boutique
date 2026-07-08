<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin management of the two (or more) physical branches that back the
 * centralized inventory's per-branch stock tracking.
 */
class Branches extends Authenticated_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_role('admin');
        $this->load->library('form_validation');
        $this->load->helper('address');
    }

    public function index() {
        redirect('admin/inventory');
    }

    public function save() {
        if ($this->input->method() !== 'post') {
            redirect('admin/branches');
        }

        $this->form_validation->set_rules('branch_name', 'Branch Name', 'required|trim');
        $this->form_validation->set_rules('city', 'City', 'required|trim');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            redirect('admin/branches');
        }

        $city = $this->input->post('city', TRUE);
        $barangay = $this->input->post('barangay', TRUE);
        if (!empty($barangay) && !is_valid_oriental_mindoro_address($city, $barangay)) {
            $this->session->set_flashdata('error', 'Please select a valid City/Municipality and Barangay within Oriental Mindoro.');
            redirect('admin/branches');
        }

        $branch_id = (int) $this->input->post('branch_id');
        $data = [
            'branch_name' => $this->input->post('branch_name', TRUE),
            'street' => $this->input->post('street', TRUE),
            'barangay' => $this->input->post('barangay', TRUE),
            'city' => $this->input->post('city', TRUE),
            'phone_number' => $this->input->post('phone_number', TRUE),
            'status' => $this->input->post('status', TRUE) === 'inactive' ? 'inactive' : 'active',
        ];

        if ($branch_id) {
            $this->db->where('branch_id', $branch_id)->update(BRANCHES_TABLE, $data);
            $this->session->set_flashdata('success', 'Branch updated successfully');
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert(BRANCHES_TABLE, $data);
            $this->session->set_flashdata('success', 'Branch added successfully');
        }

        redirect('admin/inventory');
    }

    public function delete($branch_id = '') {
        if (empty($branch_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $in_use = $this->db->from(INVENTORY_BATCH_TABLE)->where('branch_id', $branch_id)->where('status', 'active')->where('remaining_quantity >', 0)->count_all_results();
        if ($in_use > 0) {
            echo json_encode(['success' => FALSE, 'message' => 'Cannot delete a branch that still holds stock']);
            return;
        }

        $this->db->where('branch_id', $branch_id)->delete(BRANCHES_TABLE);
        echo json_encode(['success' => TRUE, 'message' => 'Branch deleted']);
    }

    /**
     * One-click deactivate/reactivate — flips branches.status without
     * needing to reopen the edit modal just to change one field.
     */
    public function toggle_status($branch_id = '') {
        if (empty($branch_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $branch = $this->db->select('branch_id, branch_name, status')->from(BRANCHES_TABLE)
            ->where('branch_id', $branch_id)->get()->row_array();
        if (!$branch) {
            echo json_encode(['success' => FALSE, 'message' => 'Branch not found']);
            return;
        }

        $new_status = $branch['status'] === 'active' ? 'inactive' : 'active';
        $this->db->where('branch_id', $branch_id)->update(BRANCHES_TABLE, ['status' => $new_status]);

        echo json_encode(['success' => TRUE, 'message' => 'Branch ' . ($new_status === 'active' ? 'reactivated' : 'deactivated') . ' successfully', 'status' => $new_status]);
    }
}
?>
