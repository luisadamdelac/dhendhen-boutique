<?php
/**
 * Reseller Portal - Inventory
 *
 * Resellers do not own or create products — all stock lives centrally in
 * product_tbl (kept in sync from the branch-level inventory_batches ledger).
 * A reseller curates their mini-shop by publishing/unpublishing existing
 * central products into reseller_products with their own asking price
 * (commission_price), never by cloning the product or its stock.
 */
class Inventory extends Authenticated_Controller {

    public function __construct() {
        parent::__construct();
        $this->require_role(ROLE_RESELLER);
        $this->load->model('product_model');
        $this->load->library('form_validation');
    }

    public function index() {
        $data = $this->set_view_data();
        $data['page_title'] = 'Inventory - Reseller Portal';

        // My Mini-Shop: this reseller's published (and unpublished) listings
        $data['myListings'] = $this->db->select('rp.*, p.product_name, p.description, p.price as base_price, p.stock as central_stock, p.status as product_status, c.category_name, pi.image_path as product_image')
            ->from(RESELLER_PRODUCTS_TABLE . ' rp')
            ->join(PRODUCT_TABLE . ' p', 'p.product_id = rp.product_id')
            ->join(CATEGORY_TABLE . ' c', 'p.category_id = c.category_id', 'left')
            ->join(PRODUCT_IMAGE_TABLE . ' pi', 'pi.product_id = p.product_id AND pi.is_primary = 1', 'left')
            ->where('rp.reseller_id', $this->user_id)
            ->order_by('rp.updated_at', 'DESC')
            ->get()->result_array();

        $published_ids = array_column($data['myListings'], 'product_id');

        // Browse Central Catalog: available products this reseller hasn't published yet
        $data['catalog'] = $this->product_model->base_query([
                'status'      => 'available',
                'exclude_ids' => $published_ids,
            ])
            ->order_by('p.product_name', 'ASC')->get()->result_array();

        // Units sold through THIS reseller's own listings (delivered orders only)
        $data['soldByProduct'] = [];
        if (!empty($published_ids)) {
            $sold = $this->db->select('od.product_id, SUM(od.quantity) as qty')
                ->from(ORDER_DETAILS_TABLE . ' od')
                ->join(ORDER_TABLE . ' o', 'o.order_id = od.order_id')
                ->where('o.reseller_id', $this->user_id)
                ->where_in('od.product_id', $published_ids)
                ->where('o.order_status', 'delivered')
                ->group_by('od.product_id')
                ->get()->result_array();
            foreach ($sold as $row) {
                $data['soldByProduct'][$row['product_id']] = (int) $row['qty'];
            }
        }

        $this->load->view('reseller/layouts/header', $data);
        $this->load->view('reseller/inventory/index', $data);
        $this->load->view('reseller/layouts/footer', $data);
    }

    /**
     * Publish a central product to this reseller's mini-shop.
     * Default asking price = base price + the product's suggested reseller commission.
     */
    public function publish($product_id = NULL) {
        $product_id = (int) $product_id;
        $product = $this->product_model->get_by_id($product_id);
        if ($product && $product['status'] !== 'available') {
            $product = NULL;
        }

        if (!$product) {
            $this->session->set_flashdata('error', 'Product not available to publish');
            redirect('reseller/inventory');
        }

        $existing = $this->db->where('reseller_id', $this->user_id)->where('product_id', $product_id)->get(RESELLER_PRODUCTS_TABLE)->row_array();
        $default_price = round((float) $product['price'] + (float) ($product['reseller_commission'] ?? 0), 2);

        if ($existing) {
            $this->db->where('reseller_product_id', $existing['reseller_product_id'])->update(RESELLER_PRODUCTS_TABLE, [
                'is_published' => 1,
                'published_at' => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
        } else {
            $this->db->insert(RESELLER_PRODUCTS_TABLE, [
                'reseller_id'      => $this->user_id,
                'product_id'       => $product_id,
                'commission_price' => $default_price,
                'is_published'     => 1,
                'published_at'     => date('Y-m-d H:i:s'),
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);
        }

        $this->log_activity('product_published', 'product', $product_id, $product['product_name']);
        $this->session->set_flashdata('success', 'Product published to your mini-shop');
        redirect('reseller/inventory');
    }

    /**
     * Update the asking price for one of this reseller's listings.
     * Floor: may never sell below the central base price.
     */
    public function update_price() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $reseller_product_id = (int) $this->input->post('reseller_product_id');
        $new_price = (float) $this->input->post('commission_price');

        $listing = $this->db->select('rp.*, p.price as base_price')
            ->from(RESELLER_PRODUCTS_TABLE . ' rp')
            ->join(PRODUCT_TABLE . ' p', 'p.product_id = rp.product_id')
            ->where('rp.reseller_product_id', $reseller_product_id)
            ->where('rp.reseller_id', $this->user_id)
            ->get()->row_array();

        if (!$listing) {
            echo json_encode(['success' => FALSE, 'message' => 'Listing not found']);
            return;
        }

        if ($new_price < (float) $listing['base_price']) {
            echo json_encode(['success' => FALSE, 'message' => 'Price cannot be lower than the base price of ₱' . number_format($listing['base_price'], 2)]);
            return;
        }

        $this->db->where('reseller_product_id', $reseller_product_id)->update(RESELLER_PRODUCTS_TABLE, [
            'commission_price' => $new_price,
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        $this->log_activity('product_price_updated', 'reseller_product', $reseller_product_id, 'New price: ' . $new_price);
        echo json_encode(['success' => TRUE, 'message' => 'Price updated']);
    }

    /**
     * Unpublish a listing (soft — keeps order/review history intact).
     */
    public function unpublish($reseller_product_id = NULL) {
        $reseller_product_id = (int) $reseller_product_id;
        $listing = $this->db->where('reseller_product_id', $reseller_product_id)->where('reseller_id', $this->user_id)->get(RESELLER_PRODUCTS_TABLE)->row_array();

        if (!$listing) {
            show_404();
        }

        $this->db->where('reseller_product_id', $reseller_product_id)->update(RESELLER_PRODUCTS_TABLE, [
            'is_published' => 0,
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        $this->log_activity('product_unpublished', 'reseller_product', $reseller_product_id);
        $this->session->set_flashdata('success', 'Product removed from your mini-shop');
        redirect('reseller/inventory');
    }

    public function history($product_id = NULL) {
        $product_id = (int) $product_id;

        $owned = $this->db->where('reseller_id', $this->user_id)->where('product_id', $product_id)->get(RESELLER_PRODUCTS_TABLE)->row_array();
        $product = $this->db->where('product_id', $product_id)->get(PRODUCT_TABLE)->row_array();

        if (!$product || !$owned) {
            show_404();
        }

        $data = $this->set_view_data();
        $data['page_title'] = 'Product History - Reseller Portal';
        $data['product'] = $product;

        require_once APPPATH . 'services/StockService.php';
        $data['history'] = StockService::getMovementHistory($product_id, 50);

        $this->load->view('reseller/layouts/header', $data);
        $this->load->view('reseller/inventory/history', $data);
        $this->load->view('reseller/layouts/footer', $data);
    }
}
