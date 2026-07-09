<?php
class Orders extends Authenticated_Controller {
    public function __construct() {
        parent::__construct();
        $this->require_role(ROLE_STAFF);
        $this->load->model(['order_model', 'activity_log_model']);
        $this->load->helper('order');
    }

    public function index() {
        $data = $this->set_view_data();
        $data['page_title'] = 'Staff Orders';
        $data['orders'] = $this->db->select("o.*, TRIM(CONCAT(c.first_name, ' ', c.last_name)) as customer_name,
                c.contact_number as customer_phone", FALSE)
            ->from(ORDER_TABLE . ' o')
            ->join(CUSTOMER_TABLE . ' c', 'o.customer_id = c.customer_id', 'left')
            ->order_by('o.order_id', 'DESC')
            ->get()->result_array();
        $this->load->view('staff/layouts/header', $data);
        $this->load->view('staff/orders/index', $data);
        $this->load->view('staff/layouts/footer', $data);
    }

    /**
     * Staff only manage fulfillment once admin has confirmed payment —
     * Paid/Pending stay admin-only, Delivered is the terminal handoff.
     */
    public function update_status($order_id = '') {
        if (empty($order_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $order = $this->db->select('*')->from(ORDER_TABLE)->where('order_id', $order_id)->get()->row_array();
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }

        if (!in_array($order['order_status'], ['paid', 'processing', 'to_ship'], TRUE)) {
            $message = $order['order_status'] === 'pending'
                ? "This order's payment hasn't been verified by admin yet — you'll be able to update it once payment is confirmed."
                : 'This order is not yet ready for staff action';
            echo json_encode(['success' => false, 'message' => $message]);
            return;
        }

        $valid_statuses = ['processing', 'to_ship', 'delivered'];
        $new_status = $this->input->post('order_status');
        if (!in_array($new_status, $valid_statuses, TRUE)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            return;
        }

        $old_status = $order['order_status'];
        $this->db->update(ORDER_TABLE, ['order_status' => $new_status, 'updated_at' => date('Y-m-d H:i:s')], ['order_id' => $order_id]);

        apply_order_status_side_effects($order, $old_status, $new_status);

        $this->activity_log_model->create(['user_type' => 'staff', 'user_id' => $this->user_id, 'action' => 'update_order_status', 'entity_type' => 'order', 'entity_id' => $order_id, 'details' => 'Updated order to ' . $new_status, 'ip_address' => $this->input->ip_address()]);
        echo json_encode(['success' => true, 'message' => 'Order status updated']);
    }
}
