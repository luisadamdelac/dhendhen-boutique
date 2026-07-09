<?php
class Withdrawals extends Authenticated_Controller {
    public function __construct() {
        parent::__construct();
        $this->require_role(ROLE_RESELLER);
        $this->load->model(['reseller_model', 'activity_log_model', 'settings_model']);
        $this->load->library('form_validation');
        require_once APPPATH . 'services/EmailQueueService.php';
        require_once APPPATH . 'services/CommissionService.php';
    }

    public function index() {
        $this->_expire_stale_otp_requests($this->user_id);

        $data = $this->set_view_data();
        $data['page_title'] = 'Reseller Withdrawals';

        $reseller = $this->reseller_model->get_by_id($this->user_id);
        $data['available_balance'] = $reseller['commission_balance'] ?? 0;
        $data['total_withdrawn'] = $reseller['total_withdrawn'] ?? 0;
        $data['next_schedule_date'] = $this->settings_model->get_next_withdrawal_schedule_date();
        $data['saved_gcash_number'] = $reseller['gcash_number'] ?? '';
        $data['saved_gcash_name'] = $reseller['gcash_name'] ?? '';

        $data['withdrawals'] = $this->db->select('*')
            ->from(WITHDRAWAL_TABLE)
            ->where('reseller_id', $this->user_id)
            ->order_by('withdrawal_id', 'DESC')
            ->get()->result_array();

        $this->load->view('reseller/layouts/header', $data);
        $this->load->view('reseller/withdrawals/index', $data);
        $this->load->view('reseller/layouts/footer', $data);
    }

    /**
     * Submit a new withdrawal request. Does NOT touch the balance yet — the
     * amount is only held once the reseller successfully verifies the OTP
     * (see verify_withdrawal_otp()), so a wrong/expired/abandoned OTP never
     * costs the reseller anything.
     */
    public function request_withdrawal() {
        if ($this->input->method() !== 'post') {
            show_error('Invalid request', 400);
        }

        $this->_expire_stale_otp_requests($this->user_id);

        $this->form_validation->set_rules('amount', 'Amount', 'required|numeric|greater_than[0]');
        $this->form_validation->set_rules('gcash_number', 'GCash Number', 'required|trim|max_length[20]');
        $this->form_validation->set_rules('gcash_name', 'GCash Account Name', 'required|trim|max_length[100]');

        if (!$this->form_validation->run()) {
            echo json_encode(['success' => FALSE, 'message' => validation_errors(' ', ' ')]);
            return;
        }

        $amount = (float) $this->input->post('amount', TRUE);

        $this->db->trans_start();

        $reseller = $this->db->query(
            'SELECT * FROM ' . RESELLER_TABLE . ' WHERE reseller_id = ? FOR UPDATE',
            [$this->user_id]
        )->row_array();
        $available = (float) ($reseller['commission_balance'] ?? 0);

        // Only requests still awaiting OTP verification need to be reserved
        // here — the moment OTP is verified, CommissionService::holdWithdrawal()
        // deducts the amount from commission_balance directly (see
        // verify_withdrawal_otp() below), so any 'approved' request (which
        // requires otp_verified = 1) is already reflected in $available.
        // Counting it again here would reserve the same amount twice.
        $already_committed = (float) ($this->db->select_sum('amount')
            ->from(WITHDRAWAL_TABLE)
            ->where('reseller_id', $this->user_id)
            ->where('status', 'pending')
            ->where('otp_verified', 0)
            ->get()->row()->amount ?? 0);

        if ($amount > ($available - $already_committed)) {
            $this->db->trans_complete();
            echo json_encode(['success' => FALSE, 'message' => 'Insufficient balance']);
            return;
        }

        $otp_code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $withdrawal_data = [
            'withdrawal_number' => 'WD-' . date('Ymd-His') . '-' . random_int(10, 99),
            'reseller_id' => $this->user_id,
            'gcash_number' => $this->input->post('gcash_number', TRUE),
            'gcash_name' => $this->input->post('gcash_name', TRUE),
            'amount' => $amount,
            'otp_code' => $otp_code,
            'otp_verified' => 0,
            'status' => 'pending',
            'scheduled_date' => $this->settings_model->get_next_withdrawal_schedule_date(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert(WITHDRAWAL_TABLE, $withdrawal_data);
        $withdrawal_id = $this->db->insert_id();

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => FALSE, 'message' => 'Failed to submit withdrawal request. Please try again.']);
            return;
        }

        EmailQueueService::queueWithdrawalOtp(
            $this->session->userdata('email'),
            trim($reseller['first_name'] . ' ' . $reseller['last_name']),
            $otp_code,
            $withdrawal_data['withdrawal_number']
        );

        $this->log_activity('withdrawal_requested', 'withdrawal', $withdrawal_id, 'Amount: ' . $amount);

        echo json_encode(['success' => TRUE, 'message' => 'Withdrawal request submitted. Check your email for the OTP.']);
    }

    /**
     * Verify the OTP for a pending withdrawal request.
     */
    public function verify_withdrawal_otp() {
        if ($this->input->method() !== 'post') {
            show_error('Invalid request', 400);
        }

        $this->form_validation->set_rules('withdrawal_id', 'Withdrawal', 'required|integer');
        $this->form_validation->set_rules('otp', 'OTP', 'required|numeric|exact_length[6]');

        if (!$this->form_validation->run()) {
            echo json_encode(['success' => FALSE, 'message' => validation_errors(' ', ' ')]);
            return;
        }

        $withdrawal_id = $this->input->post('withdrawal_id', TRUE);
        $otp = $this->input->post('otp', TRUE);

        $withdrawal = $this->db->where('withdrawal_id', $withdrawal_id)->get(WITHDRAWAL_TABLE)->row_array();

        if (!$withdrawal || (int) $withdrawal['reseller_id'] !== (int) $this->user_id) {
            echo json_encode(['success' => FALSE, 'message' => 'Withdrawal not found']);
            return;
        }

        if ($withdrawal['status'] !== 'pending' || (int) $withdrawal['otp_verified'] === 1) {
            echo json_encode(['success' => FALSE, 'message' => 'This withdrawal does not need OTP verification']);
            return;
        }

        // withdrawal_tbl has no separate otp_expires_at column — the OTP is
        // generated at request time (request_withdrawal()), so created_at
        // doubles as its issue time. A stale, unverified OTP shouldn't stay
        // valid indefinitely, and since commission_balance was never touched
        // for an unverified request, cancelling it here costs the reseller
        // nothing — it just frees up their available-to-withdraw balance
        // instead of leaving it soft-locked as "pending" forever.
        $otp_expiry_minutes = 10;
        if (strtotime($withdrawal['created_at']) < strtotime('-' . $otp_expiry_minutes . ' minutes')) {
            $this->db->where('withdrawal_id', $withdrawal_id)->update(WITHDRAWAL_TABLE, [
                'status' => 'cancelled',
                'rejection_reason' => 'OTP expired before verification',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            echo json_encode(['success' => FALSE, 'message' => 'This OTP has expired, so the request was cancelled and no amount was deducted. Please submit a new withdrawal request.']);
            return;
        }

        if (!hash_equals((string) $withdrawal['otp_code'], (string) $otp)) {
            echo json_encode(['success' => FALSE, 'message' => 'Invalid OTP']);
            return;
        }

        // Only now — after a correct, non-expired OTP — is the amount
        // actually held against the reseller's balance. Row-lock the
        // reseller so this can't race with another request/verification.
        $this->db->trans_start();

        $reseller = $this->db->query(
            'SELECT * FROM ' . RESELLER_TABLE . ' WHERE reseller_id = ? FOR UPDATE',
            [$this->user_id]
        )->row_array();
        $available = (float) ($reseller['commission_balance'] ?? 0);

        if ((float) $withdrawal['amount'] > $available) {
            $this->db->trans_complete();
            echo json_encode(['success' => FALSE, 'message' => 'Insufficient balance to hold this withdrawal. Please cancel and submit a smaller amount.']);
            return;
        }

        CommissionService::holdWithdrawal($this->user_id, (float) $withdrawal['amount'], (int) $withdrawal_id);
        $this->db->where('withdrawal_id', $withdrawal_id)->update(WITHDRAWAL_TABLE, ['otp_verified' => 1]);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => FALSE, 'message' => 'Failed to verify OTP. Please try again.']);
            return;
        }

        $this->log_activity('withdrawal_otp_verified', 'withdrawal', $withdrawal_id);

        echo json_encode(['success' => TRUE, 'message' => 'OTP verified, withdrawal is now awaiting admin approval']);
    }

    /**
     * Auto-cancel this reseller's own pending withdrawal requests whose OTP
     * window has lapsed without ever being verified. Nothing was deducted
     * for these (commission_balance is only touched on successful OTP
     * verification), so cancelling is purely bookkeeping — but without it,
     * a request the reseller simply never confirmed would sit as "pending"
     * forever and keep counting against their available-to-withdraw balance
     * in request_withdrawal()'s already_committed check.
     */
    private function _expire_stale_otp_requests($reseller_id) {
        $this->db->where('reseller_id', $reseller_id)
            ->where('status', 'pending')
            ->where('otp_verified', 0)
            ->where('created_at <', date('Y-m-d H:i:s', strtotime('-10 minutes')))
            ->update(WITHDRAWAL_TABLE, [
                'status' => 'cancelled',
                'rejection_reason' => 'OTP expired before verification',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }
}
