<?php
/**
 * Staff Inventory — scoped to the staff member's own assigned branch.
 * Stock lives per branch in inventory_batches; a staff member may only view
 * and adjust the stock at their own branch (staff_tbl.branch_id), never the
 * central total or another branch's stock.
 */
class Inventory extends Authenticated_Controller {

    private $staff_branch_id;
    private $staff_branch_name;

    public function __construct() {
        parent::__construct();
        $this->require_role(ROLE_STAFF);
        $this->load->model(['product_model', 'activity_log_model']);
        require_once APPPATH . 'services/StockService.php';

        $staff = $this->db->select('branch_id')->where('staff_id', $this->user_id)->get(STAFF_TABLE)->row_array();
        $this->staff_branch_id = $staff['branch_id'] ?? NULL;

        if ($this->staff_branch_id) {
            $branch = $this->db->select('branch_name')->where('branch_id', $this->staff_branch_id)->get(BRANCHES_TABLE)->row_array();
            $this->staff_branch_name = $branch['branch_name'] ?? NULL;
        }
    }

    public function index() {
        $data = $this->set_view_data();
        $data['page_title'] = 'Staff Inventory';
        $data['branch_name'] = $this->staff_branch_name;

        $products = $this->product_model->base_query()
            ->order_by('p.product_id', 'DESC')->get()->result_array();

        // "stock" here means this staff member's own branch, not the central
        // total — one batched query for the whole list instead of one per product.
        $stock_by_product = $this->staff_branch_id
            ? StockService::getAvailableStockForProducts(array_column($products, 'product_id'), $this->staff_branch_id)
            : [];
        foreach ($products as &$p) {
            $p['branch_name'] = $this->staff_branch_name;
            $p['stock'] = $stock_by_product[(int) $p['product_id']] ?? 0;
        }
        unset($p);
        $data['products'] = $products;

        $data['product_stats'] = [
            'total_products'        => count($products),
            'low_stock_products'    => count(array_filter($products, fn($p) => $p['stock'] > 0 && $p['stock'] <= 10)),
            'out_of_stock_products' => count(array_filter($products, fn($p) => $p['stock'] === 0)),
        ];

        $this->load->view('staff/layouts/header', $data);
        $this->load->view('staff/inventory/index', $data);
        $this->load->view('staff/layouts/footer', $data);
    }

    public function low_stock() {
        $data = $this->set_view_data();
        $data['page_title'] = 'Low Stock Items';

        $products = $this->product_model->base_query()->order_by('p.product_name', 'ASC')->get()->result_array();
        $stock_by_product = $this->staff_branch_id
            ? StockService::getAvailableStockForProducts(array_column($products, 'product_id'), $this->staff_branch_id)
            : [];
        $low = [];
        foreach ($products as $p) {
            $branch_stock = $stock_by_product[(int) $p['product_id']] ?? 0;
            if ($branch_stock <= 10) {
                $p['stock'] = $branch_stock;
                $low[] = $p;
            }
        }
        usort($low, fn($a, $b) => $a['stock'] <=> $b['stock']);

        $data['products'] = $low;
        $this->load->view('staff/layouts/header', $data);
        $this->load->view('staff/inventory/low-stock', $data);
        $this->load->view('staff/layouts/footer', $data);
    }

    /**
     * Set a product's branch stock to an exact quantity (used by the Edit
     * Stock action) — computes the delta and reuses adjustStock() so the
     * same batch/movement bookkeeping as update_stock() applies.
     */
    public function set_stock() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        if (!$this->staff_branch_id) {
            echo json_encode(['success' => FALSE, 'message' => 'Your staff account is not assigned to a branch']);
            return;
        }

        $product_id = (int) $this->input->post('product_id');
        $target_quantity = (int) $this->input->post('quantity');

        if ($target_quantity < 0) {
            echo json_encode(['success' => FALSE, 'message' => 'Quantity cannot be negative']);
            return;
        }

        $product = $this->product_model->get_by_id($product_id);
        if (!$product) {
            echo json_encode(['success' => FALSE, 'message' => 'Product not found']);
            return;
        }

        $current_stock = StockService::getAvailableStock($product_id, $this->staff_branch_id);
        $delta = $target_quantity - $current_stock;

        $ok = StockService::adjustStock($product_id, $this->staff_branch_id, $delta, $this->user_id, 'Staff stock edit');
        if (!$ok) {
            echo json_encode(['success' => FALSE, 'message' => 'Failed to update stock (insufficient stock at your branch?)']);
            return;
        }

        $new_stock = StockService::getAvailableStock($product_id, $this->staff_branch_id);

        $this->activity_log_model->create([
            'user_type'   => 'staff',
            'user_id'     => $this->user_id,
            'action'      => 'set_stock',
            'entity_type' => 'product',
            'entity_id'   => $product_id,
            'details'     => 'Set stock at branch ' . $this->staff_branch_id . ' to ' . $new_stock,
            'ip_address'  => $this->input->ip_address(),
        ]);

        echo json_encode(['success' => TRUE, 'message' => 'Stock updated successfully', 'new_stock' => $new_stock]);
    }

    public function update_stock() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        if (!$this->staff_branch_id) {
            echo json_encode(['success' => FALSE, 'message' => 'Your staff account is not assigned to a branch']);
            return;
        }

        $product_id = (int) $this->input->post('product_id');
        $delta = (int) $this->input->post('delta');

        $product = $this->product_model->get_by_id($product_id);
        if (!$product) {
            echo json_encode(['success' => FALSE, 'message' => 'Product not found']);
            return;
        }

        $ok = StockService::adjustStock($product_id, $this->staff_branch_id, $delta, $this->user_id, 'Staff stock adjustment');
        if (!$ok) {
            echo json_encode(['success' => FALSE, 'message' => 'Failed to update stock (insufficient stock at your branch?)']);
            return;
        }

        $new_stock = StockService::getAvailableStock($product_id, $this->staff_branch_id);

        $this->activity_log_model->create([
            'user_type'   => 'staff',
            'user_id'     => $this->user_id,
            'action'      => 'update_stock',
            'entity_type' => 'product',
            'entity_id'   => $product_id,
            'details'     => 'Adjusted stock at branch ' . $this->staff_branch_id . ' to ' . $new_stock,
            'ip_address'  => $this->input->ip_address(),
        ]);

        echo json_encode(['success' => TRUE, 'message' => 'Stock updated successfully', 'new_stock' => $new_stock]);
    }
}
