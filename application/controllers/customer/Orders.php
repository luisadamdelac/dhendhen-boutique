<?php
class Orders extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper(['url', 'form', 'auth']);
        $this->load->database();
        load_scoped_session('customer');
        if (!ensure_logged_in()) {
            redirect('auth/login');
        }
        $this->load->config('dropsell');
        if ($this->session->userdata('user_type') !== ROLE_CUSTOMER) {
            deny_role_access();
        }
    }

    public function index() {
        $data['page_title'] = 'My Orders';
        $customer_id = $this->session->userdata('user_id');

        // The filter tabs (Pending/Processing/Shipped/Delivered/Cancelled)
        // only ever offer these five exact order_status values, so an exact
        // match is enough — "All Orders" (or no status param) skips the filter.
        $status_filter = $this->input->get('status');
        $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $data['statusFilter'] = in_array($status_filter, $valid_statuses, TRUE) ? $status_filter : 'all';

        // Date range: either a quick preset ("date_range") or an explicit
        // custom start_date/end_date pair from the Filter modal — custom
        // dates win whenever at least one of them is actually set, since
        // picking a custom date implies "ignore the preset".
        $date_range = $this->input->get('date_range');
        $valid_ranges = ['today', '7days', '30days', '90days', 'year'];
        $data['dateRange'] = in_array($date_range, $valid_ranges, TRUE) ? $date_range : 'all';
        $data['startDate'] = $this->input->get('start_date') ?: '';
        $data['endDate'] = $this->input->get('end_date') ?: '';
        $has_custom_range = $data['startDate'] || $data['endDate'];
        if ($has_custom_range) {
            $data['dateRange'] = 'custom';
        }

        // Resolves the active preset/custom range into a concrete
        // [start, end] pair of Y-m-d dates (either bound may be null),
        // applied identically to both the count query and the main query.
        $range_bounds = function () use ($data, $has_custom_range) {
            if ($has_custom_range) {
                return [$data['startDate'] ?: null, $data['endDate'] ?: null];
            }
            $today = date('Y-m-d');
            switch ($data['dateRange']) {
                case 'today':  return [$today, $today];
                case '7days':  return [date('Y-m-d', strtotime('-6 days')), $today];
                case '30days': return [date('Y-m-d', strtotime('-29 days')), $today];
                case '90days': return [date('Y-m-d', strtotime('-89 days')), $today];
                case 'year':   return [date('Y-01-01'), $today];
                default:       return [null, null];
            }
        };
        [$rangeStart, $rangeEnd] = $range_bounds();

        // Tab badge counts — always reflect the customer's full order set,
        // regardless of which tab is currently active.
        $status_rows = $this->db->select('order_status, COUNT(*) as cnt')
            ->from(ORDER_TABLE)
            ->where('customer_id', $customer_id)
            ->group_by('order_status')
            ->get()->result_array();
        $data['statusCounts'] = array_fill_keys($valid_statuses, 0);
        foreach ($status_rows as $row) {
            if (isset($data['statusCounts'][$row['order_status']])) {
                $data['statusCounts'][$row['order_status']] = (int) $row['cnt'];
            }
        }
        $data['totalOrdersCount'] = array_sum($data['statusCounts']);

        $limit = 10;
        $data['perPage'] = $limit;
        $data['currentPage'] = max(1, (int) $this->input->get('page'));
        $offset = ($data['currentPage'] - 1) * $limit;

        $count_query = $this->db->from(ORDER_TABLE)->where('customer_id', $customer_id);
        if ($data['statusFilter'] !== 'all') {
            $count_query->where('order_status', $data['statusFilter']);
        }
        if ($rangeStart) $count_query->where('DATE(created_at) >=', $rangeStart);
        if ($rangeEnd)   $count_query->where('DATE(created_at) <=', $rangeEnd);
        $data['totalFiltered'] = $count_query->count_all_results();
        $data['totalPages'] = max(1, (int) ceil($data['totalFiltered'] / $limit));

        $query = $this->db->select('*')->from(ORDER_TABLE)->where('customer_id', $customer_id);
        if ($data['statusFilter'] !== 'all') {
            $query->where('order_status', $data['statusFilter']);
        }
        if ($rangeStart) $query->where('DATE(created_at) >=', $rangeStart);
        if ($rangeEnd)   $query->where('DATE(created_at) <=', $rangeEnd);
        $data['orders'] = $query->order_by('order_id', 'DESC')->limit($limit, $offset)->get()->result_array();

        // Line items for every order on this page — used for the thumbnail +
        // "N items" shown per row.
        $page_order_ids = array_column($data['orders'], 'order_id');
        $items_by_order = [];
        if (!empty($page_order_ids)) {
            $items = $this->db->select('od.order_id, od.product_id, p.product_name, pi.image_path as product_image')
                ->from(ORDER_DETAILS_TABLE . ' od')
                ->join(PRODUCT_TABLE . ' p', 'p.product_id = od.product_id')
                ->join(PRODUCT_IMAGE_TABLE . ' pi', 'pi.product_id = p.product_id AND pi.is_primary = 1', 'left')
                ->where_in('od.order_id', $page_order_ids)
                ->get()->result_array();
            foreach ($items as $item) {
                $items_by_order[$item['order_id']][] = $item;
            }
        }

        // Reviews this customer already left for delivered orders on this
        // page — product_reviews.order_id is set (by Review::submit()) to the
        // exact delivered order the review was tied to, so this is precise
        // per row rather than a cross-order guess.
        $delivered_ids = array_column(array_filter($data['orders'], fn($o) => $o['order_status'] === 'delivered'), 'order_id');
        $reviews_by_order = [];
        if (!empty($delivered_ids)) {
            $review_rows = $this->db->select('pr.order_id, pr.rating, pr.created_at, rp.product_id')
                ->from('product_reviews pr')
                ->join(RESELLER_PRODUCTS_TABLE . ' rp', 'rp.reseller_product_id = pr.reseller_product_id')
                ->where('pr.customer_id', $customer_id)
                ->where_in('pr.order_id', $delivered_ids)
                ->get()->result_array();
            foreach ($review_rows as $row) {
                $reviews_by_order[$row['order_id']][] = $row;
            }
        }

        foreach ($data['orders'] as &$order) {
            $order_items = $items_by_order[$order['order_id']] ?? [];
            $order['items'] = $order_items;
            $order['items_count'] = count($order_items);

            $order_reviews = $reviews_by_order[$order['order_id']] ?? [];
            $item_product_ids = array_unique(array_column($order_items, 'product_id'));
            $rated_product_ids = array_unique(array_column($order_reviews, 'product_id'));
            $order['fully_rated'] = $order['order_status'] === 'delivered'
                && !empty($item_product_ids)
                && empty(array_diff($item_product_ids, $rated_product_ids));
            $order['avg_rating_given'] = !empty($order_reviews)
                ? array_sum(array_column($order_reviews, 'rating')) / count($order_reviews)
                : 0;
            $order['rated_on'] = !empty($order_reviews) ? max(array_column($order_reviews, 'created_at')) : NULL;
        }
        unset($order);

        // Products this customer has already reviewed (any reseller listing,
        // any order) — mirrors the customer_id + reseller_product_id dedupe
        // Review::submit() enforces, so the rate modal can show "Rated"
        // instead of re-offering the form for a given product.
        $data['reviewedProductIds'] = array_map('intval', array_column(
            $this->db->distinct()->select('rp.product_id')
                ->from('product_reviews pr')
                ->join(RESELLER_PRODUCTS_TABLE . ' rp', 'rp.reseller_product_id = pr.reseller_product_id')
                ->where('pr.customer_id', $customer_id)
                ->get()->result_array(),
            'product_id'
        ));

        $this->load->view('customer/layouts/header', $data);
        $this->load->view('customer/orders/index', $data);
        $this->load->view('customer/layouts/footer', $data);
    }

    public function view($id = null) {
        if (empty($id)) {
            show_404();
        }
        $customer_id = $this->session->userdata('user_id');
        $data['order'] = $this->db->select('*')->from(ORDER_TABLE)->where('order_id', $id)->where('customer_id', $customer_id)->get()->row_array();
        if (empty($data['order'])) {
            show_404();
        }
        $data['order']['items'] = $this->db->select('od.*, od.total_price as subtotal, p.product_name, pi.image_path as product_image')
            ->from(ORDER_DETAILS_TABLE . ' od')
            ->join(PRODUCT_TABLE . ' p', 'p.product_id = od.product_id')
            ->join(PRODUCT_IMAGE_TABLE . ' pi', 'pi.product_id = p.product_id AND pi.is_primary = 1', 'left')
            ->where('od.order_id', $id)
            ->get()->result_array();

        $customer = $this->db->select('c.*, u.email')
            ->from(CUSTOMER_TABLE . ' c')
            ->join(USER_ACCOUNT_TABLE . ' u', 'c.user_account_id = u.user_account_id')
            ->where('c.customer_id', $customer_id)
            ->get()->row_array();

        $data['order']['customer_name'] = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));
        $data['order']['customer_email'] = $customer['email'] ?? '';
        $data['order']['customer_phone'] = $customer['contact_number'] ?? '';
        $data['order']['payment'] = $this->db->where('order_id', $id)->order_by('payment_id', 'DESC')->limit(1)->get(PAYMENTS_TABLE)->row_array();
        $data['order']['delivery_type'] = $data['order']['delivery_method'];
        $data['order']['delivery_address'] = trim(implode(', ', array_filter([
            $data['order']['delivery_street'],
            $data['order']['delivery_barangay'],
            $data['order']['delivery_city'],
            $data['order']['delivery_zip_code'],
        ])));
        $data['order']['subtotal'] = $data['order']['total_amount'] - $data['order']['delivery_fee'];

        $data['page_title'] = 'Order Details';
        $this->load->view('customer/layouts/header', $data);
        $this->load->view('customer/orders/details', $data);
        $this->load->view('customer/layouts/footer', $data);
    }

    public function cancel($id = null) {
        $customer_id = $this->session->userdata('user_id');
        $order = $this->db->where('order_id', $id)->where('customer_id', $customer_id)->get(ORDER_TABLE)->row_array();

        if (!$order || $order['order_status'] !== 'pending') {
            $this->session->set_flashdata('error', 'This order can no longer be cancelled');
            redirect('customer/orders/view/' . $id);
        }

        require_once APPPATH . 'services/StockService.php';
        require_once APPPATH . 'services/CommissionService.php';

        // Wrapped so a failure partway through (stock restore, commission
        // reversal) can't leave the order flipped to 'cancelled' while its
        // stock/commission consequences never actually landed.
        $this->db->trans_start();

        $items = $this->db->where('order_id', $id)->get(ORDER_DETAILS_TABLE)->result_array();
        foreach ($items as $item) {
            if (!empty($item['variant_id'])) {
                // Mirror Checkout.php's pieces_per_unit multiplier — a
                // "1 Set (10 pcs)" line deducted 10 pieces, so restoring it
                // must add back 10, not 1.
                $piecesPerUnit = StockService::getVariantPiecesPerUnit((int) $item['variant_id']);
                StockService::restoreStock($item['product_id'], $item['quantity'] * $piecesPerUnit, 'order', $id, NULL, NULL, 'ANY', (int) $item['variant_id']);
            } elseif (!empty($item['variation_id'])) {
                $piecesPerUnit = StockService::getVariationPiecesPerUnit((int) $item['variation_id']);
                StockService::restoreStock($item['product_id'], $item['quantity'] * $piecesPerUnit, 'order', $id, NULL, NULL, (int) $item['variation_id']);
            } else {
                StockService::restoreStock($item['product_id'], $item['quantity'], 'order', $id);
            }
        }
        CommissionService::reverseCommission($id, 'Order cancelled by customer');

        $this->db->where('order_id', $id)->update(ORDER_TABLE, [
            'order_status' => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Failed to cancel order. Please try again.');
            redirect('customer/orders/view/' . $id);
        }

        $this->session->set_flashdata('success', 'Order cancelled successfully');
        redirect('customer/orders/view/' . $id);
    }
}
