<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Side effects that must fire whenever an order's status changes, shared by
 * staff/Orders.php and admin/Order.php so both update paths stay consistent
 * — and, since the PayMongo checkout flow, also by
 * OrderFulfillmentService::finalizeOrderPayment() (the webhook handler).
 *
 * - paid: the matching payment_transaction_tbl row is marked completed, and
 *   — since stock/commission are no longer created eagerly at checkout —
 *   stock is deducted and a commission is created here too, guarded by
 *   "no commission exists for this order yet" so this is safe to reach via
 *   more than one path (webhook + a manual admin fallback) without double
 *   -deducting.
 * - to_ship: each item's total_sold is counted.
 * - delivered: once the order has successfully reached the Delivered status,
 *   the reseller automatically receives their commission, which is credited
 *   to their dedicated internal e-wallet. releaseCommission() only ever
 *   matches a 'pending' commission row, so re-saving Delivered (or bouncing
 *   through other statuses back to Delivered) does not credit twice.
 * - cancelled / return_refund: undoes the above — restores stock and
 *   reverses the commission (only claws back the wallet if it had already
 *   been released), and flips the payment row to refunded/failed — for
 *   orders that hadn't already been reversed via this same path.
 */
if (!function_exists('apply_order_status_side_effects')) {
    function apply_order_status_side_effects($order, $old_status, $new_status) {
        if ($old_status === $new_status) {
            return;
        }

        $CI =& get_instance();
        $CI->load->database();

        require_once APPPATH . 'services/CommissionService.php';
        require_once APPPATH . 'services/StockService.php';
        require_once APPPATH . 'services/NotificationService.php';

        $order_id = $order['order_id'];

        if ($new_status === 'paid') {
            $payment = $CI->db->where('order_id', $order_id)->get(PAYMENTS_TABLE)->row_array();
            if ($payment && $payment['status'] === 'pending') {
                $CI->db->update(PAYMENTS_TABLE, [
                    'status' => 'completed',
                    'paid_at' => date('Y-m-d H:i:s'),
                ], ['payment_id' => $payment['payment_id']]);

                NotificationService::paymentVerified($order_id, $order['customer_id'], $payment['amount']);
            }

            // Stock deduction + commission creation are deferred until an
            // order is actually paid (see customer/Checkout.php) — do that
            // here, guarded by "no commission exists yet for this order" so
            // it only ever runs once no matter which path reached 'paid'.
            $already_fulfilled = $CI->db->where('order_id', $order_id)->get(COMMISSIONS_TABLE)->row_array();
            if (!$already_fulfilled) {
                $items = $CI->db->where('order_id', $order_id)->get(ORDER_DETAILS_TABLE)->result_array();
                $stock_shortfall = FALSE;
                foreach ($items as $item) {
                    if (!empty($item['variation_id'])) {
                        $CI->db->where('variation_id', $item['variation_id'])
                            ->where('stock >=', $item['quantity'])
                            ->set('stock', 'stock - ' . (int) $item['quantity'], FALSE)
                            ->update(PRODUCT_VARIATION_TABLE);
                        if ($CI->db->affected_rows() === 0) {
                            $stock_shortfall = TRUE;
                        }
                    } elseif (!StockService::deductStock($item['product_id'], $item['quantity'], 'sale', 'order', $order_id)) {
                        $stock_shortfall = TRUE;
                    }
                }

                if ($stock_shortfall) {
                    // Payment is real; stock isn't there. Flag for a human
                    // rather than silently shipping an unfulfillable order.
                    log_message('error', 'Order ' . $order_id . ' marked paid but stock unavailable at fulfillment.');
                    NotificationService::notifyAllAdmins(
                        'Stock issue on paid order',
                        'Order #' . ($order['order_number'] ?? $order_id) . ' was marked paid but stock could not be fully deducted — please review.',
                        'order',
                        $order_id
                    );
                }

                CommissionService::createCommission($order_id, $order['reseller_id'], $order['total_amount'] - $order['delivery_fee'], NULL);
            }
        } elseif ($new_status === 'to_ship') {
            $items = $CI->db->where('order_id', $order_id)->get(ORDER_DETAILS_TABLE)->result_array();
            foreach ($items as $item) {
                $CI->db->set('total_sold', 'total_sold + ' . (int) $item['quantity'], FALSE)
                    ->where('product_id', $item['product_id'])
                    ->update(PRODUCT_TABLE);
            }
        } elseif ($new_status === 'delivered') {
            CommissionService::releaseCommission($order_id);
        } elseif (in_array($new_status, ['cancelled', 'return_refund'], TRUE) && !in_array($old_status, ['cancelled', 'return_refund'], TRUE)) {
            // Stock/commission are only ever committed once an order reaches
            // 'paid' (checkout defers both until then — see the 'paid'
            // branch above), so a still-'pending' order being cancelled has
            // nothing to restore. Restoring here anyway would incorrectly
            // *add* stock that was never deducted in the first place.
            if ($old_status !== 'pending') {
                $items = $CI->db->where('order_id', $order_id)->get(ORDER_DETAILS_TABLE)->result_array();
                foreach ($items as $item) {
                    // Variation lines were deducted from product_variation_tbl.stock
                    // at checkout, not from the branch/batch system — restoring
                    // them via StockService would wrongly inflate branch stock
                    // while leaving the variation's own stock permanently short.
                    if (!empty($item['variation_id'])) {
                        $CI->db->set('stock', 'stock + ' . (int) $item['quantity'], FALSE)
                            ->where('variation_id', $item['variation_id'])
                            ->update(PRODUCT_VARIATION_TABLE);
                    } else {
                        StockService::restoreStock($item['product_id'], $item['quantity'], 'order', $order_id);
                    }
                }
                CommissionService::reverseCommission($order_id, 'Order ' . $new_status);
            }

            $payment = $CI->db->where('order_id', $order_id)->get(PAYMENTS_TABLE)->row_array();
            if ($payment && !in_array($payment['status'], ['refunded', 'failed'], TRUE)) {
                $CI->db->update(PAYMENTS_TABLE, [
                    'status' => $payment['status'] === 'completed' ? 'refunded' : 'failed',
                ], ['payment_id' => $payment['payment_id']]);
            }
        }

        NotificationService::orderStatusChanged($order_id, $order['customer_id'], $old_status, $new_status);
    }
}
