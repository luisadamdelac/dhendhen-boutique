<?php
class Dashboard extends Authenticated_Controller {
    public function __construct() {
        parent::__construct();
        $this->require_role(ROLE_RESELLER);
        $this->load->model(['order_model', 'commission_model', 'activity_log_model', 'Notification_model', 'reseller_model']);
    }

    public function index() {
        $data = $this->set_view_data();
        $data['page_title'] = 'Reseller Dashboard';
        $reseller_id = $this->user_id;

        $total_sales = $this->db->select_sum('total_amount')->from(ORDER_TABLE)->where('reseller_id', $reseller_id)->get()->row();
        $data['stats'] = [
            'total_sales' => $total_sales->total_amount ?? 0,
            'total_orders' => $this->db->from(ORDER_TABLE)->where('reseller_id', $reseller_id)->count_all_results(),
            'pending_orders' => $this->db->from(ORDER_TABLE)->where('reseller_id', $reseller_id)->where('order_status', 'pending')->count_all_results(),
            'processing_orders' => $this->db->from(ORDER_TABLE)->where('reseller_id', $reseller_id)->where('order_status', 'processing')->count_all_results(),
            'to_ship_orders' => $this->db->from(ORDER_TABLE)->where('reseller_id', $reseller_id)->where('order_status', 'to_ship')->count_all_results(),
            'delivered_orders' => $this->db->from(ORDER_TABLE)->where('reseller_id', $reseller_id)->where('order_status', 'delivered')->count_all_results(),
            'cancelled_orders' => $this->db->from(ORDER_TABLE)->where('reseller_id', $reseller_id)->where('order_status', 'cancelled')->count_all_results(),
        ];

        $daily = $this->_get_daily_trend($reseller_id);
        $data['this_week'] = array_slice($daily, 7, 7);
        $data['last_week'] = array_slice($daily, 0, 7);
        $data['trend'] = $this->_trend_percentages($data['last_week'], $data['this_week']);

        $data['commission'] = $this->_get_commission_breakdown($reseller_id);
        $data['recent_orders'] = $this->_get_recent_orders($reseller_id, 5);
        $data['top_products'] = $this->_get_top_products($reseller_id, 5);
        $data['unread_notifications'] = $this->Notification_model->get_unread_count($reseller_id, 'reseller');

        $this->activity_log_model->create(['user_type' => 'reseller', 'user_id' => $this->user_id, 'action' => 'view_dashboard', 'details' => 'Reseller viewed dashboard', 'ip_address' => $this->input->ip_address()]);
        $this->load->view('reseller/layouts/header', $data);
        $this->load->view('reseller/dashboard/index', $data);
        $this->load->view('reseller/layouts/footer', $data);
    }

    /**
     * Per-day order activity for the last 14 days (7 for "last week" + 7 for
     * "this week"), used for both the Sales Overview comparison chart and
     * each stat card's mini sparkline. The per-status counts are "orders
     * placed that day that are currently in this status" — a simple,
     * honest read of real data rather than a true historical snapshot
     * (the app doesn't keep a day-by-day status history to reconstruct that).
     */
    private function _get_daily_trend($reseller_id) {
        $start = date('Y-m-d', strtotime('-13 days'));
        $rows = $this->db->select(
                "DATE(created_at) as day, " .
                "COUNT(*) as orders, " .
                "COALESCE(SUM(total_amount), 0) as sales, " .
                "SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending, " .
                "SUM(CASE WHEN order_status = 'to_ship' THEN 1 ELSE 0 END) as to_ship, " .
                "SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered"
            )
            ->from(ORDER_TABLE)
            ->where('reseller_id', $reseller_id)
            ->where('DATE(created_at) >=', $start)
            ->group_by('DATE(created_at)')
            ->get()->result_array();

        $byDay = [];
        foreach ($rows as $r) {
            $byDay[$r['day']] = $r;
        }

        $days = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $row = $byDay[$date] ?? ['orders' => 0, 'sales' => 0, 'pending' => 0, 'to_ship' => 0, 'delivered' => 0];
            $days[] = [
                'date' => $date,
                'label' => date('M j', strtotime($date)),
                'orders' => (int) $row['orders'],
                'sales' => (float) $row['sales'],
                'pending' => (int) $row['pending'],
                'to_ship' => (int) $row['to_ship'],
                'delivered' => (int) $row['delivered'],
            ];
        }
        return $days;
    }

    /** "vs last 7 days" % change for each stat, comparing two 7-day windows. */
    private function _trend_percentages($lastWeek, $thisWeek) {
        $sum = fn($days, $key) => array_sum(array_column($days, $key));
        $pct = function ($prev, $curr) {
            if ($prev == 0) {
                return $curr > 0 ? 100.0 : 0.0;
            }
            return (($curr - $prev) / $prev) * 100;
        };

        return [
            'total_sales' => $pct($sum($lastWeek, 'sales'), $sum($thisWeek, 'sales')),
            'total_orders' => $pct($sum($lastWeek, 'orders'), $sum($thisWeek, 'orders')),
            'pending_orders' => $pct($sum($lastWeek, 'pending'), $sum($thisWeek, 'pending')),
            'to_ship_orders' => $pct($sum($lastWeek, 'to_ship'), $sum($thisWeek, 'to_ship')),
            'delivered_orders' => $pct($sum($lastWeek, 'delivered'), $sum($thisWeek, 'delivered')),
        ];
    }

    /**
     * Commission totals by status for this reseller. commission_transaction_tbl's
     * real statuses are pending/released/withdrawn — displayed as
     * Pending/Approved/Paid to match the terms resellers see on the
     * Commissions page.
     */
    private function _get_commission_breakdown($reseller_id) {
        $sumByStatus = function ($status) use ($reseller_id) {
            $row = $this->db->select_sum('amount')->from(COMMISSIONS_TABLE)
                ->where('reseller_id', $reseller_id)->where('status', $status)
                ->get()->row();
            return (float) ($row->amount ?? 0);
        };

        // 'paid' sourced from reseller_tbl.total_withdrawn, same as
        // Commissions::index() — not re-derived by summing commission rows
        // tagged 'withdrawn' here, since Withdrawal::mark_processed() tags
        // them one at a time up to the payout amount and any drift there
        // (partial rows, rounding) previously made this stat silently
        // disagree with the Commissions page for the same reseller.
        $reseller = $this->reseller_model->get_by_id($reseller_id);

        return [
            'total_earned' => $this->commission_model->get_total_earned($reseller_id),
            'pending' => $sumByStatus('pending'),
            'approved' => $sumByStatus('released'),
            'paid' => (float) ($reseller['total_withdrawn'] ?? 0),
        ];
    }

    private function _get_recent_orders($reseller_id, $limit) {
        return $this->db->select('o.order_id, o.order_number, o.total_amount, o.order_status, o.created_at, o.delivery_city, c.first_name, c.last_name, c.municipality', FALSE)
            ->from(ORDER_TABLE . ' o')
            ->join(CUSTOMER_TABLE . ' c', 'c.customer_id = o.customer_id', 'left')
            ->where('o.reseller_id', $reseller_id)
            ->order_by('o.created_at', 'DESC')
            ->limit($limit)
            ->get()->result_array();
    }

    /** Mirrors reseller/Sales.php's top-products query, capped to a short list and with a thumbnail. */
    private function _get_top_products($reseller_id, $limit) {
        return $this->db->select('p.product_id, p.product_name, pi.image_path as product_image, SUM(od.quantity) as total_sold, SUM(od.total_price) as revenue', FALSE)
            ->from(ORDER_DETAILS_TABLE . ' od')
            ->join(ORDER_TABLE . ' o', 'o.order_id = od.order_id')
            ->join(PRODUCT_TABLE . ' p', 'p.product_id = od.product_id')
            ->join(PRODUCT_IMAGE_TABLE . ' pi', 'pi.product_id = p.product_id AND pi.is_primary = 1', 'left')
            ->where('o.reseller_id', $reseller_id)
            ->group_by('od.product_id')
            ->order_by('total_sold', 'DESC')
            ->limit($limit)
            ->get()->result_array();
    }
}
