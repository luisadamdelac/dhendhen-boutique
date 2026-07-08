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
                'p.product_name, SUM(od.quantity) as total_sold, SUM(od.total_price) as revenue'
            )
            ->from(ORDER_DETAILS_TABLE . ' od')
            ->join(ORDER_TABLE . ' o', 'o.order_id = od.order_id')
            ->join(PRODUCT_TABLE . ' p', 'p.product_id = od.product_id')
            ->where('o.reseller_id', $this->user_id)
            ->group_by('od.product_id')
            ->order_by('total_sold', 'DESC')
            ->limit(10)
            ->get()->result_array();

        $data['salesData'] = [
            'monthly' => $monthly,
            'topProducts' => $top_products,
        ];

        $this->load->view('reseller/layouts/header', $data);
        $this->load->view('reseller/sales/index', $data);
        $this->load->view('reseller/layouts/footer', $data);
    }
}
