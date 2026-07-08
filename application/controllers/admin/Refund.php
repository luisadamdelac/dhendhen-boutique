<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Refund extends Authenticated_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_role('admin');
        $this->load->model(['Refund_model', 'Activity_log_model']);
    }

    /**
     * Refund list view
     */
    public function index() {
        // The DataTable on the list view handles search, sorting, and
        // pagination client-side, so the full refund set is fetched here.
        $data['refunds'] = $this->db->select('rr.*, o.order_id, c.first_name as customer_first_name, c.last_name as customer_last_name')
            ->from(REFUND_REQUEST_TABLE . ' rr')
            ->join(ORDER_TABLE . ' o', 'rr.order_id = o.order_id', 'left')
            ->join(CUSTOMER_TABLE . ' c', 'rr.customer_id = c.customer_id', 'left')
            ->order_by('rr.refund_id', 'DESC')
            ->get()->result_array();

        $data['total'] = count($data['refunds']);

        $data['stat_total']    = $this->db->count_all(REFUND_REQUEST_TABLE);
        $data['stat_pending']  = $this->db->from(REFUND_REQUEST_TABLE)->where('status', 'pending')->count_all_results();
        $data['stat_approved'] = $this->db->from(REFUND_REQUEST_TABLE)->where('status', 'approved')->count_all_results();
        $data['stat_rejected'] = $this->db->from(REFUND_REQUEST_TABLE)->where('status', 'rejected')->count_all_results();

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/refund/list', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * View refund details
     */
    public function view($refund_id = '') {
        if (empty($refund_id)) {
            redirect('admin/refund');
        }

        $refund = $this->db->select('rr.*, o.order_id, c.first_name as customer_first_name, c.last_name as customer_last_name, u.email')
            ->from(REFUND_REQUEST_TABLE . ' rr')
            ->join(ORDER_TABLE . ' o', 'rr.order_id = o.order_id', 'left')
            ->join(CUSTOMER_TABLE . ' c', 'rr.customer_id = c.customer_id', 'left')
            ->join(USER_ACCOUNT_TABLE . ' u', 'c.user_account_id = u.user_account_id', 'left')
            ->where('rr.refund_id', $refund_id)
            ->get()->row_array();

        if (!$refund) {
            $this->session->set_flashdata('error', 'Refund request not found');
            redirect('admin/refund');
        }

        $data['refund'] = $refund;
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/refund/view', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Approve refund: restores stock, reverses the reseller's commission for
     * the order (if it had already been released), and marks the order
     * return_refund — mirroring what apply_order_status_side_effects() does
     * for a direct order-status change.
     */
    public function approve($refund_id = '') {
        if (empty($refund_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $refund = $this->db->select('*')->from(REFUND_REQUEST_TABLE)->where('refund_id', $refund_id)->get()->row_array();
        if (!$refund) {
            echo json_encode(['success' => FALSE, 'message' => 'Refund request not found']);
            return;
        }

        if ($refund['status'] !== 'pending') {
            echo json_encode(['success' => FALSE, 'message' => 'This refund request has already been reviewed']);
            return;
        }

        $this->load->helper('order');
        $order = $this->db->where('order_id', $refund['order_id'])->get(ORDER_TABLE)->row_array();

        $this->db->trans_start();

        $this->db->update(REFUND_REQUEST_TABLE, [
            'status' => 'approved',
            'admin_remarks' => $this->input->post('admin_remarks') ?? '',
            'reviewed_by' => $this->user_id,
            'admin_id' => $this->user_id,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['refund_id' => $refund_id]);

        if ($order && $order['order_status'] !== 'return_refund' && $order['order_status'] !== 'cancelled') {
            $this->db->update(ORDER_TABLE, [
                'order_status' => 'return_refund',
                'updated_at' => date('Y-m-d H:i:s'),
            ], ['order_id' => $order['order_id']]);

            apply_order_status_side_effects($order, $order['order_status'], 'return_refund');
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => FALSE, 'message' => 'Failed to approve refund']);
            return;
        }

        require_once APPPATH . 'services/NotificationService.php';
        NotificationService::refundApproved($refund_id, $refund['customer_id'], $refund['order_id']);

        $this->Activity_log_model->log_activity(get_user_id(), 'admin', 'approve_refund', "Approved refund: $refund_id", get_client_ip());
        echo json_encode(['success' => TRUE, 'message' => 'Refund approved successfully']);
    }

    /**
     * Reject refund
     */
    public function reject($refund_id = '') {
        if (empty($refund_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $refund = $this->db->select('*')->from(REFUND_REQUEST_TABLE)->where('refund_id', $refund_id)->get()->row_array();
        if (!$refund) {
            echo json_encode(['success' => FALSE, 'message' => 'Refund request not found']);
            return;
        }

        if ($refund['status'] !== 'pending') {
            echo json_encode(['success' => FALSE, 'message' => 'This refund request has already been reviewed']);
            return;
        }

        $this->db->update(REFUND_REQUEST_TABLE, [
            'status' => 'rejected',
            'admin_remarks' => $this->input->post('admin_remarks') ?? '',
            'reviewed_by' => $this->user_id,
            'admin_id' => $this->user_id,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['refund_id' => $refund_id]);

        require_once APPPATH . 'services/NotificationService.php';
        NotificationService::create('customer', $refund['customer_id'],
            'Refund Request Rejected',
            'Your refund request for Order ID ' . $refund['order_id'] . ' was not approved.',
            'refund', $refund_id);

        $this->Activity_log_model->log_activity(get_user_id(), 'admin', 'reject_refund', "Rejected refund: $refund_id", get_client_ip());
        echo json_encode(['success' => TRUE, 'message' => 'Refund rejected successfully']);
    }

    /**
     * Get pending refunds count
     */
    public function pending_count() {
        $count = $this->db->from(REFUND_REQUEST_TABLE)->where('status', 'pending')->count_all_results();
        echo json_encode(['count' => $count]);
    }
}
?>
