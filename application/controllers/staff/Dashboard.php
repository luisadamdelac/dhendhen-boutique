<?php
class Dashboard extends Authenticated_Controller {
    public function __construct() {
        parent::__construct();
        $this->require_role(ROLE_STAFF);
        $this->load->model(['product_model', 'order_model', 'activity_log_model']);
    }

    public function index() {
        $data = $this->set_view_data();
        $data['page_title'] = 'Staff Dashboard';

        // Walk-in (in-store) sales at this staff member's own branch never
        // create an order_tbl row (see WalkinSaleService), so they're folded
        // into Total Orders here instead of being invisible.
        require_once APPPATH . 'services/WalkinSaleService.php';
        $staff_branch_id = (int) ($this->db->select('branch_id')->where('staff_id', $this->user_id)->get(STAFF_TABLE)->row_array()['branch_id'] ?? 0);
        $walkin = WalkinSaleService::getTotalSales($staff_branch_id ?: NULL);

        $data['order_stats'] = [
            'total_orders' => $this->db->from(ORDER_TABLE)->count_all_results() + $walkin['count'],
            'pending_orders' => $this->db->from(ORDER_TABLE)->where('order_status', 'pending')->count_all_results(),
            'processing_orders' => $this->db->from(ORDER_TABLE)->where('order_status', 'processing')->count_all_results(),
            'to_ship_orders' => $this->db->from(ORDER_TABLE)->where('order_status', 'to_ship')->count_all_results(),
            'shipped_orders' => $this->db->from(ORDER_TABLE)->where('order_status', 'shipped')->count_all_results(),
        ];
        $data['product_stats'] = [
            'total_products' => $this->db->from(PRODUCT_TABLE)->count_all_results(),
            'low_stock_products' => $this->db->from(PRODUCT_TABLE)->where('stock <=', 10)->count_all_results(),
            'out_of_stock_products' => $this->db->from(PRODUCT_TABLE)->where('stock', 0)->count_all_results(),
        ];
        $data['inventory_stats'] = $data['product_stats'];
        $data['recent_orders'] = $this->db->select('*')->from(ORDER_TABLE)->order_by('order_id', 'DESC')->limit(5)->get()->result_array();
        $this->activity_log_model->create(['user_type' => 'staff', 'user_id' => $this->user_id, 'action' => 'view_dashboard', 'details' => 'Staff viewed dashboard', 'ip_address' => $this->input->ip_address()]);
        $this->load->view('staff/layouts/header', $data);
        $this->load->view('staff/dashboard/index', $data);
        $this->load->view('staff/layouts/footer', $data);
    }
}
