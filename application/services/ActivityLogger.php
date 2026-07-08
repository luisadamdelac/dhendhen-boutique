<?php
/**
 * ActivityLogger
 * Centralized audit logging system
 * Logs all sensitive actions across all portals
 * 
 * File: application/services/ActivityLogger.php
 */

class ActivityLogger {

    private $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    /**
     * Log an activity
     * 
     * @param string $userType User type (admin, staff, reseller, customer)
     * @param int $userId User ID (role-specific ID)
     * @param string $action Action name (e.g., 'login', 'order_created', 'password_changed')
     * @param string $entityType Entity type (user, product, order, refund, etc.)
     * @param int $entityId Entity ID
     * @param string $details Additional details/notes
     * @param string $ipAddress IP address (auto-detected if null)
     * @return int|false Log ID or false on failure
     */
    public static function log($userType, $userId, $action, $entityType = NULL, $entityId = NULL, $details = NULL, $ipAddress = NULL) {
        $CI =& get_instance();
        $CI->load->database();

        if (empty($ipAddress)) {
            $ipAddress = $CI->input->ip_address();
        }

        $data = array(
            'user_type' => $userType,
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => $details,
            'ip_address' => $ipAddress,
            'created_at' => date('Y-m-d H:i:s')
        );

        if ($CI->db->insert('activity_logs', $data)) {
            return $CI->db->insert_id();
        }

        log_message('error', 'Failed to insert activity log: ' . $CI->db->error()['message']);
        return FALSE;
    }

    /**
     * Log login success
     */
    public static function loginSuccess($userType, $userId, $email) {
        ActivityLogger::log($userType, $userId, 'login', 'user', $userId, 
            'Successful login for email: ' . $email);
    }

    /**
     * Log login failure
     */
    public static function loginFailure($email, $reason = 'Invalid credentials') {
        $CI =& get_instance();
        $CI->load->database();

        $data = array(
            'email' => $email,
            'ip_address' => $CI->input->ip_address(),
            'success' => 0,
            'reason' => $reason,
            'created_at' => date('Y-m-d H:i:s')
        );

        $CI->db->insert('login_attempt_tbl', $data);
    }

    /**
     * Log logout
     */
    public static function logout($userType, $userId) {
        ActivityLogger::log($userType, $userId, 'logout', 'user', $userId, 'User logged out');
    }

    /**
     * Log password change
     */
    public static function passwordChanged($userType, $userId) {
        ActivityLogger::log($userType, $userId, 'password_changed', 'user', $userId, 
            'Password changed successfully');
    }

    /**
     * Log profile update
     */
    public static function profileUpdated($userType, $userId, $details) {
        ActivityLogger::log($userType, $userId, 'profile_updated', 'profile', $userId, 
            'Profile updated: ' . $details);
    }

    /**
     * Log product created
     */
    public static function productCreated($adminId, $productId, $productName) {
        ActivityLogger::log('admin', $adminId, 'product_created', 'product', $productId, 
            'Product created: ' . $productName);
    }

    /**
     * Log product edited
     */
    public static function productEdited($adminId, $productId, $productName, $changes) {
        ActivityLogger::log('admin', $adminId, 'product_edited', 'product', $productId, 
            'Product edited: ' . $productName . ' | Changes: ' . $changes);
    }

    /**
     * Log product deleted
     */
    public static function productDeleted($adminId, $productId, $productName) {
        ActivityLogger::log('admin', $adminId, 'product_deleted', 'product', $productId, 
            'Product deleted: ' . $productName);
    }

    /**
     * Log stock adjustment
     */
    public static function stockAdjusted($staffId, $productId, $oldQty, $newQty, $reason) {
        ActivityLogger::log('staff', $staffId, 'stock_adjusted', 'product', $productId, 
            'Stock adjusted from ' . $oldQty . ' to ' . $newQty . ' | Reason: ' . $reason);
    }

    /**
     * Log order status change
     */
    public static function orderStatusChanged($userType, $userId, $orderId, $oldStatus, $newStatus) {
        ActivityLogger::log($userType, $userId, 'order_status_changed', 'order', $orderId, 
            'Order status changed from ' . $oldStatus . ' to ' . $newStatus);
    }

    /**
     * Log payment verified
     */
    public static function paymentVerified($adminId, $orderId, $amount) {
        ActivityLogger::log('admin', $adminId, 'payment_verified', 'order', $orderId, 
            'Payment verified: ₱' . number_format($amount, 2));
    }

    /**
     * Log refund approved
     */
    public static function refundApproved($adminId, $refundId, $orderId, $amount) {
        ActivityLogger::log('admin', $adminId, 'refund_approved', 'refund', $refundId, 
            'Refund approved for Order ' . $orderId . ': ₱' . number_format($amount, 2));
    }

    /**
     * Log withdrawal approved
     */
    public static function withdrawalApproved($adminId, $withdrawalId, $resellerId, $amount) {
        ActivityLogger::log('admin', $adminId, 'withdrawal_approved', 'withdrawal', $withdrawalId, 
            'Withdrawal approved for Reseller ' . $resellerId . ': ₱' . number_format($amount, 2));
    }

    /**
     * Get activity logs for user
     */
    public static function getActivityLogs($userType, $userId, $limit = 50) {
        $CI =& get_instance();
        $CI->load->database();

        return $CI->db->where('user_type', $userType)
                     ->where('user_id', $userId)
                     ->order_by('created_at', 'DESC')
                     ->limit($limit)
                     ->get('activity_logs')
                     ->result_array();
    }

    /**
     * Get all activity logs (admin audit)
     */
    public static function getAllActivityLogs($limit = 100, $offset = 0) {
        $CI =& get_instance();
        $CI->load->database();

        return $CI->db->order_by('created_at', 'DESC')
                     ->limit($limit, $offset)
                     ->get('activity_logs')
                     ->result_array();
    }

    /**
     * Get activity by entity
     */
    public static function getActivityByEntity($entityType, $entityId) {
        $CI =& get_instance();
        $CI->load->database();

        return $CI->db->where('entity_type', $entityType)
                     ->where('entity_id', $entityId)
                     ->order_by('created_at', 'DESC')
                     ->get('activity_logs')
                     ->result_array();
    }
}

/* End of file ActivityLogger.php */
/* Location: ./application/services/ActivityLogger.php */
