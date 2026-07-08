<?php
$page_title = 'Product Details';
$current_page = 'product';

$st = $product['stock'] ?? 0;
$min = $product['min_stock_alert'] ?? 10;
$stock_badge = $st == 0 ? 'stock-zero' : ($st <= $min ? 'stock-warn' : 'stock-ok');

$status_key = $product['status'] ?? 'available';
$status_labels = [
    'available'     => 'Available',
    'out_of_stock'  => 'Out of Stock',
    'not_available' => 'Not Available',
    'inactive'      => 'Inactive',
    'archived'      => 'Archived',
];
$status_label = $status_labels[$status_key] ?? ucfirst($status_key);
$status_badges = [
    'available'     => 'badge-success',
    'out_of_stock'  => 'badge-warning',
    'not_available' => 'badge-warning',
    'inactive'      => 'badge-info',
    'archived'      => 'badge-danger',
];
$status_badge = $status_badges[$status_key] ?? 'badge-info';

$primary_image = null;
if (!empty($images)) {
    foreach ($images as $img) {
        if (!empty($img['is_primary'])) { $primary_image = $img['image_path']; break; }
    }
    if (!$primary_image) { $primary_image = $images[0]['image_path']; }
}
?>
<div class="container-fluid py-4">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1a1a2e;"><i class="fas fa-box"></i> Product Details</h4>
            <small class="text-muted">Full product information, pricing, and stock across branches.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/product/edit/' . $product['product_id']); ?>" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
            <a href="<?= site_url('admin/product'); ?>" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col col-3" style="text-align:center;">
                <?php if ($primary_image): ?>
                    <img src="<?= base_url($primary_image); ?>" style="width:100%;max-width:220px;height:220px;border-radius:12px;object-fit:cover;border:1px solid var(--border);" alt="">
                <?php else: ?>
                    <div style="width:100%;max-width:220px;height:220px;border-radius:12px;background:var(--page-bg);display:flex;align-items:center;justify-content:center;color:var(--gray);font-size:42px;margin:0 auto;">
                        <i class="fas fa-box"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col col-9">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:10px;">
                    <h4 style="margin:0;"><?= htmlspecialchars($product['product_name']); ?></h4>
                    <span class="badge-status badge-<?= $status_key; ?>"><?= $status_label; ?></span>
                </div>

                <div class="row">
                    <div class="col col-6">
                        <div class="ds-detail-row">
                            <div class="w-100">
                                <div class="ds-detail-label">SKU</div>
                                <div class="ds-detail-val"><?= htmlspecialchars($product['sku'] ?? '-'); ?></div>
                            </div>
                        </div>
                        <div class="ds-detail-row">
                            <div class="w-100">
                                <div class="ds-detail-label">Category</div>
                                <div class="ds-detail-val"><?= htmlspecialchars($product['category_name'] ?? '-'); ?></div>
                            </div>
                        </div>
                        <div class="ds-detail-row">
                            <div class="w-100">
                                <div class="ds-detail-label">Total Stock</div>
                                <div class="ds-detail-val"><span class="stock-badge <?= $stock_badge; ?>"><?= number_format((int) $st); ?></span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col col-6">
                        <div class="ds-detail-row">
                            <div class="w-100">
                                <div class="ds-detail-label">Brand</div>
                                <div class="ds-detail-val"><?= htmlspecialchars($product['brand'] ?? '-'); ?></div>
                            </div>
                        </div>
                        <div class="ds-detail-row">
                            <div class="w-100">
                                <div class="ds-detail-label">Price</div>
                                <div class="ds-detail-val" style="color: var(--primary-pink);font-weight:700;">₱<?= number_format($product['price'] ?? 0, 2); ?></div>
                            </div>
                        </div>
                        <div class="ds-detail-row">
                            <div class="w-100">
                                <div class="ds-detail-label">Expiry Date</div>
                                <div class="ds-detail-val"><?= !empty($product['expiry_date']) ? date('M d, Y', strtotime($product['expiry_date'])) : '-'; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <p style="color: var(--gray);">Description</p>
                <p><?= nl2br(htmlspecialchars($product['description'] ?? '-')); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="section-divider" style="display:flex;align-items:center;gap:12px;margin:24px 0 14px;">
    <span class="section-title" style="font-size:.95rem;font-weight:700;color:#1a1a2e;white-space:nowrap;"><i class="fas fa-store me-2 text-success"></i>Stock per Branch</span>
    <hr style="flex:1;margin:0;border-color:#e9ecef;">
</div>

<div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th class="ps-3">Branch</th>
                    <th class="text-center pe-3">Stock</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($branches)): ?>
                <?php foreach ($branches as $b): ?>
                    <?php
                        $bqty = (int) ($branch_stock[$b['branch_id']] ?? 0);
                        $b_badge = $bqty == 0 ? 'stock-zero' : ($bqty <= $min ? 'stock-warn' : 'stock-ok');
                    ?>
                    <tr>
                        <td class="ps-3"><i class="fas fa-store" style="color: var(--gray);"></i> <?= htmlspecialchars($b['branch_name']); ?></td>
                        <td class="text-center pe-3">
                            <span class="stock-badge <?= $b_badge; ?>"><?= number_format($bqty); ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="2" class="text-center" style="padding:30px;color:var(--gray);">No branches found</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div><!-- /container-fluid -->
