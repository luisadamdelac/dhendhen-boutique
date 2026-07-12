<?php
/**
 * CartService
 * Resolves which reseller a cart line is attributed to and prices cart/
 * checkout line items against that reseller's published listing
 * (reseller_products.commission_price), not the central base price.
 *
 * Session cart shape: [ cart_key => ['product_id' => int, 'variation_id' => int|null, 'qty' => int, 'reseller_id' => int] ]
 * cart_key is "{product_id}" for a plain product or "{product_id}_v{variation_id}"
 * so the same product in two different variations gets its own cart line.
 */
class CartService {

    /**
     * Find a purchasable listing for a product: a specific reseller's
     * published listing if $resellerId is given, otherwise the cheapest
     * active listing from any reseller.
     */
    public static function resolveListing($productId, $resellerId = NULL) {
        $CI =& get_instance();
        $CI->load->database();

        $query = $CI->db->select('rp.*')
            ->from(RESELLER_PRODUCTS_TABLE . ' rp')
            ->join(RESELLER_TABLE . ' r', 'r.reseller_id = rp.reseller_id')
            ->where('rp.product_id', $productId)
            ->where('rp.is_published', 1)
            ->where('r.status', 'active');

        if ($resellerId) {
            $query->where('rp.reseller_id', $resellerId);
            return $query->get()->row_array();
        }

        return $query->order_by('rp.commission_price', 'ASC')->limit(1)->get()->row_array();
    }

    /**
     * Resolve a session cart into priced line items + subtotal, dropping
     * anything no longer purchasable (delisted, reseller suspended, product
     * pulled, or now fully out of central stock).
     *
     * @return array [items, subtotal, validCart] — validCart has the dropped
     *                entries removed, for the caller to persist back to session.
     */
    public static function getCartItems($cart) {
        $CI =& get_instance();
        $CI->load->database();

        if (empty($cart)) {
            return [[], 0, []];
        }

        $productIds = array_unique(array_column($cart, 'product_id'));
        // NOTE: rp.reseller_id must be aliased — product_tbl has its own
        // unrelated `reseller_id` column, and `p.*` (selected after) would
        // silently overwrite rp.reseller_id in the result row, corrupting
        // the byKey lookup below for every product that doesn't happen to
        // share the same value in both columns.
        $rows = $CI->db->select('rp.reseller_product_id, rp.reseller_id as listing_reseller_id, rp.commission_price, p.*, pi.image_path as product_image')
            ->from(RESELLER_PRODUCTS_TABLE . ' rp')
            ->join(PRODUCT_TABLE . ' p', 'p.product_id = rp.product_id')
            ->join(RESELLER_TABLE . ' r', 'r.reseller_id = rp.reseller_id')
            ->join(PRODUCT_IMAGE_TABLE . ' pi', 'pi.product_id = p.product_id AND pi.is_primary = 1', 'left')
            ->where_in('p.product_id', $productIds)
            ->where('p.status', 'available')
            ->where('rp.is_published', 1)
            ->where('r.status', 'active')
            ->get()->result_array();

        $byKey = [];
        foreach ($rows as $row) {
            $byKey[$row['product_id'] . ':' . $row['listing_reseller_id']] = $row;
        }

        $variationIds = array_filter(array_column($cart, 'variation_id'));
        $variationsById = [];
        if (!empty($variationIds)) {
            $variationRows = $CI->db->where_in('variation_id', array_unique($variationIds))
                ->where('status', 'active')->get(PRODUCT_VARIATION_TABLE)->result_array();
            foreach ($variationRows as $v) {
                $variationsById[$v['variation_id']] = $v;
            }
        }

        // Two-axis combination rows (product_variants) — joined to both
        // variation values so a display label ("Shade: X, Finish: Y") can be
        // built the same way the legacy single-axis path builds one.
        $variantIds = array_filter(array_column($cart, 'variant_id'));
        $variantsById = [];
        if (!empty($variantIds)) {
            require_once APPPATH . 'services/StockService.php';
            $variantRows = $CI->db->select('v.*, v1.variation_type as type_1, v1.variation_value as value_1, v2.variation_type as type_2, v2.variation_value as value_2', FALSE)
                ->from(PRODUCT_VARIANTS_TABLE . ' v')
                ->join(PRODUCT_VARIATION_TABLE . ' v1', 'v1.variation_id = v.variation_id_1', 'left')
                ->join(PRODUCT_VARIATION_TABLE . ' v2', 'v2.variation_id = v.variation_id_2', 'left')
                ->where_in('v.variant_id', array_unique($variantIds))
                ->where('v.status', 'active')
                ->get()->result_array();
            foreach ($variantRows as $v) {
                $variantsById[$v['variant_id']] = $v;
            }
        }

        $items = [];
        $subtotal = 0;
        $validCart = [];

        foreach ($cart as $cartKey => $entry) {
            $productId = (int) $entry['product_id'];
            $variationId = !empty($entry['variation_id']) ? (int) $entry['variation_id'] : NULL;
            $variantId = !empty($entry['variant_id']) ? (int) $entry['variant_id'] : NULL;
            $resellerId = $entry['reseller_id'] ?? NULL;
            $row = $byKey[$productId . ':' . $resellerId] ?? NULL;

            if (!$row) {
                continue;
            }

            $variation = NULL;
            $variant = NULL;
            $priceAdjustment = 0;
            $variationLabel = NULL;
            $availableStock = (int) $row['stock'];

            if ($variantId) {
                $variant = $variantsById[$variantId] ?? NULL;
                if (!$variant || (int) $variant['product_id'] !== $productId) {
                    continue; // combination removed/deactivated or no longer belongs to this product
                }
                $priceAdjustment = (float) $variant['price_adjustment'];
                $availableStock = array_sum(StockService::getVariantBranchStock($variantId));
                $variationLabel = $variant['type_1'] . ': ' . $variant['value_1'] . ($variant['type_2'] ? ', ' . $variant['type_2'] . ': ' . $variant['value_2'] : '');
            } elseif ($variationId) {
                $variation = $variationsById[$variationId] ?? NULL;
                if (!$variation || (int) $variation['product_id'] !== $productId) {
                    continue; // variation removed/deactivated or no longer belongs to this product
                }
                $priceAdjustment = (float) $variation['price_adjustment'];
                $availableStock = (int) $variation['stock'];
                $variationLabel = $variation['variation_type'] . ': ' . $variation['variation_value'];
            }

            $qty = min((int) $entry['qty'], $availableStock);
            if ($qty < 1) {
                continue;
            }

            $price = (float) $row['commission_price'] + $priceAdjustment;
            $itemTotal = $price * $qty;
            $subtotal += $itemTotal;

            $items[] = [
                'cart_key'            => $cartKey,
                'product_id'          => $productId,
                'variation_id'        => $variationId,
                'variant_id'          => $variantId,
                'variation_type'      => $variation['variation_type'] ?? NULL,
                'variation_value'     => $variation['variation_value'] ?? NULL,
                'variation_label'     => $variationLabel,
                'reseller_id'         => (int) $resellerId,
                'reseller_product_id' => (int) $row['reseller_product_id'],
                'product_name'        => $row['product_name'],
                'base_price'          => (float) $row['price'],
                'price'               => $price,
                'quantity'            => $qty,
                'item_total'          => $itemTotal,
                'stock_quantity'      => $availableStock,
                'product_image'       => $row['product_image'] ?? '',
            ];

            $validCart[$cartKey] = $entry;
        }

        return [$items, $subtotal, $validCart];
    }
}
