<?php
$page_title = 'Refund Report';
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
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Refund Report</h4>
            <small class="text-muted">Refund requests, approvals, and amounts for a selected period.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/reports/export/refund/pdf?start=' . urlencode($start) . '&end=' . urlencode($end)); ?>" class="btn btn-outline-danger btn-sm" target="_blank"><i class="fas fa-file-pdf me-1"></i>Preview PDF</a>
            <a href="<?= site_url('admin/reports/export/refund/excel?start=' . urlencode($start) . '&end=' . urlencode($end)); ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-file-excel me-1"></i>Export Excel</a>
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
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ffebee;color:#c62828;"><i class="fas fa-undo"></i></div>
                <div>
                    <div class="stat-label">Approved Refunds (period)</div>
                    <div class="stat-value">₱<?= number_format($total_refunded, 2); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-section">
        <span class="section-title"><i class="fas fa-chart-pie me-2 text-primary"></i>By Status</span>
        <hr>
    </div>
    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;overflow:hidden;">
        <div class="table-responsive">
            <table class="table inv-table mb-0" id="refundByStatusTable">
                <thead><tr><th class="ps-3">Status</th><th>Count</th><th>Amount</th></tr></thead>
                <tbody>
                <?php foreach ($by_status as $row): ?>
                    <tr>
                        <td class="ps-3"><?= ucfirst($row['status']); ?></td>
                        <td><?= number_format($row['total']); ?></td>
                        <td>₱<?= number_format($row['amount'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="page-section">
        <span class="section-title"><i class="fas fa-users me-2 text-primary"></i>By Reseller</span>
        <hr>
    </div>
    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;overflow:hidden;">
        <div class="table-responsive">
            <table class="table inv-table mb-0" id="refundByResellerTable">
                <thead><tr><th class="ps-3">Reseller</th><th>Requests</th><th>Approved Amount</th></tr></thead>
                <tbody>
                <?php foreach ($by_reseller as $row): ?>
                    <tr>
                        <td class="ps-3"><?= htmlspecialchars($row['business_name'] ?: trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: '-'); ?></td>
                        <td><?= number_format($row['total_requests']); ?></td>
                        <td>₱<?= number_format($row['approved_amount'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="page-section">
        <span class="section-title"><i class="fas fa-list me-2 text-primary"></i>Recent Requests</span>
        <hr>
    </div>
    <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
        <div class="table-responsive">
            <table class="table inv-table mb-0" id="refundRecentTable">
                <thead><tr><th class="ps-3">Refund #</th><th>Order</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($recent_refunds as $row): ?>
                    <tr>
                        <td class="ps-3"><?= htmlspecialchars($row['refund_number']); ?></td>
                        <td>#<?= htmlspecialchars($row['order_number'] ?? $row['order_id']); ?></td>
                        <td><?= htmlspecialchars(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: '-'); ?></td>
                        <td>₱<?= number_format($row['amount'], 2); ?></td>
                        <td><?= ucfirst($row['status']); ?></td>
                        <td><?= date('M d, Y', strtotime($row['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div><!-- /container-fluid -->

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
    $('#refundByStatusTable').DataTable({ language: { search: '_INPUT_', searchPlaceholder: 'Search…', emptyTable: 'No refund requests in this period' } });
    $('#refundByResellerTable').DataTable({ language: { search: '_INPUT_', searchPlaceholder: 'Search reseller…', emptyTable: 'No reseller refund activity in this period' } });
    $('#refundRecentTable').DataTable({ order: [[5, 'desc']], language: { search: '_INPUT_', searchPlaceholder: 'Search refund, order, or customer…', emptyTable: 'No refund requests in this period' } });
});
</script>
