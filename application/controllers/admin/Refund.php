<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Refund extends Authenticated_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_role('admin');
        $this->load->model(['Refund_model', 'Activity_log_model']);
        require_once APPPATH . 'services/ImageService.php';
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
     * Approve refund: reverses the reseller's commission for the order (if
     * it had already been released) and marks the order return_refund —
     * mirroring what apply_order_status_side_effects() does for a direct
     * order-status change, EXCEPT stock is deliberately not restored here
     * ($restock = FALSE below). Restocking on approval — before the item has
     * necessarily made it back — would credit stock the store doesn't
     * actually have if the customer never sends it back, while the refund
     * still goes out. Stock is only restored once complete() confirms the
     * item was physically received back (see restore_order_stock()).
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

            apply_order_status_side_effects($order, $order['order_status'], 'return_refund', FALSE);
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
     * Mark an approved refund as actually paid out — the step that was
     * missing before: approve() only does inventory/commission bookkeeping,
     * it never confirms the item came back in sellable condition or that
     * money actually left the store. Both are required here, same as the
     * withdrawal payout flow's GCash reference requirement.
     */
    public function complete($refund_id = '') {
        if (empty($refund_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $refund = $this->db->select('*')->from(REFUND_REQUEST_TABLE)->where('refund_id', $refund_id)->get()->row_array();
        if (!$refund) {
            echo json_encode(['success' => FALSE, 'message' => 'Refund request not found']);
            return;
        }

        if ($refund['status'] !== 'approved') {
            echo json_encode(['success' => FALSE, 'message' => 'This refund must be approved before it can be marked as refunded']);
            return;
        }

        if ($this->input->post('item_received') !== '1') {
            echo json_encode(['success' => FALSE, 'message' => 'Please confirm the item was received back in good condition.']);
            return;
        }

        $payment_reference = trim((string) $this->input->post('payment_reference', TRUE));
        if (!preg_match('/^\d{13}$/', $payment_reference)) {
            echo json_encode(['success' => FALSE, 'message' => 'GCash Reference Number is required and must be exactly 13 digits.']);
            return;
        }

        if (empty($_FILES['payment_proof']['name'])) {
            echo json_encode(['success' => FALSE, 'message' => 'Please attach proof of payment (a screenshot of the GCash send confirmation).']);
            return;
        }

        $upload_path = FCPATH . 'public/uploads/refunds/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, TRUE);
        }

        $this->load->library('upload', [
            'upload_path'   => $upload_path,
            'allowed_types' => 'jpg|jpeg|png|webp',
            'max_size'      => 5120,
            'encrypt_name'  => TRUE,
        ]);

        if (!$this->upload->do_upload('payment_proof')) {
            echo json_encode(['success' => FALSE, 'message' => 'Proof of payment upload failed: ' . $this->upload->display_errors('', '')]);
            return;
        }

        $uploaded_name = $this->upload->data('file_name');
        $file_name = ImageService::convertToWebp($upload_path, $uploaded_name) ?? $uploaded_name;
        $payment_proof = 'public/uploads/refunds/' . $file_name;

        $this->db->trans_start();

        // The item is confirmed physically back only now — restock here
        // rather than at approve(), so the store never credits stock it
        // hasn't actually received (see the note on approve() above).
        $this->load->helper('order');
        restore_order_stock($refund['order_id']);

        $this->db->update(REFUND_REQUEST_TABLE, [
            'status' => 'completed',
            'item_received' => 1,
            'payment_reference' => $payment_reference,
            'payment_proof' => $payment_proof,
            'completed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['refund_id' => $refund_id]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            if (file_exists(FCPATH . $payment_proof)) {
                @unlink(FCPATH . $payment_proof);
            }
            echo json_encode(['success' => FALSE, 'message' => 'Failed to mark refund as paid. Please try again.']);
            return;
        }

        require_once APPPATH . 'services/NotificationService.php';
        NotificationService::refundCompleted($refund_id, $refund['customer_id'], $refund['order_id'], (float) $refund['amount']);

        $this->Activity_log_model->log_activity(get_user_id(), 'admin', 'complete_refund', "Marked refund as paid: $refund_id, ref: $payment_reference", get_client_ip());
        echo json_encode(['success' => TRUE, 'message' => 'Refund marked as paid successfully']);
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
