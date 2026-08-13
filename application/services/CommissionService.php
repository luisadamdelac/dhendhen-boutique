<?php
/**
 * CommissionService
 * Centralized commission calculation, tracking, and management system
 * Handles commission lifecycle: pending → released → withdrawn
 *
 * File: application/services/CommissionService.php
 */

class CommissionService {

    private $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    /**
     * Calculate and create a pending commission record for an order
     * Called at order placement
     *
     * @param int $orderId Order ID
     * @param int $resellerId Reseller ID
     * @param float $orderTotal Total order amount the commission is computed from
     * @param float $commissionRate Commission rate (percentage); falls back to settings/default
     * @return int|false Commission ID or false on failure
     */
    public static function createCommission($orderId, $resellerId, $orderTotal, $commissionRate = NULL) {
        $CI =& get_instance();
        $CI->load->database();

        if (!$resellerId) {
            return FALSE;
        }

        try {
            if ($commissionRate === NULL) {
                $CI->load->model('settings_model');
                $commissionRate = (float) $CI->settings_model->get_commission_rate();
            }

            $amount = round(((float) $orderTotal * (float) $commissionRate) / 100, 2);

            $data = array(
                'commission_number' => self::generateCommissionNumber(),
                'reseller_id' => $resellerId,
                'order_id' => $orderId,
                'amount' => $amount,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            );

            if ($CI->db->insert(COMMISSIONS_TABLE, $data)) {
                $commissionId = $CI->db->insert_id();
                log_message('info', 'Commission created: Order ' . $orderId . ', Reseller ' . $resellerId .
                                   ', Amount ₱' . number_format($amount, 2));
                return $commissionId;
            }

            log_message('error', 'Failed to create commission: ' . $CI->db->error()['message']);
            return FALSE;

        } catch (Exception $e) {
            log_message('error', 'Commission creation error: ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Release commission (becomes available for withdrawal).
     * Called once, when order status reaches 'delivered'.
     *
     * Guarded twice against double-crediting if Delivered is ever saved more
     * than once for the same order: the initial SELECT only matches a
     * 'pending' row (already-released commissions are skipped), and the
     * UPDATE itself repeats that same status='pending' condition so that
     * even two near-simultaneous calls can't both succeed — only the first
     * actually flips the row (checked via affected_rows()), so only the
     * first ever credits the wallet.
     */
    public static function releaseCommission($orderId) {
        $CI =& get_instance();
        $CI->load->database();
        require_once APPPATH . 'services/NotificationService.php';

        try {
            $CI->db->trans_start();

            $commission = $CI->db->where('order_id', $orderId)
                                 ->where('status', 'pending')
                                 ->get(COMMISSIONS_TABLE)
                                 ->row_array();

            if (!$commission) {
                $CI->db->trans_complete();
                log_message('error', 'No pending commission found for order ' . $orderId);
                return FALSE;
            }

            $CI->db->where('commission_id', $commission['commission_id'])
                   ->where('status', 'pending')
                   ->update(COMMISSIONS_TABLE, array(
                       'status' => 'released',
                       'released_at' => date('Y-m-d H:i:s')
                   ));

            if ($CI->db->affected_rows() === 0) {
                // Already released by a concurrent/duplicate call — do not credit twice.
                $CI->db->trans_complete();
                return FALSE;
            }

            self::creditWallet($commission['reseller_id'], $commission['amount'],
                              'commission', $commission['commission_id']);

            $CI->db->trans_complete();

            if ($CI->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            $order = $CI->db->select('order_number')->where('order_id', $orderId)->get(ORDER_TABLE)->row_array();
            NotificationService::create(
                'reseller',
                $commission['reseller_id'],
                'Commission Credited',
                'Congratulations! Your commission for Order #' . ($order['order_number'] ?? $orderId) . ' has been successfully credited to your E-wallet.',
                'system',
                $commission['commission_id']
            );

            log_message('info', 'Commission released: ' . $commission['commission_id'] .
                               ', Amount ₱' . number_format($commission['amount'], 2));
            return TRUE;

        } catch (Exception $e) {
            log_message('error', 'Commission release error: ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Reverse commission (order cancelled or refunded).
     * The schema only supports pending/released/withdrawn, so a reversed
     * commission is removed outright (any already-credited balance clawed
     * back first) rather than tagged with a status that doesn't exist —
     * except when it's already 'withdrawn': see the guard below for why that
     * case is left alone instead of deleted.
     */
    public static function reverseCommission($orderId, $reason = NULL) {
        $CI =& get_instance();
        $CI->load->database();

        try {
            $commission = $CI->db->where('order_id', $orderId)
                                 ->get(COMMISSIONS_TABLE)
                                 ->row_array();

            if (!$commission) {
                log_message('error', 'No commission found to reverse for order ' . $orderId);
                return FALSE;
            }

            if ($commission['status'] === 'released') {
                self::debitWallet($commission['reseller_id'], $commission['amount'],
                                 'reversal', $orderId, $reason);
            }

            // A 'withdrawn' commission already left the store as a real
            // GCash payout (see admin/Withdrawal.php::mark_processed()) —
            // there's no wallet balance left to claw back, and deleting the
            // row here would destroy the only record linking that payout to
            // the commission it actually covered, silently understating the
            // reseller's true paid-out history with no trace of the
            // discrepancy. Leave the row in place and flag it for manual
            // admin reconciliation instead of erasing it.
            if ($commission['status'] === 'withdrawn') {
                require_once APPPATH . 'services/NotificationService.php';
                NotificationService::notifyAllAdmins(
                    'Refund on an Already Paid-Out Commission',
                    'Order #' . $orderId . ' was reversed (' . ($reason ?: 'refund/cancellation') . '), but its commission of ₱' . number_format($commission['amount'], 2) . ' was already withdrawn/paid out to the reseller. This needs manual reconciliation.',
                    'system',
                    $commission['commission_id']
                );
                log_message('error', 'Commission ' . $commission['commission_id'] . ' for order ' . $orderId . ' is already withdrawn — left in place for manual reconciliation, not deleted.');
                return FALSE;
            }

            $CI->db->where('commission_id', $commission['commission_id'])->delete(COMMISSIONS_TABLE);

            log_message('info', 'Commission reversed: ' . $commission['commission_id']);
            return TRUE;

        } catch (Exception $e) {
            log_message('error', 'Commission reversal error: ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Hold (deduct) an amount against a reseller's wallet for a withdrawal
     * request, once its OTP has been verified — logged as wallet_ledger_tbl's
     * 'hold' type so the ledger is a complete record of every balance
     * change, not just commission credits. See releaseWithdrawalHold() for
     * the inverse (a rejected request refunding the hold).
     *
     * Caller is responsible for the balance-sufficiency check and row lock
     * (see reseller/Withdrawals.php::verify_withdrawal_otp()) — this method
     * only performs the deduction + ledger write.
     */
    public static function holdWithdrawal($resellerId, $amount, $withdrawalId) {
        $CI =& get_instance();
        $CI->load->database();

        try {
            $CI->db->trans_start();

            $reseller = $CI->db->where('reseller_id', $resellerId)->get(RESELLER_TABLE)->row_array();
            $newBalance = max(0, (float) ($reseller['commission_balance'] ?? 0) - (float) $amount);

            $CI->db->where('reseller_id', $resellerId)->update(RESELLER_TABLE, ['commission_balance' => $newBalance]);

            $CI->db->insert('wallet_ledger_tbl', array(
                'reseller_id' => $resellerId,
                'transaction_type' => 'hold',
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference_type' => 'withdrawal',
                'reference_id' => $withdrawalId,
                'created_at' => date('Y-m-d H:i:s'),
            ));

            $CI->db->trans_complete();

            if ($CI->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            log_message('info', 'Withdrawal hold: Reseller ' . $resellerId . ', Withdrawal ' . $withdrawalId . ', Amount ₱' . number_format($amount, 2));
            return TRUE;

        } catch (Exception $e) {
            log_message('error', 'Withdrawal hold error: ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Release a previously-held withdrawal amount back into the reseller's
     * wallet — called when admin rejects a withdrawal that had already
     * passed OTP verification (so the amount was actually held).
     */
    public static function releaseWithdrawalHold($resellerId, $amount, $withdrawalId, $notes = NULL) {
        $CI =& get_instance();
        $CI->load->database();

        try {
            $CI->db->trans_start();

            $reseller = $CI->db->where('reseller_id', $resellerId)->get(RESELLER_TABLE)->row_array();
            $newBalance = (float) ($reseller['commission_balance'] ?? 0) + (float) $amount;

            $CI->db->where('reseller_id', $resellerId)->update(RESELLER_TABLE, ['commission_balance' => $newBalance]);

            $CI->db->insert('wallet_ledger_tbl', array(
                'reseller_id' => $resellerId,
                'transaction_type' => 'hold_release',
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference_type' => 'withdrawal',
                'reference_id' => $withdrawalId,
                'notes' => $notes,
                'created_at' => date('Y-m-d H:i:s'),
            ));

            $CI->db->trans_complete();

            if ($CI->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            log_message('info', 'Withdrawal hold released: Reseller ' . $resellerId . ', Withdrawal ' . $withdrawalId . ', Amount ₱' . number_format($amount, 2));
            return TRUE;

        } catch (Exception $e) {
            log_message('error', 'Withdrawal hold release error: ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Credit reseller wallet balance and log to the ledger
     */
    private static function creditWallet($resellerId, $amount, $referenceType = 'commission', $referenceId = NULL) {
        $CI =& get_instance();
        $CI->load->database();

        try {
            $CI->db->trans_start();

            $reseller = $CI->db->where('reseller_id', $resellerId)
                               ->get(RESELLER_TABLE)
                               ->row_array();

            $newBalance = (float) ($reseller['commission_balance'] ?? 0) + (float) $amount;

            $CI->db->where('reseller_id', $resellerId)
                   ->update(RESELLER_TABLE, array(
                       'commission_balance' => $newBalance,
                       'total_commission_earned' => (float) ($reseller['total_commission_earned'] ?? 0) + (float) $amount
                   ));

            $CI->db->insert('wallet_ledger_tbl', array(
                'reseller_id' => $resellerId,
                'transaction_type' => 'credit',
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_at' => date('Y-m-d H:i:s')
            ));

            $CI->db->trans_complete();

            if ($CI->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            log_message('info', 'Wallet credited: Reseller ' . $resellerId . ', Amount ₱' . number_format($amount, 2));
            return TRUE;

        } catch (Exception $e) {
            log_message('error', 'Wallet credit error: ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Debit reseller wallet balance and log to the ledger
     */
    private static function debitWallet($resellerId, $amount, $referenceType = 'withdrawal', $referenceId = NULL, $notes = NULL) {
        $CI =& get_instance();
        $CI->load->database();

        try {
            $CI->db->trans_start();

            $reseller = $CI->db->where('reseller_id', $resellerId)
                               ->get(RESELLER_TABLE)
                               ->row_array();

            $newBalance = max(0, (float) ($reseller['commission_balance'] ?? 0) - (float) $amount);

            $update = array('commission_balance' => $newBalance);
            if ($referenceType === 'withdrawal') {
                $update['total_withdrawn'] = (float) ($reseller['total_withdrawn'] ?? 0) + (float) $amount;
            }

            $CI->db->where('reseller_id', $resellerId)->update(RESELLER_TABLE, $update);

            $CI->db->insert('wallet_ledger_tbl', array(
                'reseller_id' => $resellerId,
                'transaction_type' => 'debit',
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'created_at' => date('Y-m-d H:i:s')
            ));

            $CI->db->trans_complete();

            if ($CI->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            log_message('info', 'Wallet debited: Reseller ' . $resellerId . ', Amount ₱' . number_format($amount, 2));
            return TRUE;

        } catch (Exception $e) {
            log_message('error', 'Wallet debit error: ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Get commission summary for reseller
     */
    public static function getResellerCommissionSummary($resellerId) {
        $CI =& get_instance();
        $CI->load->database();

        $pending = $CI->db->select('SUM(amount) as total')
                         ->where('reseller_id', $resellerId)
                         ->where('status', 'pending')
                         ->get(COMMISSIONS_TABLE)
                         ->row_array();

        $released = $CI->db->select('SUM(amount) as total')
                          ->where('reseller_id', $resellerId)
                          ->where('status', 'released')
                          ->get(COMMISSIONS_TABLE)
                          ->row_array();

        $reseller = $CI->db->select('commission_balance')
                          ->where('reseller_id', $resellerId)
                          ->get(RESELLER_TABLE)
                          ->row_array();

        return array(
            'total_pending' => $pending['total'] ?? 0,
            'total_released' => $released['total'] ?? 0,
            'total_available' => $reseller['commission_balance'] ?? 0
        );
    }

    /**
     * Get commission transactions for reseller
     */
    public static function getResellerCommissions($resellerId, $status = NULL, $limit = 50, $offset = 0) {
        $CI =& get_instance();
        $CI->load->database();

        $query = $CI->db->where('reseller_id', $resellerId);

        if ($status) {
            $query = $query->where('status', $status);
        }

        return $query->order_by('created_at', 'DESC')
                    ->limit($limit, $offset)
                    ->get(COMMISSIONS_TABLE)
                    ->result_array();
    }

    /**
     * Generate a unique commission number, e.g. CM-20260630-184213-57
     */
    private static function generateCommissionNumber() {
        return 'CM-' . date('Ymd-His') . '-' . mt_rand(10, 99);
    }
}

/* End of file CommissionService.php */
/* Location: ./application/services/CommissionService.php */
