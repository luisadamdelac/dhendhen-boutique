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

.admls-empty { text-align: center; padding: 30px 10px 22px; }
.admls-empty-illustration { position: relative; width: 108px; height: 84px; margin: 0 auto 18px; }
.admls-empty-box {
    position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
    font-size: 62px;
    background: linear-gradient(135deg, #FF8CC5 0%, #FF4FA2 100%);
    -webkit-background-clip: text; background-clip: text; color: transparent;
    filter: drop-shadow(0 10px 14px rgba(255, 79, 162, .25));
}
.admls-empty-badge {
    position: absolute; right: 2px; bottom: 2px; width: 30px; height: 30px; border-radius: 50%;
    background: linear-gradient(135deg, #FF4FA2, #E0439A); color: #fff;
    display: flex; align-items: center; justify-content: center; font-size: 13px;
    box-shadow: 0 4px 10px rgba(255, 79, 162, .4); border: 3px solid #fff;
}
.admls-empty-spark { position: absolute; border-radius: 50%; background: #FFC1D9; }
.admls-empty-spark.s1 { width: 6px; height: 6px; top: 2px; left: 16px; }
.admls-empty-spark.s2 { width: 4px; height: 4px; top: 10px; right: 6px; }
.admls-empty-spark.s3 { width: 5px; height: 5px; bottom: 16px; left: 2px; }
.admls-empty-title { margin: 0; font-size: .92rem; font-weight: 600; color: #1a1a2e; }
.admls-empty-sub { margin: 2px 0 0; font-size: .92rem; font-weight: 600; color: #8a94ad; }
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
                        <th class="text-end">Price</th>
                        <th class="text-center pe-3">Action</th>
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
                            <td class="text-end fw-semibold" style="font-size:13px;">₱<?= number_format($product['price'] ?? 0, 2); ?></td>
                            <td class="text-center pe-3">
                                <button type="button" class="btn btn-sm btn-outline-success"
                                        onclick="openRestockModal(<?= (int) $product['product_id']; ?>, '<?= addslashes(htmlspecialchars($product['product_name'])); ?>')">
                                    <i class="fas fa-boxes-stacked"></i> Restock
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">
                                <div class="admls-empty">
                                    <div class="admls-empty-illustration">
                                        <i class="fas fa-box-open admls-empty-box"></i>
                                        <span class="admls-empty-badge"><i class="fas fa-triangle-exclamation"></i></span>
                                        <span class="admls-empty-spark s1"></span>
                                        <span class="admls-empty-spark s2"></span>
                                        <span class="admls-empty-spark s3"></span>
                                    </div>
                                    <p class="admls-empty-title">No low stock items right now.</p>
                                    <p class="admls-empty-sub">You're all set!</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /container-fluid -->

<!-- Quick Restock Modal -->
<div class="modal fade" id="restockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 8px 32px rgba(0,0,0,.15);">
            <form id="restockForm">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold">Quick Restock</h5>
                        <small class="text-muted" id="restockProductName"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Branch <span class="text-danger">*</span></label>
                        <select class="form-select" name="branch_id" id="restock_branch_id" required>
                            <option value="">Select branch</option>
                            <?php foreach ($branches as $b): ?>
                                <option value="<?= (int) $b['branch_id']; ?>" data-branch-name="<?= htmlspecialchars($b['branch_name']); ?>"><?= htmlspecialchars($b['branch_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="small text-muted mt-1" id="restockCurrentStockHint"></div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-semibold small">Quantity to Add <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="quantity" id="restock_quantity" min="1" step="1" required placeholder="e.g. 50">
                    </div>
                    <div class="small text-muted mt-2">
                        <i class="fas fa-info-circle"></i> This adds to the product's base stock. For products with named variations (e.g. Size, Color), restock the specific variation instead from the product's Edit page.
                    </div>
                    <div class="alert alert-danger py-2 px-3 mt-3 mb-0 d-none" id="restockError" style="font-size:12px;"></div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="restockSaveBtn">
                        <i class="fas fa-save me-1"></i> Add Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var _restockProductId = null;
var _restockBranchStock = <?= json_encode($branch_stock ?? []); ?>;

function updateRestockStockHint() {
    var sel = document.getElementById('restock_branch_id');
    var hint = document.getElementById('restockCurrentStockHint');
    var branchId = sel.value;
    if (!branchId || !_restockProductId) {
        hint.textContent = '';
        return;
    }
    var byBranch = _restockBranchStock[_restockProductId] || {};
    var qty = byBranch[branchId] ?? 0;
    var branchName = sel.options[sel.selectedIndex].getAttribute('data-branch-name');
    hint.textContent = 'Current stock at ' + branchName + ': ' + qty;
}

function openRestockModal(productId, productName) {
    _restockProductId = productId;
    document.getElementById('restockProductName').textContent = productName;
    document.getElementById('restock_branch_id').value = '';
    document.getElementById('restock_quantity').value = '';
    document.getElementById('restockError').classList.add('d-none');
    updateRestockStockHint();
    new bootstrap.Modal(document.getElementById('restockModal')).show();
}

document.getElementById('restock_branch_id').addEventListener('change', updateRestockStockHint);

document.getElementById('restockForm').addEventListener('submit', function(e) {
    e.preventDefault();
    if (!_restockProductId) return;

    var btn = document.getElementById('restockSaveBtn');
    var errBox = document.getElementById('restockError');
    errBox.classList.add('d-none');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';

    var formData = new FormData();
    formData.append('branch_id', document.getElementById('restock_branch_id').value);
    formData.append('quantity', document.getElementById('restock_quantity').value);

    fetch('<?= site_url('admin/product/quick_restock/'); ?>' + _restockProductId, {
        method: 'POST',
        body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            window.location.reload();
        } else {
            errBox.textContent = data.message || 'Failed to restock.';
            errBox.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i> Add Stock';
        }
    })
    .catch(function() {
        errBox.textContent = 'Something went wrong. Please try again.';
        errBox.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-1"></i> Add Stock';
    });
});
</script>
