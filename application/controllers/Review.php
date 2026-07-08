<?php
/**
 * Customer product review submission.
 * Lives at the top level because the shop product page posts to
 * BASE_URL.'review/submit'.
 */
class Review extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['url', 'session']);
        $this->load->database();
    }

    public function submit() {
        if ($this->input->method() !== 'post') {
            show_error('Invalid request', 400);
        }

        load_scoped_session('customer');

        if (($this->session->userdata('user_type')) !== 'customer') {
            echo json_encode(['success' => FALSE, 'message' => 'Please log in as a customer to leave a review']);
            return;
        }

        $this->load->library('form_validation');
        $this->form_validation->set_rules('product_id', 'Product', 'required|integer');
        $this->form_validation->set_rules('rating', 'Rating', 'required|in_list[1,2,3,4,5]');
        $this->form_validation->set_rules('review', 'Review', 'required|trim|max_length[1000]');

        if (!$this->form_validation->run()) {
            echo json_encode(['success' => FALSE, 'message' => validation_errors(' ', ' ')]);
            return;
        }

        $customer_id = $this->session->userdata('user_id');
        $product_id = (int) $this->input->post('product_id', TRUE);

        // A review is tied to a specific reseller's listing of the product,
        // recovered from the customer's most recent delivered order that
        // contains this product.
        $purchase = $this->db->select('od.product_id, rp.reseller_product_id, o.order_id')
            ->from(ORDER_TABLE . ' o')
            ->join(ORDER_DETAILS_TABLE . ' od', 'od.order_id = o.order_id')
            ->join(RESELLER_PRODUCTS_TABLE . ' rp', 'rp.product_id = od.product_id AND rp.reseller_id = o.reseller_id')
            ->where('o.customer_id', $customer_id)
            ->where('od.product_id', $product_id)
            ->where('o.order_status', 'delivered')
            ->order_by('o.order_id', 'DESC')
            ->get()->row_array();

        if (!$purchase) {
            echo json_encode(['success' => FALSE, 'message' => 'You can only review products from delivered orders']);
            return;
        }

        $existing = $this->db->where('customer_id', $customer_id)
            ->where('reseller_product_id', $purchase['reseller_product_id'])
            ->get('product_reviews')->row_array();

        if ($existing) {
            echo json_encode(['success' => FALSE, 'message' => 'You have already reviewed this product']);
            return;
        }

        $this->db->insert('product_reviews', [
            'rating' => (int) $this->input->post('rating', TRUE),
            'comment' => $this->input->post('review', TRUE),
            'customer_id' => $customer_id,
            'reseller_product_id' => $purchase['reseller_product_id'],
            'order_id' => $purchase['order_id'],
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        echo json_encode(['success' => TRUE, 'message' => 'Thanks! Your review will appear once approved.']);
    }

    public function approve() {
        return $this->_moderate('approved');
    }

    public function reject() {
        return $this->_moderate('hidden');
    }

    public function delete() {
        if (!$this->_require_admin()) {
            return;
        }

        $review_id = (int) $this->input->post('review_id', TRUE);
        $this->db->where('review_id', $review_id)->delete('product_reviews');
        echo json_encode(['success' => TRUE, 'message' => 'Review deleted']);
    }

    private function _moderate($status) {
        if (!$this->_require_admin()) {
            return;
        }

        $review_id = (int) $this->input->post('review_id', TRUE);
        $this->db->where('review_id', $review_id)->update('product_reviews', [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        echo json_encode(['success' => TRUE, 'message' => 'Review ' . ($status === 'approved' ? 'approved' : 'rejected')]);
    }

    private function _require_admin() {
        load_scoped_session('admin');

        if ($this->input->method() !== 'post' || $this->session->userdata('user_type') !== 'admin') {
            echo json_encode(['success' => FALSE, 'message' => 'Not authorized']);
            return FALSE;
        }
        return TRUE;
    }
}
