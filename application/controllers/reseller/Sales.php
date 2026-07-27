<?php
/**
 * Reseller Portal - Sales Report
 */
class Sales extends Authenticated_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_role(ROLE_RESELLER);
    }

    public function index() {
        $data = $this->set_view_data();
        $data['page_title'] = 'Sales Report - Reseller Portal';

        $six_months_ago = date('Y-m-01', strtotime('-5 months'));
        $prev_six_months_start = date('Y-m-01', strtotime('-11 months'));

        $monthly = $this->db->select(
                "DATE_FORMAT(o.created_at, '%Y-%m') as month, " .
                "COUNT(DISTINCT o.order_id) as total_orders, " .
                "SUM(od.total_price) as total_sales, " .
                "COALESCE(SUM(od.commission_earned), 0) as total_commission"
            )
            ->from(ORDER_TABLE . ' o')
            ->join(ORDER_DETAILS_TABLE . ' od', 'od.order_id = o.order_id')
            ->where('o.reseller_id', $this->user_id)
            ->where('o.created_at >=', $six_months_ago)
            ->group_by("DATE_FORMAT(o.created_at, '%Y-%m')")
            ->order_by('month', 'ASC')
            ->get()->result_array();

        $top_products = $this->db->select(
                'p.product_name, pc.category_name, SUM(od.quantity) as total_sold, SUM(od.total_price) as revenue'
            )
            ->from(ORDER_DETAILS_TABLE . ' od')
            ->join(ORDER_TABLE . ' o', 'o.order_id = od.order_id')
            ->join(PRODUCT_TABLE . ' p', 'p.product_id = od.product_id')
            ->join(CATEGORY_TABLE . ' pc', 'pc.category_id = p.category_id', 'left')
            ->where('o.reseller_id', $this->user_id)
            ->group_by('od.product_id')
            ->order_by('total_sold', 'DESC')
            ->limit(10)
            ->get()->result_array();

        // Current vs. previous 6-month period totals — powers the summary
        // stat cards' "vs last 6 months" trend line.
        $period_totals = function ($from, $to = null) {
            $q = $this->db->select(
                    "COUNT(DISTINCT o.order_id) as total_orders, " .
                    "COUNT(DISTINCT o.customer_id) as total_customers, " .
                    "COALESCE(SUM(od.total_price), 0) as total_sales"
                )
                ->from(ORDER_TABLE . ' o')
                ->join(ORDER_DETAILS_TABLE . ' od', 'od.order_id = o.order_id')
                ->where('o.reseller_id', $this->user_id)
                ->where('o.created_at >=', $from);
            if ($to) {
                $q->where('o.created_at <', $to);
            }
            return $q->get()->row_array();
        };

        $current = $period_totals($six_months_ago);
        $previous = $period_totals($prev_six_months_start, $six_months_ago);

        $pct_change = function ($current, $previous) {
            if ((float) $previous <= 0) {
                return (float) $current > 0 ? 100.0 : 0.0;
            }
            return (((float) $current - (float) $previous) / (float) $previous) * 100;
        };

        $summary = [
            'total_sales' => (float) ($current['total_sales'] ?? 0),
            'total_orders' => (int) ($current['total_orders'] ?? 0),
            'total_customers' => (int) ($current['total_customers'] ?? 0),
            'average_order_value' => ((int) ($current['total_orders'] ?? 0) > 0)
                ? ((float) ($current['total_sales'] ?? 0) / (int) $current['total_orders'])
                : 0.0,
        ];
        $summary['total_sales_change'] = $pct_change($current['total_sales'] ?? 0, $previous['total_sales'] ?? 0);
        $summary['total_orders_change'] = $pct_change($current['total_orders'] ?? 0, $previous['total_orders'] ?? 0);
        $summary['total_customers_change'] = $pct_change($current['total_customers'] ?? 0, $previous['total_customers'] ?? 0);
        $prevAvgOrderValue = ((int) ($previous['total_orders'] ?? 0) > 0)
            ? ((float) ($previous['total_sales'] ?? 0) / (int) $previous['total_orders'])
            : 0.0;
        $summary['average_order_value_change'] = $pct_change($summary['average_order_value'], $prevAvgOrderValue);

        // Monthly chart footnotes: best/worst month, overall growth, and
        // the average across whatever months actually have data.
        $chartNotes = ['highest' => null, 'lowest' => null, 'growth_pct' => 0.0, 'average_monthly' => 0.0];
        if (!empty($monthly)) {
            $salesByMonth = array_column($monthly, 'total_sales', 'month');
            $highestMonth = array_keys($salesByMonth, max($salesByMonth))[0];
            $lowestMonth = array_keys($salesByMonth, min($salesByMonth))[0];
            $chartNotes['highest'] = ['month' => $highestMonth, 'amount' => (float) $salesByMonth[$highestMonth]];
            $chartNotes['lowest'] = ['month' => $lowestMonth, 'amount' => (float) $salesByMonth[$lowestMonth]];
            $firstAmount = (float) reset($salesByMonth);
            $lastAmount = (float) end($salesByMonth);
            $chartNotes['growth_pct'] = $pct_change($lastAmount, $firstAmount);
            $chartNotes['average_monthly'] = array_sum($salesByMonth) / count($salesByMonth);
        }

        $data['salesData'] = [
            'monthly' => $monthly,
            'topProducts' => $top_products,
            'summary' => $summary,
            'chartNotes' => $chartNotes,
        ];

        $this->load->view('reseller/layouts/header', $data);
        $this->load->view('reseller/sales/index', $data);
        $this->load->view('reseller/layouts/footer', $data);
    }
}
