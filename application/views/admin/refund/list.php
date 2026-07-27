<?php
$page_title = 'Refund Management';
$current_page = 'refund';
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.css">
<div class="container-fluid py-4 fade-in">

    <?php include __DIR__ . '/../partials/order_tabs.php'; ?>

    <!-- Hero + Stats + Refunds header — merged into a single card -->
    <div class="ds-hero-card">

        <div class="ds-hero-banner">
            <svg class="ds-hero-wave" viewBox="0 0 1440 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,110 C240,170 480,50 720,90 C960,130 1200,50 1440,100 L1440,200 L0,200 Z" fill="rgba(255,105,180,0.16)"></path>
                <path d="M0,140 C280,80 560,180 840,120 C1080,70 1280,140 1440,130 L1440,200 L0,200 Z" fill="rgba(233,30,99,0.22)"></path>
            </svg>
            <div class="ds-hero-banner-content">
                <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Refund Requests</h4>
                <small class="text-muted">Review refund requests and approval status.</small>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="ds-hero-stats">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="ds-stat-tile">
                        <div class="ds-stat-tile-icon" style="background:#eef0ff;color:#4361ee;"><i class="fas fa-undo"></i></div>
                        <div>
                            <div class="ds-stat-tile-label">Total Refund Requests</div>
                            <div class="ds-stat-tile-value"><?= number_format($stat_total); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="ds-stat-tile">
                        <div class="ds-stat-tile-icon" style="background:#fff8e1;color:#f57f17;"><i class="fas fa-clock"></i></div>
                        <div>
                            <div class="ds-stat-tile-label">Pending</div>
                            <div class="ds-stat-tile-value"><?= number_format($stat_pending); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="ds-stat-tile">
                        <div class="ds-stat-tile-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-check-circle"></i></div>
                        <div>
                            <div class="ds-stat-tile-label">Approved</div>
                            <div class="ds-stat-tile-value"><?= number_format($stat_approved); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="ds-stat-tile">
                        <div class="ds-stat-tile-icon" style="background:#ffebee;color:#c62828;"><i class="fas fa-times-circle"></i></div>
                        <div>
                            <div class="ds-stat-tile-label">Rejected</div>
                            <div class="ds-stat-tile-value"><?= number_format($stat_rejected); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Refunds header -->
        <div class="ds-hero-section-row">
            <span class="section-title"><i class="fas fa-undo me-2 text-primary"></i>Refund Requests</span>
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
                    <button type="button" id="clearRefundFiltersBtn" class="btn btn-sm ds-clear-btn flex-fill">
                        <i class="fas fa-times"></i> Clear Filters
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Refunds Table -->
    <div class="card ds-pink-table-card mb-2">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table inv-table ds-pink-table mb-0" id="refundsDataTable">
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
                                       class="ds-action-btn" title="View">
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
