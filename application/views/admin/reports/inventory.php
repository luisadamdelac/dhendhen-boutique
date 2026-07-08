<?php
$page_title = 'Inventory Status Report';
$current_page = 'reports';
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<style>
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid var(--border); border-radius: var(--radius-md);
    padding: .4rem .75rem; font-size: .85rem; margin-left: .5rem;
}
.dataTables_wrapper .dataTables_filter input:focus { outline: none; border-color: var(--primary-pink); }
table.dataTable thead th { position: relative; }
table.dataTable thead th.sorting:hover { color: var(--primary-pink); cursor: pointer; }
</style>
<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Inventory Status Report</h4>
            <small class="text-muted">Review stock levels, branches, categories, and low inventory.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/reports/export/inventory/pdf?threshold=' . urlencode($threshold)); ?>" class="btn btn-outline-danger btn-sm" target="_blank"><i class="fas fa-file-pdf me-1"></i>Preview PDF</a>
            <a href="<?= site_url('admin/reports/export/inventory/excel?threshold=' . urlencode($threshold)); ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-file-excel me-1"></i>Export Excel</a>
            <a href="<?= site_url('admin/reports'); ?>" class="btn btn-secondary btn-sm">Back to Reports</a>
        </div>
    </div>

    <div class="row g-3 mb-2">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#eef0ff;color:#4361ee;"><i class="fas fa-boxes"></i></div>
                <div>
                    <div class="stat-label">Total Products</div>
                    <div class="stat-value"><?= number_format($summary['total_products']); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-label">Available</div>
                    <div class="stat-value"><?= number_format($summary['available']); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff8e1;color:#f57f17;"><i class="fas fa-exclamation-triangle"></i></div>
                <div>
                    <div class="stat-label">Low Stock (&le;<?= $threshold; ?>)</div>
                    <div class="stat-value"><?= number_format($summary['low_stock']); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ffebee;color:#c62828;"><i class="fas fa-ban"></i></div>
                <div>
                    <div class="stat-label">Out of Stock</div>
                    <div class="stat-value"><?= number_format($summary['out_of_stock']); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-section">
        <span class="section-title"><i class="fas fa-store me-2 text-primary"></i>Stock by Branch</span>
        <hr>
    </div>
    <div class="card border-0 shadow-sm mb-3" style="border-radius:12px;overflow:hidden;">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table inv-table mb-0" id="inventoryByBranchTable">
                    <thead><tr><th class="ps-3">Branch</th><th>Products</th><th>Total Stock</th></tr></thead>
                    <tbody>
                    <?php foreach ($by_branch as $row): ?>
                        <tr>
                            <td class="ps-3"><?= htmlspecialchars($row['branch_name']); ?></td>
                            <td><?= number_format($row['total_products']); ?></td>
                            <td><?= number_format($row['total_stock']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="page-section">
        <span class="section-title"><i class="fas fa-tags me-2 text-primary"></i>Stock by Category</span>
        <hr>
    </div>
    <div class="card border-0 shadow-sm mb-3" style="border-radius:12px;overflow:hidden;">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table inv-table mb-0" id="inventoryByCategoryTable">
                    <thead><tr><th class="ps-3">Category</th><th>Products</th><th>Total Stock</th></tr></thead>
                    <tbody>
                    <?php foreach ($by_category as $row): ?>
                        <tr>
                            <td class="ps-3"><?= htmlspecialchars($row['category_name']); ?></td>
                            <td><?= number_format($row['total_products']); ?></td>
                            <td><?= number_format($row['total_stock']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="page-section">
        <span class="section-title"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Low / Out of Stock Products</span>
        <hr>
    </div>
    <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table inv-table mb-0" id="inventoryLowStockTable">
                    <thead><tr><th class="ps-3">SKU</th><th>Product</th><th>Category</th><th>Stock</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($low_stock_products as $p): ?>
                            <tr>
                                <td class="ps-3"><?= htmlspecialchars($p['sku']); ?></td>
                                <td><?= htmlspecialchars($p['product_name']); ?></td>
                                <td><?= htmlspecialchars($p['category_name'] ?? '-'); ?></td>
                                <td><?= number_format($p['stock']); ?></td>
                                <td>
                                    <span class="badge-status <?= $p['stock'] == 0 ? 'badge-out_of_stock' : 'badge-pending'; ?>">
                                        <?= $p['stock'] == 0 ? 'Out of Stock' : 'Low Stock'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div><!-- /container-fluid -->

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
    $('#inventoryByBranchTable').DataTable({ language: { search: '_INPUT_', searchPlaceholder: 'Search…', emptyTable: 'No branches found' } });
    $('#inventoryByCategoryTable').DataTable({ language: { search: '_INPUT_', searchPlaceholder: 'Search…', emptyTable: 'No categories found' } });
    $('#inventoryLowStockTable').DataTable({ language: { search: '_INPUT_', searchPlaceholder: 'Search product or SKU…', emptyTable: 'No low-stock products' } });
});
</script>
