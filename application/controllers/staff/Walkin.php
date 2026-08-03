<?php
/**
 * Staff Walk-in Sale — records an in-store cash-register sale at the staff
 * member's own assigned branch. Skips the customer-facing checkout entirely
 * (no account, address, or online payment proof needed) and deducts stock
 * directly via StockService, same ledger the online checkout uses.
 */
class Walkin extends Authenticated_Controller {

    private $staff_branch_id;
    private $staff_branch_name;

    public function __construct() {
        parent::__construct();
        $this->require_role(ROLE_STAFF);
        require_once APPPATH . 'services/StockService.php';
        require_once APPPATH . 'services/WalkinSaleService.php';

        $staff = $this->db->select('branch_id')->where('staff_id', $this->user_id)->get(STAFF_TABLE)->row_array();
        $this->staff_branch_id = $staff['branch_id'] ?? NULL;

        if ($this->staff_branch_id) {
            $branch = $this->db->select('branch_name')->where('branch_id', $this->staff_branch_id)->get(BRANCHES_TABLE)->row_array();
            $this->staff_branch_name = $branch['branch_name'] ?? NULL;
        }
    }

    public function index() {
        $data = $this->set_view_data();
        $data['page_title'] = 'Walk-in Sale';
        $data['branch_name'] = $this->staff_branch_name;
        $data['branch_id'] = $this->staff_branch_id;
        $data['recent_sales'] = $this->staff_branch_id
            ? WalkinSaleService::getRecentSales($this->staff_branch_id, 15)
            : [];

        $this->load->view('staff/layouts/header', $data);
        $this->load->view('staff/walkin/index', $data);
        $this->load->view('staff/layouts/footer', $data);
    }

    /**
     * Live product search for the picker — only products with available
     * stock at this staff member's own branch, same scoping as staff/Inventory.
     * Matches by name (anywhere) or SKU (prefix) — a SKU is normally typed
     * from the start, so prefix matching surfaces the right item faster
     * than substring-anywhere would.
     */
    public function search_products() {
        if (!$this->staff_branch_id) {
            echo json_encode(['success' => FALSE, 'message' => 'Your staff account is not assigned to a branch', 'products' => []]);
            return;
        }

        $term = trim((string) $this->input->get('q'));

        $this->db->select('p.*, pi.image_path as product_image')
            ->from(PRODUCT_TABLE . ' p')
            ->join(PRODUCT_IMAGE_TABLE . ' pi', 'pi.product_id = p.product_id AND pi.is_primary = 1', 'left')
            ->where('p.status', 'available')
            ->where('p.is_archived', 0);
        if ($term !== '') {
            $this->db->group_start()
                ->like('p.product_name', $term, 'both')
                ->or_like('p.sku', $term, 'after')
                ->group_end();
        }
        $products = $this->db->order_by('p.product_name', 'ASC')->limit(30)->get()->result_array();

        $product_ids = array_column($products, 'product_id');
        $stock_by_product = StockService::getAvailableStockForProducts($product_ids, $this->staff_branch_id);

        $out = [];
        foreach ($products as $p) {
            $pid = (int) $p['product_id'];
            if (($stock_by_product[$pid] ?? 0) <= 0) {
                continue; // nothing sellable at this branch
            }
            $out[] = $this->_product_payload($p, $pid, $stock_by_product[$pid] ?? 0, $this->staff_branch_id);
        }

        echo json_encode(['success' => TRUE, 'products' => $out]);
    }

    /**
     * Mirrors Shop.php/Checkout.php's own two variant systems: a product
     * either has generated combinations (product_variants — one or more
     * axes resolved to a single variant_id) or plain single-axis values
     * (product_variation_tbl, no combinations row) — never both. Whichever
     * it has, a real selection is required before it can be sold, same as
     * the customer-facing product page enforces (no "base" fallback when
     * options exist).
     */
    private function _product_payload($p, $pid, $aggregate_stock, $branchId) {
        $variants = $this->_variants_for_branch($pid, $branchId);
        if (!empty($variants)) {
            return [
                'product_id' => $pid,
                'product_name' => $p['product_name'],
                'sku' => $p['sku'],
                'price' => (float) $p['price'],
                'image' => !empty($p['product_image']) ? base_url($p['product_image']) : NULL,
                'stock' => $aggregate_stock,
                'mode' => 'variant',
                'options' => $variants,
            ];
        }

        $variations = $this->db->select('*')->from(PRODUCT_VARIATION_TABLE)
            ->where('product_id', $pid)->where('status', 'active')
            ->order_by('variation_type', 'ASC')->get()->result_array();

        if (!empty($variations)) {
            $options = [];
            foreach ($variations as $v) {
                $options[] = [
                    'id' => (int) $v['variation_id'],
                    'label' => $v['variation_type'] . ': ' . $v['variation_value'],
                    'price_adjustment' => (float) $v['price_adjustment'],
                    'stock' => StockService::getBranchStock($pid, (int) $v['variation_id'])[$branchId] ?? 0,
                ];
            }
            return [
                'product_id' => $pid,
                'product_name' => $p['product_name'],
                'sku' => $p['sku'],
                'price' => (float) $p['price'],
                'image' => !empty($p['product_image']) ? base_url($p['product_image']) : NULL,
                'stock' => $aggregate_stock,
                'mode' => 'variation',
                'options' => $options,
            ];
        }

        // No variants/variations at all — a plain product, aggregate stock
        // IS the only stock scope there is.
        return [
            'product_id' => $pid,
            'product_name' => $p['product_name'],
            'sku' => $p['sku'],
            'price' => (float) $p['price'],
            'image' => !empty($p['product_image']) ? base_url($p['product_image']) : NULL,
            'stock' => $aggregate_stock,
            'mode' => 'none',
            'options' => [],
        ];
    }

    /**
     * Generated variant combinations (product_variants) for a product, each
     * labeled by its axis values (e.g. "White", "Red / Glossy") with this
     * branch's real stock — mirrors staff/Inventory.php's own
     * _variants_for_branch(), plus the price_adjustment Inventory doesn't need.
     */
    private function _variants_for_branch($product_id, $branchId) {
        $variants = $this->db->select('v.*')
            ->from(PRODUCT_VARIANTS_TABLE . ' v')
            ->where('v.product_id', $product_id)
            ->where('v.status', 'active')
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
            $branch_stock = StockService::getVariantBranchStock((int) $v['variant_id']);

            $out[] = [
                'id' => (int) $v['variant_id'],
                'label' => $label,
                'price_adjustment' => (float) $v['price_adjustment'],
                'stock' => $branch_stock[$branchId] ?? 0,
            ];
        }
        return $out;
    }

    public function create() {
        if ($this->input->method() !== 'post') {
            echo json_encode(['success' => FALSE, 'message' => 'Invalid request']);
            return;
        }

        if (!$this->staff_branch_id) {
            echo json_encode(['success' => FALSE, 'message' => 'Your staff account is not assigned to a branch']);
            return;
        }

        $items = json_decode($this->input->post('items', TRUE), TRUE);
        if (!is_array($items) || empty($items)) {
            echo json_encode(['success' => FALSE, 'message' => 'Please add at least one product to the sale.']);
            return;
        }

        $payment_method = $this->input->post('payment_method', TRUE) === 'gcash' ? 'gcash' : 'cash';
        $notes = trim((string) $this->input->post('notes', TRUE)) ?: NULL;

        try {
            $sale_id = WalkinSaleService::createSale($items, $payment_method, 'staff', $this->user_id, $this->staff_branch_id, $notes);
            $this->log_activity('Recorded walk-in sale', 'walkin_sale', $sale_id);
            echo json_encode(['success' => TRUE, 'message' => 'Sale recorded successfully!', 'sale_id' => $sale_id]);
        } catch (Exception $e) {
            echo json_encode(['success' => FALSE, 'message' => $e->getMessage()]);
        }
    }
}

/* End of file Walkin.php */
/* Location: ./application/controllers/staff/Walkin.php */
