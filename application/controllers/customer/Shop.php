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
        $ratings_by_product = [];
        if (!empty($product_ids)) {
            $variation_product_ids = $this->db->distinct()->select('product_id')
                ->from(PRODUCT_VARIATION_TABLE)
                ->where_in('product_id', $product_ids)
                ->where('status', 'active')
                ->get()->result_array();
            $variation_product_ids = array_column($variation_product_ids, 'product_id');

            // Approved review average/count per product — shown as stars on
            // the product card once a customer's rating has been moderated in.
            $rating_rows = $this->db->select('rp.product_id, AVG(pr.rating) as avg_rating, COUNT(pr.review_id) as review_count')
                ->from('product_reviews pr')
                ->join(RESELLER_PRODUCTS_TABLE . ' rp', 'rp.reseller_product_id = pr.reseller_product_id')
                ->where_in('rp.product_id', $product_ids)
                ->where('pr.status', 'approved')
                ->group_by('rp.product_id')
                ->get()->result_array();
            foreach ($rating_rows as $row) {
                $ratings_by_product[$row['product_id']] = $row;
            }
        }

        foreach ($products as &$p) {
            $p['purchasable'] = $p['buy_price'] !== NULL;
            if ($p['purchasable']) {
                $p['price'] = (float) $p['buy_price'];
            }
            $p['has_variations'] = in_array($p['product_id'], $variation_product_ids);
            $p['avg_rating'] = isset($ratings_by_product[$p['product_id']]) ? (float) $ratings_by_product[$p['product_id']]['avg_rating'] : NULL;
            $p['review_count'] = isset($ratings_by_product[$p['product_id']]) ? (int) $ratings_by_product[$p['product_id']]['review_count'] : 0;
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

        foreach ($variations as &$v) {
            $v['image_url'] = !empty($v['image_path']) ? base_url($v['image_path']) : NULL;
        }
        unset($v);

        echo json_encode(['success' => TRUE, 'variations' => $variations, 'combinations' => $this->_combinations_for_product($id)]);
    }

    /**
     * Generated variant combinations (product_variants) for a product, each
     * with its own SKU/price adjustment/status, real total stock across
     * branches, and primary image (if any) — used to resolve the exact
     * variant once a customer has picked a value for every variation type
     * (e.g. Shade + Finish), and to filter which values are still pickable
     * together.
     */
    private function _combinations_for_product($product_id) {
        require_once APPPATH . 'services/StockService.php';

        $combos = $this->db->select('v.*')
            ->from(PRODUCT_VARIANTS_TABLE . ' v')
            ->where('v.product_id', $product_id)
            ->where('v.status', 'active')
            ->get()->result_array();

        if (empty($combos)) {
            return [];
        }

        // Every axis value (type/value/image) referenced by any combo above —
        // the first two axes via variation_id_1/variation_id_2, any 3rd+ via
        // the extra-values table — looked up once instead of per-row.
        $variantIds = array_column($combos, 'variant_id');
        $variationIds = array_unique(array_filter(array_merge(
            array_column($combos, 'variation_id_1'),
            array_column($combos, 'variation_id_2')
        )));
        $extraByVariant = [];
        $extraRows = $this->db->select('variant_id, variation_id')
            ->from(PRODUCT_VARIANT_EXTRA_VALUES_TABLE)
            ->where_in('variant_id', $variantIds)
            ->order_by('variant_id, axis_order', 'ASC')
            ->get()->result_array();
        foreach ($extraRows as $er) {
            $extraByVariant[(int) $er['variant_id']][] = (int) $er['variation_id'];
            $variationIds[] = (int) $er['variation_id'];
        }
        $variationIds = array_unique($variationIds);

        $valueById = [];
        if (!empty($variationIds)) {
            $valueRows = $this->db->select('variation_id, variation_type, variation_value, image_path')
                ->from(PRODUCT_VARIATION_TABLE)
                ->where_in('variation_id', $variationIds)
                ->get()->result_array();
            foreach ($valueRows as $vr) {
                $valueById[(int) $vr['variation_id']] = $vr;
            }
        }

        foreach ($combos as &$c) {
            $variant_id = (int) $c['variant_id'];
            $c['variant_id'] = $variant_id;
            $c['price_adjustment'] = (float) $c['price_adjustment'];
            // Branch stock is tracked in individual pieces, but a value like
            // "Package Type: 1 Set (10 pcs)" is SOLD in units of 10 pieces —
            // show/limit by how many whole units are actually purchasable.
            $piecesPerUnit = StockService::getVariantPiecesPerUnit($variant_id);
            $c['total_stock'] = intdiv(array_sum(StockService::getVariantBranchStock($variant_id)), $piecesPerUnit);

            $axisIds = [(int) $c['variation_id_1']];
            if (!empty($c['variation_id_2'])) {
                $axisIds[] = (int) $c['variation_id_2'];
            }
            foreach ($extraByVariant[$variant_id] ?? [] as $vid) {
                $axisIds[] = $vid;
            }

            $c['axes'] = [];
            $axisImage = NULL;
            foreach ($axisIds as $vid) {
                $v = $valueById[$vid] ?? NULL;
                if (!$v) {
                    continue;
                }
                $c['axes'][] = ['type' => $v['variation_type'], 'value' => $v['variation_value']];
                // No dedicated variant photo — fall back to whichever axis
                // value has its own image (e.g. "Volume: 50ml" has a bottle
                // shot even though this combination is "50ml / Original Pink").
                if ($axisImage === NULL && !empty($v['image_path'])) {
                    $axisImage = $v['image_path'];
                }
            }

            $image = $this->db->select('image_path')->from(PRODUCT_VARIANT_IMAGES_TABLE)
                ->where('variant_id', $variant_id)->where('is_primary', 1)->get()->row_array();

            if ($image) {
                $c['image_url'] = base_url($image['image_path']);
            } elseif ($axisImage) {
                $c['image_url'] = base_url($axisImage);
            } else {
                $c['image_url'] = NULL;
            }
        }
        unset($c);

        return $combos;
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
        $data['selected_reseller'] = $selected;
        $data['purchasable'] = $selected !== NULL;
        if ($selected) {
            $data['product']['price'] = (float) $selected['commission_price'];
        }

        // Same product, other shops — other resellers selling this exact
        // product at their own price, for the "You Might Also Like" cross-sell
        // shown at the bottom of the product page.
        $data['otherShopListings'] = array_values(array_filter($data['sellers'], function ($seller) use ($data) {
            return (int) $seller['reseller_id'] !== (int) $data['selected_reseller_id'];
        }));

        // Full image gallery for this product (product_image supports many
        // rows per product_id) — primary image first, then display_order.
        $data['galleryImages'] = $this->db->select('image_path, is_primary')
            ->from(PRODUCT_IMAGE_TABLE)
            ->where('product_id', $id)
            ->order_by('is_primary', 'DESC')
            ->order_by('display_order', 'ASC')
            ->get()->result_array();

        // Shop Information panel — real data about the currently selected
        // reseller's mini-shop (not the product's own category/global stats).
        $data['shopInfo'] = NULL;
        if ($selected) {
            $reseller_row = $this->db->select('profile_image, business_name, first_name, last_name, created_at')
                ->from(RESELLER_TABLE)
                ->where('reseller_id', $selected['reseller_id'])
                ->get()->row_array();

            $total_products = (int) $this->db
                ->where('reseller_id', $selected['reseller_id'])
                ->where('is_published', 1)
                ->count_all_results(RESELLER_PRODUCTS_TABLE);

            $rating_row = $this->db->select('AVG(pr.rating) as avg_rating, COUNT(pr.review_id) as review_count')
                ->from('product_reviews pr')
                ->join(RESELLER_PRODUCTS_TABLE . ' rp', 'rp.reseller_product_id = pr.reseller_product_id')
                ->where('rp.reseller_id', $selected['reseller_id'])
                ->where('pr.status', 'approved')
                ->get()->row_array();

            $data['shopInfo'] = [
                'reseller_id'    => (int) $selected['reseller_id'],
                'logo'           => $reseller_row['profile_image'] ?? NULL,
                'name'           => !empty($reseller_row['business_name']) ? $reseller_row['business_name'] : trim(($reseller_row['first_name'] ?? '') . ' ' . ($reseller_row['last_name'] ?? '')),
                'joined_at'      => $reseller_row['created_at'] ?? NULL,
                'total_products' => $total_products,
                'avg_rating'     => $rating_row['avg_rating'] !== NULL ? (float) $rating_row['avg_rating'] : NULL,
                'review_count'   => (int) ($rating_row['review_count'] ?? 0),
            ];
        }

        $data['variations'] = $this->db->select('*')->from(PRODUCT_VARIATION_TABLE)
            ->where('product_id', $id)->where('status', 'active')
            ->order_by('variation_type', 'ASC')->order_by('variation_id', 'ASC')
            ->get()->result_array();

        foreach ($data['variations'] as &$v) {
            $v['image_url'] = !empty($v['image_path']) ? base_url($v['image_path']) : NULL;
        }
        unset($v);
        $data['combinations'] = $this->_combinations_for_product($id);

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
        $data['product']['avg_rating'] = $data['product']['review_count'] > 0
            ? array_sum(array_column($data['reviews'], 'rating')) / $data['product']['review_count']
            : NULL;

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
