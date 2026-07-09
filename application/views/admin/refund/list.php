<?php
$page_title = 'Refund Management';
$current_page = 'refund';
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

    <!-- Page Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Refund Requests</h4>
            <small class="text-muted">Review refund requests and approval status.</small>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/order_tabs.php'; ?>

    <!-- Stat Cards -->
    <div class="row g-3 mb-2">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#eef0ff;color:#4361ee;"><i class="fas fa-undo"></i></div>
                <div>
                    <div class="stat-label">Total Refund Requests</div>
                    <div class="stat-value"><?= number_format($stat_total); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff8e1;color:#f57f17;"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="stat-label">Pending</div>
                    <div class="stat-value"><?= number_format($stat_pending); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-label">Approved</div>
                    <div class="stat-value"><?= number_format($stat_approved); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ffebee;color:#c62828;"><i class="fas fa-times-circle"></i></div>
                <div>
                    <div class="stat-label">Rejected</div>
                    <div class="stat-value"><?= number_format($stat_rejected); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== REFUNDS SECTION ===================== -->
    <div class="page-section">
        <span class="section-title"><i class="fas fa-undo me-2 text-primary"></i>Refund Requests</span>
        <hr>
        <span class="text-muted small" style="white-space:nowrap;"><?= number_format($total); ?> result<?= $total != 1 ? 's' : ''; ?></span>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="row g-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label class="form-label small text-muted mb-1" for="filterRefundStatus">Filter by status</label>
                <select id="filterRefundStatus" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="col-md-2 d-flex">
                <button type="button" id="clearRefundFiltersBtn" class="btn btn-sm btn-outline-secondary flex-fill">
                    <i class="fas fa-times"></i> Clear Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Refunds Table -->
    <div class="card border-0 shadow-sm mb-2" style="border-radius:12px;overflow:hidden;">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table inv-table mb-0" id="refundsDataTable">
                    <thead>
                        <tr>
                            <th class="ps-3">Refund ID</th>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($refunds as $refund): ?>
                            <tr data-status="<?= htmlspecialchars($refund['status'] ?? 'pending'); ?>">
                                <td class="ps-3">#<?= htmlspecialchars($refund['refund_id']); ?></td>
                                <td>#<?= htmlspecialchars($refund['order_id']); ?></td>
                                <td><?= htmlspecialchars(trim(($refund['customer_first_name'] ?? '') . ' ' . ($refund['customer_last_name'] ?? '')) ?: '-'); ?></td>
                                <td>₱<?= number_format($refund['amount'] ?? 0, 2); ?></td>
                                <td class="text-center"><span class="badge-status badge-<?= $refund['status'] ?? 'pending'; ?>"><?= ucfirst($refund['status'] ?? 'pending'); ?></span></td>
                                <td class="text-center pe-3">
                                    <a href="<?= site_url('admin/refund/view/' . $refund['refund_id']); ?>"
                                       class="btn btn-sm btn-outline-info" title="View" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-eye" style="font-size:11px;"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="<?php echo BASE_URL; ?>public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
    // Excel/PDF export lives in Reports, not on this operational list page.
    const table = $('#refundsDataTable').DataTable({
        order: [[0, 'desc']],
        columnDefs: [{ orderable: false, targets: [5] }],
        language: { search: '_INPUT_', searchPlaceholder: 'Search refund, order, or customer…', emptyTable: 'No refund requests found' }
    });

    $.fn.dataTable.ext.search.push(function (settings, searchData, index) {
        if (settings.nTable.id !== 'refundsDataTable') return true;
        const statusFilter = $('#filterRefundStatus').val();
        if (!statusFilter) return true;
        const $row = $(settings.aoData[index].nTr);
        return $row.data('status') === statusFilter;
    });

    $('#filterRefundStatus').on('change', function () { table.draw(); });
    $('#clearRefundFiltersBtn').on('click', function () {
        $('#filterRefundStatus').val('');
        table.search('').draw();
    });
});
</script>
