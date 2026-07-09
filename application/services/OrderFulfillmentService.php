<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Finalizes an order once PayMongo confirms payment (called from the
 * webhook), or reverses one on a failed/expired payment. Static and
 * self-contained like StockService/CommissionService — safe to call
 * standalone with just an order id.
 *
 * Stock deduction and commission creation are deferred here (rather than
 * happening eagerly at checkout, as the old manual-GCash flow did) so a
 * cart never ties up stock for a payment that's abandoned or fails.
 */
class OrderFulfillmentService {

    /**
     * Finalize ONE order after payment is confirmed. The actual stock
     * deduction, commission creation, and payment-completed notification
     * live in apply_order_status_side_effects() (application/helpers/
     * order_helper.php) — the same shared helper admin/Order.php and
     * staff/Orders.php already use for every other status transition, so
     * an order paid via the webhook and one paid via an admin's manual
     * fallback both get identical treatment (that helper's own
     * "no commission exists yet" check makes it safe to reach from either
     * path without double-deducting).
     *
     * This method's own job is just the webhook-specific atomic guard:
     * only a currently-'pending' order can transition here, so a duplicate
     * webhook delivery for the same event is harmless.
     *
     * @return bool TRUE if this call actually performed finalization.
     */
    public static function finalizeOrderPayment($orderId, $paymongoPaymentId = NULL, $paymongoChannel = NULL) {
        $CI =& get_instance();
        $CI->load->database();
        $CI->load->helper('order');

        $CI->db->trans_start();

        $CI->db->where('order_id', $orderId)->where('order_status', 'pending')
            ->update(ORDER_TABLE, ['order_status' => 'paid', 'updated_at' => date('Y-m-d H:i:s')]);

        if ($CI->db->affected_rows() === 0) {
            $CI->db->trans_complete();
            return FALSE;
        }

        // PayMongo-specific fields on the payment row — everything else
        // about "this order is now paid" is handled by the shared helper.
        $CI->db->where('order_id', $orderId)->update(PAYMENTS_TABLE, [
            'paymongo_payment_id' => $paymongoPaymentId,
            'paymongo_channel' => $paymongoChannel,
        ]);

        $order = $CI->db->where('order_id', $orderId)->get(ORDER_TABLE)->row_array();
        apply_order_status_side_effects($order, 'pending', 'paid');

        $CI->db->trans_complete();
        return $CI->db->trans_status() !== FALSE;
    }

    /**
     * PayMongo reports payment.failed / checkout expired for this order.
     *
     * If the order is still 'pending' (the normal case — nothing was ever
     * deducted for it under this design), just mark it cancelled/failed
     * directly. If it's somehow already 'paid' (a late/out-of-order webhook
     * — should not happen, but handled defensively), delegate to the same
     * shared cancelled/return_refund side effects admin/staff order updates
     * already use, so the reversal (restore stock, reverse commission,
     * flip payment to refunded/failed) is identical either way.
     */
    public static function markOrderPaymentFailed($orderId) {
        $CI =& get_instance();
        $CI->load->database();
        $CI->load->helper('order');

        $order = $CI->db->where('order_id', $orderId)->get(ORDER_TABLE)->row_array();
        if (!$order || in_array($order['order_status'], ['cancelled', 'return_refund'], TRUE)) {
            return FALSE;
        }

        $old_status = $order['order_status'];
        $CI->db->where('order_id', $orderId)->update(ORDER_TABLE, ['order_status' => 'cancelled', 'updated_at' => date('Y-m-d H:i:s')]);

        if ($old_status === 'paid') {
            apply_order_status_side_effects($order, 'paid', 'cancelled');
        } else {
            // Nothing was ever committed for a still-'pending' order — just
            // fail the payment row, no stock/commission to reverse.
            $CI->db->where('order_id', $orderId)->update(PAYMENTS_TABLE, ['status' => 'failed']);
        }

        return TRUE;
    }
}
