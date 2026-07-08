<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
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

    <!-- Page Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Reseller Management</h4>
            <small class="text-muted">Manage reseller accounts, commissions, and registrations.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/reseller/pending-registrations'); ?>" class="btn btn-warning btn-sm" style="position:relative;" title="Users who signed up directly as a reseller">
                <i class="fas fa-user-clock me-1"></i> Pending Registrations
                <?php if (!empty($pending_count) && $pending_count > 0): ?>
                    <span style="position:absolute;top:-6px;right:-6px;background:#ef4444;color:#fff;border-radius:50%;width:18px;height:18px;font-size:10px;display:flex;align-items:center;justify-content:center;font-weight:700;line-height:1;">
                        <?= $pending_count > 9 ? '9+' : $pending_count; ?>
                    </span>
                <?php endif; ?>
            </a>
            <a href="<?= site_url('admin/reseller/applications'); ?>" class="btn btn-outline-secondary btn-sm" title="Existing customers requesting to become a reseller">
                <i class="fas fa-arrow-up-from-bracket me-1"></i> Upgrade Requests
            </a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-2">
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#eef0ff;color:#4361ee;"><i class="fas fa-users"></i></div>
                <div>
                    <div class="stat-label">Total Resellers</div>
                    <div class="stat-value"><?= number_format($total ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff8e1;color:#f57f17;"><i class="fas fa-user-clock"></i></div>
                <div>
                    <div class="stat-label">Pending Registration</div>
                    <div class="stat-value"><?= number_format($pending_count ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-peso-sign"></i></div>
                <div>
                    <div class="stat-label">Total Commission Balance</div>
                    <div class="stat-value" style="font-size:18px;">₱<?= number_format(array_sum(array_column($resellers ?? [], 'commission_balance')), 2); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resellers Table -->
    <div class="page-section">
        <span class="section-title"><i class="fas fa-users me-2 text-primary"></i>Resellers</span>
        <hr>
        <span class="text-muted small" style="white-space:nowrap;"><?= number_format($total ?? 0); ?> result<?= ($total ?? 0) != 1 ? 's' : ''; ?></span>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="row g-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label class="form-label small text-muted mb-1">Status</label>
                <select id="filterResellerStatus" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-2 d-flex">
                <button type="button" id="clearResellerFiltersBtn" class="btn btn-sm btn-outline-secondary flex-fill">
                    <i class="fas fa-times"></i> Clear Filters
                </button>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-2" style="border-radius:12px;overflow:hidden;">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table inv-table mb-0" id="resellersTable">
                    <thead>
                        <tr>
                            <th class="ps-3" style="width:35%;">Reseller</th>
                            <th>Contact</th>
                            <th class="text-end">Commission Balance</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($resellers as $r): ?>
                    <?php
                        $rStatus  = $r['status'] ?? 'active';
                        $fullName = htmlspecialchars(trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''))) ?: '—';
                        $initials = strtoupper(substr($r['first_name'] ?? 'R', 0, 1) . substr($r['last_name'] ?? '', 0, 1));
                    ?>
                    <tr data-status="<?= htmlspecialchars($rStatus); ?>">
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:40px;height:40px;border-radius:50%;background:var(--gradient-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;">
                                    <?= $initials; ?>
                                </div>
                                <div>
                                    <div class="fw-semibold" style="font-size:13px;color:#1a1a2e;"><?= $fullName; ?></div>
                                    <?php if (!empty($r['business_name'])): ?>
                                        <div style="font-size:11px;color:#8a94ad;"><?= htmlspecialchars($r['business_name']); ?></div>
                                    <?php else: ?>
                                        <div style="font-size:11px;color:#8a94ad;"><?= htmlspecialchars($r['email'] ?? ''); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size:13px;"><?= htmlspecialchars($r['email'] ?? '—'); ?></div>
                            <?php if (!empty($r['contact_number'])): ?>
                                <div style="font-size:11px;color:#8a94ad;"><?= htmlspecialchars($r['contact_number']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-end fw-semibold" style="font-size:13px;">
                            ₱<?= number_format($r['commission_balance'] ?? 0, 2); ?>
                        </td>
                        <td class="text-center">
                            <span class="badge-status <?= $rStatus === 'active' ? 'badge-active' : 'badge-inactive'; ?>"><?= ucfirst($rStatus); ?></span>
                        </td>
                        <td class="text-center pe-3">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="<?= site_url('admin/reseller/view/' . $r['reseller_id']); ?>"
                                   class="btn btn-sm btn-outline-info" title="View" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-eye" style="font-size:11px;"></i>
                                </a>
                                <a href="<?= site_url('admin/reseller/edit/' . $r['reseller_id']); ?>"
                                   class="btn btn-sm btn-outline-warning" title="Edit" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-edit" style="font-size:11px;"></i>
                                </a>
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

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
    // Excel/PDF export lives in Reports, not on this operational list page.
    const table = $('#resellersTable').DataTable({
        columnDefs: [{ orderable: false, targets: [4] }],
        language: { search: '_INPUT_', searchPlaceholder: 'Search name, business, or email…' }
    });

    $.fn.dataTable.ext.search.push(function (settings, searchData, index) {
        if (settings.nTable.id !== 'resellersTable') return true;
        const statusFilter = $('#filterResellerStatus').val();
        if (!statusFilter) return true;
        const $row = $(settings.aoData[index].nTr);
        return $row.data('status') === statusFilter;
    });

    $('#filterResellerStatus').on('change', function () { table.draw(); });
    $('#clearResellerFiltersBtn').on('click', function () {
        $('#filterResellerStatus').val('');
        table.search('').draw();
    });
});
</script>
