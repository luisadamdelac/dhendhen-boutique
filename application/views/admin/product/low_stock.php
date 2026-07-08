<?php
$page_title = 'Low Stock Products';
$current_page = 'product';
?>
<style>
/* ── Product table (matches product/list.php convention) ─────── */
.inv-table th {
    font-size: 11px; text-transform: uppercase; letter-spacing: .5px;
    color: #8a94ad; font-weight: 600; border-bottom: 2px solid #f0f0f8;
    background: #fafbff; padding: 10px 12px;
}
.inv-table td { padding: 10px 12px; vertical-align: middle; border-color: #f4f6fb; }
.inv-table tbody tr:hover { background: #fafbff; }
</style>

<div class="container-fluid py-4">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1a1a2e;"><i class="fas fa-exclamation-triangle"></i> Low Stock Products</h4>
            <small class="text-muted">Products at or below their minimum stock threshold.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/product'); ?>" class="btn btn-outline-secondary btn-sm">Back to Inventory</a>
        </div>
    </div>

    <div class="filter-bar">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label class="form-label fw-semibold small mb-1">Stock Threshold</label>
                <input type="number" name="threshold" class="form-control form-control-sm" value="<?= htmlspecialchars($threshold); ?>">
            </div>
            <div class="col-md-2 col-sm-6">
                <button type="submit" class="btn btn-sm btn-primary">Apply</button>
            </div>
        </form>
    </div>

    <div class="card border-0 shadow-sm mb-2" style="border-radius:12px;overflow:hidden;">
        <div class="table-responsive">
            <table class="table inv-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Product</th>
                        <th class="text-center">Stock</th>
                        <th class="text-end pe-3">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                        <?php
                            $st = $product['stock'] ?? 0;
                            $min = $product['min_stock_alert'] ?? 10;
                            $stock_badge = $st == 0 ? 'stock-zero' : ($st <= $min ? 'stock-warn' : 'stock-ok');
                        ?>
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold" style="font-size:13px;color:#1a1a2e;"><?= htmlspecialchars($product['product_name']); ?></div>
                                <div style="font-size:11px;color:#8a94ad;">
                                    <?php if (!empty($product['sku'])): ?>SKU: <?= htmlspecialchars($product['sku']); ?><?php endif; ?>
                                    <?php if (!empty($product['category_name'])): ?> &nbsp;·&nbsp; <?= htmlspecialchars($product['category_name']); ?><?php endif; ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="stock-badge <?= $stock_badge; ?>"><?= number_format((int) $st); ?></span>
                            </td>
                            <td class="text-end pe-3 fw-semibold" style="font-size:13px;">₱<?= number_format($product['price'] ?? 0, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <i class="fas fa-box-open fa-2x text-muted mb-2 d-block"></i>
                                <span class="text-muted">No low stock products found</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /container-fluid -->
