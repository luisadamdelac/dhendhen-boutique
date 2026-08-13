<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Order extends Authenticated_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_role('admin');
        $this->load->model(['Order_model', 'Activity_log_model']);
        $this->load->helper('order');
        $this->load->library('form_validation');
    }

    /**
     * Order list view
     */
    public function index() {
        $data['page_title'] = 'Orders';
        $data['current_page'] = 'orders';

        // The DataTable on the list view now handles search, sorting,
        // pagination, and filtering client-side, so the full order set is
        // fetched here in one go rather than one server-paginated slice.
        $data['orders'] = $this->_order_list_query()
            ->order_by('o.order_id', 'DESC')
            ->get()->result_array();
        $this->_attach_latest_payments($data['orders']);

        $data['total'] = count($data['orders']);
        $data['order_stats'] = $this->_get_order_stats();

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/order/list', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Base select for an order row, including the customer's name/contact
     * captured at registration (previously missing — list.php expects
     * customer_name/customer_phone/customer_email and always showed "N/A").
     */
    private function _order_list_query() {
        return $this->db->select("o.*, r.first_name as reseller_first_name, r.last_name as reseller_last_name,
                TRIM(CONCAT(c.first_name, ' ', c.last_name)) as customer_name,
                c.contact_number as customer_phone,
                u.email as customer_email", FALSE)
            ->from(ORDER_TABLE . ' o')
            ->join(RESELLER_TABLE . ' r', 'o.reseller_id = r.reseller_id', 'left')
            ->join(CUSTOMER_TABLE . ' c', 'o.customer_id = c.customer_id', 'left')
            ->join(USER_ACCOUNT_TABLE . ' u', 'c.user_account_id = u.user_account_id', 'left');
    }

    /**
     * Attach each order's most recent payment transaction (payment_method +
     * status). Done as a separate batched query instead of joining
     * payment_transaction_tbl directly, since an order can have more than
     * one transaction (retries) and we only want the latest.
     */
    private function _attach_latest_payments(array &$orders): void {
        if (empty($orders)) {
            return;
        }
        $order_ids = array_column($orders, 'order_id');
        $latest = $this->db
            ->select('pt.order_id, pt.payment_method, pt.status as payment_tx_status, pt.payment_reference, pt.gcash_sender_number, pt.receipt_image, pt.amount as payment_amount, pt.paid_at')
            ->from(PAYMENTS_TABLE . ' pt')
            ->join('(SELECT order_id, MAX(payment_id) as latest_id FROM ' . PAYMENTS_TABLE . ' GROUP BY order_id) latest_pt',
                'latest_pt.order_id = pt.order_id AND latest_pt.latest_id = pt.payment_id', 'inner', FALSE)
            ->where_in('pt.order_id', $order_ids)
            ->get()->result_array();

        $by_order_id = [];
        foreach ($latest as $row) {
            $by_order_id[$row['order_id']] = $row;
        }

        foreach ($orders as &$order) {
            if (isset($by_order_id[$order['order_id']])) {
                $order = array_merge($order, $by_order_id[$order['order_id']]);
            }
        }
        unset($order);
    }

    private function _get_order_stats() {
        $total_sales_row = $this->db->select_sum('total_amount')->from(ORDER_TABLE)->get()->row();
        $total_orders = $this->db->from(ORDER_TABLE)->count_all_results();
        $total_sales = (float) ($total_sales_row->total_amount ?? 0);

        return [
            'total_orders'          => $total_orders,
            'pending_orders'        => $this->db->from(ORDER_TABLE)->where('order_status', 'pending')->count_all_results(),
            'processing_orders'     => $this->db->from(ORDER_TABLE)->where('order_status', 'processing')->count_all_results(),
            'shipped_orders'        => $this->db->from(ORDER_TABLE)->where_in('order_status', ['to_ship', 'shipped'])->count_all_results(),
            'delivered_orders'      => $this->db->from(ORDER_TABLE)->where('order_status', 'delivered')->count_all_results(),
            'paid_orders'           => $this->db->from(ORDER_TABLE)->where('order_status', 'paid')->count_all_results(),
            'cancelled_orders'      => $this->db->from(ORDER_TABLE)->where('order_status', 'cancelled')->count_all_results(),
            'refund_return_orders'  => $this->db->from(ORDER_TABLE)->where('order_status', 'return_refund')->count_all_results(),
            'total_sales'           => $total_sales,
            'average_order_value'  => $total_orders > 0 ? $total_sales / $total_orders : 0,
        ];
    }

    /**
     * View order details
     */
    public function view($order_id = '') {
        $data['page_title'] = 'Order Details';
        $data['current_page'] = 'orders';
        if (empty($order_id)) {
            redirect('admin/order');
        }

        $order = $this->_order_list_query()->where('o.order_id', $order_id)->get()->row_array();

        if (!$order) {
            $this->session->set_flashdata('error', 'Order not found');
            redirect('admin/order');
        }

        $orders = [$order];
        $this->_attach_latest_payments($orders);
        $data['order'] = $orders[0];
        $data['items'] = $this->db->select('od.*, p.product_name')
            ->from(ORDER_DETAILS_TABLE . ' od')
            ->join(PRODUCT_TABLE . ' p', 'od.product_id = p.product_id', 'left')
            ->where('od.order_id', $order_id)
            ->get()->result_array();

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/order/view', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Create or update the payment transaction record for an order —
     * previously there was no way for an admin to record/change a
     * payment's status from the orders list at all.
     */
    public function update_payment($order_id = '') {
        if (empty($order_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $order = $this->db->select('*')->from(ORDER_TABLE)->where('order_id', $order_id)->get()->row_array();
        if (!$order) {
            echo json_encode(['success' => FALSE, 'message' => 'Order not found']);
            return;
        }

        $valid_statuses = ['pending', 'completed', 'failed', 'refunded'];
        $status = $this->input->post('payment_status', TRUE);
        if (!in_array($status, $valid_statuses, TRUE)) {
            echo json_encode(['success' => FALSE, 'message' => 'Invalid payment status']);
            return;
        }

        $rejection_reason = trim((string) $this->input->post('rejection_reason', TRUE));
        if ($status === 'failed' && $rejection_reason === '') {
            echo json_encode(['success' => FALSE, 'message' => 'Please provide a reason for rejecting this payment.']);
            return;
        }

        $existing = $this->db->select('payment_id, status')->from(PAYMENTS_TABLE)
            ->where('order_id', $order_id)->order_by('payment_id', 'DESC')->limit(1)->get()->row_array();

        // Once a payment is Completed, it's final — approving it already
        // deducted stock and created a commission, so silently reverting it
        // here would desync those from the payment record. Use Refunds
        // (which properly reverses stock/commission) to undo a completed
        // payment instead.
        if ($existing && $existing['status'] === 'completed' && $status !== 'completed') {
            echo json_encode(['success' => FALSE, 'message' => 'This payment is already marked Completed and can\'t be changed back. Use the Refund flow to reverse it instead.']);
            return;
        }

        // Status only — the payment_reference column (the customer's GCash
        // reference number, captured at checkout) is intentionally left
        // untouched here so this action can never overwrite it.
        $payment_data = [
            'status'             => $status,
            'paid_at'            => $status === 'completed' ? date('Y-m-d H:i:s') : NULL,
            'rejection_reason'   => $status === 'failed' ? $rejection_reason : NULL,
            'updated_at'         => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->db->where('payment_id', $existing['payment_id'])->update(PAYMENTS_TABLE, $payment_data);
        } else {
            $payment_data['payment_number'] = 'PAY-' . date('YmdHis') . '-' . $order_id;
            $payment_data['order_id']       = $order_id;
            $payment_data['customer_id']    = $order['customer_id'];
            $payment_data['amount']         = $order['total_amount'];
            $payment_data['payment_method'] = 'GCash';
            $payment_data['created_at']     = date('Y-m-d H:i:s');
            $this->db->insert(PAYMENTS_TABLE, $payment_data);
        }

        $this->Activity_log_model->log_activity(get_user_id(), 'admin', 'update_order_payment', "Set payment status to $status for order $order_id", get_client_ip());

        if ($status === 'completed') {
            require_once APPPATH . 'services/NotificationService.php';
            NotificationService::paymentApprovedForStaff($order_id, $order['order_number']);

            // Confirming payment is what unlocks an order for staff fulfillment
            // (staff can only act once order_status is paid/processing/to_ship),
            // so advance it here rather than leaving it stuck on pending.
            if ($order['order_status'] === 'pending') {
                $this->db->update(ORDER_TABLE, ['order_status' => 'paid', 'updated_at' => date('Y-m-d H:i:s')], ['order_id' => $order_id]);
                apply_order_status_side_effects($order, 'pending', 'paid');
            }
        } elseif ($status === 'failed') {
            require_once APPPATH . 'services/NotificationService.php';
            NotificationService::paymentRejected($order_id, $order['customer_id'], $rejection_reason);
        }

        echo json_encode(['success' => TRUE, 'message' => 'Payment status updated successfully']);
    }

    /**
     * Read-only details panel for the Actions column on statuses past
     * Payment (Delivered → commission, Return/Refund → refund request,
     * Cancelled → basic cancellation info). Never updates anything.
     */
    public function action_details($order_id = '') {
        $order = $this->db->select('*')->from(ORDER_TABLE)->where('order_id', $order_id)->get()->row_array();
        if (!$order) {
            echo json_encode(['success' => FALSE, 'message' => 'Order not found']);
            return;
        }

        $status = $order['order_status'];

        if ($status === 'delivered') {
            $commission = $this->db->select('c.*, r.first_name, r.last_name')
                ->from(COMMISSIONS_TABLE . ' c')
                ->join(RESELLER_TABLE . ' r', 'c.reseller_id = r.reseller_id', 'left')
                ->where('c.order_id', $order_id)
                ->get()->row_array();
            echo json_encode(['success' => TRUE, 'type' => 'commission', 'data' => $commission ?: NULL]);
            return;
        }

        if ($status === 'return_refund') {
            $refund = $this->db->select('*')->from(REFUND_REQUEST_TABLE)
                ->where('order_id', $order_id)->order_by('refund_id', 'DESC')->limit(1)->get()->row_array();
            echo json_encode(['success' => TRUE, 'type' => 'refund', 'data' => $refund ?: NULL]);
            return;
        }

        if ($status === 'cancelled') {
            $commission_exists = $this->db->from(COMMISSIONS_TABLE)->where('order_id', $order_id)->count_all_results() > 0;
            echo json_encode(['success' => TRUE, 'type' => 'cancellation', 'data' => [
                'cancelled_at' => $order['updated_at'],
                'commission_reversed' => !$commission_exists,
            ]]);
            return;
        }

        echo json_encode(['success' => FALSE, 'message' => 'No details available for this status']);
    }

    /**
     * Update order status
     */
    public function update_status($order_id = '') {
        if (empty($order_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $this->form_validation->set_rules('order_status', 'Order Status', 'required');
        if ($this->form_validation->run() === FALSE) {
            echo json_encode(['success' => FALSE, 'message' => validation_errors(' ', ' ')]);
            return;
        }

        $order = $this->db->select('*')->from(ORDER_TABLE)->where('order_id', $order_id)->get()->row_array();
        if (!$order) {
            echo json_encode(['success' => FALSE, 'message' => 'Order not found']);
            return;
        }

        $valid_statuses = ['pending', 'paid', 'processing', 'to_ship', 'shipped', 'delivered', 'return_refund', 'cancelled'];
        $new_status = $this->input->post('order_status');
        if (!in_array($new_status, $valid_statuses, TRUE)) {
            echo json_encode(['success' => FALSE, 'message' => 'Invalid status']);
            return;
        }

        $old_status = $order['order_status'];

        // Wrapped so a failure partway through the side effects (stock
        // restore, commission release/reversal, payment status flip) can't
        // leave order_status changed while the rest of that transition's
        // consequences never actually landed.
        $this->db->trans_start();
        $this->db->update(ORDER_TABLE, ['order_status' => $new_status, 'updated_at' => date('Y-m-d H:i:s')], ['order_id' => $order_id]);
        apply_order_status_side_effects($order, $old_status, $new_status);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => FALSE, 'message' => 'Failed to update order status. Please try again.']);
            return;
        }

        $this->Activity_log_model->log_activity(get_user_id(), 'admin', 'update_order_status', "Updated order $order_id status to $new_status", get_client_ip());
        echo json_encode(['success' => TRUE, 'message' => 'Order status updated successfully']);
    }

    /**
     * Generate invoice
     */
    public function invoice($order_id = '') {
        $data['page_title'] = 'Order Invoice';
        $data['current_page'] = 'orders';
        if (empty($order_id)) {
            redirect('admin/order');
        }

        $order = $this->_order_list_query()->where('o.order_id', $order_id)->get()->row_array();

        if (!$order) {
            $this->session->set_flashdata('error', 'Order not found');
            redirect('admin/order');
        }

        $data['order'] = $order;
        $data['items'] = $this->db->select('od.*, p.product_name')
            ->from(ORDER_DETAILS_TABLE . ' od')
            ->join(PRODUCT_TABLE . ' p', 'od.product_id = p.product_id', 'left')
            ->where('od.order_id', $order_id)
            ->get()->result_array();

        $this->load->view('admin/order/invoice', $data);
    }

    /**
     * Delete order
     */
    public function delete($order_id = '') {
        if (empty($order_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $order = $this->db->select('*')->from(ORDER_TABLE)->where('order_id', $order_id)->get()->row_array();
        if (!$order) {
            echo json_encode(['success' => FALSE, 'message' => 'Order not found']);
            return;
        }

        $this->db->trans_start();
        $this->db->delete(ORDER_DETAILS_TABLE, ['order_id' => $order_id]);
        $this->db->delete(ORDER_TABLE, ['order_id' => $order_id]);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => FALSE, 'message' => 'Failed to delete order']);
        } else {
            $this->Activity_log_model->log_activity(get_user_id(), 'admin', 'delete_order', 'Deleted order: ' . $order_id, get_client_ip());
            echo json_encode(['success' => TRUE, 'message' => 'Order deleted successfully']);
        }
    }
}
?>
