<?php
class Landing extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
        $this->load->database();
        $this->load->model('Product_model');
    }

    public function index() {
        $data['page_title'] = 'Welcome';
        $data['featuredProducts'] = $this->_featured_products();
        $this->load->view('landing/landing_page', $data);
    }

    public function landing_page() {
        $this->index();
    }

    /** A handful of purchasable products to preview on the landing page. */
    private function _featured_products() {
        $buy_price_subquery = '(SELECT MIN(rp.commission_price) FROM ' . RESELLER_PRODUCTS_TABLE . ' rp '
            . 'JOIN ' . RESELLER_TABLE . ' r ON r.reseller_id = rp.reseller_id '
            . 'WHERE rp.product_id = p.product_id AND rp.is_published = 1 AND r.status = \'active\') as buy_price';

        $products = $this->Product_model->base_query(['status' => 'available'])
            ->select($buy_price_subquery, FALSE)
            ->order_by('p.product_id', 'DESC')
            ->limit(50)
            ->get()->result_array();

        // Only products someone is actually selling belong on the landing
        // page — filtered in PHP (not SQL) since buy_price is a computed
        // column, then capped to the first 8 for the showcase grid.
        $products = array_values(array_filter($products, function ($p) {
            return $p['buy_price'] !== NULL;
        }));
        $products = array_slice($products, 0, 8);

        foreach ($products as &$p) {
            $p['price'] = (float) $p['buy_price'];
        }
        unset($p);

        return $products;
    }
}
