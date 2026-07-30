<?php
class Orders extends Authenticated_Controller {
    const PER_PAGE = 10;
    const VALID_STATUSES = ['pending', 'paid', 'processing', 'to_ship', 'shipped', 'delivered', 'cancelled', 'return_refund'];
    const VALID_PAYMENT_STATUSES = ['pending', 'completed', 'failed', 'refunded'];

    public function __construct() {
        parent::__construct();
        $this->require_role(ROLE_RESELLER);
        $this->load->model(['order_model', 'activity_log_model', 'Settings_model']);
    }

    public function index() {
        $data = $this->set_view_data();
        $data['page_title'] = 'Reseller Orders';
        $data['commissionRate'] = (float) $this->Settings_model->get_commission_rate();

        $search = trim((string) $this->input->get('search', TRUE));
        $status_filter = $this->input->get('status', TRUE);
        $payment_filter = $this->input->get('payment', TRUE);
        $start_date = $this->input->get('start_date', TRUE);
        $end_date = $this->input->get('end_date', TRUE);

        $data['search'] = $search;
        $data['statusFilter'] = in_array($status_filter, self::VALID_STATUSES, TRUE) ? $status_filter : '';
        $data['paymentFilter'] = in_array($payment_filter, self::VALID_PAYMENT_STATUSES, TRUE) ? $payment_filter : '';
        $data['startDate'] = $start_date ?: '';
        $data['endDate'] = $end_date ?: '';

        // Every order's most recent payment attempt — a single order can have
        // several payment_tbl rows (e.g. a failed try followed by a retry),
        // so this correlated-subquery join keeps exactly one row per order
        // instead of multiplying the result set.
        $apply_filters = function ($builder) use ($data) {
            $builder->join(CUSTOMER_TABLE . ' c', 'o.customer_id = c.customer_id', 'left')
                ->join(PAYMENTS_TABLE . ' pay', 'pay.payment_id = (SELECT MAX(p2.payment_id) FROM ' . PAYMENTS_TABLE . ' p2 WHERE p2.order_id = o.order_id)', 'left', FALSE)
                ->where('o.reseller_id', $this->user_id);

            if ($data['search'] !== '') {
                $builder->group_start()
                    ->like('o.order_id', $data['search'])
                    ->or_like('c.first_name', $data['search'])
                    ->or_like('c.last_name', $data['search'])
                    ->group_end();
            }
            if ($data['statusFilter'] !== '') {
                $builder->where('o.order_status', $data['statusFilter']);
            }
            if ($data['paymentFilter'] !== '') {
                $builder->where('pay.status', $data['paymentFilter']);
            }
            if (!empty($data['startDate'])) {
                $builder->where('o.created_at >=', $data['startDate'] . ' 00:00:00');
            }
            if (!empty($data['endDate'])) {
                $builder->where('o.created_at <=', $data['endDate'] . ' 23:59:59');
            }
            return $builder;
        };

        $data['totalFiltered'] = $apply_filters($this->db->from(ORDER_TABLE . ' o'))->count_all_results();

        $data['perPage'] = self::PER_PAGE;
        $data['currentPage'] = max(1, (int) $this->input->get('page'));
        $data['totalPages'] = max(1, (int) ceil($data['totalFiltered'] / self::PER_PAGE));
        $offset = ($data['currentPage'] - 1) * self::PER_PAGE;

        $data['orders'] = $apply_filters(
            $this->db->select('o.*, c.first_name, c.last_name, c.contact_number, c.profile_image, pay.status as payment_status')
                ->from(ORDER_TABLE . ' o')
        )->order_by('o.order_id', 'DESC')->limit(self::PER_PAGE, $offset)->get()->result_array();

        // Commission per order is a separate LEFT JOIN (not folded into the
        // filtered builder above) since it isn't filterable here and keeping
        // it out avoids a second correlated subquery on every filter call.
        $order_ids = array_column($data['orders'], 'order_id');
        $commissions_by_order = [];
        if (!empty($order_ids)) {
            $commission_rows = $this->db->select('order_id, amount')->from(COMMISSIONS_TABLE)->where_in('order_id', $order_ids)->get()->result_array();
            foreach ($commission_rows as $row) {
                $commissions_by_order[$row['order_id']] = $row['amount'];
            }
        }

        // Product thumbnails + item count per order, for the "Products" column.
        $items_by_order = [];
        if (!empty($order_ids)) {
            $items = $this->db->select('od.order_id, od.product_id, p.product_name, pi.image_path as product_image')
                ->from(ORDER_DETAILS_TABLE . ' od')
                ->join(PRODUCT_TABLE . ' p', 'p.product_id = od.product_id', 'left')
                ->join(PRODUCT_IMAGE_TABLE . ' pi', 'pi.product_id = p.product_id AND pi.is_primary = 1', 'left')
                ->where_in('od.order_id', $order_ids)
                ->get()->result_array();
            foreach ($items as $item) {
                $items_by_order[$item['order_id']][] = $item;
            }
        }

        foreach ($data['orders'] as &$order) {
            $order['customer_name'] = trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? ''));
            $order['commission_amount'] = $commissions_by_order[$order['order_id']] ?? NULL;
            $order['items'] = $items_by_order[$order['order_id']] ?? [];
            $order['items_count'] = count($order['items']);
        }
        unset($order);

        $this->load->view('reseller/layouts/header', $data);
        $this->load->view('reseller/orders/index', $data);
        $this->load->view('reseller/layouts/footer', $data);
    }

    public function view($order_id = NULL) {
        $order = $this->db->select('o.*, c.first_name, c.last_name, c.contact_number, u.email')
            ->from(ORDER_TABLE . ' o')
            ->join(CUSTOMER_TABLE . ' c', 'o.customer_id = c.customer_id', 'left')
            ->join(USER_ACCOUNT_TABLE . ' u', 'c.user_account_id = u.user_account_id', 'left')
            ->where('o.order_id', $order_id)
            ->where('o.reseller_id', $this->user_id)
            ->get()->row_array();

        if (!$order) {
            show_404();
        }

        $order['customer_name'] = trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? ''));
        $order['customer_email'] = $order['email'] ?? '';
        $order['customer_phone'] = $order['contact_number'] ?? '';
        $order['delivery_address'] = trim(implode(', ', array_filter([
            $order['delivery_street'], $order['delivery_barangay'], $order['delivery_city'], $order['delivery_zip_code'],
        ])));

        $payment = $this->db->where('order_id', $order_id)->get(PAYMENTS_TABLE)->row_array();
        $order['payment_method'] = $payment['payment_method'] ?? 'GCash';
        $order['payment_status'] = $payment['status'] ?? 'pending';

        $commission = $this->db->where('order_id', $order_id)->get(COMMISSIONS_TABLE)->row_array();
        $order['commission_amount'] = $commission['amount'] ?? 0;

        $order['items'] = $this->db->select('od.*, od.unit_price as price, p.product_name, pi.image_path as product_image')
            ->from(ORDER_DETAILS_TABLE . ' od')
            ->join(PRODUCT_TABLE . ' p', 'od.product_id = p.product_id', 'left')
            ->join(PRODUCT_IMAGE_TABLE . ' pi', 'pi.product_id = p.product_id AND pi.is_primary = 1', 'left')
            ->where('od.order_id', $order_id)
            ->get()->result_array();

        $data = $this->set_view_data();
        $data['page_title'] = 'Order Details';
        $data['order'] = $order;

        $this->load->view('reseller/layouts/header', $data);
        $this->load->view('reseller/orders/view', $data);
        $this->load->view('reseller/layouts/footer', $data);
    }
}
