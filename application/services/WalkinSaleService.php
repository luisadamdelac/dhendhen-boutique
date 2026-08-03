<?php
/**
 * WalkinSaleService
 * Records an in-store (walk-in) sale: deducts stock through the normal
 * StockService ledger and writes a minimal walkin_sale_tbl/walkin_sale_details_tbl
 * pair — no customer_id/reseller_id/delivery info required, since these
 * never go through the customer-facing checkout.
 */
class WalkinSaleService {

    /**
     * @param array  $items   [['product_id'=>int, 'option_type'=>'variant'|'variation'|null,
     *                          'option_id'=>int|null, 'option_label'=>string|null,
     *                          'quantity'=>int, 'unit_price'=>float], ...]
     *                        option_type mirrors Shop.php/Checkout.php's two variant
     *                        systems: 'variant' = a generated product_variants
     *                        combination row, 'variation' = a plain single-axis
     *                        product_variation_tbl value, null = no options on
     *                        this product at all.
     * @param string $paymentMethod 'cash' or 'gcash'
     * @param string $recordedByRole 'admin' or 'staff'
     * @param int    $recordedById
     * @param int    $branchId    which branch's stock to deduct from
     * @param string|null $notes
     * @return int walkin_sale_id
     * @throws Exception on validation/stock failure (transaction is rolled back)
     */
    public static function createSale(array $items, $paymentMethod, $recordedByRole, $recordedById, $branchId, $notes = NULL) {
        $CI =& get_instance();
        $CI->load->database();
        require_once APPPATH . 'services/StockService.php';

        if (empty($items)) {
            throw new Exception('Please add at least one product to the sale.');
        }
        if (empty($branchId)) {
            throw new Exception('Please select a branch for this sale.');
        }

        $total_amount = 0;
        foreach ($items as $item) {
            $total_amount += (float) $item['unit_price'] * (int) $item['quantity'];
        }

        $CI->db->trans_start();

        $sale_number = 'WS' . date('ymdHis') . str_pad((string) random_int(0, 99), 2, '0', STR_PAD_LEFT);

        $CI->db->insert('walkin_sale_tbl', [
            'sale_number'      => $sale_number,
            'branch_id'        => $branchId,
            'recorded_by_role' => $recordedByRole,
            'recorded_by_id'   => $recordedById,
            'total_amount'     => $total_amount,
            'payment_method'   => $paymentMethod,
            'notes'            => $notes,
            'created_at'       => date('Y-m-d H:i:s'),
        ]);
        $walkin_sale_id = $CI->db->insert_id();

        foreach ($items as $item) {
            $quantity = (int) $item['quantity'];
            $unit_price = (float) $item['unit_price'];
            $optionType = $item['option_type'] ?? NULL;
            $optionId = !empty($item['option_id']) ? (int) $item['option_id'] : NULL;
            $isVariant = $optionType === 'variant' && $optionId;
            $isVariation = $optionType === 'variation' && $optionId;

            $CI->db->insert('walkin_sale_details_tbl', [
                'walkin_sale_id'  => $walkin_sale_id,
                'product_id'      => $item['product_id'],
                'variation_id'    => $isVariation ? $optionId : NULL,
                'variant_id'      => $isVariant ? $optionId : NULL,
                'variation_label' => $item['option_label'] ?? NULL,
                'quantity'        => $quantity,
                'unit_price'      => $unit_price,
                'total_price'     => round($unit_price * $quantity, 2),
            ]);

            if ($isVariant) {
                $piecesPerUnit = StockService::getVariantPiecesPerUnit($optionId);
                $deducted = StockService::deductStock(
                    $item['product_id'], $quantity * $piecesPerUnit, 'sale', NULL, NULL,
                    $recordedById, 'Walk-in sale #' . $sale_number, $branchId, NULL, $optionId
                );
            } elseif ($isVariation) {
                $piecesPerUnit = StockService::getVariationPiecesPerUnit($optionId);
                $deducted = StockService::deductStock(
                    $item['product_id'], $quantity * $piecesPerUnit, 'sale', NULL, NULL,
                    $recordedById, 'Walk-in sale #' . $sale_number, $branchId, $optionId, NULL
                );
            } else {
                $deducted = StockService::deductStock(
                    $item['product_id'], $quantity, 'sale', NULL, NULL,
                    $recordedById, 'Walk-in sale #' . $sale_number, $branchId, NULL, NULL
                );
            }

            if (!$deducted) {
                throw new Exception('Insufficient stock at this branch for one or more items. Please review the sale.');
            }
        }

        $CI->db->trans_complete();

        if ($CI->db->trans_status() === FALSE) {
            throw new Exception('Failed to record the sale. Please try again.');
        }

        return $walkin_sale_id;
    }

    /**
     * Recent walk-in sales, optionally scoped to one branch (staff view).
     * Each row's items are attached as $row['items'] for a receipt-style listing.
     */
    public static function getRecentSales($branchId = NULL, $limit = 20) {
        $CI =& get_instance();
        $CI->load->database();

        $query = $CI->db->select('ws.*, b.branch_name')
            ->from('walkin_sale_tbl ws')
            ->join('branches b', 'b.branch_id = ws.branch_id', 'left');
        if ($branchId) {
            $query->where('ws.branch_id', $branchId);
        }
        $sales = $query->order_by('ws.walkin_sale_id', 'DESC')->limit($limit)->get()->result_array();

        if (empty($sales)) {
            return [];
        }

        $sale_ids = array_column($sales, 'walkin_sale_id');
        $details = $CI->db->select('wsd.*, p.product_name')
            ->from('walkin_sale_details_tbl wsd')
            ->join(PRODUCT_TABLE . ' p', 'p.product_id = wsd.product_id', 'left')
            ->where_in('wsd.walkin_sale_id', $sale_ids)
            ->get()->result_array();

        $details_by_sale = [];
        foreach ($details as $d) {
            $details_by_sale[$d['walkin_sale_id']][] = $d;
        }

        foreach ($sales as &$sale) {
            $sale['items'] = $details_by_sale[$sale['walkin_sale_id']] ?? [];
        }
        unset($sale);

        return $sales;
    }

    /**
     * Sum of walk-in sale totals, optionally scoped to one branch and/or a
     * created_at date range — folded into the admin/staff dashboard "Total
     * Sales" stat (and the Sales/Financial reports) so in-store revenue
     * isn't invisible next to online order sales.
     */
    public static function getTotalSales($branchId = NULL, $start = NULL, $end = NULL) {
        $CI =& get_instance();
        $CI->load->database();

        $query = $CI->db->select('COALESCE(SUM(total_amount), 0) as total, COUNT(*) as cnt')->from('walkin_sale_tbl');
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        if ($start) {
            $query->where('DATE(created_at) >=', $start);
        }
        if ($end) {
            $query->where('DATE(created_at) <=', $end);
        }
        $row = $query->get()->row_array();
        return ['total' => (float) ($row['total'] ?? 0), 'count' => (int) ($row['cnt'] ?? 0)];
    }

    /**
     * Walk-in sales grouped by month (Y-m), for merging into the Sales
     * Report's "Monthly Sales" trend alongside online order totals.
     * Returns ['2026-08' => ['total_orders'=>n, 'total_sales'=>x], ...].
     */
    public static function getMonthlySales($start = NULL, $end = NULL) {
        $CI =& get_instance();
        $CI->load->database();

        $query = $CI->db->select("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total_orders, COALESCE(SUM(total_amount), 0) as total_sales")
            ->from('walkin_sale_tbl');
        if ($start) {
            $query->where('DATE(created_at) >=', $start);
        }
        if ($end) {
            $query->where('DATE(created_at) <=', $end);
        }
        $rows = $query->group_by("DATE_FORMAT(created_at, '%Y-%m')")->get()->result_array();

        $out = [];
        foreach ($rows as $row) {
            $out[$row['month']] = [
                'total_orders' => (int) $row['total_orders'],
                'total_sales' => (float) $row['total_sales'],
            ];
        }
        return $out;
    }

    /**
     * Product-level sold quantity/revenue from walk-in sales within a date
     * range, for merging into the Sales Report's "Top Selling Products"
     * list alongside online order_details — otherwise a product that only
     * ever sells well in-store would never show up as a top seller.
     */
    public static function getProductSales($start = NULL, $end = NULL) {
        $CI =& get_instance();
        $CI->load->database();

        $query = $CI->db->select('wsd.product_id, p.product_name, SUM(wsd.quantity) as total_sold, SUM(wsd.total_price) as revenue')
            ->from('walkin_sale_details_tbl wsd')
            ->join('walkin_sale_tbl ws', 'ws.walkin_sale_id = wsd.walkin_sale_id')
            ->join(PRODUCT_TABLE . ' p', 'p.product_id = wsd.product_id');
        if ($start) {
            $query->where('DATE(ws.created_at) >=', $start);
        }
        if ($end) {
            $query->where('DATE(ws.created_at) <=', $end);
        }
        return $query->group_by('wsd.product_id')->get()->result_array();
    }
}

/* End of file WalkinSaleService.php */
/* Location: ./application/services/WalkinSaleService.php */
