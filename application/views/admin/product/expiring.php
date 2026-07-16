<?php
$page_title = 'Expiring Products';
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
            <h4 class="fw-bold mb-1" style="color:#1a1a2e;"><i class="fas fa-calendar-times"></i> Expiring Products</h4>
            <small class="text-muted">Products already expired or expiring soon.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/product'); ?>" class="btn btn-outline-secondary btn-sm">Back to Inventory</a>
        </div>
    </div>

    <div class="filter-bar">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label class="form-label fw-semibold small mb-1">Within (days)</label>
                <input type="number" name="days" class="form-control form-control-sm" value="<?= htmlspecialchars($days); ?>">
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
                        <th>Branch</th>
                        <th class="text-center">Expiry Date</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Stock</th>
                        <th class="text-end pe-3">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                        <?php
                            $today = date('Y-m-d');
                            $isExpired = $product['expiry_date'] < $today;
                            $badgeClass = $isExpired ? 'badge-cancelled' : 'badge-pending';
                            $badgeLabel = $isExpired ? 'Expired' : 'Expiring';
                        ?>
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold" style="font-size:13px;color:#1a1a2e;"><?= htmlspecialchars($product['product_name']); ?></div>
                                <?php if (!empty($product['category_name'])): ?>
                                    <div style="font-size:11px;color:#8a94ad;"><?= htmlspecialchars($product['category_name']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= date('M d, Y', strtotime($product['expiry_date'])); ?></td>
                            <td class="text-center">
                                <span class="badge-status <?= $badgeClass; ?>"><?= $badgeLabel; ?></span>
                            </td>
                            <td class="text-center"><?= number_format((int) ($product['stock'] ?? 0)); ?></td>
                            <td class="text-end fw-semibold pe-3" style="font-size:13px;">₱<?= number_format($product['price'] ?? 0, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="fas fa-calendar-check fa-2x text-muted mb-2 d-block"></i>
                                <span class="text-muted">No expiring products found</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /container-fluid -->
