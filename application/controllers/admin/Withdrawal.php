<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Withdrawal extends Authenticated_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_role('admin');
        $this->load->model(['Withdrawal_model', 'Activity_log_model']);
    }

    /**
     * Withdrawal list view
     */
    public function index() {
        $data['page_title'] = 'Withdrawal Requests';
        $data['withdrawals'] = $this->db->select('w.*, r.first_name, r.last_name, r.business_name')
            ->from(WITHDRAWAL_TABLE . ' w')
            ->join(RESELLER_TABLE . ' r', 'w.reseller_id = r.reseller_id')
            ->order_by('w.withdrawal_id', 'DESC')
            ->get()->result_array();

        $data['total'] = count($data['withdrawals']);

        $data['pending_count']  = $this->db->from(WITHDRAWAL_TABLE)->where('status', 'pending')->count_all_results();
        $data['approved_count'] = $this->db->from(WITHDRAWAL_TABLE)->where('status', 'approved')->count_all_results();
        $data['rejected_count'] = $this->db->from(WITHDRAWAL_TABLE)->where('status', 'rejected')->count_all_results();
        $data['total_amount']   = (float) ($this->db->select_sum('amount')->from(WITHDRAWAL_TABLE)->get()->row()->amount ?? 0);

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/withdrawal/list', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * View withdrawal details
     */
    public function view($withdrawal_id = '') {
        if (empty($withdrawal_id)) {
            redirect('admin/withdrawal');
        }

        $withdrawal = $this->db->select('w.*, r.first_name, r.last_name, r.business_name, u.email')
            ->from(WITHDRAWAL_TABLE . ' w')
            ->join(RESELLER_TABLE . ' r', 'w.reseller_id = r.reseller_id')
            ->join(USER_ACCOUNT_TABLE . ' u', 'r.user_account_id = u.user_account_id')
            ->where('w.withdrawal_id', $withdrawal_id)
            ->get()->row_array();

        if (!$withdrawal) {
            $this->session->set_flashdata('error', 'Withdrawal request not found');
            redirect('admin/withdrawal');
        }

        $data['withdrawal'] = $withdrawal;
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/withdrawal/view', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Approve withdrawal
     */
    public function approve($withdrawal_id = '') {
        if (empty($withdrawal_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $withdrawal = $this->db->select('*')->from(WITHDRAWAL_TABLE)->where('withdrawal_id', $withdrawal_id)->get()->row_array();
        if (!$withdrawal) {
            echo json_encode(['success' => FALSE, 'message' => 'Withdrawal request not found']);
            return;
        }

        if ((int) $withdrawal['otp_verified'] !== 1) {
            echo json_encode(['success' => FALSE, 'message' => 'This withdrawal cannot be approved until the reseller verifies their OTP']);
            return;
        }

        $scheduled_date = $this->input->post('scheduled_date') ?: ($withdrawal['scheduled_date'] ?? date('Y-m-d'));

        $this->db->update(WITHDRAWAL_TABLE, [
            'status' => 'approved',
            'approved_by' => $this->user_id,
            'approved_at' => date('Y-m-d H:i:s'),
            'scheduled_date' => $scheduled_date
        ], ['withdrawal_id' => $withdrawal_id]);

        require_once APPPATH . 'services/NotificationService.php';
        NotificationService::withdrawalApproved($withdrawal_id, $withdrawal['reseller_id'], $withdrawal['amount']);

        $this->Activity_log_model->log_activity(get_user_id(), 'admin', 'approve_withdrawal', "Approved withdrawal: $withdrawal_id", get_client_ip());
        echo json_encode(['success' => TRUE, 'message' => 'Withdrawal approved successfully']);
    }

    /**
     * Reject withdrawal
     */
    public function reject($withdrawal_id = '') {
        if (empty($withdrawal_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $withdrawal = $this->db->select('*')->from(WITHDRAWAL_TABLE)->where('withdrawal_id', $withdrawal_id)->get()->row_array();
        if (!$withdrawal) {
            echo json_encode(['success' => FALSE, 'message' => 'Withdrawal request not found']);
            return;
        }

        $this->db->trans_start();

        // Funds are only held (deducted) once the reseller verifies their
        // OTP — an unverified request never touched commission_balance, so
        // refunding it here would incorrectly credit money never taken.
        if ((int) $withdrawal['otp_verified'] === 1) {
            $this->db->set('commission_balance', 'commission_balance + ' . (float) $withdrawal['amount'], FALSE)
                ->where('reseller_id', $withdrawal['reseller_id'])
                ->update(RESELLER_TABLE);
        }

        $this->db->update(WITHDRAWAL_TABLE, [
            'status' => 'rejected',
            'admin_id' => $this->user_id,
            'rejection_reason' => $this->input->post('admin_remarks') ?? ''
        ], ['withdrawal_id' => $withdrawal_id]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => FALSE, 'message' => 'Failed to reject withdrawal']);
        } else {
            $this->Activity_log_model->log_activity(get_user_id(), 'admin', 'reject_withdrawal', "Rejected withdrawal: $withdrawal_id", get_client_ip());
            echo json_encode(['success' => TRUE, 'message' => 'Withdrawal rejected successfully']);
        }
    }

    /**
     * Mark as processed
     */
    public function mark_processed($withdrawal_id = '') {
        if (empty($withdrawal_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $withdrawal = $this->db->select('*')->from(WITHDRAWAL_TABLE)->where('withdrawal_id', $withdrawal_id)->get()->row_array();
        if (!$withdrawal) {
            echo json_encode(['success' => FALSE, 'message' => 'Withdrawal request not found']);
            return;
        }

        $this->db->trans_start();

        $this->db->update(WITHDRAWAL_TABLE, [
            'status' => 'completed',
            'payment_reference' => $this->input->post('payment_reference') ?? NULL
        ], ['withdrawal_id' => $withdrawal_id]);

        $this->load->model('reseller_model');
        $this->reseller_model->update_withdrawn_amount($withdrawal['reseller_id'], (float) $withdrawal['amount']);

        // Tag the specific commission rows this payout actually covers as
        // 'withdrawn' (oldest released first) — otherwise commission_transaction_tbl
        // rows stay stuck at 'released' forever, so the "Withdrawn"/"Paid" stats
        // (reseller Commissions page, admin Commission Stats dashboard) never
        // move off ₱0.00 even after real payouts go out.
        $remaining = (float) $withdrawal['amount'];
        $released_rows = $this->db->select('commission_id, amount')
            ->from(COMMISSIONS_TABLE)
            ->where('reseller_id', $withdrawal['reseller_id'])
            ->where('status', 'released')
            ->order_by('released_at', 'ASC')
            ->order_by('commission_id', 'ASC')
            ->get()->result_array();

        foreach ($released_rows as $row) {
            if ($remaining < (float) $row['amount']) {
                break;
            }
            $this->db->where('commission_id', $row['commission_id'])->update(COMMISSIONS_TABLE, [
                'status' => 'withdrawn',
                'withdrawal_id' => $withdrawal_id,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $remaining -= (float) $row['amount'];
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => FALSE, 'message' => 'Failed to process withdrawal. Please try again.']);
            return;
        }

        require_once APPPATH . 'services/NotificationService.php';
        NotificationService::create('reseller', $withdrawal['reseller_id'],
            'Withdrawal Sent via GCash',
            'Your withdrawal of ₱' . number_format($withdrawal['amount'], 2) . ' has been sent to your GCash account.',
            'withdrawal', $withdrawal_id);

        $this->Activity_log_model->log_activity(get_user_id(), 'admin', 'process_withdrawal', "Processed withdrawal: $withdrawal_id", get_client_ip());
        echo json_encode(['success' => TRUE, 'message' => 'Withdrawal marked as processed']);
    }

    /**
     * Get pending withdrawals count
     */
    public function pending_count() {
        $count = $this->db->from(WITHDRAWAL_TABLE)->where('status', 'pending')->count_all_results();
        echo json_encode(['count' => $count]);
    }
}
?>
