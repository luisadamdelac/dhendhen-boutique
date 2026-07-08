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
     * Total available stock for a product, optionally scoped to one branch.
     */
    public static function getAvailableStock($productId, $branchId = NULL) {
        $CI =& get_instance();
        $CI->load->database();

        $query = $CI->db->select('COALESCE(SUM(remaining_quantity), 0) as total')
            ->where('product_id', $productId)
            ->where('status', 'active');
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        $row = $query->get(INVENTORY_BATCH_TABLE)->row_array();
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Per-branch stock breakdown for a product: [branch_id => qty, ...]
     */
    public static function getBranchStock($productId) {
        $CI =& get_instance();
        $CI->load->database();

        $rows = $CI->db->select('branch_id, COALESCE(SUM(remaining_quantity), 0) as qty')
            ->where('product_id', $productId)
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
     * Receive a new batch of stock into a branch (product creation or restock).
     */
    public static function addBatch($productId, $branchId, $quantity, $unitCost = 0, $receivedBy = NULL) {
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
            'inventory_batch_id' => $batchId,
            'previous_quantity'  => 0,
            'quantity_changed'   => $quantity,
            'new_quantity'       => $quantity,
            'transaction_type'   => 'restock',
            'user_id'            => $receivedBy,
            'created_at'         => $now,
        ]);

        self::_syncProductTotal($productId);

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
     */
    public static function deductStock($productId, $quantity, $movementType = 'sale', $referenceType = NULL,
                                       $referenceId = NULL, $performedById = NULL, $notes = NULL, $branchId = NULL) {
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

            $CI->db->trans_complete();

            if ($CI->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

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
     * recently touched batch for that product/branch, or opens a fresh
     * adjustment batch if none exists yet.
     */
    public static function restoreStock($productId, $quantity, $referenceType = NULL, $referenceId = NULL, $notes = NULL, $branchId = NULL) {
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
            $batch = $query->order_by('updated_at', 'DESC')->get(INVENTORY_BATCH_TABLE)->row_array();

            $now = date('Y-m-d H:i:s');

            if (!$batch) {
                $fallbackBranch = $branchId ?: self::_defaultBranchId();
                self::addBatch($productId, $fallbackBranch, $quantity, 0, NULL);
                self::_syncProductTotal($productId);
                $CI->db->trans_complete();
                return $CI->db->trans_status() !== FALSE;
            }

            $newQty = (int) $batch['remaining_quantity'] + $quantity;
            $CI->db->where('inventory_batch_id', $batch['inventory_batch_id'])
                ->update(INVENTORY_BATCH_TABLE, ['remaining_quantity' => $newQty, 'updated_at' => $now]);

            $CI->db->insert(INVENTORY_MOVEMENTS_TABLE, [
                'branch_id'          => $batch['branch_id'],
                'product_id'         => $productId,
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
    public static function adjustStock($productId, $branchId, $delta, $performedById = NULL, $notes = NULL) {
        if ($delta === 0) {
            return TRUE;
        }
        if ($delta > 0) {
            return self::addBatch($productId, $branchId, $delta, 0, $performedById) !== FALSE;
        }
        return self::deductStock($productId, abs($delta), 'adjustment', 'manual', NULL, $performedById, $notes, $branchId);
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
