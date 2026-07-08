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
        foreach ($data['products'] as &$p) {
            $p['branch_stock'] = StockService::getBranchStock($p['product_id']);
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

        // Per-branch stock quantities: stock_by_branch[branch_id] = qty
        $branch_stock_input = (array) $this->input->post('stock_by_branch');
        $branch_stock = [];
        foreach ($branch_stock_input as $b_id => $qty) {
            $qty = (int) $qty;
            if ($qty > 0) {
                $branch_stock[(int) $b_id] = $qty;
            }
        }
        $stock = array_sum($branch_stock);

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

        // Initial stock batch per branch (also keeps product_tbl.stock in sync as the total)
        foreach ($branch_stock as $b_id => $qty) {
            StockService::addBatch($product_id, $b_id, $qty, $cost_price, $this->user_id);
        }

        $this->_save_variations($product_id);

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
            $this->_notify_low_stock($product_id, $product_name, $stock, count($branch_stock) . ' branch(es)');
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

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/product/low_stock', $data);
        $this->load->view('admin/layout/footer', $data);
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

        // Apply any per-branch stock adjustments (stock_by_branch[branch_id] = new total for that branch)
        $posted_branch_stock = (array) $this->input->post('stock_by_branch');
        if (!empty($posted_branch_stock)) {
            require_once APPPATH . 'services/StockService.php';
            $current = StockService::getBranchStock($product_id);
            foreach ($posted_branch_stock as $b_id => $new_qty) {
                $b_id = (int) $b_id;
                $new_qty = (int) $new_qty;
                $delta = $new_qty - ($current[$b_id] ?? 0);
                if ($delta !== 0) {
                    StockService::adjustStock($product_id, $b_id, $delta, $this->user_id, 'Admin stock edit');
                }
            }
        }

        $this->_save_variations($product_id);

        $this->Activity_log_model->log_activity(get_user_id(), 'admin', 'update_product', 'Updated product: ' . $product_data['product_name'], get_client_ip());
        $this->session->set_flashdata('success', 'Product updated successfully');
        redirect('admin/product');
    }

    /**
     * Replace a product's variations (Color, Size, Shade, etc.) from the
     * wizard's variations_json hidden field. Used by both add and edit —
     * simplest correct approach is to wipe and re-insert rather than diff
     * against what's already there.
     */
    private function _save_variations($product_id) {
        $json = $this->input->post('variations_json', TRUE);

        $this->db->where('product_id', $product_id)->delete(PRODUCT_VARIATION_TABLE);

        $variations = [];
        if (!empty($json)) {
            $decoded = json_decode($json, TRUE);
            if (is_array($decoded)) {
                $variations = $decoded;
            }
        }

        $now = date('Y-m-d H:i:s');
        $variation_stock_total = 0;
        $saved_any = false;
        foreach ($variations as $v) {
            $type = trim((string) ($v['type'] ?? ''));
            $value = trim((string) ($v['value'] ?? ''));
            if ($type === '' || $value === '') {
                continue;
            }
            $stock = max(0, (int) ($v['stock'] ?? 0));
            $this->db->insert(PRODUCT_VARIATION_TABLE, [
                'product_id'       => $product_id,
                'variation_type'   => $type,
                'variation_value'  => $value,
                'price_adjustment' => (float) ($v['price_adjustment'] ?? 0),
                'stock'            => $stock,
                'status'           => 'active',
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
            $variation_stock_total += $stock;
            $saved_any = true;
        }

        // Stock is tracked per-variation (not per-branch) once a product has
        // variations, so product_tbl.stock — which the list/shop/reports pages
        // all read as the denormalized total — must be kept in sync with the
        // variation stock instead of the (unused) branch batches. Reverting a
        // product back to no variations re-syncs it from the branch total.
        if ($saved_any) {
            $this->db->where('product_id', $product_id)->update(PRODUCT_TABLE, ['stock' => $variation_stock_total]);
        } else {
            $this->db->where('product_id', $product_id)
                ->update(PRODUCT_TABLE, ['stock' => StockService::getAvailableStock($product_id)]);
        }
    }
}
?>
