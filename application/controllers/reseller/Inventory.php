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

        // Mini-shop hero title: the reseller's own storefront/business name,
        // same fallback used on the public storefront (Shop::reseller()) —
        // business_name if set, else their full name.
        $this->load->model('reseller_model');
        $reseller = $this->reseller_model->get_by_id($this->user_id);
        $data['shopName'] = !empty($reseller['business_name'])
            ? $reseller['business_name']
            : trim(($reseller['first_name'] ?? '') . ' ' . ($reseller['last_name'] ?? ''));

        // My Mini-Shop: this reseller's published (and unpublished) listings
        $data['myListings'] = $this->db->select('rp.*, p.product_name, p.description, p.price as base_price, p.stock as central_stock, p.status as product_status, p.expiry_date, c.category_name, pi.image_path as product_image')
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

        $data['stats'] = $this->_get_inventory_stats($data['myListings'], $published_ids, $data['soldByProduct']);

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

    /**
     * Mini-shop stat cards + "vs last month" trends, all derived from real
     * records (published_at timestamps and inventory_movements deltas) — not
     * fabricated. Stock/product counts don't have a daily-snapshot table, so
     * "last month" for those two is reconstructed by walking back real
     * movements/publish dates rather than guessed.
     */
    private function _get_inventory_stats($myListings, $published_ids, $soldByProduct) {
        $monthStart = date('Y-m-01 00:00:00');
        $lastMonthStart = date('Y-m-01 00:00:00', strtotime('-1 month'));
        $lastMonthEnd = date('Y-m-t 23:59:59', strtotime('-1 month'));
        $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));

        $published = array_filter($myListings, fn($l) => (int) $l['is_published'] === 1);
        $totalProducts = count($published);
        $totalStock = array_sum(array_column($published, 'central_stock'));
        $totalSoldAllTime = array_sum($soldByProduct);

        // Products: how many of today's published listings were already
        // published 30+ days ago (their published_at gets refreshed only on
        // (re)publish, so this is a real, if approximate, tenure check).
        $productsLastMonth = count(array_filter($published, function ($l) use ($thirtyDaysAgo) {
            return !empty($l['published_at']) && $l['published_at'] <= $thirtyDaysAgo;
        }));

        // Stock: current total minus the net real movement on these exact
        // products over the last 30 days = what the total would have read
        // 30 days ago.
        $stockLastMonth = $totalStock;
        if (!empty($published_ids)) {
            $row = $this->db->select('COALESCE(SUM(quantity_changed), 0) as net_change')
                ->from(INVENTORY_MOVEMENTS_TABLE)
                ->where_in('product_id', $published_ids)
                ->where('created_at >', $thirtyDaysAgo)
                ->get()->row();
            $stockLastMonth = $totalStock - (int) ($row->net_change ?? 0);
        }

        // Sold + Sales: real calendar-month comparison.
        $soldThisMonth = 0;
        $soldLastMonth = 0;
        $salesThisMonth = 0.0;
        $salesLastMonth = 0.0;
        if (!empty($published_ids)) {
            $soldThisMonth = (int) ($this->db->select_sum('od.quantity', 'qty')
                ->from(ORDER_DETAILS_TABLE . ' od')
                ->join(ORDER_TABLE . ' o', 'o.order_id = od.order_id')
                ->where('o.reseller_id', $this->user_id)
                ->where_in('od.product_id', $published_ids)
                ->where('o.order_status', 'delivered')
                ->where('o.created_at >=', $monthStart)
                ->get()->row()->qty ?? 0);

            $soldLastMonth = (int) ($this->db->select_sum('od.quantity', 'qty')
                ->from(ORDER_DETAILS_TABLE . ' od')
                ->join(ORDER_TABLE . ' o', 'o.order_id = od.order_id')
                ->where('o.reseller_id', $this->user_id)
                ->where_in('od.product_id', $published_ids)
                ->where('o.order_status', 'delivered')
                ->where('o.created_at >=', $lastMonthStart)
                ->where('o.created_at <=', $lastMonthEnd)
                ->get()->row()->qty ?? 0);
        }

        $salesThisMonth = (float) ($this->db->select_sum('total_amount', 'total')
            ->from(ORDER_TABLE)->where('reseller_id', $this->user_id)
            ->where('created_at >=', $monthStart)
            ->get()->row()->total ?? 0);

        $salesLastMonth = (float) ($this->db->select_sum('total_amount', 'total')
            ->from(ORDER_TABLE)->where('reseller_id', $this->user_id)
            ->where('created_at >=', $lastMonthStart)->where('created_at <=', $lastMonthEnd)
            ->get()->row()->total ?? 0);

        $pct = function ($prev, $curr) {
            if ($prev == 0) {
                return $curr > 0 ? 100.0 : 0.0;
            }
            return (($curr - $prev) / $prev) * 100;
        };

        // In stock / low stock / out of stock — same >10 / 1-10 / 0 thresholds
        // the table's own stock badges already use, so the donut always
        // agrees with what's shown in the list below it.
        $inStockUnits = 0;
        $lowStockUnits = 0;
        $outOfStockCount = 0;
        $lowStockItems = [];
        foreach ($published as $l) {
            $stock = (int) $l['central_stock'];
            if ($stock > 10) {
                $inStockUnits += $stock;
            } elseif ($stock > 0) {
                $lowStockUnits += $stock;
                $lowStockItems[] = ['name' => $l['product_name'], 'stock' => $stock];
            } else {
                $outOfStockCount++;
            }
        }
        usort($lowStockItems, fn($a, $b) => $a['stock'] <=> $b['stock']);

        return [
            'total_products' => $totalProducts,
            'total_stock' => $totalStock,
            'sold_this_month' => $soldThisMonth,
            'sales_this_month' => $salesThisMonth,
            'trend' => [
                'products' => $pct($productsLastMonth, $totalProducts),
                'stock' => $pct($stockLastMonth, $totalStock),
                'sold' => $pct($soldLastMonth, $soldThisMonth),
                'sales' => $pct($salesLastMonth, $salesThisMonth),
            ],
            'in_stock_units' => $inStockUnits,
            'low_stock_units' => $lowStockUnits,
            'out_of_stock_count' => $outOfStockCount,
            'sold_all_time' => $totalSoldAllTime,
            'low_stock_items' => array_slice($lowStockItems, 0, 5),
        ];
    }
}
