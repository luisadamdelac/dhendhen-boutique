<?php
$page_title = 'Financial Records Report';
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
<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Financial Records Report</h4>
            <small class="text-muted">Monitor revenue, commissions, and cash flow for a selected period.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/reports/export/financial/pdf?start=' . urlencode($start) . '&end=' . urlencode($end)); ?>" class="btn btn-outline-danger btn-sm" target="_blank"><i class="fas fa-file-pdf me-1"></i>Preview PDF</a>
            <a href="<?= site_url('admin/reports/export/financial/excel?start=' . urlencode($start) . '&end=' . urlencode($end)); ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-file-excel me-1"></i>Export Excel</a>
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
                <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-dollar-sign"></i></div>
                <div>
                    <div class="stat-label">Revenue (period)</div>
                    <div class="stat-value">₱<?= number_format($revenue, 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e3f2fd;color:#1565c0;"><i class="fas fa-wallet"></i></div>
                <div>
                    <div class="stat-label">Commissions Released (period)</div>
                    <div class="stat-value">₱<?= number_format($commissions_released, 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff8e1;color:#f57f17;"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="stat-label">Commissions Pending (all time)</div>
                    <div class="stat-value">₱<?= number_format($commissions_pending, 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ede7f6;color:#5e35b1;"><i class="fas fa-money-check-alt"></i></div>
                <div>
                    <div class="stat-label">Withdrawals Completed (period)</div>
                    <div class="stat-value">₱<?= number_format($withdrawals_completed, 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#eef0ff;color:#4361ee;"><i class="fas fa-hourglass-half"></i></div>
                <div>
                    <div class="stat-label">Withdrawals Pending/Approved (all time)</div>
                    <div class="stat-value">₱<?= number_format($withdrawals_pending, 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ffebee;color:#c62828;"><i class="fas fa-undo"></i></div>
                <div>
                    <div class="stat-label">Refunds Issued (period)</div>
                    <div class="stat-value">₱<?= number_format($refunds_issued, 2); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-section">
        <span class="section-title"><i class="fas fa-receipt me-2 text-primary"></i>Payments by Status</span>
        <hr>
    </div>
    <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
        <div class="table-responsive">
            <table class="table inv-table mb-0" id="financialPaymentsTable">
                <thead><tr><th class="ps-3">Status</th><th>Count</th><th>Amount</th></tr></thead>
                <tbody>
                <?php foreach ($payments_by_status as $row): ?>
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
</div><!-- /container-fluid -->

<script src="<?php echo BASE_URL; ?>public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
    $('#financialPaymentsTable').DataTable({ language: { search: '_INPUT_', searchPlaceholder: 'Search…', emptyTable: 'No payments in this period' } });
});
</script>
