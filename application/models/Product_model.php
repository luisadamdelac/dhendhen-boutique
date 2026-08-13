<?php
/**
 * Product Model
 * Handles product management from product_tbl
 */

class Product_model extends CI_Model {
    
    protected $table = 'product_tbl';
    protected $categories_table = 'product_category';
    protected $images_table = 'product_image';

    public function __construct() {
        parent::__construct();
        $this->load->config('dropsell');
    }

    /**
     * Get product by product_id, optionally joined with its category name.
     */
    public function get_by_id($product_id, $with_category = false) {
        if ($with_category) {
            $product = $this->db->select('p.*, c.category_name, pi.image_path as product_image')
                ->from($this->table . ' p')
                ->join($this->categories_table . ' c', 'p.category_id = c.category_id', 'left')
                ->join($this->images_table . ' pi', 'pi.product_id = p.product_id AND pi.is_primary = 1', 'left')
                ->where('p.product_id', $product_id)
                ->get()->row_array();

            return $product;
        }

        return $this->db->where('product_id', $product_id)
                         ->get($this->table)->row_array();
    }

    /**
     * Apply shared catalog filters (search/category/status/branch/exclude)
     * to whatever query is currently being built. Used by both base_query()
     * and count_filtered() so the filter logic only exists once.
     */
    private function _apply_filters(array $filters = []) {
        if (!empty($filters['search'])) {
            $this->db->group_start()
                ->like('p.product_name', $filters['search'], 'both')
                ->or_like('p.sku', $filters['search'], 'both')
                ->or_like('p.description', $filters['search'], 'both')
                ->group_end();
        }
        if (!empty($filters['category_id'])) {
            $this->db->where('p.category_id', $filters['category_id']);
        }
        if (!empty($filters['subcategory_id'])) {
            $this->db->where('p.subcategory_id', $filters['subcategory_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('p.status', $filters['status']);
        }
        if (!empty($filters['branch_id'])) {
            $this->db->where(
                'p.product_id IN (SELECT product_id FROM ' . INVENTORY_BATCH_TABLE .
                ' WHERE branch_id = ' . (int) $filters['branch_id'] . " AND status = 'active' AND remaining_quantity > 0)",
                NULL, FALSE
            );
        }
        if (!empty($filters['exclude_ids'])) {
            $this->db->where_not_in('p.product_id', $filters['exclude_ids']);
        }

        // Archived products are hidden from every normal listing (catalog,
        // shop, admin inventory) unless explicitly asked for.
        $this->db->where('p.is_archived', !empty($filters['archived_only']) ? 1 : 0);
    }

    /**
     * Shared "product catalog" query (product + category), left unexecuted
     * so callers can chain further joins/selects before calling ->get().
     */
    public function base_query(array $filters = []) {
        $this->db->select('p.*, c.category_name, pi.image_path as product_image')
            ->from($this->table . ' p')
            ->join($this->categories_table . ' c', 'p.category_id = c.category_id', 'left')
            ->join($this->images_table . ' pi', 'pi.product_id = p.product_id AND pi.is_primary = 1', 'left');

        $this->_apply_filters($filters);

        return $this->db;
    }

    /**
     * Count of products matching the same filters as base_query().
     */
    public function count_filtered(array $filters = []) {
        $this->db->from($this->table . ' p');
        $this->_apply_filters($filters);
        return $this->db->count_all_results();
    }

    /**
     * Get all products with pagination
     */
    public function get_all($limit = NULL, $offset = NULL) {
        $query = $this->db->select('p.*, c.category_name')
                         ->from($this->table . ' p')
                         ->join($this->categories_table . ' c', 'p.category_id = c.category_id', 'left');
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        $query->order_by('p.date_added', 'DESC');
        return $query->get()->result_array();
    }

    /**
     * Get products by category
     */
    public function get_by_category($category_id, $limit = NULL, $offset = NULL) {
        $query = $this->db->where('category_id', $category_id)
                         ->where('status', 'available');
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        $query->order_by('product_name', 'ASC');
        return $query->get($this->table)->result_array();
    }

    /**
     * Get low stock products, optionally paginated and restricted to
     * status = 'available' (Admin's low-stock page shows every status).
     */
    public function get_low_stock($threshold = 20, $limit = NULL, $offset = NULL, $only_available = true) {
        $query = $this->db->select('p.*, c.category_name')
                         ->from($this->table . ' p')
                         ->join($this->categories_table . ' c', 'p.category_id = c.category_id', 'left')
                         ->where('p.stock <=', $threshold)
                         ->where('p.is_archived', 0);

        if ($only_available) {
            $query->where('p.status', 'available');
        }

        $query->order_by('p.stock', 'ASC');

        if ($limit) {
            $query->limit($limit, $offset);
        }

        return $query->get()->result_array();
    }

    /**
     * Count of low-stock products, mirroring get_low_stock()'s filters.
     */
    public function count_low_stock($threshold = 20, $only_available = true) {
        $this->db->from($this->table)->where('stock <=', $threshold)->where('is_archived', 0);
        if ($only_available) {
            $this->db->where('status', 'available');
        }
        return $this->db->count_all_results();
    }

    /**
     * Products already expired or expiring within $days — expiry_date is
     * optional per product, so NULL (never set) is excluded rather than
     * treated as "expiring now".
     */
    public function get_expiring($days = 30, $limit = NULL, $offset = NULL) {
        $query = $this->db->select('p.*, c.category_name')
                         ->from($this->table . ' p')
                         ->join($this->categories_table . ' c', 'p.category_id = c.category_id', 'left')
                         ->where('p.expiry_date IS NOT NULL', NULL, FALSE)
                         ->where('p.expiry_date <=', date('Y-m-d', strtotime('+' . (int) $days . ' days')))
                         ->where('p.is_archived', 0)
                         ->order_by('p.expiry_date', 'ASC');

        if ($limit) {
            $query->limit($limit, $offset);
        }

        return $query->get()->result_array();
    }

    public function count_expiring($days = 30) {
        return $this->db->from($this->table)
            ->where('expiry_date IS NOT NULL', NULL, FALSE)
            ->where('expiry_date <=', date('Y-m-d', strtotime('+' . (int) $days . ' days')))
            ->where('is_archived', 0)
            ->count_all_results();
    }

    /**
     * Get out of stock products
     */
    public function get_out_of_stock() {
        $query = $this->db->select('p.*, c.category_name')
                         ->from($this->table . ' p')
                         ->join($this->categories_table . ' c', 'p.category_id = c.category_id', 'left')
                         ->where('p.stock', 0)
                         ->order_by('p.product_name', 'ASC')
                         ->get();
        
        return $query->result_array();
    }

    /**
     * Count all products
     */
    public function count_all() {
        return $this->db->count_all($this->table);
    }

    /**
     * Count by status
     */
    public function count_by_status($status) {
        $this->db->where('status', $status);
        return $this->db->count_all_results($this->table);
    }

    /**
     * Create new product. `stock` is intentionally not settable here —
     * it's a denormalized total maintained exclusively by StockService
     * from the branch batch ledger; pass initial stock through
     * StockService::addBatch() per branch after creating the row.
     */
    public function create($data) {
        $now = date('Y-m-d H:i:s');
        $product_data = array(
            'sku'                  => $data['sku'],
            'product_name'         => $data['product_name'],
            'brand'                => $data['brand'] ?? NULL,
            'description'          => $data['description'] ?? NULL,
            'category_id'          => $data['category_id'] ?? NULL,
            'subcategory_id'       => $data['subcategory_id'] ?? NULL,
            'price'                => (float) $data['price'],
            'cost_price'           => isset($data['cost_price']) ? (float) $data['cost_price'] : 0,
            'reseller_commission'  => isset($data['reseller_commission']) ? (float) $data['reseller_commission'] : 0,
            'stock'                => 0,
            'min_stock_alert'      => isset($data['min_stock_alert']) ? (int) $data['min_stock_alert'] : NULL,
            'expiry_date'          => $data['expiry_date'] ?? NULL,
            'status'               => $data['status'] ?? 'available',
            'tags'                 => $data['tags'] ?? NULL,
            'added_by'             => $data['added_by'] ?? NULL,
            'created_by_role'      => $data['created_by_role'] ?? NULL,
            'date_added'           => $now,
            'updated_at'           => $now,
        );

        if ($this->db->insert($this->table, $product_data)) {
            return $this->db->insert_id();
        }

        return FALSE;
    }

    /**
     * Update product fields. Never touches `stock` — that only ever
     * changes through StockService's branch batch ledger.
     */
    public function update($product_id, $data) {
        // Only touch a column when the caller actually passed it — that's
        // what protects fields like `sku` (never included by the Edit
        // controller) from being wiped. Previously this instead stripped
        // any NULL *value* out of the update, which silently protected
        // untouched fields but also meant nullable fields the caller
        // explicitly clears (e.g. tags, expiry_date, brand) could never
        // actually be cleared — the UPDATE just skipped that column and
        // the old value stuck around forever.
        $update_data = ['updated_at' => date('Y-m-d H:i:s')];

        foreach (['sku', 'product_name', 'brand', 'description', 'category_id', 'subcategory_id', 'expiry_date', 'status', 'tags'] as $field) {
            if (array_key_exists($field, $data)) {
                $update_data[$field] = $data[$field];
            }
        }

        foreach (['price' => 'float', 'cost_price' => 'float', 'reseller_commission' => 'float', 'min_stock_alert' => 'int'] as $field => $cast) {
            if (isset($data[$field])) {
                $update_data[$field] = $cast === 'float' ? (float) $data[$field] : (int) $data[$field];
            }
        }

        $this->db->where('product_id', $product_id);
        return $this->db->update($this->table, $update_data);
    }

    /**
     * Search products
     */
    public function search($keyword, $limit = NULL, $offset = NULL) {
        $query = $this->db->select('p.*, c.category_name')
                         ->from($this->table . ' p')
                         ->join($this->categories_table . ' c', 'p.category_id = c.category_id', 'left')
                         ->group_start()
                         ->like('p.sku', $keyword)
                         ->or_like('p.product_name', $keyword)
                         ->or_like('p.description', $keyword)
                         ->group_end();
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        $query->order_by('p.product_name', 'ASC');
        return $query->get()->result_array();
    }

    /**
     * Get product with images
     */
    public function get_with_images($product_id) {
        $product = $this->get_by_id($product_id);
        
        if (!$product) {
            return FALSE;
        }

        // Get images
        $images = $this->db->where('product_id', $product_id)
                          ->order_by('display_order', 'ASC')
                          ->get($this->images_table)
                          ->result_array();
        
        $product['images'] = $images;
        
        return $product;
    }

    /**
     * Get all categories
     */
    public function get_categories() {
        return $this->db->order_by('category_name', 'ASC')
                       ->get($this->categories_table)
                       ->result_array();
    }

    /**
     * Create category
     */
    public function create_category($category_name) {
        $data = array('category_name' => $category_name);
        return $this->db->insert($this->categories_table, $data);
    }

    /**
     * Record a customer's interest in a product no reseller has published
     * yet. Silently no-ops on a repeat click (unique product_id+customer_id)
     * instead of erroring — the customer is already on the list either way.
     */
    public function add_preorder($product_id, $customer_id) {
        if ($this->has_preordered($product_id, $customer_id)) {
            return TRUE;
        }

        return (bool) $this->db->insert(PRODUCT_PREORDERS_TABLE, [
            'product_id'  => (int) $product_id,
            'customer_id' => (int) $customer_id,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function has_preordered($product_id, $customer_id) {
        return (bool) $this->db->where('product_id', (int) $product_id)
            ->where('customer_id', (int) $customer_id)
            ->count_all_results(PRODUCT_PREORDERS_TABLE);
    }

    /**
     * Which of these product IDs has this customer already pre-ordered —
     * one query for a whole shop-grid page instead of one per card.
     */
    public function preordered_product_ids($customer_id, array $product_ids) {
        if (empty($product_ids)) {
            return [];
        }

        $rows = $this->db->select('product_id')
            ->where('customer_id', (int) $customer_id)
            ->where_in('product_id', $product_ids)
            ->get(PRODUCT_PREORDERS_TABLE)->result_array();

        return array_map('intval', array_column($rows, 'product_id'));
    }

    /**
     * Pre-order counts per product, for however many product IDs the caller
     * is about to render (a reseller's catalog carousel, their published
     * listings, or a customer's own preorder-state check) — one grouped
     * query instead of one per product.
     */
    public function preorder_counts(array $product_ids) {
        if (empty($product_ids)) {
            return [];
        }

        $rows = $this->db->select('product_id, COUNT(*) as cnt')
            ->where_in('product_id', $product_ids)
            ->group_by('product_id')
            ->get(PRODUCT_PREORDERS_TABLE)->result_array();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['product_id']] = (int) $row['cnt'];
        }
        return $counts;
    }

    /**
     * Pre-orders for one product that haven't yet been announced to a
     * reseller — called right after a reseller publishes that product, so
     * the same waiting customers are never announced twice on a republish.
     */
    public function unnotified_preorders($product_id) {
        return $this->db->select('pp.*, c.first_name, c.last_name')
            ->from(PRODUCT_PREORDERS_TABLE . ' pp')
            ->join(CUSTOMER_TABLE . ' c', 'c.customer_id = pp.customer_id')
            ->where('pp.product_id', (int) $product_id)
            ->where('pp.reseller_notified_at IS NULL', NULL, FALSE)
            ->order_by('pp.created_at', 'ASC')
            ->get()->result_array();
    }

    public function mark_preorders_notified($product_id) {
        return $this->db->where('product_id', (int) $product_id)
            ->where('reseller_notified_at IS NULL', NULL, FALSE)
            ->update(PRODUCT_PREORDERS_TABLE, ['reseller_notified_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Every pre-order for a product, newest first — the "who's waiting"
     * list shown under an already-published listing in Inventory.
     */
    public function preorders_for_product($product_id) {
        return $this->db->select('pp.*, c.first_name, c.last_name')
            ->from(PRODUCT_PREORDERS_TABLE . ' pp')
            ->join(CUSTOMER_TABLE . ' c', 'c.customer_id = pp.customer_id')
            ->where('pp.product_id', (int) $product_id)
            ->order_by('pp.created_at', 'DESC')
            ->get()->result_array();
    }
}

/* End of file Product_model.php */
/* Location: ./application/models/Product_model.php */
