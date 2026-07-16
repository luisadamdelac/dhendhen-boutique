<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Product extends Authenticated_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_role('admin');
        $this->load->model(['Product_model', 'Activity_log_model']);
        $this->load->library('form_validation');
        require_once APPPATH . 'services/StockService.php';
    }

    /**
     * Product list view
     */
    public function index() {
        $data['page_title'] = 'Inventory';
        $data['current_page'] = 'inventory';

        $search   = $this->input->get('search')   ?? '';
        $category = $this->input->get('category') ?? '';
        $branch   = $this->input->get('branch')   ?? '';
        $status   = $this->input->get('status')   ?? '';
        $view     = $this->input->get('view')     ?? '';

        $filters = [
            'search'        => $search,
            'category_id'   => $category,
            'branch_id'     => $branch,
            'status'        => $status,
            'archived_only' => $view === 'archived',
        ];

        // Product query — includes primary image in one go (avoids N+1 in view).
        // base_query() already LEFT JOINs product_image as "pi" (aliased to
        // product_image); reuse that same join for the view's primary_image
        // key instead of joining "pi" a second time (which errors with
        // "Not unique table/alias: 'pi'").
        // The DataTable on the list view now handles pagination/sorting
        // client-side, so the full filtered set is fetched in one go rather
        // than one server-paginated slice.
        $data['products'] = $this->Product_model->base_query($filters)
            ->select('pi.image_path as primary_image')
            ->order_by('p.product_id', 'DESC')
            ->get()->result_array();

        // Attach a per-branch stock breakdown to each row for display (product can now live in multiple branches)
        // — one batched query for the whole list instead of one per product.
        $branch_stock_by_product = StockService::getBranchStockForProducts(array_column($data['products'], 'product_id'));
        foreach ($data['products'] as &$p) {
            $p['branch_stock'] = $branch_stock_by_product[(int) $p['product_id']] ?? [];
        }
        unset($p);

        $data['total']  = count($data['products']);
        $data['search']   = $search;
        $data['category'] = $category;
        $data['branch']   = $branch;
        $data['status']   = $status;
        $data['view']     = $view;
        $data['archived_count'] = $this->db->where('is_archived', 1)->count_all_results(PRODUCT_TABLE);

        $data['categories'] = $this->db->select('*')->from(CATEGORY_TABLE)->order_by('category_name')->get()->result_array();
        $data['branches']   = $this->db->select('*')->from(BRANCHES_TABLE)->order_by('branch_name')->get()->result_array();

        // Stat cards — count all non-available as "not available" (archived products excluded from every stat)
        $data['stat_total']         = $this->db->where('is_archived', 0)->count_all_results(PRODUCT_TABLE);
        $data['stat_available']     = $this->db->where('status', 'available')->where('is_archived', 0)->count_all_results(PRODUCT_TABLE);
        $data['stat_not_available'] = $this->db->where('status !=', 'available')->where('is_archived', 0)->count_all_results(PRODUCT_TABLE);

        // Low-stock: use min_stock_alert column if it exists, else stock < 10
        $prod_fields = $this->db->list_fields(PRODUCT_TABLE);
        if (in_array('min_stock_alert', $prod_fields)) {
            $data['stat_low_stock'] = $this->db->query(
                'SELECT COUNT(*) AS cnt FROM ' . PRODUCT_TABLE . ' WHERE stock > 0 AND stock <= min_stock_alert AND is_archived = 0'
            )->row()->cnt;
        } else {
            $data['stat_low_stock'] = $this->db->where('stock <', 10)->where('stock >', 0)->where('is_archived', 0)->count_all_results(PRODUCT_TABLE);
        }

        $data['stat_expiring'] = $this->Product_model->count_expiring(30);

        // Branches section — per-branch product/stock counts from the batch ledger
        $data['branches_list'] = $this->db
            ->select('b.*, COUNT(DISTINCT ib.product_id) as total_products, COALESCE(SUM(ib.remaining_quantity), 0) as total_stock')
            ->from(BRANCHES_TABLE . ' b')
            ->join(INVENTORY_BATCH_TABLE . ' ib', 'ib.branch_id = b.branch_id AND ib.status = \'active\'', 'left')
            ->group_by('b.branch_id')
            ->order_by('b.branch_name', 'ASC')
            ->get()->result_array();

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/product/list', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Add product
     */
    public function add() {
        $data['page_title'] = 'Add Product';
        $data['current_page'] = 'inventory';

        if ($this->input->method() === 'post') {
            $this->_handle_add_product();
            return;
        }

        $data['categories'] = $this->db->select('*')
            ->from(CATEGORY_TABLE)
            ->order_by('category_name', 'ASC')
            ->get()->result_array();
        $data['brands'] = $this->db
            ->distinct()
            ->select('brand')
            ->from(PRODUCT_TABLE)
            ->where('brand IS NOT NULL')
            ->where('brand !=', '')
            ->order_by('brand', 'ASC')
            ->get()
            ->result_array();
        $data['branches'] = $this->db->select('*')
            ->from(BRANCHES_TABLE)
            ->where('status', 'active')
            ->order_by('branch_name', 'ASC')
            ->get()->result_array();
        $data['suggested_sku'] = $this->_generate_sku();
        $data['error']   = $this->session->flashdata('error');
        $data['success'] = $this->session->flashdata('success');

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/product/add', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * AJAX: quick-create a brand from the Add Product page's brand picker.
     * Brand has no dedicated table — it only persists once a product is
     * saved with this value (same as the pre-existing free-text brand
     * field). This just reserves/validates the name so the picker can
     * offer it immediately without waiting for the product to be saved.
     */
    public function quick_create_brand() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $name = trim((string) $this->input->post('name', TRUE));

        if ($name === '') {
            return $this->_json_out(['success' => FALSE, 'message' => 'Brand name is required.']);
        }
        if (mb_strlen($name) > 100) {
            return $this->_json_out(['success' => FALSE, 'message' => 'Brand name must be 100 characters or fewer.']);
        }

        $existing = $this->db
            ->distinct()
            ->select('brand')
            ->from(PRODUCT_TABLE)
            ->where('LOWER(brand)', strtolower($name))
            ->get()
            ->row_array();

        if ($existing) {
            return $this->_json_out(['success' => FALSE, 'message' => 'This brand already exists.']);
        }

        return $this->_json_out(['success' => TRUE, 'brand' => ['brand' => $name]]);
    }

    /**
     * AJAX: quick-create a category from the Add Product page's category
     * picker. Mirrors the same case-insensitive dedupe + insert used by
     * _handle_add_product() for new_category_name, just triggered earlier
     * (on demand) instead of at final submit time.
     */
    public function quick_create_category() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $name = trim((string) $this->input->post('name', TRUE));

        if ($name === '') {
            return $this->_json_out(['success' => FALSE, 'message' => 'Category name is required.']);
        }
        if (mb_strlen($name) > 100) {
            return $this->_json_out(['success' => FALSE, 'message' => 'Category name must be 100 characters or fewer.']);
        }

        $existing = $this->db
            ->from(CATEGORY_TABLE)
            ->where('LOWER(category_name)', strtolower($name))
            ->get()
            ->row_array();

        if ($existing) {
            return $this->_json_out(['success' => FALSE, 'message' => 'This category already exists.']);
        }

        $this->db->insert(CATEGORY_TABLE, ['category_name' => $name]);
        $category_id = $this->db->insert_id();

        return $this->_json_out(['success' => TRUE, 'category' => ['category_id' => $category_id, 'category_name' => $name]]);
    }

    private function _json_out($payload) {
        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    /**
     * Process add-product form submission
     */
    private function _handle_add_product() {
        // --- Form validation ---
        $this->form_validation->set_rules('product_name',        'Product Name',       'required|trim|max_length[150]');
        $this->form_validation->set_rules('brand',               'Brand',              'trim|max_length[100]');
        $this->form_validation->set_rules('new_brand_name',      'New Brand',          'trim|max_length[100]');
        $this->form_validation->set_rules('category_id',         'Category',           'integer');
        $this->form_validation->set_rules('new_category_name',   'New Category',       'trim|max_length[100]');
        $this->form_validation->set_rules('cost_price',          'Cost Price',         'required|numeric');
        $this->form_validation->set_rules('price',               'Selling Price',      'required|numeric');
        $this->form_validation->set_rules('min_stock_alert',     'Min Stock Alert',    'required|integer|greater_than_equal_to[1]');
        $this->form_validation->set_rules('status',              'Status',             'required');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors('<li>', '</li>'));
            redirect('admin/product/add');
            return;
        }

        // --- Collect and sanitize values ---
        $product_name        = $this->input->post('product_name', TRUE);
        $brand               = $this->input->post('brand', TRUE) ?: NULL;
        $new_brand_name      = trim($this->input->post('new_brand_name', TRUE) ?? '');
        $category_id         = (int) $this->input->post('category_id') ?: NULL;
        $new_category_name   = trim($this->input->post('new_category_name', TRUE) ?? '');
        $sku                 = $this->_generate_sku();
        $cost_price          = (float) $this->input->post('cost_price');
        $price               = (float) $this->input->post('price');
        $reseller_commission = 0.0;
        $min_stock_alert     = (int) $this->input->post('min_stock_alert');
        $status              = $this->input->post('status', TRUE);
        $expiry_date         = $this->input->post('expiry_date', TRUE) ?: NULL;
        $description         = $this->input->post('description', TRUE) ?: NULL;
        $tags                = $this->input->post('tags', TRUE) ?: NULL;

        // Initial stock comes entirely from the Product Variations step now
        // (each variation carries its own per-branch breakdown) — there is no
        // separate flat branch-stock field on the Add Product form anymore.
        $stock = $this->_posted_variation_stock_total();

        // --- Business-logic checks ---
        $errors = [];

        if ($new_brand_name !== '') {
            $brand = $new_brand_name;
        }

        if ($new_category_name !== '') {
            $existing_category = $this->db
                ->from(CATEGORY_TABLE)
                ->where('LOWER(category_name)', strtolower($new_category_name))
                ->get()
                ->row_array();

            if ($existing_category) {
                $category_id = (int) $existing_category['category_id'];
            } else {
                $this->db->insert(CATEGORY_TABLE, ['category_name' => $new_category_name]);
                $category_id = $this->db->insert_id();
            }
        }

        if ($cost_price <= 0) {
            $errors[] = 'Cost Price must be greater than 0.';
        }
        if ($price <= $cost_price) {
            $errors[] = 'Selling Price must be greater than Cost Price.';
        }
        // Variations are optional, but every product needs stock somewhere —
        // either in named variation values or, if it has none, in the base
        // (no-variation) branch stock table. The JS wizard enforces this
        // client-side too; this is the server-side backstop.
        if ($stock <= 0) {
            $errors[] = 'Product stock is required — enter a quantity for at least one branch.';
        }
        if ($min_stock_alert > $stock && $stock > 0) {
            $errors[] = 'Minimum Stock Alert cannot exceed current Stock Quantity.';
        }

        // product_tbl.status is enum('available','not_available') — matches
        // what _validate_and_save_product() (the Edit flow) already writes.
        $allowed_statuses = ['available', 'not_available'];
        if (!in_array($status, $allowed_statuses, TRUE)) {
            $status = 'available';
        }

        // Image is required
        $has_image = !empty($_FILES['product_image']['name']);
        if (!$has_image) {
            $errors[] = 'Product Image is required.';
        }

        if (!empty($errors)) {
            $this->session->set_flashdata('error', '<ul class="mb-0">' . implode('', array_map(fn($e) => '<li>' . $e . '</li>', $errors)) . '</ul>');
            redirect('admin/product/add');
            return;
        }

        // --- Image upload ---
        $upload_path = FCPATH . 'uploads/products/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, TRUE);
        }

        $this->load->library('upload', [
            'upload_path'   => $upload_path,
            'allowed_types' => 'jpg|jpeg|png|webp',
            'max_size'      => 5120,
            'encrypt_name'  => TRUE,
        ]);

        if (!$this->upload->do_upload('product_image')) {
            $this->session->set_flashdata('error', 'Image upload failed: ' . $this->upload->display_errors('', ''));
            redirect('admin/product/add');
            return;
        }

        $upload_data = $this->upload->data();
        $image_path  = 'uploads/products/' . $upload_data['file_name'];

        // --- Persist in a transaction ---
        $this->db->trans_start();

        $now = date('Y-m-d H:i:s');
        $product_id = $this->Product_model->create([
            'sku'                  => $sku,
            'product_name'         => $product_name,
            'brand'                => $brand,
            'category_id'          => $category_id,
            'price'                => $price,
            'cost_price'           => $cost_price,
            'reseller_commission'  => $reseller_commission,
            'min_stock_alert'      => $min_stock_alert,
            'status'               => $status,
            'expiry_date'          => $expiry_date,
            'description'          => $description,
            'tags'                 => $tags,
            'added_by'             => $this->user_id,
            'created_by_role'      => $this->session->userdata('user_type') === 'staff' ? 'staff' : 'admin',
        ]);

        // Primary product image
        $this->db->insert(PRODUCT_IMAGE_TABLE, [
            'product_id'    => $product_id,
            'image_path'    => $image_path,
            'is_primary'    => 1,
            'display_order' => 0,
            'created_at'    => $now,
        ]);

        // Initial stock (base + per-combination) is created here — this
        // also keeps product_tbl.stock in sync as the total.
        try {
            $this->_save_base_stock($product_id);
            $valueIdMap = $this->_save_variation_values($product_id);
            $this->_save_variant_combinations($product_id, $valueIdMap);
        } catch (Exception $e) {
            $this->db->trans_complete();
            if (file_exists(FCPATH . $image_path)) {
                @unlink(FCPATH . $image_path);
            }
            $this->session->set_flashdata('error', $e->getMessage());
            redirect('admin/product/add');
            return;
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            // Roll back uploaded file
            if (file_exists(FCPATH . $image_path)) {
                @unlink(FCPATH . $image_path);
            }
            $this->session->set_flashdata('error', 'Database error — product could not be saved. Please try again.');
            redirect('admin/product/add');
            return;
        }

        // Low-stock notification
        if ($stock <= $min_stock_alert) {
            $this->_notify_low_stock($product_id, $product_name, $stock, 'across branches');
        }

        // Audit log
        $this->Activity_log_model->log_activity(
            get_user_id(),
            'admin',
            'create_product',
            'Added product: ' . $product_name . ' (SKU: ' . $sku . ')',
            get_client_ip()
        );

        $this->session->set_flashdata('success', 'Product "' . htmlspecialchars($product_name) . '" added successfully!');
        redirect('admin/inventory');
    }

    /**
     * Generate next DHB-XXXXXX SKU
     */
    private function _generate_sku(): string {
        $row = $this->db->query(
            "SELECT MAX(CAST(SUBSTRING_INDEX(sku, '-', -1) AS UNSIGNED)) AS max_num
             FROM " . PRODUCT_TABLE . " WHERE sku REGEXP '^DHB-[0-9]+$'"
        )->row_array();
        $num = !empty($row['max_num']) ? (int) $row['max_num'] + 1 : 1;

        // Guarantee uniqueness
        do {
            $sku    = 'DHB-' . str_pad($num, 6, '0', STR_PAD_LEFT);
            $exists = $this->db->where('sku', $sku)->count_all_results(PRODUCT_TABLE);
            $num++;
        } while ($exists);

        return $sku;
    }

    /**
     * Insert a low-stock notification for all admins
     */
    private function _notify_low_stock(int $product_id, string $product_name, int $stock, string $branch_name): void {
        $admins = $this->db->select('admin_id')->get(ADMIN_TABLE)->result_array();
        $now    = date('Y-m-d H:i:s');
        foreach ($admins as $admin) {
            $this->db->insert(NOTIFICATIONS_TABLE, [
                'user_type'  => 'admin',
                'user_id'    => $admin['admin_id'],
                'title'      => 'Low Stock Alert',
                'message'    => '"' . $product_name . '" has only ' . $stock . ' unit(s) at ' . $branch_name . '.',
                'is_read'    => 0,
                'type'       => 'system',
                'created_at' => $now,
            ]);
        }
    }

    /**
     * Edit product
     */
    public function edit($product_id = '') {
        $data['page_title'] = 'Edit Inventory';
        $data['current_page'] = 'inventory';
        if (empty($product_id)) {
            redirect('admin/product');
        }

        $product = $this->Product_model->get_by_id($product_id);
        if (!$product) {
            $this->session->set_flashdata('error', 'Product not found');
            redirect('admin/product');
        }

        if ($this->input->method() === 'post') {
            $this->_validate_and_save_product($product_id);
            return;
        }

        $data['product'] = $product;
        $data['categories'] = $this->db->select('*')->from(CATEGORY_TABLE)->get()->result_array();
        $data['branches'] = $this->db->select('*')->from(BRANCHES_TABLE)->get()->result_array();
        $data['branch_stock'] = StockService::getBranchStock($product_id);
        $data['brands'] = $this->db
            ->distinct()
            ->select('brand')
            ->from(PRODUCT_TABLE)
            ->where('brand IS NOT NULL')
            ->where('brand !=', '')
            ->order_by('brand', 'ASC')
            ->get()
            ->result_array();
        $data['primary_image'] = $this->db->select('image_path')->from(PRODUCT_IMAGE_TABLE)
            ->where('product_id', $product_id)->where('is_primary', 1)->get()->row_array();
        $data['variations'] = $this->db->select('*')->from(PRODUCT_VARIATION_TABLE)
            ->where('product_id', $product_id)->order_by('variation_id', 'ASC')->get()->result_array();

        // Generated variant combinations — each carries its own SKU/barcode/
        // price/status plus a real per-branch stock breakdown (via
        // inventory_batches.variant_id), used to pre-populate the wizard's
        // "Generated Variant Combinations" table on load.
        $data['combinations'] = $this->db->select('*')->from(PRODUCT_VARIANTS_TABLE)
            ->where('product_id', $product_id)->order_by('variant_id', 'ASC')->get()->result_array();
        $valueById = [];
        foreach ($data['variations'] as $v) {
            $valueById[(int) $v['variation_id']] = $v;
        }
        foreach ($data['combinations'] as &$combo) {
            $v1 = $valueById[(int) $combo['variation_id_1']] ?? NULL;
            $v2 = $combo['variation_id_2'] ? ($valueById[(int) $combo['variation_id_2']] ?? NULL) : NULL;
            $combo['type_1'] = $v1['variation_type'] ?? '';
            $combo['value_1'] = $v1['variation_value'] ?? '';
            $combo['type_2'] = $v2['variation_type'] ?? '';
            $combo['value_2'] = $v2['variation_value'] ?? '';
            $combo['branch_stock'] = StockService::getVariantBranchStock((int) $combo['variant_id']);
        }
        unset($combo);

        // Base (no-variation) product stock — still supported for products
        // with zero variation types.
        $data['base_stock'] = StockService::getBranchStock($product_id, NULL);

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/product/edit', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * View product
     */
    public function view($product_id = '') {
        $data['page_title'] = 'Product Details';
        $data['current_page'] = 'inventory';
        if (empty($product_id)) {
            redirect('admin/product');
        }

        $product = $this->Product_model->get_by_id($product_id, true);

        if (!$product) {
            $this->session->set_flashdata('error', 'Product not found');
            redirect('admin/product');
        }

        $data['product'] = $product;
        $data['images'] = $this->db->select('*')->from(PRODUCT_IMAGE_TABLE)->where('product_id', $product_id)->get()->result_array();
        $data['branches'] = $this->db->select('*')->from(BRANCHES_TABLE)->get()->result_array();
        $data['branch_stock'] = StockService::getBranchStock($product_id);

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/product/view', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Low stock products
     */
    public function low_stock() {
        $data['page_title'] = 'Low Stock';
        $data['current_page'] = 'inventory';
        $page = $this->input->get('page') ?? 1;
        $limit = PAGINATION_LIMIT;
        $offset = ($page - 1) * $limit;
        $threshold = $this->input->get('threshold') ?? 50;

        $data['products'] = $this->Product_model->get_low_stock($threshold, $limit, $offset, false);

        $data['total'] = $this->Product_model->count_low_stock($threshold, false);
        $data['threshold'] = $threshold;
        $data['page'] = $page;
        $data['pages'] = ceil($data['total'] / $limit);
        $data['branches'] = $this->db->select('*')->from(BRANCHES_TABLE)->order_by('branch_name')->get()->result_array();
        $data['branch_stock'] = StockService::getBranchStockForProducts(array_column($data['products'], 'product_id'));

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/product/low_stock', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Products already expired or expiring soon (default: within 30 days).
     */
    public function expiring() {
        $data['page_title'] = 'Expiring Products';
        $data['current_page'] = 'inventory';
        $page = $this->input->get('page') ?? 1;
        $limit = PAGINATION_LIMIT;
        $offset = ($page - 1) * $limit;
        $days = $this->input->get('days') ?? 30;

        $data['products'] = $this->Product_model->get_expiring($days, $limit, $offset);
        $data['total'] = $this->Product_model->count_expiring($days);
        $data['days'] = $days;
        $data['page'] = $page;
        $data['pages'] = ceil($data['total'] / $limit);
        $data['branches'] = $this->db->select('*')->from(BRANCHES_TABLE)->order_by('branch_name')->get()->result_array();
        $data['branch_stock'] = StockService::getBranchStockForProducts(array_column($data['products'], 'product_id'));

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/product/expiring', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    /**
     * Quick restock from the Low Stock page — adds straight to a branch's
     * base (no-variation) stock. Products whose stock lives entirely in a
     * named variation (e.g. "Size: Small") aren't restockable here, since
     * there's no single variation to target from this list view — those
     * need the full Edit Product page instead.
     */
    public function quick_restock($product_id = '') {
        if (empty($product_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $branch_id = (int) $this->input->post('branch_id');
        $quantity = (int) $this->input->post('quantity');

        if ($quantity <= 0) {
            echo json_encode(['success' => FALSE, 'message' => 'Enter a quantity greater than 0.']);
            return;
        }
        $branch = $this->db->where('branch_id', $branch_id)->get(BRANCHES_TABLE)->row_array();
        if (!$branch) {
            echo json_encode(['success' => FALSE, 'message' => 'Select a valid branch.']);
            return;
        }

        $ok = StockService::adjustStock($product_id, $branch_id, $quantity, $this->user_id, 'Quick restock from Low Stock page', NULL);
        if (!$ok) {
            echo json_encode(['success' => FALSE, 'message' => 'Failed to restock. Please try again.']);
            return;
        }

        $this->Activity_log_model->log_activity(get_user_id(), 'admin', 'quick_restock', "Restocked product $product_id: +$quantity at {$branch['branch_name']}", get_client_ip());
        echo json_encode(['success' => TRUE, 'message' => 'Stock added successfully']);
    }

    /**
     * Delete product
     */
    public function delete($product_id = '') {
        if (empty($product_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $product = $this->db->select('*')->from(PRODUCT_TABLE)->where('product_id', $product_id)->get()->row_array();
        if (!$product) {
            echo json_encode(['success' => FALSE, 'message' => 'Product not found']);
            return;
        }

        $this->db->trans_start();
        $this->db->delete(PRODUCT_IMAGE_TABLE, ['product_id' => $product_id]);
        $this->db->delete(PRODUCT_TABLE, ['product_id' => $product_id]);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => FALSE, 'message' => 'Failed to delete product']);
        } else {
            $this->Activity_log_model->log_activity(get_user_id(), 'admin', 'delete_product', 'Deleted product: ' . $product['product_name'], get_client_ip());
            echo json_encode(['success' => TRUE, 'message' => 'Product deleted successfully']);
        }
    }

    /**
     * Archive a product — hides it from every normal listing (inventory,
     * reseller catalog, customer shop) without deleting its data or history.
     */
    public function archive($product_id = '') {
        if (empty($product_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $product = $this->db->select('product_name')->from(PRODUCT_TABLE)->where('product_id', $product_id)->get()->row_array();
        if (!$product) {
            echo json_encode(['success' => FALSE, 'message' => 'Product not found']);
            return;
        }

        $this->db->where('product_id', $product_id)->update(PRODUCT_TABLE, ['is_archived' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
        $this->Activity_log_model->log_activity(get_user_id(), 'admin', 'archive_product', 'Archived product: ' . $product['product_name'], get_client_ip());
        echo json_encode(['success' => TRUE, 'message' => 'Product archived successfully']);
    }

    /**
     * Restore a previously archived product back into normal listings.
     */
    public function restore($product_id = '') {
        if (empty($product_id) || $this->input->method() !== 'post') {
            show_404();
        }

        $product = $this->db->select('product_name')->from(PRODUCT_TABLE)->where('product_id', $product_id)->get()->row_array();
        if (!$product) {
            echo json_encode(['success' => FALSE, 'message' => 'Product not found']);
            return;
        }

        $this->db->where('product_id', $product_id)->update(PRODUCT_TABLE, ['is_archived' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
        $this->Activity_log_model->log_activity(get_user_id(), 'admin', 'restore_product', 'Restored product: ' . $product['product_name'], get_client_ip());
        echo json_encode(['success' => TRUE, 'message' => 'Product restored successfully']);
    }

    /**
     * Validate and save product
     */
    private function _validate_and_save_product($product_id = NULL) {
        $this->form_validation->set_rules('product_name', 'Product Name', 'required|trim');
        $this->form_validation->set_rules('brand', 'Brand', 'trim|max_length[100]');
        $this->form_validation->set_rules('price', 'Price', 'required|numeric');
        $this->form_validation->set_rules('cost_price', 'Cost Price', 'required|numeric');
        $this->form_validation->set_rules('min_stock_alert', 'Min Stock Alert', 'required|integer|greater_than_equal_to[1]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            redirect($product_id ? "admin/product/edit/{$product_id}" : 'admin/product/add');
            return;
        }

        $category_id = (int) $this->input->post('category_id');
        $price = (float) $this->input->post('price');
        $cost_price = (float) $this->input->post('cost_price');

        $errors = [];
        if ($cost_price <= 0) {
            $errors[] = 'Cost Price must be greater than 0.';
        }
        if ($price <= $cost_price) {
            $errors[] = 'Selling Price must be greater than Cost Price.';
        }
        if (!empty($errors)) {
            $this->session->set_flashdata('error', '<ul class="mb-0">' . implode('', array_map(fn($e) => '<li>' . $e . '</li>', $errors)) . '</ul>');
            redirect("admin/product/edit/{$product_id}");
            return;
        }

        $product_data = [
            'product_name'     => $this->input->post('product_name'),
            'brand'            => $this->input->post('brand', TRUE) ?: NULL,
            'description'      => $this->input->post('description') ?? '',
            'price'            => $price,
            'cost_price'       => $cost_price,
            'category_id'      => $category_id ?: NULL,
            'min_stock_alert'  => (int) $this->input->post('min_stock_alert'),
            'expiry_date'      => $this->input->post('expiry_date', TRUE) ?: NULL,
            'tags'             => $this->input->post('tags', TRUE) ?: NULL,
            'status'           => $this->input->post('status') === 'not_available' ? 'not_available' : 'available',
        ];

        // Replace the primary product image only if a new file was uploaded — leave the existing one otherwise.
        if (!empty($_FILES['product_image']['name'])) {
            $upload_path = FCPATH . 'uploads/products/';
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, TRUE);
            }
            $this->load->library('upload', [
                'upload_path'   => $upload_path,
                'allowed_types' => 'jpg|jpeg|png|webp',
                'max_size'      => 5120,
                'encrypt_name'  => TRUE,
            ]);

            if (!$this->upload->do_upload('product_image')) {
                $this->session->set_flashdata('error', 'Image upload failed: ' . $this->upload->display_errors('', ''));
                redirect("admin/product/edit/{$product_id}");
                return;
            }

            $new_image_path = 'uploads/products/' . $this->upload->data('file_name');
            $existing_image = $this->db->select('image_id, image_path')->from(PRODUCT_IMAGE_TABLE)
                ->where('product_id', $product_id)->where('is_primary', 1)->get()->row_array();

            if ($existing_image) {
                $this->db->where('image_id', $existing_image['image_id'])->update(PRODUCT_IMAGE_TABLE, ['image_path' => $new_image_path]);
                if (!empty($existing_image['image_path']) && file_exists(FCPATH . $existing_image['image_path'])) {
                    @unlink(FCPATH . $existing_image['image_path']);
                }
            } else {
                $this->db->insert(PRODUCT_IMAGE_TABLE, [
                    'product_id'    => $product_id,
                    'image_path'    => $new_image_path,
                    'is_primary'    => 1,
                    'display_order' => 0,
                    'created_at'    => date('Y-m-d H:i:s'),
                ]);
            }
        }

        $this->Product_model->update($product_id, $product_data);

        // Base stock + variation values + generated combinations — Product
        // Variations & Branch Stock is the only place stock is entered now.
        try {
            $this->_save_base_stock($product_id);
            $valueIdMap = $this->_save_variation_values($product_id);
            $this->_save_variant_combinations($product_id, $valueIdMap);
        } catch (Exception $e) {
            $this->session->set_flashdata('error', $e->getMessage());
            redirect("admin/product/edit/{$product_id}");
            return;
        }

        $this->Activity_log_model->log_activity(get_user_id(), 'admin', 'update_product', 'Updated product: ' . $product_data['product_name'], get_client_ip());
        $this->session->set_flashdata('success', 'Product updated successfully');
        redirect('admin/product');
    }

    /**
     * Sum of stock across base stock + every generated combination — used
     * for the initial-stock validation on Add Product.
     */
    private function _posted_variation_stock_total() {
        $total = 0;

        $baseJson = $this->input->post('base_stock_json', TRUE);
        if (!empty($baseJson)) {
            $decoded = json_decode($baseJson, TRUE);
            if (is_array($decoded)) {
                foreach ($decoded as $qty) {
                    $total += max(0, (int) $qty);
                }
            }
        }

        $combosJson = $this->input->post('combinations_json', TRUE);
        if (!empty($combosJson)) {
            $decoded = json_decode($combosJson, TRUE);
            if (is_array($decoded)) {
                foreach ($decoded as $c) {
                    $branch_stock = is_array($c['branch_stock'] ?? NULL) ? $c['branch_stock'] : [];
                    foreach ($branch_stock as $qty) {
                        $total += max(0, (int) $qty);
                    }
                }
            }
        }

        return $total;
    }

    /**
     * Base (no-variation) product stock — the plain per-branch quantities
     * for a product that has no variation types at all (or stock that sits
     * outside any combination). Diffed against existing batches so re-saves
     * only create an adjustment for the delta, never a blind re-add.
     */
    private function _save_base_stock($product_id) {
        // The wizard no longer renders this field at all (base stock is only
        // ever entered through the Generated Combinations table now) — if it
        // truly isn't in the request, leave existing branch stock untouched
        // instead of reading a missing value as "0 everywhere" and deducting
        // an already-saved product's stock down to zero on its next edit.
        if ($this->input->post('base_stock_json') === NULL) {
            return;
        }
        $json = $this->input->post('base_stock_json', TRUE);
        $posted = [];
        if (!empty($json)) {
            $decoded = json_decode($json, TRUE);
            if (is_array($decoded)) {
                $posted = $decoded;
            }
        }

        $existingBranchStock = StockService::getBranchStock($product_id, NULL);
        $allBranchIds = array_unique(array_merge(
            array_keys($existingBranchStock),
            array_map('intval', array_keys($posted))
        ));

        foreach ($allBranchIds as $branch_id) {
            $current = $existingBranchStock[$branch_id] ?? 0;
            $target = (int) ($posted[$branch_id] ?? $posted[(string) $branch_id] ?? 0);
            $delta = $target - $current;
            if ($delta !== 0) {
                StockService::adjustStock($product_id, $branch_id, $delta, $this->user_id, 'Base stock edit', NULL);
            }
        }
    }

    /**
     * Save the product's variation TYPES + VALUES only (Section 1/2 of the
     * wizard) — no stock, no price/status-per-combination here anymore.
     * Diffs against what already exists by the natural key
     * (product_id, variation_type, variation_value) instead of wiping and
     * recreating every save, so variation_id stays stable across edits
     * (order_details references, FIFO batch history, and combination FKs
     * all depend on this id not changing under them).
     *
     * Returns [type => [value => variation_id]] so the caller can resolve
     * combination rows (which are posted by type/value name, not id, since
     * a brand new value has no id yet on the client).
     */
    private function _save_variation_values($product_id) {
        $json = $this->input->post('variations_json', TRUE);
        $posted = [];
        if (!empty($json)) {
            $decoded = json_decode($json, TRUE);
            if (is_array($decoded)) {
                $posted = $decoded;
            }
        }

        $postedKeyed = [];
        foreach ($posted as $v) {
            $type = trim((string) ($v['type'] ?? ''));
            $value = trim((string) ($v['value'] ?? ''));
            if ($type === '' || $value === '') {
                continue;
            }
            $status = $v['default_status'] ?? 'active';
            $postedKeyed[$type . '|' . $value] = [
                'type'                     => $type,
                'value'                    => $value,
                'default_price_adjustment' => (float) ($v['default_price_adjustment'] ?? 0),
                'default_status'           => in_array($status, ['active', 'inactive'], TRUE) ? $status : 'active',
                // Client-side row id — correlates this row to its optional
                // uploaded file in $_FILES['variation_images'][id], since a
                // brand new value has no variation_id yet at post time.
                'client_row_id'            => (string) ($v['client_row_id'] ?? ''),
            ];
        }

        $existing = $this->db->select('*')->from(PRODUCT_VARIATION_TABLE)
            ->where('product_id', $product_id)->get()->result_array();
        $existingKeyed = [];
        foreach ($existing as $row) {
            $existingKeyed[$row['variation_type'] . '|' . $row['variation_value']] = $row;
        }

        $now = date('Y-m-d H:i:s');
        $valueIdMap = [];

        foreach ($postedKeyed as $key => $row) {
            if (isset($existingKeyed[$key])) {
                $variation_id = (int) $existingKeyed[$key]['variation_id'];
                $this->db->where('variation_id', $variation_id)->update(PRODUCT_VARIATION_TABLE, [
                    'price_adjustment' => $row['default_price_adjustment'],
                    'status'           => $row['default_status'],
                    'updated_at'       => $now,
                ]);
            } else {
                $this->db->insert(PRODUCT_VARIATION_TABLE, [
                    'product_id'       => $product_id,
                    'variation_type'   => $row['type'],
                    'variation_value'  => $row['value'],
                    'price_adjustment' => $row['default_price_adjustment'],
                    'stock'            => 0,
                    'status'           => $row['default_status'],
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
                $variation_id = $this->db->insert_id();
            }
            $valueIdMap[$row['type']][$row['value']] = $variation_id;

            if ($row['client_row_id'] !== '') {
                $this->_maybe_upload_variation_image($variation_id, $row['client_row_id'], $existingKeyed[$key]['image_path'] ?? NULL);
            }
        }

        // Remove values no longer posted — but never silently destroy stock
        // that lives in a combination still using this value.
        foreach ($existingKeyed as $key => $row) {
            if (isset($postedKeyed[$key])) {
                continue;
            }
            $variation_id = (int) $row['variation_id'];
            if ($this->_variation_value_has_stock($variation_id)) {
                throw new Exception('Cannot remove "' . $row['variation_type'] . ': ' . $row['variation_value'] . '" — it still has stock in one or more variant combinations. Reduce that stock to 0 first.');
            }
            if (!empty($row['image_path']) && file_exists(FCPATH . $row['image_path'])) {
                @unlink(FCPATH . $row['image_path']);
            }
            // Cascades (FK) to delete any product_variants rows (and their
            // now-empty inventory_batches) still referencing this value.
            $this->db->where('variation_id', $variation_id)->delete(PRODUCT_VARIATION_TABLE);
        }

        return $valueIdMap;
    }

    /**
     * Uploads $_FILES['variation_images'][$client_row_id] (if present) as the
     * image for a single variation value. CI3's Upload library only reads
     * flat $_FILES entries, so an indexed file input is remapped to a
     * single-file shape before calling do_upload(). Bad uploads (wrong type,
     * too large) are skipped rather than failing the whole product save,
     * since the image is a nice-to-have, not a required field.
     */
    private function _maybe_upload_variation_image($variation_id, $client_row_id, $previous_image_path) {
        if (empty($_FILES['variation_images']['name'][$client_row_id])) {
            return;
        }

        $upload_path = FCPATH . 'uploads/variations/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, TRUE);
        }

        $_FILES['variation_image_single'] = [
            'name'     => $_FILES['variation_images']['name'][$client_row_id],
            'type'     => $_FILES['variation_images']['type'][$client_row_id],
            'tmp_name' => $_FILES['variation_images']['tmp_name'][$client_row_id],
            'error'    => $_FILES['variation_images']['error'][$client_row_id],
            'size'     => $_FILES['variation_images']['size'][$client_row_id],
        ];

        $this->load->library('upload', [
            'upload_path'   => $upload_path,
            'allowed_types' => 'jpg|jpeg|png|webp',
            'max_size'      => 5120,
            'encrypt_name'  => TRUE,
        ]);
        $this->upload->initialize([
            'upload_path'   => $upload_path,
            'allowed_types' => 'jpg|jpeg|png|webp',
            'max_size'      => 5120,
            'encrypt_name'  => TRUE,
        ]);

        if (!$this->upload->do_upload('variation_image_single')) {
            unset($_FILES['variation_image_single']);
            return;
        }

        $new_image_path = 'uploads/variations/' . $this->upload->data('file_name');
        $this->db->where('variation_id', $variation_id)->update(PRODUCT_VARIATION_TABLE, ['image_path' => $new_image_path]);

        if (!empty($previous_image_path) && file_exists(FCPATH . $previous_image_path)) {
            @unlink(FCPATH . $previous_image_path);
        }

        unset($_FILES['variation_image_single']);
    }

    /**
     * Uploads $_FILES['variant_image'][$row_key] (if present) as the primary
     * image for a generated variant combination (Section 4 of the product
     * edit wizard — the "Variant Combination" table's per-row "Choose File").
     * Mirrors _maybe_upload_variation_image()'s single-file remap trick.
     * $row_key is "existing_<variant_id>" or "new_<seq>" from the client —
     * a brand new combination has no variant_id yet at post time.
     */
    private function _maybe_upload_variant_image($variant_id, $row_key) {
        if (empty($_FILES['variant_image']['name'][$row_key])) {
            return;
        }

        $upload_path = FCPATH . 'uploads/variants/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, TRUE);
        }

        $_FILES['variant_image_single'] = [
            'name'     => $_FILES['variant_image']['name'][$row_key],
            'type'     => $_FILES['variant_image']['type'][$row_key],
            'tmp_name' => $_FILES['variant_image']['tmp_name'][$row_key],
            'error'    => $_FILES['variant_image']['error'][$row_key],
            'size'     => $_FILES['variant_image']['size'][$row_key],
        ];

        $this->load->library('upload', [
            'upload_path'   => $upload_path,
            'allowed_types' => 'jpg|jpeg|png|webp',
            'max_size'      => 5120,
            'encrypt_name'  => TRUE,
        ]);
        $this->upload->initialize([
            'upload_path'   => $upload_path,
            'allowed_types' => 'jpg|jpeg|png|webp',
            'max_size'      => 5120,
            'encrypt_name'  => TRUE,
        ]);

        if (!$this->upload->do_upload('variant_image_single')) {
            unset($_FILES['variant_image_single']);
            return;
        }

        $new_image_path = 'uploads/variants/' . $this->upload->data('file_name');
        $existing_image = $this->db->select('variant_image_id, image_path')->from(PRODUCT_VARIANT_IMAGES_TABLE)
            ->where('variant_id', $variant_id)->where('is_primary', 1)->get()->row_array();

        if ($existing_image) {
            $this->db->where('variant_image_id', $existing_image['variant_image_id'])
                ->update(PRODUCT_VARIANT_IMAGES_TABLE, ['image_path' => $new_image_path]);
            if (!empty($existing_image['image_path']) && file_exists(FCPATH . $existing_image['image_path'])) {
                @unlink(FCPATH . $existing_image['image_path']);
            }
        } else {
            $this->db->insert(PRODUCT_VARIANT_IMAGES_TABLE, [
                'variant_id'  => $variant_id,
                'image_path'  => $new_image_path,
                'is_primary'  => 1,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        unset($_FILES['variant_image_single']);
    }

    /**
     * True if any product_variants combination referencing this variation
     * value still has stock (cached stock column, kept in sync by
     * StockService::_syncVariantTotal()).
     */
    private function _variation_value_has_stock($variation_id) {
        $count = $this->db
            ->from(PRODUCT_VARIANTS_TABLE)
            ->group_start()
                ->where('variation_id_1', $variation_id)
                ->or_where('variation_id_2', $variation_id)
            ->group_end()
            ->where('stock >', 0)
            ->count_all_results();
        return $count > 0;
    }

    /**
     * Save the generated variant combinations (Section 3/4/5/6 of the
     * wizard) — each row is one actual purchasable/inventory item (SKU,
     * barcode, its own price adjustment/status, per-branch stock). Diffed
     * against what already exists by natural key
     * (product_id, variation_id_1, variation_id_2) so variant_id stays
     * stable across edits, and stock is adjusted by DELTA (not wiped and
     * re-added), so unchanged rows create zero new batches/movements and
     * FIFO history survives no-op edits.
     *
     * $valueIdMap: [type => [value => variation_id]] from
     * _save_variation_values(), used to resolve each posted combination's
     * type/value names into real variation_id_1/variation_id_2.
     */
    private function _save_variant_combinations($product_id, array $valueIdMap) {
        $json = $this->input->post('combinations_json', TRUE);
        $posted = [];
        if (!empty($json)) {
            $decoded = json_decode($json, TRUE);
            if (is_array($decoded)) {
                $posted = $decoded;
            }
        }

        $postedKeyed = [];
        foreach ($posted as $c) {
            $type1 = trim((string) ($c['type_1'] ?? ''));
            $value1 = trim((string) ($c['value_1'] ?? ''));
            $type2 = trim((string) ($c['type_2'] ?? ''));
            $value2 = trim((string) ($c['value_2'] ?? ''));

            if ($type1 === '' || $value1 === '' || !isset($valueIdMap[$type1][$value1])) {
                continue;
            }
            $variation_id_1 = (int) $valueIdMap[$type1][$value1];
            $variation_id_2 = ($type2 !== '' && $value2 !== '' && isset($valueIdMap[$type2][$value2]))
                ? (int) $valueIdMap[$type2][$value2]
                : NULL;

            $status = $c['status'] ?? 'active';
            $key = $variation_id_1 . '|' . ($variation_id_2 ?? '0');
            $postedKeyed[$key] = [
                'variation_id_1'   => $variation_id_1,
                'variation_id_2'   => $variation_id_2,
                'sku'              => trim((string) ($c['sku'] ?? '')) ?: NULL,
                'barcode'          => trim((string) ($c['barcode'] ?? '')) ?: NULL,
                'price_adjustment' => (float) ($c['price_adjustment'] ?? 0),
                'status'           => in_array($status, ['active', 'inactive'], TRUE) ? $status : 'active',
                'branch_stock'     => is_array($c['branch_stock'] ?? NULL) ? $c['branch_stock'] : [],
                // Client-side row key ("existing_<variant_id>" or "new_<seq>")
                // — correlates this row to its optional uploaded file in
                // $_FILES['variant_image'][key], since a brand new
                // combination has no variant_id yet at post time.
                'row_key'          => (string) ($c['row_key'] ?? ''),
            ];
        }

        $existing = $this->db->select('*')->from(PRODUCT_VARIANTS_TABLE)
            ->where('product_id', $product_id)->get()->result_array();
        $existingKeyed = [];
        foreach ($existing as $row) {
            $key = $row['variation_id_1'] . '|' . ($row['variation_id_2'] ?? '0');
            $existingKeyed[$key] = $row;
        }

        $now = date('Y-m-d H:i:s');

        foreach ($postedKeyed as $key => $row) {
            if (isset($existingKeyed[$key])) {
                $variant_id = (int) $existingKeyed[$key]['variant_id'];
                $this->db->where('variant_id', $variant_id)->update(PRODUCT_VARIANTS_TABLE, [
                    'sku'              => $row['sku'],
                    'barcode'          => $row['barcode'],
                    'price_adjustment' => $row['price_adjustment'],
                    'status'           => $row['status'],
                    'updated_at'       => $now,
                ]);
            } else {
                $this->db->insert(PRODUCT_VARIANTS_TABLE, [
                    'product_id'       => $product_id,
                    'variation_id_1'   => $row['variation_id_1'],
                    'variation_id_2'   => $row['variation_id_2'],
                    'sku'              => $row['sku'],
                    'barcode'          => $row['barcode'],
                    'price_adjustment' => $row['price_adjustment'],
                    'status'           => $row['status'],
                    'stock'            => 0,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
                $variant_id = $this->db->insert_id();
            }

            if ($row['row_key'] !== '') {
                $this->_maybe_upload_variant_image($variant_id, $row['row_key']);
            }

            // Stock deltas — preserve existing batches, only add/deduct the
            // difference, so unchanged rows create zero new movements.
            $existingBranchStock = StockService::getVariantBranchStock($variant_id);
            $allBranchIds = array_unique(array_merge(
                array_keys($existingBranchStock),
                array_map('intval', array_keys($row['branch_stock']))
            ));
            foreach ($allBranchIds as $branch_id) {
                $current = $existingBranchStock[$branch_id] ?? 0;
                $target = (int) ($row['branch_stock'][$branch_id] ?? $row['branch_stock'][(string) $branch_id] ?? 0);
                $delta = $target - $current;
                if ($delta !== 0) {
                    StockService::adjustStock($product_id, $branch_id, $delta, $this->user_id, 'Variant combination stock edit', NULL, $variant_id);
                }
            }
        }

        // Remove combinations no longer posted — block if they still hold stock.
        foreach ($existingKeyed as $key => $row) {
            if (isset($postedKeyed[$key])) {
                continue;
            }
            $variant_id = (int) $row['variant_id'];
            $stock = array_sum(StockService::getVariantBranchStock($variant_id));
            if ($stock > 0) {
                throw new Exception('Cannot remove a variant combination that still has ' . $stock . ' unit(s) of stock. Reduce its stock to 0 first.');
            }
            // Cascades (FK) to delete its inventory_batches (empty) and images.
            $this->db->where('variant_id', $variant_id)->delete(PRODUCT_VARIANTS_TABLE);
        }

        // product_tbl.stock is the denormalized total the list/shop/reports
        // pages read — StockService keeps it in sync as it goes, but
        // re-syncing here also correctly reflects a product that ends up
        // with no stock anywhere left.
        $new_stock = StockService::getAvailableStock($product_id);
        $this->db->where('product_id', $product_id)
            ->update(PRODUCT_TABLE, ['stock' => $new_stock]);
    }
}
?>
