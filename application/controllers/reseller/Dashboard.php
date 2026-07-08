<?php
class Dashboard extends Authenticated_Controller {
    public function __construct() {
        parent::__construct();
        $this->require_role(ROLE_RESELLER);
        $this->load->model(['order_model', 'commission_model', 'activity_log_model']);
    }

    public function index() {
        $data = $this->set_view_data();
        $data['page_title'] = 'Reseller Dashboard';
        $total_sales = $this->db->select_sum('total_amount')->from(ORDER_TABLE)->where('reseller_id', $this->user_id)->get()->row();
        $data['stats'] = [
            'total_sales' => $total_sales->total_amount ?? 0,
            'total_orders' => $this->db->from(ORDER_TABLE)->where('reseller_id', $this->user_id)->count_all_results(),
            'pending_orders' => $this->db->from(ORDER_TABLE)->where('reseller_id', $this->user_id)->where('order_status', 'pending')->count_all_results(),
            'processing_orders' => $this->db->from(ORDER_TABLE)->where('reseller_id', $this->user_id)->where('order_status', 'processing')->count_all_results(),
            'to_ship_orders' => $this->db->from(ORDER_TABLE)->where('reseller_id', $this->user_id)->where('order_status', 'to_ship')->count_all_results(),
            'delivered_orders' => $this->db->from(ORDER_TABLE)->where('reseller_id', $this->user_id)->where('order_status', 'delivered')->count_all_results(),
            'daily_sales' => [],
            'monthly_sales' => [],
            'top_products' => [],
        ];
        $this->activity_log_model->create(['user_type' => 'reseller', 'user_id' => $this->user_id, 'action' => 'view_dashboard', 'details' => 'Reseller viewed dashboard', 'ip_address' => $this->input->ip_address()]);
        $this->load->view('reseller/layouts/header', $data);
        $this->load->view('reseller/dashboard/index', $data);
        $this->load->view('reseller/layouts/footer', $data);
    }
}
