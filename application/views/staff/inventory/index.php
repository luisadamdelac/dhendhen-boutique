<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<style>
.hdr-pill { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border-radius:20px; font-size:12.5px; font-weight:600; background:#fff; border:1px solid #fbc02d; color:#a66a00; text-decoration:none; transition:box-shadow .15s; }
.hdr-pill:hover { color:#a66a00; box-shadow:0 2px 8px rgba(0,0,0,.08); }
.hdr-pill .badge { margin-left:2px; }

.btn-icon-sm {
    width: 30px; height: 30px; padding: 0; border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    border: 1.5px solid; background: transparent; cursor: pointer; font-size: 11px;
    transition: background-color .15s, color .15s;
}
.btn-icon-info    { border-color: #17a2b8; color: #17a2b8; }
.btn-icon-info:hover    { background: #17a2b8; color: #fff; }
.btn-icon-warning { border-color: #ffc107; color: #856404; }
.btn-icon-warning:hover { background: #ffc107; color: #333; }

.staff-modal-overlay {
    display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center;
}
.staff-modal-box {
    background: #fff; border-radius: 16px; max-width: 440px; width: 90%; margin: auto;
    padding: 28px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
.staff-modal-box h3 { margin-bottom: 18px; font-size: 18px; color: #1a1a2e; }
.staff-modal-box .detail-row {
    display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; font-size: 14px;
}
.staff-modal-box .detail-row:last-child { border-bottom: none; }
.staff-modal-actions { display: flex; gap: 10px; margin-top: 20px; }
</style>

<div class="ds-hero-card mb-3">

    <div class="ds-hero-banner">
        <svg class="ds-hero-wave" viewBox="0 0 1440 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,110 C240,170 480,50 720,90 C960,130 1200,50 1440,100 L1440,200 L0,200 Z" fill="rgba(255,105,180,0.16)"></path>
            <path d="M0,140 C280,80 560,180 840,120 C1080,70 1280,140 1440,130 L1440,200 L0,200 Z" fill="rgba(233,30,99,0.22)"></path>
        </svg>
        <div class="ds-hero-banner-content d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Inventory</h4>
                <small class="text-muted">Welcome back, <strong><?php echo htmlspecialchars($user_full_name ?? 'Staff'); ?></strong></small>
            </div>
            <div class="d-flex gap-2">
                <a href="<?php echo site_url('staff/inventory/low_stock'); ?>" class="hdr-pill">
                    <i class="fas fa-exclamation-triangle"></i> Low Stock
                    <?php if (!empty($product_stats['low_stock_products'])): ?>
                        <span class="badge bg-danger"><?php echo (int) $product_stats['low_stock_products']; ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo site_url('staff/inventory/expiring'); ?>" class="hdr-pill">
                    <i class="fas fa-calendar-times"></i> Expiring
                    <?php if (!empty($product_stats['expiring_products'])): ?>
                        <span class="badge bg-danger"><?php echo (int) $product_stats['expiring_products']; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>

    <div class="ds-hero-stats">
        <div class="row g-3">
            <div class="col-6 col-md-4">
                <div class="ds-stat-tile">
                    <div class="ds-stat-tile-icon" style="background:#eef0ff;color:#4361ee;"><i class="fas fa-boxes"></i></div>
                    <div>
                        <div class="ds-stat-tile-label">Total Products</div>
                        <div class="ds-stat-tile-value"><?php echo number_format($product_stats['total_products'] ?? 0); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="ds-stat-tile">
                    <div class="ds-stat-tile-icon" style="background:#fff8e1;color:#f57f17;"><i class="fas fa-exclamation-triangle"></i></div>
                    <div>
                        <div class="ds-stat-tile-label">Low Stock</div>
                        <div class="ds-stat-tile-value" style="<?php echo !empty($product_stats['low_stock_products']) ? 'color:#f57f17;' : ''; ?>"><?php echo number_format($product_stats['low_stock_products'] ?? 0); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="ds-stat-tile">
                    <div class="ds-stat-tile-icon" style="background:#fdecea;color:#c62828;"><i class="fas fa-times-circle"></i></div>
                    <div>
                        <div class="ds-stat-tile-label">Out of Stock</div>
                        <div class="ds-stat-tile-value"><?php echo number_format($product_stats['out_of_stock_products'] ?? 0); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ds-hero-section-row">
        <span class="section-title"><i class="fas fa-boxes me-2 text-primary"></i>Products</span>
        <span class="text-muted small" style="white-space:nowrap;"><?php echo number_format(count($products)); ?> result<?php echo count($products) != 1 ? 's' : ''; ?></span>
    </div>

</div>

<div class="row">
    <div class="col col-12">
        <div class="card ds-pink-table-card mb-2">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table inv-table ds-pink-table table-stack" id="staffInventoryTable">
                        <thead>
                            <tr><th></th><th>Product</th><th>SKU</th><th>Branch</th><th>Category</th><th>Stock</th><th>Expiry</th><th>Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <?php
                                    $productJson = htmlspecialchars(json_encode([
                                        'id' => $product['product_id'],
                                        'name' => $product['product_name'],
                                        'sku' => $product['sku'],
                                        'branch' => $product['branch_name'] ?? '-',
                                        'category' => $product['category_name'] ?? '-',
                                        'price' => (float) ($product['price'] ?? 0),
                                        'stock' => (int) $product['stock'],
                                        'status' => $product['status'],
                                        'variants' => $product['variants'] ?? [],
                                    ]), ENT_QUOTES);
                                ?>
                                <tr id="product-row-<?php echo $product['product_id']; ?>">
                                    <td>
                                        <?php if (!empty($product['product_image'])): ?>
                                            <img src="<?php echo BASE_URL . htmlspecialchars($product['product_image']); ?>" alt="" style="width:36px;height:36px;object-fit:cover;border-radius:6px;">
                                        <?php else: ?>
                                            <div style="width:36px;height:36px;border-radius:6px;background:#f0f2f8;display:flex;align-items:center;justify-content:center;color:#b0b8d4;">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td><code><?php echo htmlspecialchars($product['sku']); ?></code></td>
                                    <td><?php echo htmlspecialchars($product['branch_name'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name'] ?? '-'); ?></td>
                                    <td id="stock-<?php echo $product['product_id']; ?>"><?php echo (int) $product['stock']; ?></td>
                                    <td>
                                        <?php if (!empty($product['expiry_date'])): ?>
                                            <?php $daysLeft = (int) ceil((strtotime($product['expiry_date']) - strtotime(date('Y-m-d'))) / 86400); ?>
                                            <span class="small" style="color:#1a1a2e;"><?php echo date('M j, Y', strtotime($product['expiry_date'])); ?></span><br>
                                            <span style="font-size:10.5px;font-weight:600;color:<?php echo $daysLeft <= 30 ? '#dc3545' : '#8a94ad'; ?>;">
                                                <?php echo $daysLeft < 0 ? 'Expired' : $daysLeft . ' days left'; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $product['status'] === 'available' ? 'success' : 'secondary'; ?>">
                                            <?php echo $product['status'] === 'available' ? 'Available' : 'Not Available'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <button type="button" class="ds-action-btn" title="View" data-product="<?php echo $productJson; ?>" onclick="openViewModal(this)">
                                                <i class="fas fa-eye" style="font-size:11px;"></i>
                                            </button>
                                            <button type="button" class="ds-action-btn" title="Edit Stock" data-product="<?php echo $productJson; ?>" onclick="openEditStockModal(this)">
                                                <i class="fas fa-edit" style="font-size:11px;"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Product Modal -->
<div class="staff-modal-overlay" id="viewProductModal">
    <div class="staff-modal-box">
        <h3><i class="fas fa-box"></i> Product Details</h3>
        <div class="detail-row"><span>Product</span><strong id="vpName">-</strong></div>
        <div class="detail-row"><span>SKU</span><strong id="vpSku">-</strong></div>
        <div class="detail-row"><span>Branch</span><strong id="vpBranch">-</strong></div>
        <div class="detail-row"><span>Category</span><strong id="vpCategory">-</strong></div>
        <div class="detail-row"><span>Price</span><strong id="vpPrice">-</strong></div>
        <div class="detail-row"><span>Stock (this branch)</span><strong id="vpStock">-</strong></div>
        <div class="detail-row"><span>Status</span><strong id="vpStatus">-</strong></div>
        <div class="staff-modal-actions">
            <button type="button" class="btn btn-outline flex-fill" onclick="closeStaffModal('viewProductModal')">Close</button>
        </div>
    </div>
</div>

<!-- Edit Stock Modal -->
<div class="staff-modal-overlay" id="editStockModal">
    <div class="staff-modal-box">
        <h3><i class="fas fa-edit"></i> Edit Stock</h3>
        <p class="text-muted" style="font-size:13px;margin-bottom:12px;" id="esProductLabel"></p>
        <div class="form-group" id="esVariantGroup" style="display:none;">
            <label for="esVariant">Combination</label>
            <select class="form-control" id="esVariant"></select>
        </div>
        <div class="form-group">
            <label for="esQuantity">New Stock Quantity</label>
            <input type="number" class="form-control" id="esQuantity" min="0" required>
        </div>
        <input type="hidden" id="esProductId">
        <div class="staff-modal-actions">
            <button type="button" class="btn btn-outline flex-fill" onclick="closeStaffModal('editStockModal')">Cancel</button>
            <button type="button" class="btn btn-primary flex-fill" id="esSaveBtn" onclick="submitEditStock()">
                <i class="fas fa-save"></i> Save
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
    $('#staffInventoryTable').DataTable({
        columnDefs: [{ orderable: false, targets: [0, 8] }],
        language: { search: '_INPUT_', searchPlaceholder: 'Search product or SKU…', emptyTable: 'No products found' },
        drawCallback: function() { if (typeof initResponsiveTableStacking === 'function') initResponsiveTableStacking(); }
    });
});

function closeStaffModal(id) {
    document.getElementById(id).style.display = 'none';
}

function openViewModal(btn) {
    const p = JSON.parse(btn.dataset.product);
    document.getElementById('vpName').textContent = p.name;
    document.getElementById('vpSku').textContent = p.sku;
    document.getElementById('vpBranch').textContent = p.branch;
    document.getElementById('vpCategory').textContent = p.category;
    document.getElementById('vpPrice').textContent = '₱' + Number(p.price).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('vpStock').textContent = p.stock + ' units';
    document.getElementById('vpStatus').textContent = p.status === 'available' ? 'Available' : 'Not Available';
    document.getElementById('viewProductModal').style.display = 'flex';
}

function openEditStockModal(btn) {
    const p = JSON.parse(btn.dataset.product);
    document.getElementById('esProductId').value = p.id;
    document.getElementById('esProductLabel').textContent = p.name + ' (' + p.sku + ') — ' + p.branch;

    const variantGroup = document.getElementById('esVariantGroup');
    const variantSelect = document.getElementById('esVariant');
    variantSelect.innerHTML = '';

    if (p.variants && p.variants.length) {
        variantGroup.style.display = 'block';
        const baseOpt = new Option('Base product (no combination)', '');
        baseOpt.dataset.stock = p.stock;
        variantSelect.appendChild(baseOpt);
        p.variants.forEach(v => {
            const opt = new Option(v.label, v.variant_id);
            opt.dataset.stock = v.stock;
            variantSelect.appendChild(opt);
        });
        variantSelect.onchange = function() {
            document.getElementById('esQuantity').value = this.selectedOptions[0].dataset.stock;
        };
        document.getElementById('esQuantity').value = p.stock;
    } else {
        variantGroup.style.display = 'none';
        document.getElementById('esQuantity').value = p.stock;
    }

    document.getElementById('editStockModal').style.display = 'flex';
}

function submitEditStock() {
    const productId = document.getElementById('esProductId').value;
    const variantId = document.getElementById('esVariant').value;
    const quantity = document.getElementById('esQuantity').value;
    const btn = document.getElementById('esSaveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';

    fetch('<?php echo site_url("staff/inventory/set_stock"); ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + encodeURIComponent(productId) + '&quantity=' + encodeURIComponent(quantity) + '&variant_id=' + encodeURIComponent(variantId)
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Save';
                alert(data.message || 'Failed to update stock');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Save';
            alert('Request failed');
        });
}
</script>
