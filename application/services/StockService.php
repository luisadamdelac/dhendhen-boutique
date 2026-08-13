<?php
/**
 * StockService
 * Centralized, per-branch inventory management.
 *
 * Stock lives in inventory_batches (one row per branch per delivery/adjustment,
 * FIFO consumed), audited via inventory_movements. product_tbl.stock is kept as
 * a denormalized TOTAL (SUM of remaining_quantity across branches) so existing
 * simple reads (shop grid, cart cap, reports) don't need to change.
 *
 * File: application/services/StockService.php
 */

class StockService {

    public function __construct() {
        $CI =& get_instance();
        $CI->load->database();
    }

    /**
     * Total available stock for a product, optionally scoped to one branch
     * and/or one variant combination.
     *
     * $variantId: 'ANY' (default) aggregates across the base product and
     * every variant combination — unchanged behavior for existing callers.
     * Pass an int to scope to one variant_id, or explicit NULL to scope to
     * base-product-only batches (no variant). Callers that adjust a specific
     * scope (e.g. set_stock() editing "Base product (no combination)") must
     * pass the same scope here that they'll pass to adjustStock(), or the
     * computed delta is sized against a different pool than the one it's
     * actually applied to.
     */
    public static function getAvailableStock($productId, $branchId = NULL, $variantId = 'ANY') {
        $CI =& get_instance();
        $CI->load->database();

        $query = $CI->db->select('COALESCE(SUM(remaining_quantity), 0) as total')
            ->where('product_id', $productId)
            ->where('status', 'active');
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        if ($variantId !== 'ANY') {
            $variantId === NULL ? $query->where('variant_id IS NULL', NULL, FALSE) : $query->where('variant_id', $variantId);
        }
        $row = $query->get(INVENTORY_BATCH_TABLE)->row_array();
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Per-branch stock breakdown for a product: [branch_id => qty, ...]
     *
     * $variationId: 'ALL' (default) aggregates across every variation (and
     * the base product) — used for the product-level branch/stock display.
     * Pass an int to scope to one variation, or explicit NULL to scope to
     * base-product-only batches (no variation).
     */
    public static function getBranchStock($productId, $variationId = 'ALL') {
        $CI =& get_instance();
        $CI->load->database();

        $query = $CI->db->select('branch_id, COALESCE(SUM(remaining_quantity), 0) as qty')
            ->where('product_id', $productId)
            ->where('status', 'active');
        if ($variationId !== 'ALL') {
            $query->where('variation_id', $variationId);
        }
        $rows = $query->group_by('branch_id')->get(INVENTORY_BATCH_TABLE)->result_array();

        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row['branch_id']] = (int) $row['qty'];
        }
        return $out;
    }

    /**
     * Per-branch stock breakdown for one product_variants combination row:
     * [branch_id => qty, ...]. Mirrors getBranchStock() but scoped by
     * variant_id (a two-axis combination) instead of variation_id (a single
     * value) — the two are independent dimensions on inventory_batches.
     */
    public static function getVariantBranchStock($variantId) {
        $CI =& get_instance();
        $CI->load->database();

        $rows = $CI->db->select('branch_id, COALESCE(SUM(remaining_quantity), 0) as qty')
            ->where('variant_id', $variantId)
            ->where('status', 'active')
            ->group_by('branch_id')
            ->get(INVENTORY_BATCH_TABLE)->result_array();

        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row['branch_id']] = (int) $row['qty'];
        }
        return $out;
    }

    /**
     * A variant combination's effective "pieces per unit" — the product of
     * pieces_per_unit across every axis value that makes up the combination
     * (e.g. a "Package Type: 1 Set (10 pcs)" axis value with
     * pieces_per_unit=10 makes the whole combination 10). Defaults to 1 for
     * a NULL variant (no-variation base product) or any value that hasn't
     * set a multiplier, so ordinary single-piece variations are unaffected.
     */
    public static function getVariantPiecesPerUnit($variantId) {
        if (empty($variantId)) {
            return 1;
        }

        $CI =& get_instance();
        $CI->load->database();

        $variant = $CI->db->select('variation_id_1, variation_id_2')
            ->from(PRODUCT_VARIANTS_TABLE)
            ->where('variant_id', $variantId)
            ->get()->row_array();
        if (!$variant) {
            return 1;
        }

        $axisIds = [(int) $variant['variation_id_1']];
        if (!empty($variant['variation_id_2'])) {
            $axisIds[] = (int) $variant['variation_id_2'];
        }

        $extraRows = $CI->db->select('variation_id')
            ->from(PRODUCT_VARIANT_EXTRA_VALUES_TABLE)
            ->where('variant_id', $variantId)
            ->get()->result_array();
        foreach ($extraRows as $row) {
            $axisIds[] = (int) $row['variation_id'];
        }

        if (empty($axisIds)) {
            return 1;
        }

        $valueRows = $CI->db->select('pieces_per_unit')
            ->from(PRODUCT_VARIATION_TABLE)
            ->where_in('variation_id', array_unique($axisIds))
            ->get()->result_array();

        $multiplier = 1;
        foreach ($valueRows as $row) {
            $multiplier *= max(1, (int) $row['pieces_per_unit']);
        }

        return $multiplier;
    }

    /**
     * Same idea as getVariantPiecesPerUnit(), for the legacy single-axis
     * path — a cart line carrying variation_id directly instead of a
     * product_variants row.
     */
    public static function getVariationPiecesPerUnit($variationId) {
        if (empty($variationId)) {
            return 1;
        }

        $CI =& get_instance();
        $CI->load->database();

        $row = $CI->db->select('pieces_per_unit')
            ->from(PRODUCT_VARIATION_TABLE)
            ->where('variation_id', $variationId)
            ->get()->row_array();

        return $row ? max(1, (int) $row['pieces_per_unit']) : 1;
    }

    /**
     * Batched version of getBranchStock() for a whole product list — one
     * query instead of one-per-product. Returns [product_id => [branch_id
     * => qty, ...], ...]; a product with no batches simply has no key (same
     * "missing = empty array" contract callers already handle via `?? []`).
     */
    public static function getBranchStockForProducts(array $productIds) {
        $CI =& get_instance();
        $CI->load->database();

        if (empty($productIds)) {
            return [];
        }

        $rows = $CI->db->select('product_id, branch_id, COALESCE(SUM(remaining_quantity), 0) as qty')
            ->where('status', 'active')
            ->where_in('product_id', $productIds)
            ->group_by(['product_id', 'branch_id'])
            ->get(INVENTORY_BATCH_TABLE)->result_array();

        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row['product_id']][(int) $row['branch_id']] = (int) $row['qty'];
        }
        return $out;
    }

    /**
     * Batched version of getAvailableStock() for a whole product list,
     * scoped to one branch — one query instead of one-per-product. Returns
     * [product_id => qty, ...]; a product with no batches simply has no key.
     */
    public static function getAvailableStockForProducts(array $productIds, $branchId) {
        $CI =& get_instance();
        $CI->load->database();

        if (empty($productIds) || !$branchId) {
            return [];
        }

        $rows = $CI->db->select('product_id, COALESCE(SUM(remaining_quantity), 0) as qty')
            ->where('status', 'active')
            ->where('branch_id', $branchId)
            ->where_in('product_id', $productIds)
            ->group_by('product_id')
            ->get(INVENTORY_BATCH_TABLE)->result_array();

        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row['product_id']] = (int) $row['qty'];
        }
        return $out;
    }

    /**
     * Receive a new batch of stock into a branch (product creation or restock).
     * $variationId: NULL (default) = stock belongs to the base product.
     * $variantId: NULL (default) = not scoped to a two-axis combination row;
     * pass a product_variants.variant_id to tag this batch to that combination
     * (independent of $variationId — a batch may carry either, both, or neither).
     */
    public static function addBatch($productId, $branchId, $quantity, $unitCost = 0, $receivedBy = NULL, $variationId = NULL, $variantId = NULL) {
        $CI =& get_instance();
        $CI->load->database();

        if ($quantity <= 0 || empty($branchId)) {
            return FALSE;
        }

        $CI->db->trans_start();

        $now = date('Y-m-d H:i:s');
        $CI->db->insert(INVENTORY_BATCH_TABLE, [
            'branch_id'          => $branchId,
            'product_id'         => $productId,
            'variation_id'       => $variationId,
            'variant_id'         => $variantId,
            'batch_quantity'     => $quantity,
            'remaining_quantity' => $quantity,
            'unit_cost'          => $unitCost,
            'received_date'      => date('Y-m-d'),
            'received_by'        => $receivedBy,
            'status'             => 'active',
            'created_at'         => $now,
            'updated_at'         => $now,
        ]);
        $batchId = $CI->db->insert_id();

        $CI->db->insert(INVENTORY_MOVEMENTS_TABLE, [
            'branch_id'          => $branchId,
            'product_id'         => $productId,
            'variation_id'       => $variationId,
            'variant_id'         => $variantId,
            'inventory_batch_id' => $batchId,
            'previous_quantity'  => 0,
            'quantity_changed'   => $quantity,
            'new_quantity'       => $quantity,
            'transaction_type'   => 'restock',
            'user_id'            => $receivedBy,
            'created_at'         => $now,
        ]);

        self::_syncProductTotal($productId);
        if ($variationId !== NULL) {
            self::_syncVariationTotal($variationId);
        }
        if ($variantId !== NULL) {
            self::_syncVariantTotal($variantId);
        }

        $CI->db->trans_complete();
        return $CI->db->trans_status() !== FALSE ? $batchId : FALSE;
    }

    /**
     * Deduct stock FIFO (oldest batch first). Locks the candidate batch rows
     * for the duration of the transaction so concurrent checkouts can't both
     * read the same "available" stock and oversell it.
     *
     * If $branchId is given, only that branch's batches are consumed;
     * otherwise the oldest available batch across all branches is used first
     * (a single order line may then span branches if one alone can't cover it).
     *
     * $variationId defaults to 'ANY' (no filter — consumes from any variation's
     * batches, matching today's checkout behavior, which is not variation-aware).
     * Admin-side callers (e.g. adjustStock) may pass an explicit variation id
     * or NULL to scope the deduction correctly.
     *
     * $variantId works the same way but scopes to a product_variants
     * combination row instead — pass 'ANY' (default, no filter), an explicit
     * variant_id, or NULL (base/non-combination batches only).
     */
    public static function deductStock($productId, $quantity, $movementType = 'sale', $referenceType = NULL,
                                       $referenceId = NULL, $performedById = NULL, $notes = NULL, $branchId = NULL,
                                       $variationId = 'ANY', $variantId = 'ANY') {
        $CI =& get_instance();
        $CI->load->database();

        if ($quantity <= 0) {
            return FALSE;
        }

        try {
            $CI->db->trans_start();

            $sql = 'SELECT * FROM ' . INVENTORY_BATCH_TABLE . '
                    WHERE product_id = ? AND status = ? AND remaining_quantity > 0';
            $params = [$productId, 'active'];
            if ($branchId) {
                $sql .= ' AND branch_id = ?';
                $params[] = $branchId;
            }
            if ($variationId !== 'ANY') {
                $sql .= $variationId === NULL ? ' AND variation_id IS NULL' : ' AND variation_id = ?';
                if ($variationId !== NULL) {
                    $params[] = $variationId;
                }
            }
            if ($variantId !== 'ANY') {
                $sql .= $variantId === NULL ? ' AND variant_id IS NULL' : ' AND variant_id = ?';
                if ($variantId !== NULL) {
                    $params[] = $variantId;
                }
            }
            $sql .= ' ORDER BY received_date ASC, inventory_batch_id ASC FOR UPDATE';

            $batches = $CI->db->query($sql, $params)->result_array();

            $available = array_sum(array_column($batches, 'remaining_quantity'));
            if ($available < $quantity) {
                throw new Exception('Insufficient stock for product ' . $productId . ': requested ' . $quantity . ', available ' . $available);
            }

            $remainingToDeduct = $quantity;
            $now = date('Y-m-d H:i:s');

            foreach ($batches as $batch) {
                if ($remainingToDeduct <= 0) {
                    break;
                }

                $take = min($remainingToDeduct, (int) $batch['remaining_quantity']);
                $newQty = (int) $batch['remaining_quantity'] - $take;

                $CI->db->where('inventory_batch_id', $batch['inventory_batch_id'])
                    ->update(INVENTORY_BATCH_TABLE, ['remaining_quantity' => $newQty, 'updated_at' => $now]);

                $CI->db->insert(INVENTORY_MOVEMENTS_TABLE, [
                    'branch_id'          => $batch['branch_id'],
                    'product_id'         => $productId,
                    'variation_id'       => $batch['variation_id'],
                    'variant_id'         => $batch['variant_id'],
                    'inventory_batch_id' => $batch['inventory_batch_id'],
                    'previous_quantity'  => (int) $batch['remaining_quantity'],
                    'quantity_changed'   => -$take,
                    'new_quantity'       => $newQty,
                    'transaction_type'   => $movementType,
                    'user_id'            => $performedById,
                    'remarks'            => $notes,
                    'related_order_id'   => $referenceType === 'order' ? $referenceId : NULL,
                    'created_at'         => $now,
                ]);

                $remainingToDeduct -= $take;
            }

            self::_syncProductTotal($productId);
            if ($variationId !== 'ANY' && $variationId !== NULL) {
                self::_syncVariationTotal($variationId);
            }
            if ($variantId !== 'ANY' && $variantId !== NULL) {
                self::_syncVariantTotal($variantId);
            }

            $CI->db->trans_complete();

            if ($CI->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            self::_maybeNotifyLowStock($productId, $quantity);

            log_message('info', 'Stock deducted: product ' . $productId . ', qty ' . $quantity . ', type ' . $movementType);
            return TRUE;

        } catch (Exception $e) {
            $CI->db->trans_complete();
            log_message('error', 'Stock deduction error: ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Restore stock (order cancellation or refund). Adds back into the most
     * recently touched batch for that product/branch(/variation), or opens
     * a fresh adjustment batch if none exists yet.
     *
     * $variationId defaults to 'ANY' (no filter, matching deductStock()'s
     * default) — pass an explicit variation id (or NULL for base-product-only)
     * when restoring a variant line, so the restore lands back in a batch
     * for that same variation rather than a different one's.
     *
     * $variantId works the same way for a product_variants combination row.
     */
    public static function restoreStock($productId, $quantity, $referenceType = NULL, $referenceId = NULL, $notes = NULL, $branchId = NULL, $variationId = 'ANY', $variantId = 'ANY') {
        $CI =& get_instance();
        $CI->load->database();

        if ($quantity <= 0) {
            return FALSE;
        }

        try {
            $CI->db->trans_start();

            $query = $CI->db->where('product_id', $productId)->where('status', 'active');
            if ($branchId) {
                $query->where('branch_id', $branchId);
            }
            if ($variationId !== 'ANY') {
                $variationId === NULL ? $query->where('variation_id IS NULL', NULL, FALSE) : $query->where('variation_id', $variationId);
            }
            if ($variantId !== 'ANY') {
                $variantId === NULL ? $query->where('variant_id IS NULL', NULL, FALSE) : $query->where('variant_id', $variantId);
            }
            $batch = $query->order_by('updated_at', 'DESC')->get(INVENTORY_BATCH_TABLE)->row_array();

            $now = date('Y-m-d H:i:s');

            if (!$batch) {
                $fallbackBranch = $branchId ?: self::_defaultBranchId();
                self::addBatch($productId, $fallbackBranch, $quantity, 0, NULL, $variationId === 'ANY' ? NULL : $variationId, $variantId === 'ANY' ? NULL : $variantId);
                self::_syncProductTotal($productId);
                if ($variationId !== 'ANY' && $variationId !== NULL) {
                    self::_syncVariationTotal($variationId);
                }
                if ($variantId !== 'ANY' && $variantId !== NULL) {
                    self::_syncVariantTotal($variantId);
                }
                $CI->db->trans_complete();
                return $CI->db->trans_status() !== FALSE;
            }

            $newQty = (int) $batch['remaining_quantity'] + $quantity;
            $CI->db->where('inventory_batch_id', $batch['inventory_batch_id'])
                ->update(INVENTORY_BATCH_TABLE, ['remaining_quantity' => $newQty, 'updated_at' => $now]);

            $CI->db->insert(INVENTORY_MOVEMENTS_TABLE, [
                'branch_id'          => $batch['branch_id'],
                'product_id'         => $productId,
                'variation_id'       => $batch['variation_id'],
                'variant_id'         => $batch['variant_id'],
                'inventory_batch_id' => $batch['inventory_batch_id'],
                'previous_quantity'  => (int) $batch['remaining_quantity'],
                'quantity_changed'   => $quantity,
                'new_quantity'       => $newQty,
                'transaction_type'   => 'return',
                'remarks'            => $notes ?? 'Stock restored',
                'related_order_id'   => $referenceType === 'order' ? $referenceId : NULL,
                'created_at'         => $now,
            ]);

            self::_syncProductTotal($productId);
            if ($variationId !== 'ANY' && $variationId !== NULL) {
                self::_syncVariationTotal($variationId);
            }
            if ($variantId !== 'ANY' && $variantId !== NULL) {
                self::_syncVariantTotal($variantId);
            }

            $CI->db->trans_complete();
            return $CI->db->trans_status() !== FALSE;

        } catch (Exception $e) {
            $CI->db->trans_complete();
            log_message('error', 'Stock restoration error: ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Manual stock adjustment for one branch (staff/admin correction), signed
     * quantity delta. Positive = add a small adjustment batch, negative =
     * deduct via the normal FIFO path.
     */
    public static function adjustStock($productId, $branchId, $delta, $performedById = NULL, $notes = NULL, $variationId = NULL, $variantId = NULL) {
        if ($delta === 0) {
            return TRUE;
        }
        if ($delta > 0) {
            return self::addBatch($productId, $branchId, $delta, 0, $performedById, $variationId, $variantId) !== FALSE;
        }
        return self::deductStock($productId, abs($delta), 'adjustment', 'manual', NULL, $performedById, $notes, $branchId, $variationId, $variantId);
    }

    /**
     * Movement history for a product (optionally scoped to a branch).
     */
    public static function getMovementHistory($productId, $limit = 50, $branchId = NULL) {
        $CI =& get_instance();
        $CI->load->database();

        $query = $CI->db->where('product_id', $productId);
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get(INVENTORY_MOVEMENTS_TABLE)
            ->result_array();
    }

    /**
     * Get low-stock products (by the cached product_tbl.stock total).
     */
    public static function getLowStockProducts($threshold = 10) {
        $CI =& get_instance();
        $CI->load->database();

        return $CI->db->where('stock <=', $threshold)
            ->where('status', 'available')
            ->order_by('stock', 'ASC')
            ->get(PRODUCT_TABLE)
            ->result_array();
    }

    /**
     * Recompute and cache product_tbl.stock as the sum across all branches.
     */
    private static function _syncProductTotal($productId) {
        $CI =& get_instance();
        $CI->load->database();

        $total = self::getAvailableStock($productId);
        $CI->db->where('product_id', $productId)->update(PRODUCT_TABLE, ['stock' => $total]);
    }

    /**
     * Alert admins the moment a deduction pushes a product's total stock
     * at/below its own min_stock_alert — previously this only ever fired
     * once, at product-creation time (admin/Product.php), so a product that
     * later got sold down to (or below) its threshold never notified anyone.
     *
     * Triggers only on the crossing (old total was above the threshold, new
     * total isn't), not on every subsequent sale while already low — so
     * this can't spam one notification per unit sold.
     */
    private static function _maybeNotifyLowStock($productId, $justDeducted) {
        $CI =& get_instance();
        $CI->load->database();

        $product = $CI->db->select('product_name, stock, min_stock_alert')
            ->where('product_id', $productId)->get(PRODUCT_TABLE)->row_array();
        if (!$product || $product['min_stock_alert'] === NULL) {
            return;
        }

        $newStock = (int) $product['stock'];
        $minAlert = (int) $product['min_stock_alert'];
        $oldStock = $newStock + (int) $justDeducted;

        if ($newStock <= $minAlert && $oldStock > $minAlert) {
            require_once APPPATH . 'services/NotificationService.php';
            NotificationService::notifyAllAdmins(
                'Low Stock Alert',
                '"' . $product['product_name'] . '" has only ' . $newStock . ' unit(s) left across all branches (alert threshold: ' . $minAlert . ').',
                'system',
                $productId
            );
        }
    }

    /**
     * Recompute and cache product_variation_tbl.stock (a display-only
     * convenience column — inventory_batches is the real ledger) as the sum
     * across all branches for that one variation. Called after any
     * deduct/restore that was scoped to a specific variation, so admin's
     * product edit page (which seeds its stock inputs from this column)
     * doesn't show a stale number.
     */
    private static function _syncVariationTotal($variationId) {
        $CI =& get_instance();
        $CI->load->database();

        $total = (int) ($CI->db->select('COALESCE(SUM(remaining_quantity), 0) as total')
            ->where('variation_id', $variationId)
            ->where('status', 'active')
            ->get(INVENTORY_BATCH_TABLE)->row_array()['total'] ?? 0);
        $CI->db->where('variation_id', $variationId)->update(PRODUCT_VARIATION_TABLE, ['stock' => $total]);
    }

    /**
     * Recompute and cache product_variants.stock (a display-only convenience
     * column — inventory_batches is the real ledger) as the sum across all
     * branches for that one combination row. Mirrors _syncVariationTotal().
     */
    private static function _syncVariantTotal($variantId) {
        $CI =& get_instance();
        $CI->load->database();

        $total = (int) ($CI->db->select('COALESCE(SUM(remaining_quantity), 0) as total')
            ->where('variant_id', $variantId)
            ->where('status', 'active')
            ->get(INVENTORY_BATCH_TABLE)->row_array()['total'] ?? 0);
        $CI->db->where('variant_id', $variantId)->update(PRODUCT_VARIANTS_TABLE, ['stock' => $total]);
    }

    private static function _defaultBranchId() {
        $CI =& get_instance();
        $CI->load->database();

        $row = $CI->db->select('branch_id')
            ->where('status', 'active')
            ->order_by('branch_id', 'ASC')
            ->get(BRANCHES_TABLE)->row_array();

        return $row['branch_id'] ?? NULL;
    }
}

/* End of file StockService.php */
/* Location: ./application/services/StockService.php */
