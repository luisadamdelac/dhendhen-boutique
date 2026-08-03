<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin analytics dashboard: Sales Performance, Reseller Activity,
 * Inventory Status, and Financial Records reports.
 */
class Reports extends Authenticated_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_role('admin');
    }

    public function index() {
        $data['page_title'] = 'Reports';
        $data['current_page'] = 'reports';
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/reports/index', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * 4.1 Sales Performance Report
     */
    public function sales() {
        $start = $this->input->get('start') ?: date('Y-m-01', strtotime('-5 months'));
        $end = $this->input->get('end') ?: date('Y-m-d');

        $data = $this->_sales_data($start, $end);
        $data['page_title'] = 'Sales Performance Report';
        $data['current_page'] = 'reports';
        $data['start'] = $start;
        $data['end'] = $end;

        $this->_log_report('sales', 'Sales Performance Report', $start, $end);

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/reports/sales', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    private function _sales_data($start, $end) {
        require_once APPPATH . 'services/WalkinSaleService.php';

        $summary = $this->db->select('COUNT(*) as total_orders, COALESCE(SUM(total_amount), 0) as total_revenue')
            ->from(ORDER_TABLE)
            ->where('DATE(created_at) >=', $start)
            ->where('DATE(created_at) <=', $end)
            ->where_not_in('order_status', ['cancelled'])
            ->get()->row_array();

        // Walk-in (in-store) sales never create an order_tbl row — see
        // WalkinSaleService — so they're merged in here rather than being
        // invisible next to online orders in every figure below.
        $walkin_summary = WalkinSaleService::getTotalSales(NULL, $start, $end);
        $summary['online_orders'] = (int) $summary['total_orders'];
        $summary['online_revenue'] = (float) $summary['total_revenue'];
        $summary['walkin_orders'] = $walkin_summary['count'];
        $summary['walkin_revenue'] = $walkin_summary['total'];
        $summary['total_orders'] = $summary['online_orders'] + $walkin_summary['count'];
        $summary['total_revenue'] = $summary['online_revenue'] + $walkin_summary['total'];

        $by_status = $this->db->select('order_status, COUNT(*) as total, COALESCE(SUM(total_amount), 0) as revenue')
            ->from(ORDER_TABLE)
            ->where('DATE(created_at) >=', $start)
            ->where('DATE(created_at) <=', $end)
            ->group_by('order_status')
            ->get()->result_array();

        $monthly = $this->db->select("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total_orders, COALESCE(SUM(total_amount), 0) as total_sales")
            ->from(ORDER_TABLE)
            ->where('DATE(created_at) >=', $start)
            ->where('DATE(created_at) <=', $end)
            ->where_not_in('order_status', ['cancelled'])
            ->group_by("DATE_FORMAT(created_at, '%Y-%m')")
            ->order_by('month', 'ASC')
            ->get()->result_array();

        // Merge the walk-in monthly totals into the same month rows (by
        // month key) so "Monthly Sales" reflects true combined revenue,
        // keeping the online/walk-in split visible per month.
        $walkin_monthly = WalkinSaleService::getMonthlySales($start, $end);
        $monthly_by_month = [];
        foreach ($monthly as $row) {
            $monthly_by_month[$row['month']] = [
                'month' => $row['month'],
                'online_orders' => (int) $row['total_orders'],
                'online_sales' => (float) $row['total_sales'],
                'walkin_orders' => 0,
                'walkin_sales' => 0.0,
            ];
        }
        foreach ($walkin_monthly as $month => $w) {
            if (!isset($monthly_by_month[$month])) {
                $monthly_by_month[$month] = ['month' => $month, 'online_orders' => 0, 'online_sales' => 0.0, 'walkin_orders' => 0, 'walkin_sales' => 0.0];
            }
            $monthly_by_month[$month]['walkin_orders'] = $w['total_orders'];
            $monthly_by_month[$month]['walkin_sales'] = $w['total_sales'];
        }
        ksort($monthly_by_month);
        $monthly = array_values(array_map(function ($m) {
            $m['total_orders'] = $m['online_orders'] + $m['walkin_orders'];
            $m['total_sales'] = $m['online_sales'] + $m['walkin_sales'];
            return $m;
        }, $monthly_by_month));

        $top_products = $this->db->select('od.product_id, p.product_name, SUM(od.quantity) as total_sold, SUM(od.total_price) as revenue')
            ->from(ORDER_DETAILS_TABLE . ' od')
            ->join(ORDER_TABLE . ' o', 'o.order_id = od.order_id')
            ->join(PRODUCT_TABLE . ' p', 'p.product_id = od.product_id')
            ->where('DATE(o.created_at) >=', $start)
            ->where('DATE(o.created_at) <=', $end)
            ->group_by('od.product_id')
            ->get()->result_array();

        // Combine with walk-in product sales (same product across both
        // channels adds up into one true "top seller" ranking) before
        // taking the top 10 — otherwise a product that only sells well
        // in-store would never appear here at all.
        $combined_products = [];
        foreach ($top_products as $row) {
            $combined_products[$row['product_id']] = [
                'product_name' => $row['product_name'],
                'total_sold' => (int) $row['total_sold'],
                'revenue' => (float) $row['revenue'],
            ];
        }
        foreach (WalkinSaleService::getProductSales($start, $end) as $row) {
            $pid = $row['product_id'];
            if (!isset($combined_products[$pid])) {
                $combined_products[$pid] = ['product_name' => $row['product_name'], 'total_sold' => 0, 'revenue' => 0.0];
            }
            $combined_products[$pid]['total_sold'] += (int) $row['total_sold'];
            $combined_products[$pid]['revenue'] += (float) $row['revenue'];
        }
        usort($combined_products, fn($a, $b) => $b['total_sold'] <=> $a['total_sold']);
        $top_products = array_slice($combined_products, 0, 10);

        return [
            'summary' => $summary,
            'walkin_summary' => $walkin_summary,
            'by_status' => $by_status,
            'monthly' => $monthly,
            'top_products' => $top_products,
        ];
    }

    /**
     * 4.2 Reseller Activity Report
     */
    public function reseller_activity() {
        $data = $this->_reseller_activity_data();
        $data['page_title'] = 'Reseller Activity Report';
        $data['current_page'] = 'reports';

        $this->_log_report('reseller_activity', 'Reseller Activity Report');

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/reports/reseller_activity', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    private function _reseller_activity_data() {
        $resellers = $this->db->select(
                'r.reseller_id, r.first_name, r.last_name, r.business_name, r.commission_balance, ' .
                'r.total_commission_earned, r.total_withdrawn, ' .
                'COUNT(DISTINCT o.order_id) as total_orders, COALESCE(SUM(DISTINCT o.total_amount), 0) as total_sales'
            )
            ->from(RESELLER_TABLE . ' r')
            ->join(ORDER_TABLE . ' o', 'o.reseller_id = r.reseller_id', 'left')
            ->group_by('r.reseller_id')
            ->order_by('r.total_commission_earned', 'DESC')
            ->get()->result_array();

        return [
            'resellers' => $resellers,
            'total_resellers' => count($resellers),
            'total_commission_paid' => array_sum(array_column($resellers, 'total_withdrawn')),
        ];
    }

    /**
     * 4.3 Inventory Status Report
     */
    public function inventory() {
        $threshold = (int) ($this->input->get('threshold') ?: 20);

        $data = $this->_inventory_data($threshold);
        $data['page_title'] = 'Inventory Status Report';
        $data['current_page'] = 'reports';
        $data['threshold'] = $threshold;

        $this->_log_report('inventory', 'Inventory Status Report');

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/reports/inventory', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    private function _inventory_data($threshold) {
        $summary = [
            'total_products' => $this->db->from(PRODUCT_TABLE)->count_all_results(),
            'available' => $this->db->from(PRODUCT_TABLE)->where('status', 'available')->count_all_results(),
            'out_of_stock' => $this->db->from(PRODUCT_TABLE)->where('stock', 0)->count_all_results(),
            'low_stock' => $this->db->from(PRODUCT_TABLE)->where('stock >', 0)->where('stock <=', $threshold)->count_all_results(),
        ];

        $by_branch = $this->db->select('b.branch_name, COUNT(DISTINCT ib.product_id) as total_products, COALESCE(SUM(ib.remaining_quantity), 0) as total_stock')
            ->from(BRANCHES_TABLE . ' b')
            ->join(INVENTORY_BATCH_TABLE . ' ib', 'ib.branch_id = b.branch_id AND ib.status = \'active\'', 'left')
            ->group_by('b.branch_id')
            ->get()->result_array();

        $by_category = $this->db->select('c.category_name, COUNT(p.product_id) as total_products, COALESCE(SUM(p.stock), 0) as total_stock')
            ->from(CATEGORY_TABLE . ' c')
            ->join(PRODUCT_TABLE . ' p', 'p.category_id = c.category_id', 'left')
            ->group_by('c.category_id')
            ->get()->result_array();

        $low_stock_products = $this->db->select('p.*, c.category_name')
            ->from(PRODUCT_TABLE . ' p')
            ->join(CATEGORY_TABLE . ' c', 'p.category_id = c.category_id', 'left')
            ->where('p.stock <=', $threshold)
            ->order_by('p.stock', 'ASC')
            ->get()->result_array();

        return [
            'summary' => $summary,
            'by_branch' => $by_branch,
            'by_category' => $by_category,
            'low_stock_products' => $low_stock_products,
        ];
    }

    /**
     * 4.4 Financial Records Report
     */
    public function financial() {
        $start = $this->input->get('start') ?: date('Y-m-01', strtotime('-5 months'));
        $end = $this->input->get('end') ?: date('Y-m-d');

        $data = $this->_financial_data($start, $end);
        $data['page_title'] = 'Financial Records Report';
        $data['current_page'] = 'reports';
        $data['start'] = $start;
        $data['end'] = $end;

        $this->_log_report('financial', 'Financial Records Report', $start, $end);

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/reports/financial', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    private function _financial_data($start, $end) {
        require_once APPPATH . 'services/WalkinSaleService.php';

        $online_revenue = (float) ($this->db->select_sum('total_amount')
            ->from(ORDER_TABLE)
            ->where('DATE(created_at) >=', $start)->where('DATE(created_at) <=', $end)
            ->where_not_in('order_status', ['cancelled'])
            ->get()->row()->total_amount ?? 0);

        // Walk-in (in-store) sales never create an order_tbl row, so they're
        // added in here rather than being invisible from total revenue.
        $walkin_sales = WalkinSaleService::getTotalSales(NULL, $start, $end);
        $walkin_revenue = $walkin_sales['total'];
        $revenue = $online_revenue + $walkin_revenue;

        $commissions_released = (float) ($this->db->select_sum('amount')
            ->from(COMMISSIONS_TABLE)
            ->where('status !=', 'pending')
            ->where('DATE(created_at) >=', $start)->where('DATE(created_at) <=', $end)
            ->get()->row()->amount ?? 0);

        $commissions_pending = (float) ($this->db->select_sum('amount')
            ->from(COMMISSIONS_TABLE)
            ->where('status', 'pending')
            ->get()->row()->amount ?? 0);

        $withdrawals_completed = (float) ($this->db->select_sum('amount')
            ->from(WITHDRAWAL_TABLE)
            ->where('status', 'completed')
            ->where('DATE(created_at) >=', $start)->where('DATE(created_at) <=', $end)
            ->get()->row()->amount ?? 0);

        $withdrawals_pending = (float) ($this->db->select_sum('amount')
            ->from(WITHDRAWAL_TABLE)
            ->where_in('status', ['pending', 'approved'])
            ->get()->row()->amount ?? 0);

        $refunds_issued = (float) ($this->db->select_sum('amount')
            ->from(REFUND_REQUEST_TABLE)
            ->where('status', 'approved')
            ->where('DATE(created_at) >=', $start)->where('DATE(created_at) <=', $end)
            ->get()->row()->amount ?? 0);

        $payments_by_status = $this->db->select('status, COUNT(*) as total, COALESCE(SUM(amount), 0) as amount')
            ->from(PAYMENTS_TABLE)
            ->where('DATE(created_at) >=', $start)->where('DATE(created_at) <=', $end)
            ->group_by('status')
            ->get()->result_array();

        return [
            'revenue' => $revenue,
            'online_revenue' => $online_revenue,
            'walkin_revenue' => $walkin_revenue,
            'walkin_count' => $walkin_sales['count'],
            'commissions_released' => $commissions_released,
            'commissions_pending' => $commissions_pending,
            'withdrawals_completed' => $withdrawals_completed,
            'withdrawals_pending' => $withdrawals_pending,
            'refunds_issued' => $refunds_issued,
            'payments_by_status' => $payments_by_status,
        ];
    }

    /**
     * 4.5 Commission Report
     */
    public function commission() {
        $start = $this->input->get('start') ?: date('Y-m-01', strtotime('-5 months'));
        $end = $this->input->get('end') ?: date('Y-m-d');

        $data = $this->_commission_data($start, $end);
        $data['page_title'] = 'Commission Report';
        $data['current_page'] = 'reports';
        $data['start'] = $start;
        $data['end'] = $end;

        $this->_log_report('commission', 'Commission Report', $start, $end);

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/reports/commission', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    private function _commission_data($start, $end) {
        $by_status = $this->db->select('status, COUNT(*) as total, COALESCE(SUM(amount), 0) as amount')
            ->from(COMMISSIONS_TABLE)
            ->where('DATE(created_at) >=', $start)
            ->where('DATE(created_at) <=', $end)
            ->group_by('status')
            ->get()->result_array();

        $total_commission = (float) ($this->db->select_sum('amount')
            ->from(COMMISSIONS_TABLE)
            ->where('DATE(created_at) >=', $start)->where('DATE(created_at) <=', $end)
            ->get()->row()->amount ?? 0);

        $by_reseller = $this->db->select(
                'r.reseller_id, r.first_name, r.last_name, r.business_name, ' .
                'COUNT(ct.commission_id) as total_commissions, COALESCE(SUM(ct.amount), 0) as total_amount'
            )
            ->from(COMMISSIONS_TABLE . ' ct')
            ->join(RESELLER_TABLE . ' r', 'r.reseller_id = ct.reseller_id')
            ->where('DATE(ct.created_at) >=', $start)
            ->where('DATE(ct.created_at) <=', $end)
            ->group_by('r.reseller_id')
            ->order_by('total_amount', 'DESC')
            ->get()->result_array();

        return [
            'by_status' => $by_status,
            'total_commission' => $total_commission,
            'by_reseller' => $by_reseller,
        ];
    }

    /**
     * 4.6 Refund Report
     */
    public function refund() {
        $start = $this->input->get('start') ?: date('Y-m-01', strtotime('-5 months'));
        $end = $this->input->get('end') ?: date('Y-m-d');

        $data = $this->_refund_data($start, $end);
        $data['page_title'] = 'Refund Report';
        $data['current_page'] = 'reports';
        $data['start'] = $start;
        $data['end'] = $end;

        $this->_log_report('refund', 'Refund Report', $start, $end);

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/reports/refund', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    private function _refund_data($start, $end) {
        $by_status = $this->db->select('status, COUNT(*) as total, COALESCE(SUM(amount), 0) as amount')
            ->from(REFUND_REQUEST_TABLE)
            ->where('DATE(created_at) >=', $start)
            ->where('DATE(created_at) <=', $end)
            ->group_by('status')
            ->get()->result_array();

        $total_refunded = (float) ($this->db->select_sum('amount')
            ->from(REFUND_REQUEST_TABLE)
            ->where('status', 'approved')
            ->where('DATE(created_at) >=', $start)->where('DATE(created_at) <=', $end)
            ->get()->row()->amount ?? 0);

        $by_reseller = $this->db->select(
                'r.reseller_id, r.first_name, r.last_name, r.business_name, ' .
                'COUNT(rr.refund_id) as total_requests, COALESCE(SUM(CASE WHEN rr.status = \'approved\' THEN rr.amount ELSE 0 END), 0) as approved_amount'
            )
            ->from(REFUND_REQUEST_TABLE . ' rr')
            ->join(RESELLER_TABLE . ' r', 'r.reseller_id = rr.reseller_id', 'left')
            ->where('DATE(rr.created_at) >=', $start)
            ->where('DATE(rr.created_at) <=', $end)
            ->group_by('r.reseller_id')
            ->order_by('approved_amount', 'DESC')
            ->get()->result_array();

        $recent_refunds = $this->db->select('rr.*, o.order_number, c.first_name, c.last_name')
            ->from(REFUND_REQUEST_TABLE . ' rr')
            ->join(ORDER_TABLE . ' o', 'o.order_id = rr.order_id', 'left')
            ->join(CUSTOMER_TABLE . ' c', 'c.customer_id = rr.customer_id', 'left')
            ->where('DATE(rr.created_at) >=', $start)
            ->where('DATE(rr.created_at) <=', $end)
            ->order_by('rr.refund_id', 'DESC')
            ->limit(20)
            ->get()->result_array();

        return [
            'by_status' => $by_status,
            'total_refunded' => $total_refunded,
            'by_reseller' => $by_reseller,
            'recent_refunds' => $recent_refunds,
        ];
    }

    /**
     * PDF / Excel export for any of the six reports above. Reuses the exact
     * same data-fetch methods as the on-screen views so the export always
     * matches what the admin sees.
     *
     * URL: admin/reports/export/{type}/{format}
     */
    public function export($type = '', $format = 'pdf') {
        $start = $this->input->get('start') ?: date('Y-m-01', strtotime('-5 months'));
        $end = $this->input->get('end') ?: date('Y-m-d');
        $subtitle = '';

        switch ($type) {
            case 'sales':
                $d = $this->_sales_data($start, $end);
                $title = 'Sales Performance Report';
                $subtitle = "Period: $start to $end";
                $sections = [
                    'Summary' => [
                        'headers' => ['Channel', 'Orders', 'Revenue'],
                        'rows' => [
                            ['Online (Checkout)', $d['summary']['online_orders'], number_format($d['summary']['online_revenue'], 2)],
                            ['Walk-in (In-Store)', $d['summary']['walkin_orders'], number_format($d['summary']['walkin_revenue'], 2)],
                            ['Total', $d['summary']['total_orders'], number_format($d['summary']['total_revenue'], 2)],
                        ],
                    ],
                    'Online Orders by Status' => [
                        'headers' => ['Status', 'Orders', 'Revenue'],
                        'rows' => array_map(fn($r) => [ucfirst(str_replace('_', ' ', $r['order_status'])), $r['total'], number_format($r['revenue'], 2)], $d['by_status']),
                    ],
                    'Monthly Sales' => [
                        'headers' => ['Month', 'Online Orders', 'Online Sales', 'Walk-in Sales', 'Total Sales'],
                        'rows' => array_map(fn($r) => [
                            date('F Y', strtotime($r['month'] . '-01')),
                            $r['online_orders'],
                            number_format($r['online_sales'], 2),
                            number_format($r['walkin_sales'], 2),
                            number_format($r['total_sales'], 2),
                        ], $d['monthly']),
                    ],
                    'Top Selling Products' => [
                        'headers' => ['Product', 'Sold', 'Revenue'],
                        'rows' => array_map(fn($r) => [$r['product_name'], $r['total_sold'], number_format($r['revenue'], 2)], $d['top_products']),
                    ],
                ];
                break;

            case 'reseller_activity':
                $d = $this->_reseller_activity_data();
                $title = 'Reseller Activity Report';
                $sections = [
                    'Resellers' => [
                        'headers' => ['Reseller', 'Orders', 'Total Sales', 'Earned', 'Withdrawn', 'Balance'],
                        'rows' => array_map(fn($r) => [
                            trim($r['business_name'] ?: ($r['first_name'] . ' ' . $r['last_name'])),
                            $r['total_orders'],
                            number_format($r['total_sales'], 2),
                            number_format($r['total_commission_earned'], 2),
                            number_format($r['total_withdrawn'], 2),
                            number_format($r['commission_balance'], 2),
                        ], $d['resellers']),
                    ],
                ];
                break;

            case 'inventory':
                $threshold = (int) ($this->input->get('threshold') ?: 20);
                $d = $this->_inventory_data($threshold);
                $title = 'Inventory Status Report';
                $subtitle = "Low-stock threshold: $threshold";
                $sections = [
                    'Stock by Branch' => [
                        'headers' => ['Branch', 'Products', 'Total Stock'],
                        'rows' => array_map(fn($r) => [$r['branch_name'], $r['total_products'], $r['total_stock']], $d['by_branch']),
                    ],
                    'Stock by Category' => [
                        'headers' => ['Category', 'Products', 'Total Stock'],
                        'rows' => array_map(fn($r) => [$r['category_name'], $r['total_products'], $r['total_stock']], $d['by_category']),
                    ],
                    'Low / Out of Stock Products' => [
                        'headers' => ['SKU', 'Product', 'Category', 'Stock', 'Status'],
                        'rows' => array_map(fn($r) => [$r['sku'], $r['product_name'], $r['category_name'] ?? '-', $r['stock'], $r['stock'] == 0 ? 'Out of Stock' : 'Low Stock'], $d['low_stock_products']),
                    ],
                ];
                break;

            case 'financial':
                $d = $this->_financial_data($start, $end);
                $title = 'Financial Records Report';
                $subtitle = "Period: $start to $end";
                $sections = [
                    'Summary' => [
                        'headers' => ['Metric', 'Amount'],
                        'rows' => [
                            ['Revenue (period) — Online', number_format($d['online_revenue'], 2)],
                            ['Revenue (period) — Walk-in', number_format($d['walkin_revenue'], 2)],
                            ['Revenue (period) — Total', number_format($d['revenue'], 2)],
                            ['Commissions Released (period)', number_format($d['commissions_released'], 2)],
                            ['Commissions Pending (all time)', number_format($d['commissions_pending'], 2)],
                            ['Withdrawals Completed (period)', number_format($d['withdrawals_completed'], 2)],
                            ['Withdrawals Pending/Approved (all time)', number_format($d['withdrawals_pending'], 2)],
                            ['Refunds Issued (period)', number_format($d['refunds_issued'], 2)],
                        ],
                    ],
                    'Payments by Status' => [
                        'headers' => ['Status', 'Count', 'Amount'],
                        'rows' => array_map(fn($r) => [ucfirst($r['status']), $r['total'], number_format($r['amount'], 2)], $d['payments_by_status']),
                    ],
                ];
                break;

            case 'commission':
                $d = $this->_commission_data($start, $end);
                $title = 'Commission Report';
                $subtitle = "Period: $start to $end";
                $sections = [
                    'By Status' => [
                        'headers' => ['Status', 'Count', 'Amount'],
                        'rows' => array_map(fn($r) => [ucfirst($r['status']), $r['total'], number_format($r['amount'], 2)], $d['by_status']),
                    ],
                    'By Reseller' => [
                        'headers' => ['Reseller', 'Commissions', 'Total Amount'],
                        'rows' => array_map(fn($r) => [$r['business_name'] ?: trim($r['first_name'] . ' ' . $r['last_name']), $r['total_commissions'], number_format($r['total_amount'], 2)], $d['by_reseller']),
                    ],
                ];
                break;

            case 'refund':
                $d = $this->_refund_data($start, $end);
                $title = 'Refund Report';
                $subtitle = "Period: $start to $end";
                $sections = [
                    'By Status' => [
                        'headers' => ['Status', 'Count', 'Amount'],
                        'rows' => array_map(fn($r) => [ucfirst($r['status']), $r['total'], number_format($r['amount'], 2)], $d['by_status']),
                    ],
                    'By Reseller' => [
                        'headers' => ['Reseller', 'Requests', 'Approved Amount'],
                        'rows' => array_map(fn($r) => [$r['business_name'] ?: trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?: '-', $r['total_requests'], number_format($r['approved_amount'], 2)], $d['by_reseller']),
                    ],
                    'Recent Requests' => [
                        'headers' => ['Refund #', 'Order', 'Customer', 'Amount', 'Status', 'Date'],
                        'rows' => array_map(fn($r) => [
                            $r['refund_number'],
                            '#' . ($r['order_number'] ?? $r['order_id']),
                            trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?: '-',
                            number_format($r['amount'], 2),
                            ucfirst($r['status']),
                            date('M d, Y', strtotime($r['created_at'])),
                        ], $d['recent_refunds']),
                    ],
                ];
                break;

            default:
                show_404();
                return;
        }

        $this->_log_report($type, $title . ' (' . strtoupper($format) . ' export)', $start, $end);

        if ($format === 'excel') {
            $this->_render_excel($title, $sections);
        } else {
            $this->_render_pdf($title, $subtitle, $sections);
        }
    }

    private function _render_pdf($title, $subtitle, array $sections) {
        require_once FCPATH . 'vendor/autoload.php';

        $html = '<html><head><meta charset="utf-8"><style>'
            . 'body{font-family:sans-serif;color:#1a1a2e;}'
            . 'h2{margin-bottom:2px;} p.subtitle{color:#666;margin-top:0;}'
            . 'h4{margin-bottom:4px;margin-top:18px;}'
            . 'table{width:100%;border-collapse:collapse;font-size:11px;margin-bottom:10px;}'
            . 'th,td{border:1px solid #ccc;padding:5px 7px;text-align:left;}'
            . 'th{background:#f5f5f7;font-weight:bold;}'
            . '</style></head><body>';
        $html .= '<h2>' . htmlspecialchars($title) . '</h2>';
        if ($subtitle) {
            $html .= '<p class="subtitle">' . htmlspecialchars($subtitle) . '</p>';
        }

        foreach ($sections as $secTitle => $sec) {
            $html .= '<h4>' . htmlspecialchars($secTitle) . '</h4><table><tr>';
            foreach ($sec['headers'] as $h) {
                $html .= '<th>' . htmlspecialchars($h) . '</th>';
            }
            $html .= '</tr>';
            if (empty($sec['rows'])) {
                $html .= '<tr><td colspan="' . count($sec['headers']) . '">No data</td></tr>';
            } else {
                foreach ($sec['rows'] as $row) {
                    $html .= '<tr>';
                    foreach ($row as $cell) {
                        $html .= '<td>' . htmlspecialchars((string) $cell) . '</td>';
                    }
                    $html .= '</tr>';
                }
            }
            $html .= '</table>';
        }
        $html .= '</body></html>';

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        // Opens inline in a new tab as a preview; the admin can save it from the browser's own PDF viewer.
        $dompdf->stream(preg_replace('/[^A-Za-z0-9]+/', '_', $title) . '_' . date('Ymd_His') . '.pdf', ['Attachment' => false]);
    }

    private function _render_excel($title, array $sections) {
        require_once FCPATH . 'vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach ($sections as $secTitle => $sec) {
            $sheet = $spreadsheet->createSheet();
            $safeName = trim(substr(preg_replace('/[^A-Za-z0-9 ]/', '', $secTitle), 0, 31));
            $sheet->setTitle($safeName ?: 'Sheet');

            $sheet->fromArray($sec['headers'], NULL, 'A1');
            foreach ($sheet->getColumnIterator('A', $sheet->getHighestColumn()) as $col) {
                $sheet->getStyle($col->getColumnIndex() . '1')->getFont()->setBold(true);
            }
            if (!empty($sec['rows'])) {
                $sheet->fromArray($sec['rows'], NULL, 'A2');
            }
            foreach ($sheet->getColumnIterator('A', $sheet->getHighestColumn()) as $col) {
                $sheet->getColumnDimension($col->getColumnIndex())->setAutoSize(true);
            }
        }
        $spreadsheet->setActiveSheetIndex(0);

        $filename = preg_replace('/[^A-Za-z0-9]+/', '_', $title) . '_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
    }

    private function _log_report($type, $title, $start = NULL, $end = NULL) {
        $this->db->insert(REPORTS_TABLE, [
            'report_type' => $type,
            'title' => $title,
            'date_range_start' => $start,
            'date_range_end' => $end,
            'generated_by' => $this->user_id,
            'admin_id' => $this->user_id,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
