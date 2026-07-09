<?php
$page_title = 'Reseller Activity Report';
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
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Reseller Activity Report</h4>
            <small class="text-muted">Per-reseller sales and commission performance.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/reports/export/reseller_activity/pdf'); ?>" class="btn btn-outline-danger btn-sm" target="_blank"><i class="fas fa-file-pdf me-1"></i>Preview PDF</a>
            <a href="<?= site_url('admin/reports/export/reseller_activity/excel'); ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-file-excel me-1"></i>Export Excel</a>
            <a href="<?= site_url('admin/reports'); ?>" class="btn btn-secondary btn-sm">Back to Reports</a>
        </div>
    </div>

    <div class="row g-3 mb-2">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#eef0ff;color:#4361ee;"><i class="fas fa-users"></i></div>
                <div>
                    <div class="stat-label">Total Resellers</div>
                    <div class="stat-value"><?= number_format($total_resellers); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-wallet"></i></div>
                <div>
                    <div class="stat-label">Total Commission Paid Out</div>
                    <div class="stat-value">₱<?= number_format($total_commission_paid, 2); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-section">
        <span class="section-title"><i class="fas fa-users me-2 text-primary"></i>Resellers</span>
        <hr>
    </div>
    <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
        <div class="table-responsive">
            <table class="table inv-table mb-0" id="resellerActivityTable">
                <thead>
                    <tr>
                        <th class="ps-3">Reseller</th><th>Orders</th><th>Total Sales</th>
                        <th>Earned</th><th>Withdrawn</th><th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resellers as $r): ?>
                        <tr>
                            <td class="ps-3">
                                <strong><?= htmlspecialchars(trim($r['first_name'] . ' ' . $r['last_name'])); ?></strong>
                                <?php if (!empty($r['business_name'])): ?><br><small class="text-muted"><?= htmlspecialchars($r['business_name']); ?></small><?php endif; ?>
                            </td>
                            <td><?= number_format($r['total_orders']); ?></td>
                            <td>₱<?= number_format($r['total_sales'], 2); ?></td>
                            <td>₱<?= number_format($r['total_commission_earned'], 2); ?></td>
                            <td>₱<?= number_format($r['total_withdrawn'], 2); ?></td>
                            <td><span class="badge-status badge-completed">₱<?= number_format($r['commission_balance'], 2); ?></span></td>
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
    $('#resellerActivityTable').DataTable({ language: { search: '_INPUT_', searchPlaceholder: 'Search reseller…', emptyTable: 'No resellers found' } });
});
</script>
