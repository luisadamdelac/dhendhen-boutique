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
            $p['variants'] = $this->_variants_for_branch((int) $p['product_id']);
        }
        unset($p);
        $data['products'] = $products;

        $expiryCutoff = date('Y-m-d', strtotime('+30 days'));
        $data['product_stats'] = [
            'total_products'        => count($products),
            'low_stock_products'    => count(array_filter($products, fn($p) => $p['stock'] > 0 && $p['stock'] <= 10)),
            'out_of_stock_products' => count(array_filter($products, fn($p) => $p['stock'] === 0)),
            'expiring_products'     => count(array_filter($products, fn($p) => !empty($p['expiry_date']) && $p['expiry_date'] <= $expiryCutoff)),
        ];

        $this->load->view('staff/layouts/header', $data);
        $this->load->view('staff/inventory/index', $data);
        $this->load->view('staff/layouts/footer', $data);
    }

    /**
     * Products already expired or expiring within 30 days — same lookahead
     * window as the admin/dashboard "Expiring Soon" stat, but scoped to this
     * staff member's own branch stock like the rest of this controller.
     */
    public function expiring() {
        $data = $this->set_view_data();
        $data['page_title'] = 'Expiring Products';

        $products = $this->product_model->base_query()->order_by('p.expiry_date', 'ASC')->get()->result_array();
        $stock_by_product = $this->staff_branch_id
            ? StockService::getAvailableStockForProducts(array_column($products, 'product_id'), $this->staff_branch_id)
            : [];

        $cutoff = date('Y-m-d', strtotime('+30 days'));
        $expiring = [];
        foreach ($products as $p) {
            if (empty($p['expiry_date']) || $p['expiry_date'] > $cutoff) continue;
            $p['stock'] = $stock_by_product[(int) $p['product_id']] ?? 0;
            $expiring[] = $p;
        }

        $data['products'] = $expiring;
        $this->load->view('staff/layouts/header', $data);
        $this->load->view('staff/inventory/expiring', $data);
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
     * This product's generated variant combinations, each with its current
     * stock at this staff member's own branch — lets the Edit Stock modal
     * offer a specific combination instead of only the base product.
     */
    private function _variants_for_branch($product_id) {
        $variants = $this->db->select('v.*')
            ->from(PRODUCT_VARIANTS_TABLE . ' v')
            ->where('v.product_id', $product_id)
            ->get()->result_array();

        if (empty($variants)) {
            return [];
        }

        $variantIds = array_column($variants, 'variant_id');
        $extraByVariant = [];
        $extraRows = $this->db->select('variant_id, variation_id')
            ->from(PRODUCT_VARIANT_EXTRA_VALUES_TABLE)
            ->where_in('variant_id', $variantIds)
            ->order_by('variant_id, axis_order', 'ASC')
            ->get()->result_array();
        foreach ($extraRows as $er) {
            $extraByVariant[(int) $er['variant_id']][] = (int) $er['variation_id'];
        }

        $allAxisIds = [];
        foreach ($variants as $v) {
            $allAxisIds[] = (int) $v['variation_id_1'];
            if (!empty($v['variation_id_2'])) {
                $allAxisIds[] = (int) $v['variation_id_2'];
            }
        }
        foreach ($extraByVariant as $extraIds) {
            foreach ($extraIds as $vid) {
                $allAxisIds[] = $vid;
            }
        }
        $allAxisIds = array_unique($allAxisIds);

        $valueById = [];
        if (!empty($allAxisIds)) {
            $valueRows = $this->db->select('variation_id, variation_value')
                ->from(PRODUCT_VARIATION_TABLE)
                ->where_in('variation_id', $allAxisIds)
                ->get()->result_array();
            foreach ($valueRows as $vr) {
                $valueById[(int) $vr['variation_id']] = $vr['variation_value'];
            }
        }

        $out = [];
        foreach ($variants as $v) {
            $axisIds = [(int) $v['variation_id_1']];
            if (!empty($v['variation_id_2'])) {
                $axisIds[] = (int) $v['variation_id_2'];
            }
            foreach ($extraByVariant[(int) $v['variant_id']] ?? [] as $vid) {
                $axisIds[] = $vid;
            }
            $label = implode(' / ', array_filter(array_map(fn($vid) => $valueById[$vid] ?? NULL, $axisIds)));
            $branch_stock = $this->staff_branch_id ? StockService::getVariantBranchStock((int) $v['variant_id']) : [];
            $out[] = [
                'variant_id' => (int) $v['variant_id'],
                'label'      => $label,
                'stock'      => $branch_stock[$this->staff_branch_id] ?? 0,
            ];
        }
        return $out;
    }

    /**
     * Set a product's (or one of its variant combination's) branch stock to
     * an exact quantity (used by the Edit Stock action) — computes the delta
     * and reuses adjustStock() so the same batch/movement bookkeeping as
     * update_stock() applies.
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
        $variant_id = (int) $this->input->post('variant_id') ?: NULL;
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

        // Scoped to the same pool adjustStock() below will actually touch —
        // previously this used the product's combined total (base + every
        // variant) as the baseline even when editing "Base product (no
        // combination)", so the computed delta was sized against the wrong
        // pool and either under/over-adjusted base-only batches or failed
        // outright with a false "insufficient stock" error.
        $current_stock = $variant_id
            ? (StockService::getVariantBranchStock($variant_id)[$this->staff_branch_id] ?? 0)
            : StockService::getAvailableStock($product_id, $this->staff_branch_id, NULL);
        $delta = $target_quantity - $current_stock;

        $ok = StockService::adjustStock($product_id, $this->staff_branch_id, $delta, $this->user_id, 'Staff stock edit', NULL, $variant_id);
        if (!$ok) {
            echo json_encode(['success' => FALSE, 'message' => 'Failed to update stock (insufficient stock at your branch?)']);
            return;
        }

        $new_stock = $variant_id
            ? (StockService::getVariantBranchStock($variant_id)[$this->staff_branch_id] ?? 0)
            : StockService::getAvailableStock($product_id, $this->staff_branch_id, NULL);

        $this->activity_log_model->create([
            'user_type'   => 'staff',
            'user_id'     => $this->user_id,
            'action'      => 'set_stock',
            'entity_type' => 'product',
            'entity_id'   => $product_id,
            'details'     => 'Set stock at branch ' . $this->staff_branch_id . ' to ' . $new_stock . ($variant_id ? ' (variant ' . $variant_id . ')' : ''),
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
        $variant_id = (int) $this->input->post('variant_id') ?: NULL;
        $delta = (int) $this->input->post('delta');

        $product = $this->product_model->get_by_id($product_id);
        if (!$product) {
            echo json_encode(['success' => FALSE, 'message' => 'Product not found']);
            return;
        }

        $ok = StockService::adjustStock($product_id, $this->staff_branch_id, $delta, $this->user_id, 'Staff stock adjustment', NULL, $variant_id);
        if (!$ok) {
            echo json_encode(['success' => FALSE, 'message' => 'Failed to update stock (insufficient stock at your branch?)']);
            return;
        }

        // Scoped to match what adjustStock() above actually touched — see
        // the note in set_stock() above for why the unscoped total misreports
        // this for products that have variants.
        $new_stock = $variant_id
            ? (StockService::getVariantBranchStock($variant_id)[$this->staff_branch_id] ?? 0)
            : StockService::getAvailableStock($product_id, $this->staff_branch_id, NULL);

        $this->activity_log_model->create([
            'user_type'   => 'staff',
            'user_id'     => $this->user_id,
            'action'      => 'update_stock',
            'entity_type' => 'product',
            'entity_id'   => $product_id,
            'details'     => 'Adjusted stock at branch ' . $this->staff_branch_id . ' to ' . $new_stock . ($variant_id ? ' (variant ' . $variant_id . ')' : ''),
            'ip_address'  => $this->input->ip_address(),
        ]);

        echo json_encode(['success' => TRUE, 'message' => 'Stock updated successfully', 'new_stock' => $new_stock]);
    }
}
