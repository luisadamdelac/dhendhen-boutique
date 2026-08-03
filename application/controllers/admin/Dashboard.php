<?php
/**
 * Admin Dashboard Controller
 * Main dashboard for administrators
 */

class Dashboard extends Authenticated_Controller {

    private $valid_periods = [7, 30, 90, 365];

    public function __construct() {
        parent::__construct();

        // Require admin role
        $this->require_role(ROLE_ADMIN);

        // Load admin models
        $this->load->model('admin_model');
        $this->load->model('product_model');
        $this->load->model('order_model');
        $this->load->model('reseller_model');
        $this->load->model('staff_model');
        $this->load->model('customer_model');
    }

    /**
     * Display admin dashboard
     */
    public function index() {
        $data = $this->set_view_data();
        $data['page_title'] = 'Admin Dashboard';

        $period = (int) $this->input->get('period', TRUE);
        if (!in_array($period, $this->valid_periods, TRUE)) {
            $period = 30;
        }
        $data['selected_period'] = $period;

        $data['order_stats'] = $this->_get_order_stats();
        $data['product_stats'] = ['total_products' => $this->product_model->count_all()];
        $data['reseller_stats'] = ['approved_count' => $this->db->from(RESELLER_TABLE)->where('status', 'active')->count_all_results()];
        $data['refund_stats'] = ['pending_requests' => $this->db->from(REFUND_REQUEST_TABLE)->where('status', 'pending')->count_all_results()];

        $trend = $this->_get_30_day_trend();
        $data['order_stats']['sales_change_pct'] = $trend['sales_change_pct'];
        $data['order_stats']['orders_change_pct'] = $trend['orders_change_pct'];
        $data['reseller_stats']['change_pct'] = $this->_get_reseller_30_day_trend($data['reseller_stats']['approved_count']);

        $data['commission_stats'] = $this->_get_commission_stats();
        $data['daily_sales'] = $this->_get_daily_sales($period);
        $data['top_products'] = $this->_get_top_products();
        $data['top_resellers'] = $this->_get_top_resellers();
        $data['email_stats'] = $this->_get_email_stats();

        // Log activity
        $this->log_activity('view_dashboard');

        // Load view
        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/dashboard/index', $data);
        $this->load->view('admin/layout/footer');
    }

    private function _get_order_stats() {
        $total_sales = $this->db->select_sum('total_amount')->from(ORDER_TABLE)->get()->row();

        // Walk-in (in-store) sales never create an order_tbl row (see
        // WalkinSaleService), so they'd otherwise be invisible in these
        // totals despite being real revenue — folded in here instead.
        require_once APPPATH . 'services/WalkinSaleService.php';
        $walkin = WalkinSaleService::getTotalSales();

        return [
            'total_sales' => ($total_sales->total_amount ?? 0) + $walkin['total'],
            'total_orders' => $this->db->from(ORDER_TABLE)->count_all_results() + $walkin['count'],
            'pending_orders' => $this->db->from(ORDER_TABLE)->where('order_status', 'pending')->count_all_results(),
            'processing_orders' => $this->db->from(ORDER_TABLE)->where('order_status', 'processing')->count_all_results(),
            'shipped_orders' => $this->db->from(ORDER_TABLE)->where('order_status', 'shipped')->count_all_results(),
            'paid_orders' => $this->db->from(ORDER_TABLE)->where('order_status', 'paid')->count_all_results(),
            'cancelled_orders' => $this->db->from(ORDER_TABLE)->where('order_status', 'cancelled')->count_all_results(),
        ];
    }

    /**
     * Sales/orders change vs the prior 30-day window, for the dashboard's
     * stat-card trend lines.
     */
    private function _get_30_day_trend() {
        $period_start = date('Y-m-d H:i:s', strtotime('-30 days'));
        $prev_period_start = date('Y-m-d H:i:s', strtotime('-60 days'));

        $current = $this->_get_sales_and_orders_since($period_start);
        $previous = $this->_get_sales_and_orders_since($prev_period_start, $period_start);

        return [
            'sales_change_pct' => $this->_calc_change_pct($current['sales'], $previous['sales']),
            'orders_change_pct' => $this->_calc_change_pct($current['orders'], $previous['orders']),
        ];
    }

    private function _get_sales_and_orders_since($start, $end = null) {
        $this->db->select_sum('total_amount')->from(ORDER_TABLE)->where('created_at >=', $start);
        if ($end) {
            $this->db->where('created_at <', $end);
        }
        $sales_row = $this->db->get()->row();

        $this->db->from(ORDER_TABLE)->where('created_at >=', $start);
        if ($end) {
            $this->db->where('created_at <', $end);
        }
        $orders = $this->db->count_all_results();

        return ['sales' => $sales_row->total_amount ?? 0, 'orders' => $orders];
    }

    /**
     * Approximates "active resellers 30 days ago" by counting resellers
     * that were both active and already registered as of that date —
     * there's no historical status-change log to check against.
     */
    private function _get_reseller_30_day_trend($current_count) {
        $period_start = date('Y-m-d H:i:s', strtotime('-30 days'));
        $previous_count = $this->db->from(RESELLER_TABLE)
            ->where('status', 'active')
            ->where('created_at <', $period_start)
            ->count_all_results();

        return $this->_calc_change_pct($current_count, $previous_count);
    }

    private function _calc_change_pct($current, $previous) {
        $current = (float) $current;
        $previous = (float) $previous;

        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return (($current - $previous) / $previous) * 100;
    }

    private function _get_commission_stats() {
        $sum_by_status = function ($status) {
            $row = $this->db->select_sum('amount')->from(COMMISSIONS_TABLE)->where('status', $status)->get()->row();
            return $row->amount ?? 0;
        };

        return [
            'pending_amount' => $sum_by_status('pending'),
            'approved_amount' => $sum_by_status('released'),
        ];
    }

    private function _get_daily_sales($period) {
        $start_date = date('Y-m-d', strtotime('-' . ($period - 1) . ' days'));

        $rows = $this->db->select('DATE(created_at) as date, SUM(total_amount) as sales', FALSE)
            ->from(ORDER_TABLE)
            ->where('DATE(created_at) >=', $start_date)
            ->group_by('DATE(created_at)')
            ->order_by('date', 'ASC')
            ->get()->result_array();

        $by_date = [];
        foreach ($rows as $row) {
            $by_date[$row['date']] = (float) $row['sales'];
        }

        $daily_sales = [];
        for ($i = $period - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime('-' . $i . ' days'));
            $daily_sales[] = ['date' => $date, 'sales' => $by_date[$date] ?? 0];
        }

        return $daily_sales;
    }

    private function _get_top_products() {
        return $this->db->select('p.product_name, p.total_sold, p.price, pi.image_path as product_image', FALSE)
            ->from(PRODUCT_TABLE . ' p')
            ->join(PRODUCT_IMAGE_TABLE . ' pi', 'pi.product_id = p.product_id AND pi.is_primary = 1', 'left')
            ->where('p.total_sold >', 0)
            ->order_by('p.total_sold', 'DESC')
            ->limit(5)
            ->get()->result_array();
    }

    private function _get_top_resellers() {
        $rows = $this->db->select("r.reseller_id, r.first_name, r.last_name, r.business_name, r.profile_image, u.email, SUM(o.total_amount) as total_sales", FALSE)
            ->from(ORDER_TABLE . ' o')
            ->join(RESELLER_TABLE . ' r', 'r.reseller_id = o.reseller_id')
            ->join(USER_ACCOUNT_TABLE . ' u', 'u.user_account_id = r.user_account_id', 'left')
            ->group_by('o.reseller_id')
            ->order_by('total_sales', 'DESC')
            ->limit(5)
            ->get()->result_array();

        foreach ($rows as &$row) {
            $row['full_name'] = trim($row['business_name'] ?: ($row['first_name'] . ' ' . $row['last_name']));
            $commission = $this->db->select_sum('amount')->from(COMMISSIONS_TABLE)->where('reseller_id', $row['reseller_id'])->get()->row();
            $row['total_commission'] = $commission->amount ?? 0;
        }
        unset($row);

        return $rows;
    }

    /**
     * SMTP/email queue status for the dashboard's Email Status card. The
     * "configured" flag is a static host/user/pass-present check, not a
     * live connection test — a real per-page-load SMTP handshake would
     * risk tripping Gmail's rate limiting on every dashboard refresh.
     */
    private function _get_email_stats() {
        require_once APPPATH . 'services/EmailQueueService.php';

        $this->load->model('Settings_model');
        $settings = $this->Settings_model->get_settings_array();

        $configured = !empty($settings['smtp_host']) && !empty($settings['smtp_user']) && !empty($settings['smtp_password']);
        $queueStats = EmailQueueService::getQueueStats();

        return array_merge($queueStats, [
            'smtp_enabled' => EmailQueueService::isSmtpEnabled(),
            'configured' => $configured,
            'last_sent' => EmailQueueService::getLastSentAt(),
        ]);
    }
}

/* End of file Dashboard.php */
/* Location: ./application/controllers/admin/Dashboard.php */
