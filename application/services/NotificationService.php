<?php
/**
 * NotificationService
 * Centralized notification creation system
 * Triggered at every workflow event
 * 
 * File: application/services/NotificationService.php
 */

class NotificationService {
    
    private $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    /**
     * Create a notification for a user
     * 
     * @param string $userType User type (admin, staff, reseller, customer)
     * @param int $userId User ID (admin_id, staff_id, reseller_id, customer_id)
     * @param string $title Notification title/subject
     * @param string $message Notification message body
     * @param string $type Notification type (order, payment, withdrawal, refund, system)
     * @param int $relatedId Optional related entity ID
     * @return int|false Notification ID or false on failure
     */
    public static function create($userType, $userId, $title, $message, $type = 'system', $relatedId = NULL) {
        $CI =& get_instance();
        $CI->load->database();

        $data = array(
            'user_type' => $userType,
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        );

        if ($CI->db->insert('notifications', $data)) {
            return $CI->db->insert_id();
        }

        return FALSE;
    }

    /**
     * Create the same notification for every admin account.
     * Replaces the previous hardcoded admin_id=1 broadcasts.
     */
    public static function notifyAllAdmins($title, $message, $type = 'system', $relatedId = NULL) {
        $CI =& get_instance();
        $CI->load->database();

        $admin_ids = $CI->db->select('admin_id')->get('admin_tbl')->result_array();
        foreach ($admin_ids as $admin) {
            NotificationService::create('admin', $admin['admin_id'], $title, $message, $type, $relatedId);
        }
    }

    /**
     * Create the same notification for every staff account.
     */
    private static function notifyAllStaff($title, $message, $type = 'system', $relatedId = NULL) {
        $CI =& get_instance();
        $CI->load->database();

        $staff_ids = $CI->db->select('staff_id')->get('staff_tbl')->result_array();
        foreach ($staff_ids as $staff) {
            NotificationService::create('staff', $staff['staff_id'], $title, $message, $type, $relatedId);
        }
    }

    /**
     * Notify staff that a customer's payment has been confirmed by admin
     * and the order is ready to be moved into processing.
     */
    public static function paymentApprovedForStaff($orderId, $orderNumber) {
        NotificationService::notifyAllStaff(
            'Order Ready for Processing',
            'Payment for Order #' . $orderNumber . ' has been confirmed by admin. Please update the order to Processing.',
            'payment', $orderId);
    }

    /**
     * Create notification for order placed
     * Sent to: customer, admin, assigned reseller
     */
    public static function orderPlaced($orderId, $customerId, $resellerId, $customerEmail) {
        NotificationService::create('customer', $customerId, 
            'Order Confirmed', 
            'Your order has been placed successfully. Order ID: ' . $orderId,
            'order', $orderId);

        NotificationService::notifyAllAdmins(
            'New Order',
            'New order placed: Order ID ' . $orderId . ' from customer email: ' . $customerEmail,
            'order', $orderId);

        if ($resellerId) {
            NotificationService::create('reseller', $resellerId,
                'New Sales Order',
                'You have a new order: Order ID ' . $orderId,
                'order', $orderId);
        }
    }

    /**
     * Create notification for payment verified
     */
    public static function paymentVerified($orderId, $customerId, $amount) {
        NotificationService::create('customer', $customerId,
            'Payment Verified',
            'Payment of ₱' . number_format($amount, 2) . ' has been verified. Order ID: ' . $orderId,
            'payment', $orderId);

        NotificationService::notifyAllAdmins(
            'Payment Verified',
            'Payment verified for Order ID ' . $orderId . ': ₱' . number_format($amount, 2),
            'payment', $orderId);
    }

    /**
     * Create notification for a rejected payment — tells the customer why,
     * so they know what to fix before resubmitting proof for the same order.
     */
    public static function paymentRejected($orderId, $customerId, $reason) {
        NotificationService::create('customer', $customerId,
            'Payment Rejected',
            'Your payment proof for Order ID ' . $orderId . ' was rejected: ' . $reason . '. Please resubmit your reference number and receipt.',
            'payment', $orderId);
    }

    /**
     * Create notification for a resubmitted payment proof after rejection.
     */
    public static function paymentResubmitted($orderId, $orderNumber) {
        NotificationService::notifyAllAdmins(
            'Payment Resubmitted',
            'Customer resubmitted payment proof for Order #' . $orderNumber . '. Please review.',
            'payment', $orderId);
    }

    /**
     * Create notification for order status change
     */
    public static function orderStatusChanged($orderId, $customerId, $oldStatus, $newStatus) {
        $statusMessages = array(
            'paid' => 'Your payment has been verified',
            'processing' => 'Your order is being processed',
            'to_ship' => 'Your order is ready for shipment',
            'delivered' => 'Your order has been delivered',
            'cancelled' => 'Your order has been cancelled',
            'refund_return' => 'Refund/return initiated for your order'
        );

        $message = isset($statusMessages[$newStatus]) 
            ? $statusMessages[$newStatus] 
            : 'Order status updated to: ' . $newStatus;

        NotificationService::create('customer', $customerId,
            'Order Status Update',
            'Order ID ' . $orderId . ': ' . $message,
            'order', $orderId);
    }

    /**
     * Create notification for refund request submitted
     */
    public static function refundSubmitted($refundId, $customerId, $orderId) {
        NotificationService::create('customer', $customerId,
            'Refund Request Submitted',
            'Your refund request for Order ID ' . $orderId . ' has been submitted for review.',
            'refund', $refundId);

        NotificationService::notifyAllAdmins(
            'New Refund Request',
            'New refund request submitted for Order ID ' . $orderId,
            'refund', $refundId);
    }

    /**
     * Create notification for refund approved
     */
    public static function refundApproved($refundId, $customerId, $orderId) {
        NotificationService::create('customer', $customerId,
            'Refund Approved',
            'Your refund request for Order ID ' . $orderId . ' has been approved.',
            'refund', $refundId);
    }

    /**
     * Create notification for withdrawal request submitted
     */
    public static function withdrawalSubmitted($withdrawalId, $resellerId, $amount) {
        NotificationService::create('reseller', $resellerId,
            'Withdrawal Request Submitted',
            'Your withdrawal request for ₱' . number_format($amount, 2) . ' has been submitted.',
            'withdrawal', $withdrawalId);

        NotificationService::notifyAllAdmins(
            'New Withdrawal Request',
            'New withdrawal request from reseller: ₱' . number_format($amount, 2),
            'withdrawal', $withdrawalId);
    }

    /**
     * Create notification for withdrawal approved
     */
    public static function withdrawalApproved($withdrawalId, $resellerId, $amount) {
        NotificationService::create('reseller', $resellerId,
            'Withdrawal Approved',
            'Your withdrawal request for ₱' . number_format($amount, 2) . ' has been approved.',
            'withdrawal', $withdrawalId);
    }

    /**
     * Create notification for reseller application
     */
    public static function resellerApplicationApproved($resellerId, $resellerEmail) {
        NotificationService::create('reseller', $resellerId,
            'Application Approved',
            'Congratulations! Your reseller application has been approved. You can now log in and start selling.',
            'system', $resellerId);

        NotificationService::notifyAllAdmins(
            'Reseller Application Approved',
            'Reseller application approved for ' . $resellerEmail,
            'system', $resellerId);
    }

    /**
     * Get unread count for user
     */
    public static function getUnreadCount($userType, $userId) {
        $CI =& get_instance();
        $CI->load->database();

        $result = $CI->db->where('user_type', $userType)
                        ->where('user_id', $userId)
                        ->where('is_read', 0)
                        ->count_all_results('notifications');

        return $result;
    }

    /**
     * Get notifications for user
     */
    public static function getNotifications($userType, $userId, $limit = 10) {
        $CI =& get_instance();
        $CI->load->database();

        return $CI->db->where('user_type', $userType)
                     ->where('user_id', $userId)
                     ->order_by('is_read', 'ASC')
                     ->order_by('created_at', 'DESC')
                     ->limit($limit)
                     ->get('notifications')
                     ->result_array();
    }

    /**
     * Mark notification as read
     */
    public static function markAsRead($notificationId) {
        $CI =& get_instance();
        $CI->load->database();

        return $CI->db->where('notification_id', $notificationId)
                     ->update('notifications', array('is_read' => 1));
    }

    /**
     * Mark all as read for user
     */
    public static function markAllAsRead($userType, $userId) {
        $CI =& get_instance();
        $CI->load->database();

        $CI->db->where('user_type', $userType)
               ->where('user_id', $userId);

        return $CI->db->update('notifications', array('is_read' => 1));
    }
}

/* End of file NotificationService.php */
/* Location: ./application/services/NotificationService.php */
