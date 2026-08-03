<?php
$page_title = 'Sales Performance Report';
$current_page = 'reports';
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.css">
<style>
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid var(--border); border-radius: var(--radius-md);
    padding: .4rem .75rem; font-size: .85rem; margin-left: .5rem;
}
.dataTables_wrapper .dataTables_filter input:focus { outline: none; border-color: var(--primary-pink); }
table.dataTable thead th { position: relative; }
table.dataTable thead th.sorting:hover { color: var(--primary-pink); cursor: pointer; }
</style>
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Sales Performance Report</h4>
            <small class="text-muted">Revenue, orders, and top products over time.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/reports/export/sales/pdf?start=' . urlencode($start) . '&end=' . urlencode($end)); ?>" class="btn btn-outline-danger btn-sm" target="_blank"><i class="fas fa-file-pdf me-1"></i>Preview PDF</a>
            <a href="<?= site_url('admin/reports/export/sales/excel?start=' . urlencode($start) . '&end=' . urlencode($end)); ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-file-excel me-1"></i>Export Excel</a>
            <a href="<?= site_url('admin/reports'); ?>" class="btn btn-secondary btn-sm">Back to Reports</a>
        </div>
    </div>

    <div class="filter-bar">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label class="form-label small fw-semibold mb-1">From</label>
                <input type="date" name="start" class="form-control form-control-sm" value="<?= htmlspecialchars($start); ?>">
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label small fw-semibold mb-1">To</label>
                <input type="date" name="end" class="form-control form-control-sm" value="<?= htmlspecialchars($end); ?>">
            </div>
            <div class="col-md-2 col-sm-6">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </div>
        </form>
    </div>

    <div class="row g-3 mb-2">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ede7f6;color:#5e35b1;"><i class="fas fa-shopping-cart"></i></div>
                <div>
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-value"><?= number_format($summary['total_orders'] ?? 0); ?></div>
                    <small class="text-muted"><?= number_format($summary['online_orders'] ?? 0); ?> online + <?= number_format($summary['walkin_orders'] ?? 0); ?> walk-in</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-dollar-sign"></i></div>
                <div>
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-value">₱<?= number_format($summary['total_revenue'] ?? 0, 2); ?></div>
                    <small class="text-muted">₱<?= number_format($summary['online_revenue'] ?? 0, 2); ?> online + ₱<?= number_format($summary['walkin_revenue'] ?? 0, 2); ?> walk-in</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fce4ec;color:#c2185b;"><i class="fas fa-cash-register"></i></div>
                <div>
                    <div class="stat-label">Walk-in Sales</div>
                    <div class="stat-value"><?= number_format($walkin_summary['count'] ?? 0); ?></div>
                    <small class="text-muted">₱<?= number_format($walkin_summary['total'] ?? 0, 2); ?> in-store revenue</small>
                </div>
            </div>
        </div>
    </div>

    <div class="page-section">
        <span class="section-title"><i class="fas fa-list me-2 text-primary"></i>Online Orders by Status</span>
        <hr>
    </div>
    <p class="text-muted small mb-2" style="margin-top:-10px;">Walk-in (in-store) sales don't have a delivery status — see the "Walk-in Sales" figures above and the breakdown in Monthly Sales below.</p>
    <div class="card border-0 shadow-sm mb-3" style="border-radius:12px;overflow:hidden;">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table inv-table mb-0" id="salesByStatusTable">
                    <thead><tr><th class="ps-3">Status</th><th>Orders</th><th>Revenue</th></tr></thead>
                    <tbody>
                    <?php foreach ($by_status as $row): ?>
                        <tr>
                            <td class="ps-3"><?= ucfirst(str_replace('_', ' ', $row['order_status'])); ?></td>
                            <td><?= number_format($row['total']); ?></td>
                            <td>₱<?= number_format($row['revenue'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="page-section">
        <span class="section-title"><i class="fas fa-trophy me-2 text-primary"></i>Top Selling Products</span>
        <hr>
    </div>
    <div class="card border-0 shadow-sm mb-3" style="border-radius:12px;overflow:hidden;">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table inv-table mb-0" id="salesTopProductsTable">
                    <thead><tr><th class="ps-3">Product</th><th>Sold</th><th>Revenue</th></tr></thead>
                    <tbody>
                    <?php foreach ($top_products as $row): ?>
                        <tr>
                            <td class="ps-3"><?= htmlspecialchars($row['product_name']); ?></td>
                            <td><?= number_format($row['total_sold']); ?></td>
                            <td>₱<?= number_format($row['revenue'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="page-section">
        <span class="section-title"><i class="fas fa-calendar-alt me-2 text-primary"></i>Monthly Sales</span>
        <hr>
    </div>
    <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table inv-table mb-0" id="salesMonthlyTable">
                    <thead><tr><th class="ps-3">Month</th><th>Online Orders</th><th>Online Sales</th><th>Walk-in Sales</th><th>Total Sales</th></tr></thead>
                    <tbody>
                    <?php foreach ($monthly as $row): ?>
                        <tr>
                            <td class="ps-3"><?= date('F Y', strtotime($row['month'] . '-01')); ?></td>
                            <td><?= number_format($row['online_orders']); ?></td>
                            <td>₱<?= number_format($row['online_sales'], 2); ?></td>
                            <td>₱<?= number_format($row['walkin_sales'], 2); ?></td>
                            <td>₱<?= number_format($row['total_sales'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div><!-- /container-fluid -->

<script src="<?php echo BASE_URL; ?>public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
    $('#salesByStatusTable').DataTable({ language: { search: '_INPUT_', searchPlaceholder: 'Search…', emptyTable: 'No data' } });
    $('#salesMonthlyTable').DataTable({ language: { search: '_INPUT_', searchPlaceholder: 'Search…', emptyTable: 'No data' } });
    $('#salesTopProductsTable').DataTable({ language: { search: '_INPUT_', searchPlaceholder: 'Search…', emptyTable: 'No data' } });
});
</script>
