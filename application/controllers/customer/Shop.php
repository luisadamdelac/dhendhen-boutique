<?php
class Shop extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper(['url', 'form', 'session']);
        $this->load->database();
        $this->load->model('Product_model');

        // Shop browsing is public (no login required), but if a customer
        // happens to be logged in, this opens their session so the shared
        // header (customer/layouts/header.php) can tell — otherwise $_SESSION
        // is never populated here and a logged-in customer looks logged-out
        // on every shop/product page.
        load_scoped_session('customer');
    }

    /** SQL fragment: cheapest active, published listing price for a product. */
    private function _buy_price_subquery() {
        return '(SELECT MIN(rp.commission_price) FROM ' . RESELLER_PRODUCTS_TABLE . ' rp '
            . 'JOIN ' . RESELLER_TABLE . ' r ON r.reseller_id = rp.reseller_id '
            . 'WHERE rp.product_id = p.product_id AND rp.is_published = 1 AND r.status = \'active\') as buy_price';
    }

    public function index() {
        $data['page_title'] = 'Shop';
        $data['searchQuery'] = $this->input->get('search', true);
        $data['currentCategory'] = $this->input->get('category', true);
        $data['categories'] = $this->db->select('*')->from(CATEGORY_TABLE)->order_by('category_name', 'ASC')->get()->result_array();

        $limit = 20;
        $data['currentPage'] = max(1, (int) $this->input->get('page', true));
        $offset = ($data['currentPage'] - 1) * $limit;

        $filters = [
            'status'      => 'available',
            'search'      => $data['searchQuery'],
            'category_id' => (!empty($data['currentCategory']) && $data['currentCategory'] !== 'all') ? $data['currentCategory'] : NULL,
        ];

        $total = $this->Product_model->count_filtered($filters);

        $products = $this->Product_model->base_query($filters)
            ->select($this->_buy_price_subquery(), FALSE)
            ->order_by('p.product_id', 'DESC')->limit($limit, $offset)
            ->get()->result_array();

        $product_ids = array_column($products, 'product_id');
        $variation_product_ids = [];
        if (!empty($product_ids)) {
            $variation_product_ids = $this->db->distinct()->select('product_id')
                ->from(PRODUCT_VARIATION_TABLE)
                ->where_in('product_id', $product_ids)
                ->where('status', 'active')
                ->get()->result_array();
            $variation_product_ids = array_column($variation_product_ids, 'product_id');
        }

        foreach ($products as &$p) {
            $p['purchasable'] = $p['buy_price'] !== NULL;
            if ($p['purchasable']) {
                $p['price'] = (float) $p['buy_price'];
            }
            $p['has_variations'] = in_array($p['product_id'], $variation_product_ids);
        }
        unset($p);

        $data['products'] = $products;
        $data['totalPages'] = (int) ceil($total / $limit);

        $this->load->view('customer/layouts/header', $data);
        $this->load->view('customer/shop/index', $data);
        $this->load->view('customer/layouts/footer', $data);
    }

    /** JSON: active variations for a product, used by the shop grid's Add to Cart modal. */
    public function variations($id = null) {
        $id = (int) $id;
        if (!$id) {
            echo json_encode(['success' => FALSE]);
            return;
        }

        $variations = $this->db->select('*')->from(PRODUCT_VARIATION_TABLE)
            ->where('product_id', $id)->where('status', 'active')
            ->order_by('variation_type', 'ASC')->order_by('variation_id', 'ASC')
            ->get()->result_array();

        echo json_encode(['success' => TRUE, 'variations' => $variations]);
    }

    public function product($id = null) {
        if (empty($id)) {
            show_404();
        }
        $data['page_title'] = 'Product Details';
        $data['product'] = $this->Product_model->get_by_id($id, true);
        if (empty($data['product'])) {
            show_404();
        }

        // Every reseller currently selling this product, cheapest first — lets
        // the customer pick a preferred mini-shop to buy through.
        $data['sellers'] = $this->db->select('rp.reseller_product_id, rp.reseller_id, rp.commission_price, r.business_name, r.first_name, r.last_name')
            ->from(RESELLER_PRODUCTS_TABLE . ' rp')
            ->join(RESELLER_TABLE . ' r', 'r.reseller_id = rp.reseller_id')
            ->where('rp.product_id', $id)
            ->where('rp.is_published', 1)
            ->where('r.status', 'active')
            ->order_by('rp.commission_price', 'ASC')
            ->get()->result_array();

        $requested_reseller = (int) $this->input->get('reseller', TRUE);
        $selected = NULL;
        if ($requested_reseller) {
            foreach ($data['sellers'] as $seller) {
                if ((int) $seller['reseller_id'] === $requested_reseller) {
                    $selected = $seller;
                    break;
                }
            }
        }
        if (!$selected && !empty($data['sellers'])) {
            $selected = $data['sellers'][0];
        }

        $data['selected_reseller_id'] = $selected['reseller_id'] ?? NULL;
        $data['purchasable'] = $selected !== NULL;
        if ($selected) {
            $data['product']['price'] = (float) $selected['commission_price'];
        }

        $data['variations'] = $this->db->select('*')->from(PRODUCT_VARIATION_TABLE)
            ->where('product_id', $id)->where('status', 'active')
            ->order_by('variation_type', 'ASC')->order_by('variation_id', 'ASC')
            ->get()->result_array();

        $data['reviews'] = $this->db->select('pr.*, pr.comment as review, c.first_name, c.last_name')
            ->from('product_reviews pr')
            ->join('reseller_products rp', 'pr.reseller_product_id = rp.reseller_product_id')
            ->join(CUSTOMER_TABLE . ' c', 'pr.customer_id = c.customer_id')
            ->where('rp.product_id', $id)
            ->where('pr.status', 'approved')
            ->order_by('pr.created_at', 'DESC')
            ->get()->result_array();

        foreach ($data['reviews'] as &$review) {
            $review['customer_name'] = trim($review['first_name'] . ' ' . $review['last_name']);
        }
        unset($review);

        $data['product']['review_count'] = count($data['reviews']);

        $data['relatedProducts'] = $this->db->select('p.*, pi.image_path as product_image')
            ->from(PRODUCT_TABLE . ' p')
            ->join(PRODUCT_IMAGE_TABLE . ' pi', 'pi.product_id = p.product_id AND pi.is_primary = 1', 'left')
            ->where('p.category_id', $data['product']['category_id'])
            ->where('p.product_id !=', $id)
            ->where('p.status', 'available')
            ->limit(4)
            ->get()->result_array();

        $this->load->view('customer/layouts/header', $data);
        $this->load->view('customer/shop/product', $data);
        $this->load->view('customer/layouts/footer', $data);
    }

    /**
     * A reseller's mini-shop: only the products they've published
     * (reseller_products), priced at their own asking price, so a sale made
     * here is unambiguously attributed to them.
     */
    public function reseller($reseller_id = null) {
        if (empty($reseller_id)) {
            show_404();
        }

        $reseller = $this->db->where('reseller_id', $reseller_id)->where('status', 'active')->get(RESELLER_TABLE)->row_array();
        if (!$reseller) {
            show_404();
        }

        $data['page_title'] = (!empty($reseller['business_name']) ? $reseller['business_name'] : trim($reseller['first_name'] . ' ' . $reseller['last_name'])) . "'s Shop";
        $data['reseller'] = $reseller;
        $data['searchQuery'] = $this->input->get('search', true);
        $data['currentCategory'] = $this->input->get('category', true);
        $data['categories'] = $this->db->select('*')->from(CATEGORY_TABLE)->order_by('category_name', 'ASC')->get()->result_array();

        $limit = 20;
        $data['currentPage'] = max(1, (int) $this->input->get('page', true));
        $offset = ($data['currentPage'] - 1) * $limit;

        $apply_filters = function () use ($data, $reseller_id) {
            $this->db->from(RESELLER_PRODUCTS_TABLE . ' rp');
            $this->db->join(PRODUCT_TABLE . ' p', 'p.product_id = rp.product_id');
            $this->db->join(CATEGORY_TABLE . ' c', 'p.category_id = c.category_id', 'left');
            $this->db->join(PRODUCT_IMAGE_TABLE . ' pi', 'pi.product_id = p.product_id AND pi.is_primary = 1', 'left');
            $this->db->where('p.status', 'available');
            $this->db->where('rp.reseller_id', $reseller_id);
            $this->db->where('rp.is_published', 1);
            if (!empty($data['searchQuery'])) {
                $this->db->group_start();
                $this->db->like('p.product_name', $data['searchQuery']);
                $this->db->or_like('p.description', $data['searchQuery']);
                $this->db->group_end();
            }
            if (!empty($data['currentCategory']) && $data['currentCategory'] !== 'all') {
                $this->db->where('p.category_id', $data['currentCategory']);
            }
        };

        $apply_filters();
        $total = $this->db->count_all_results();

        $this->db->select('p.*, c.category_name, rp.commission_price, pi.image_path as product_image');
        $apply_filters();
        $this->db->order_by('p.product_id', 'DESC')->limit($limit, $offset);
        $products = $this->db->get()->result_array();

        foreach ($products as &$p) {
            $p['price'] = (float) $p['commission_price'];
            $p['purchasable'] = TRUE;
        }
        unset($p);

        $data['products'] = $products;
        $data['totalPages'] = (int) ceil($total / $limit);
        $data['isResellerShop'] = true;
        $data['shopResellerId'] = (int) $reseller_id;

        $this->load->view('customer/layouts/header', $data);
        $this->load->view('customer/shop/index', $data);
        $this->load->view('customer/layouts/footer', $data);
    }
}
