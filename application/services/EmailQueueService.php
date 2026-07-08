<?php
/**
 * EmailQueueService
 * Queue-based transactional email system, sent via Gmail SMTP (App Password).
 * queue() processes the queue immediately when SMTP is enabled — there is
 * no cron/worker in this app, so nothing would otherwise ever send.
 *
 * File: application/services/EmailQueueService.php
 */

class EmailQueueService {

    /**
     * Queue an email for sending, then attempt delivery immediately if
     * SMTP is enabled (see isSmtpEnabled()).
     *
     * @param string $toEmail Recipient email address
     * @param string $toName Recipient name
     * @param string $subject Email subject
     * @param string $bodyHtml HTML email body (already wrapped, if desired,
     *                          via wrapEmailTemplate())
     * @return int|false Queue ID or false on failure
     */
    public static function queue($toEmail, $toName, $subject, $bodyHtml) {
        $CI =& get_instance();
        $CI->load->database();

        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            log_message('error', 'Invalid email address: ' . $toEmail);
            return FALSE;
        }

        $data = array(
            'recipient_email' => $toEmail,
            'recipient_name' => $toName,
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'status' => 'pending',
            'attempts' => 0,
            'created_at' => date('Y-m-d H:i:s')
        );

        if (!$CI->db->insert('email_queue_tbl', $data)) {
            log_message('error', 'Failed to queue email: ' . $CI->db->error()['message']);
            return FALSE;
        }

        $queueId = $CI->db->insert_id();

        if (self::isSmtpEnabled()) {
            self::processQueue();
        }

        return $queueId;
    }

    /**
     * Queue order confirmation email
     */
    public static function queueOrderConfirmation($customerEmail, $customerName, $orderId, $orderTotal, $itemsHtml) {
        $subject = 'Order Confirmation - Order #' . $orderId;

        $bodyHtml = '<p>Hello ' . htmlspecialchars($customerName) . ',</p>'
            . '<p>Thank you for your order! Your order has been successfully placed.</p>'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0;">'
            . '<tr><td style="padding:6px 0;color:#6b7280;font-size:13px;">Order Number</td><td style="padding:6px 0;text-align:right;font-weight:600;">#' . (int) $orderId . '</td></tr>'
            . '<tr><td style="padding:6px 0;color:#6b7280;font-size:13px;">Order Total</td><td style="padding:6px 0;text-align:right;font-weight:600;">₱' . number_format((float) $orderTotal, 2) . '</td></tr>'
            . '</table>'
            . '<div style="margin:16px 0;">' . $itemsHtml . '</div>'
            . '<p>We will notify you when your order is ready for shipment.</p>';

        return self::queue($customerEmail, $customerName, $subject, self::wrapEmailTemplate('Order Confirmation', $bodyHtml));
    }

    /**
     * Queue payment verification email
     */
    public static function queuePaymentVerified($customerEmail, $customerName, $orderId, $amount) {
        $subject = 'Payment Verified - Order #' . $orderId;

        $bodyHtml = '<p>Hello ' . htmlspecialchars($customerName) . ',</p>'
            . '<p>Your payment has been verified successfully.</p>'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0;">'
            . '<tr><td style="padding:6px 0;color:#6b7280;font-size:13px;">Order Number</td><td style="padding:6px 0;text-align:right;font-weight:600;">#' . (int) $orderId . '</td></tr>'
            . '<tr><td style="padding:6px 0;color:#6b7280;font-size:13px;">Amount</td><td style="padding:6px 0;text-align:right;font-weight:600;">₱' . number_format((float) $amount, 2) . '</td></tr>'
            . '</table>'
            . '<p>Your order is now being processed. We will notify you when it is ready for shipment.</p>';

        return self::queue($customerEmail, $customerName, $subject, self::wrapEmailTemplate('Payment Verified', $bodyHtml));
    }

    /**
     * Queue refund approval email
     */
    public static function queueRefundApproved($customerEmail, $customerName, $orderId, $refundAmount) {
        $subject = 'Refund Approved - Order #' . $orderId;

        $bodyHtml = '<p>Hello ' . htmlspecialchars($customerName) . ',</p>'
            . '<p>Your refund request has been approved.</p>'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0;">'
            . '<tr><td style="padding:6px 0;color:#6b7280;font-size:13px;">Order Number</td><td style="padding:6px 0;text-align:right;font-weight:600;">#' . (int) $orderId . '</td></tr>'
            . '<tr><td style="padding:6px 0;color:#6b7280;font-size:13px;">Refund Amount</td><td style="padding:6px 0;text-align:right;font-weight:600;">₱' . number_format((float) $refundAmount, 2) . '</td></tr>'
            . '</table>'
            . '<p>The refund will be processed to your original payment method within 3-5 business days.</p>';

        return self::queue($customerEmail, $customerName, $subject, self::wrapEmailTemplate('Refund Approved', $bodyHtml));
    }

    /**
     * Queue withdrawal OTP email (for reseller) — the one-time code needed
     * to confirm a withdrawal request they just submitted.
     */
    public static function queueWithdrawalOtp($resellerEmail, $resellerName, $otpCode, $withdrawalNumber) {
        $subject = 'Your Withdrawal OTP Code';

        $bodyHtml = '<p>Hello ' . htmlspecialchars($resellerName) . ',</p>'
            . '<p>Use the one-time code below to confirm your withdrawal request <strong>' . htmlspecialchars($withdrawalNumber) . '</strong>.</p>'
            . '<div style="text-align:center;margin:20px 0;">'
            . '<span style="display:inline-block;padding:14px 28px;background:#faf7fb;border:2px dashed #e0559c;border-radius:10px;font-size:28px;font-weight:700;letter-spacing:6px;color:#e0559c;">' . htmlspecialchars($otpCode) . '</span>'
            . '</div>'
            . '<p style="color:#6b7280;font-size:13px;">This code expires in 10 minutes. If you didn\'t request this withdrawal, please ignore this email.</p>';

        return self::queue($resellerEmail, $resellerName, $subject, self::wrapEmailTemplate('Withdrawal OTP', $bodyHtml));
    }

    /**
     * Queue withdrawal approval email (for reseller)
     */
    public static function queueWithdrawalApproved($resellerEmail, $resellerName, $amount) {
        $subject = 'Withdrawal Request Approved';

        $bodyHtml = '<p>Hello ' . htmlspecialchars($resellerName) . ',</p>'
            . '<p>Your withdrawal request has been approved and is being processed.</p>'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0;">'
            . '<tr><td style="padding:6px 0;color:#6b7280;font-size:13px;">Withdrawal Amount</td><td style="padding:6px 0;text-align:right;font-weight:600;">₱' . number_format((float) $amount, 2) . '</td></tr>'
            . '</table>'
            . '<p>The funds will be transferred to your registered GCash account within 1-2 business days.</p>';

        return self::queue($resellerEmail, $resellerName, $subject, self::wrapEmailTemplate('Withdrawal Approved', $bodyHtml));
    }

    /**
     * Queue reseller application approval email
     */
    public static function queueResellerApplicationApproved($resellerEmail, $resellerName, $businessName) {
        $subject = 'Your Reseller Application Has Been Approved';

        $bodyHtml = '<p>Hello ' . htmlspecialchars($resellerName) . ',</p>'
            . '<p>Congratulations! Your reseller application' . (!empty($businessName) ? ' for <strong>' . htmlspecialchars($businessName) . '</strong>' : '') . ' has been approved.</p>'
            . '<p>You can now log in to your reseller account to start listing and selling products.</p>';

        return self::queue($resellerEmail, $resellerName, $subject, self::wrapEmailTemplate('Application Approved', $bodyHtml));
    }

    /**
     * Queue reseller application rejection email
     */
    public static function queueResellerApplicationRejected($resellerEmail, $resellerName, $reason = '') {
        $subject = 'Update on Your Reseller Application';

        $bodyHtml = '<p>Hello ' . htmlspecialchars($resellerName) . ',</p>'
            . '<p>After reviewing your reseller application, we are unable to approve it at this time.</p>'
            . (!empty($reason) ? '<p><strong>Reason:</strong> ' . htmlspecialchars($reason) . '</p>' : '')
            . '<p>If you believe this was a mistake or would like more information, please contact our support team.</p>';

        return self::queue($resellerEmail, $resellerName, $subject, self::wrapEmailTemplate('Application Update', $bodyHtml));
    }

    /**
     * Wrap a chunk of email body HTML in the shared DropSell-branded layout
     * (pink-purple header, white content card, footer) — used by every
     * queue* method and by sendTestEmail(), so the visual design lives in
     * exactly one place.
     */
    public static function wrapEmailTemplate($title, $bodyHtml) {
        $CI =& get_instance();
        $CI->load->database();
        $companyName = self::getSetting('company_name') ?: 'DropSell';
        $year = date('Y');

        return '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1.0"></head>'
            . '<body style="margin:0;padding:0;background:#f3e5f5;font-family:Arial,Helvetica,sans-serif;">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3e5f5;padding:24px 0;">'
            . '<tr><td align="center">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 24px rgba(133,49,122,0.12);">'
            . '<tr><td style="background:linear-gradient(135deg,#ff69b4 0%,#ee82ee 50%,#9370db 100%);padding:28px 32px;text-align:center;">'
            . '<div style="font-size:34px;line-height:1;">🛍️</div>'
            . '<div style="color:#fff;font-size:20px;font-weight:700;margin-top:6px;">' . htmlspecialchars($companyName) . '</div>'
            . '<div style="color:rgba(255,255,255,0.9);font-size:14px;margin-top:4px;">' . htmlspecialchars($title) . '</div>'
            . '</td></tr>'
            . '<tr><td style="padding:32px;color:#24202b;font-size:14px;line-height:1.6;">' . $bodyHtml . '</td></tr>'
            . '<tr><td style="padding:20px 32px;background:#faf7fb;border-top:1px solid #f0e6f5;text-align:center;color:#9ca3af;font-size:12px;">'
            . htmlspecialchars($companyName) . ' &middot; This is an automated message, please do not reply directly.<br>'
            . '&copy; ' . $year . ' ' . htmlspecialchars($companyName) . '. All rights reserved.'
            . '</td></tr>'
            . '</table>'
            . '</td></tr>'
            . '</table>'
            . '</body></html>';
    }

    /**
     * Send a one-off test email immediately (bypasses the queue table
     * entirely) so an admin gets a synchronous success/failure result.
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public static function sendTestEmail($toEmail) {
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => FALSE, 'message' => 'Please enter a valid email address.'];
        }

        $CI =& get_instance();
        $CI->load->database();
        $CI->load->library('email');

        $smtpConfig = self::getSmtpConfig();

        if (empty($smtpConfig['smtp_host']) || empty($smtpConfig['smtp_user']) || empty($smtpConfig['smtp_pass'])) {
            return ['success' => FALSE, 'message' => 'SMTP host, username, and password must all be configured and saved before sending a test email.'];
        }

        $bodyHtml = '<p>Hello,</p>'
            . '<p>This is a test email from your DropSell admin panel. If you received this, your Gmail SMTP configuration is working correctly.</p>'
            . '<p style="color:#6b7280;font-size:13px;">Sent ' . date('F j, Y \a\t g:i A') . '</p>';

        try {
            $CI->email->initialize($smtpConfig);
            $CI->email->from($smtpConfig['from'], $smtpConfig['from_name']);
            if (!empty($smtpConfig['reply_to'])) {
                $CI->email->reply_to($smtpConfig['reply_to']);
            }
            $CI->email->to($toEmail);
            $CI->email->subject('DropSell SMTP Test Email');
            $CI->email->message(self::wrapEmailTemplate('SMTP Test Email', $bodyHtml));

            // CI_Email::send() can trip a raw PHP warning from fsockopen()
            // (e.g. DNS lookup failure on a bad host) that isn't converted
            // into a catchable exception — left alone, that warning prints
            // directly into the response body ahead of any redirect/headers
            // this call site's caller still needs to send. @ here only
            // silences that specific native warning; the actual outcome is
            // still read from the boolean return value right after.
            if (@$CI->email->send()) {
                $CI->email->clear();
                return ['success' => TRUE, 'message' => 'Test email sent successfully to ' . $toEmail . '.'];
            }

            $debug = $CI->email->print_debugger(['headers']);
            $CI->email->clear();
            return ['success' => FALSE, 'message' => self::describeSmtpError($debug)];
        } catch (Exception $e) {
            return ['success' => FALSE, 'message' => self::describeSmtpError($e->getMessage())];
        }
    }

    /**
     * Process queued emails immediately (called by queue() when SMTP is
     * enabled). Processes up to 20 pending emails per call.
     *
     * @return array Results with sent count, failed count, errors
     */
    public static function processQueue() {
        $CI =& get_instance();
        $CI->load->database();

        $results = array('sent' => 0, 'failed' => 0, 'errors' => array());

        if (!self::isSmtpEnabled()) {
            return $results;
        }

        $CI->load->library('email');

        // Get pending emails (limit 20)
        $pending = $CI->db->where('status', 'pending')
                         ->where('attempts <', 3)
                         ->order_by('created_at', 'ASC')
                         ->limit(20)
                         ->get('email_queue_tbl')
                         ->result_array();

        $smtpConfig = self::getSmtpConfig();

        foreach ($pending as $email) {
            try {
                $CI->email->initialize($smtpConfig);
                $CI->email->from($smtpConfig['from'], $smtpConfig['from_name']);
                if (!empty($smtpConfig['reply_to'])) {
                    $CI->email->reply_to($smtpConfig['reply_to']);
                }
                $CI->email->to($email['recipient_email']);
                $CI->email->subject($email['subject']);
                $CI->email->message($email['body_html']);

                // See the matching comment in sendTestEmail() — @ only
                // silences fsockopen()'s native PHP warning on connect
                // failure so it can't leak into the response before this
                // request's own redirect/headers go out; the try/catch
                // below still fully handles the failure path.
                if (@$CI->email->send()) {
                    $CI->db->where('queue_id', $email['queue_id'])
                           ->update('email_queue_tbl', array(
                               'status' => 'sent',
                               'last_attempt' => date('Y-m-d H:i:s'),
                               'error_message' => NULL,
                           ));

                    $results['sent']++;
                    log_message('info', 'Email sent successfully to: ' . $email['recipient_email']);
                } else {
                    $newAttempts = $email['attempts'] + 1;
                    $newStatus = $newAttempts >= 3 ? 'failed' : 'pending';
                    $rawError = $CI->email->print_debugger(['headers']);

                    $CI->db->where('queue_id', $email['queue_id'])
                           ->update('email_queue_tbl', array(
                               'status' => $newStatus,
                               'attempts' => $newAttempts,
                               'last_attempt' => date('Y-m-d H:i:s'),
                               'error_message' => $rawError,
                           ));

                    $results['failed']++;
                    $error = 'Email send failed for: ' . $email['recipient_email'] . ' | ' . self::describeSmtpError($rawError);
                    $results['errors'][] = $error;
                    log_message('error', $error);
                }

                $CI->email->clear();

            } catch (Exception $e) {
                $newAttempts = $email['attempts'] + 1;
                $newStatus = $newAttempts >= 3 ? 'failed' : 'pending';

                $CI->db->where('queue_id', $email['queue_id'])
                       ->update('email_queue_tbl', array(
                           'status' => $newStatus,
                           'attempts' => $newAttempts,
                           'last_attempt' => date('Y-m-d H:i:s'),
                           'error_message' => $e->getMessage(),
                       ));

                $results['failed']++;
                $error = 'Exception sending email to: ' . $email['recipient_email'] . ' | ' . $e->getMessage();
                $results['errors'][] = $error;
                log_message('error', $error);
            }
        }

        return $results;
    }

    /**
     * Turn a raw SMTP/exception error string into a short, specific reason
     * (per spec: distinguish auth failure vs. connection failure), while
     * the raw text is always the one persisted to email_queue_tbl.error_message
     * separately — this is only for the friendlier message shown to admins.
     */
    private static function describeSmtpError($rawError) {
        $raw = (string) $rawError;

        if (preg_match('/\b(534|535|530)\b/', $raw) || stripos($raw, 'Username and Password not accepted') !== FALSE || stripos($raw, 'authentication failed') !== FALSE) {
            return 'SMTP Authentication Failed — check that the SMTP username and Google App Password are correct.';
        }

        if (stripos($raw, 'Failed to connect') !== FALSE || stripos($raw, 'Unable to connect') !== FALSE || stripos($raw, 'Connection timed out') !== FALSE || stripos($raw, 'getaddrinfo') !== FALSE) {
            return 'Unable to connect to Gmail SMTP server. Check the SMTP host/port and your internet connection.';
        }

        if (trim($raw) === '') {
            return 'Unknown SMTP error — no response from the mail server.';
        }

        return 'SMTP error: ' . strip_tags($raw);
    }

    /**
     * Whether SMTP sending is enabled (admin toggle in Settings > Email).
     * Disabled by default until an admin explicitly turns it on.
     */
    public static function isSmtpEnabled() {
        return self::getSetting('smtp_enabled') === '1';
    }

    private static function getSetting($key) {
        $CI =& get_instance();
        $CI->load->database();

        if (!$CI->db->table_exists('settings_tbl')) {
            return NULL;
        }

        $row = $CI->db->select('setting_value')->from('settings_tbl')->where('setting_key', $key)->get()->row_array();
        return $row['setting_value'] ?? NULL;
    }

    /**
     * Get SMTP configuration from settings_tbl, mapped to the exact
     * property names CI3's CI_Email class recognizes.
     */
    private static function getSmtpConfig() {
        $CI =& get_instance();
        $CI->load->database();

        $settings = array();
        if ($CI->db->table_exists('settings_tbl')) {
            $rows = $CI->db->get('settings_tbl')->result_array();
            foreach ($rows as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        }

        // NOTE: admin/Settings::email() saves 'smtp_password' (not 'smtp_pass'),
        // and CI3's CI_Email class only recognizes the property names
        // smtp_pass / smtp_crypto (not smtp_password / smtp_encryption) — using
        // the wrong key here means initialize() silently drops the value
        // (CI_Email::initialize() only assigns keys that match an existing
        // property), so the connection would always attempt sending with an
        // empty password and no TLS/SSL regardless of what was saved.
        return array(
            'protocol' => 'smtp',
            'smtp_host' => $settings['smtp_host'] ?? 'smtp.gmail.com',
            'smtp_port' => (int) ($settings['smtp_port'] ?? 587),
            'smtp_user' => $settings['smtp_user'] ?? '',
            'smtp_pass' => $settings['smtp_password'] ?? '',
            'smtp_crypto' => $settings['smtp_encryption'] ?? 'tls',
            'smtp_timeout' => 30,
            'reply_to' => $settings['reply_to'] ?? '',
            'from' => $settings['from_email'] ?? $settings['company_email'] ?? 'noreply@dropsell.com',
            'from_name' => $settings['from_name'] ?? $settings['company_name'] ?? 'DropSell',
            'mailtype' => 'html',
            'charset' => 'utf-8',
            'newline' => "\r\n",
            'crlf' => "\r\n",
            'wordwrap' => TRUE,
        );
    }

    /**
     * Get queue statistics
     */
    public static function getQueueStats() {
        $CI =& get_instance();
        $CI->load->database();

        return array(
            'pending' => $CI->db->where('status', 'pending')->count_all_results('email_queue_tbl'),
            'sent' => $CI->db->where('status', 'sent')->count_all_results('email_queue_tbl'),
            'failed' => $CI->db->where('status', 'failed')->count_all_results('email_queue_tbl'),
        );
    }

    /**
     * Timestamp of the most recently successfully sent email, or NULL.
     */
    public static function getLastSentAt() {
        $CI =& get_instance();
        $CI->load->database();

        $row = $CI->db->select_max('last_attempt')
            ->where('status', 'sent')
            ->get('email_queue_tbl')->row_array();

        return $row['last_attempt'] ?? NULL;
    }
}

/* End of file EmailQueueService.php */
/* Location: ./application/services/EmailQueueService.php */
