<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.css">

<div class="container-fluid py-4 fade-in">

    <!-- Hero + Stats + Resellers header — merged into a single card -->
    <div class="ds-hero-card">

        <div class="ds-hero-banner">
            <svg class="ds-hero-wave" viewBox="0 0 1440 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,110 C240,170 480,50 720,90 C960,130 1200,50 1440,100 L1440,200 L0,200 Z" fill="rgba(255,105,180,0.16)"></path>
                <path d="M0,140 C280,80 560,180 840,120 C1080,70 1280,140 1440,130 L1440,200 L0,200 Z" fill="rgba(233,30,99,0.22)"></path>
            </svg>
            <div class="ds-hero-banner-content d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:46px;height:46px;border-radius:14px;background:var(--primary-pink-dark);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Reseller Management</h4>
                        <small class="text-muted">Manage reseller accounts, commissions, and registrations.</small>
                    </div>
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
                    <a href="<?= site_url('admin/reseller/applications'); ?>" class="btn btn-outline btn-sm" title="Existing customers requesting to become a reseller">
                        <i class="fas fa-arrow-up-from-bracket me-1"></i> Upgrade Requests
                    </a>
                </div>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="ds-hero-stats">
            <div class="row g-3">
                <div class="col-6 col-md-4">
                    <div class="ds-stat-tile">
                        <div class="ds-stat-tile-icon" style="background:#eef0ff;color:#4361ee;"><i class="fas fa-users"></i></div>
                        <div>
                            <div class="ds-stat-tile-label">Total Resellers</div>
                            <div class="ds-stat-tile-value"><?= number_format($total ?? 0); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="ds-stat-tile">
                        <div class="ds-stat-tile-icon" style="background:#fff8e1;color:#f57f17;"><i class="fas fa-user-clock"></i></div>
                        <div>
                            <div class="ds-stat-tile-label">Pending Registration</div>
                            <div class="ds-stat-tile-value"><?= number_format($pending_count ?? 0); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="ds-stat-tile">
                        <div class="ds-stat-tile-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-peso-sign"></i></div>
                        <div>
                            <div class="ds-stat-tile-label">Total Commission Balance</div>
                            <div class="ds-stat-tile-value" style="font-size:18px;">₱<?= number_format(array_sum(array_column($resellers ?? [], 'commission_balance')), 2); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resellers header -->
        <div class="ds-hero-section-row">
            <span class="section-title"><i class="fas fa-users me-2 text-primary"></i>Resellers</span>
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
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex">
                    <button type="button" id="clearResellerFiltersBtn" class="btn btn-sm ds-clear-btn flex-fill">
                        <i class="fas fa-times"></i> Clear Filters
                    </button>
                </div>
            </div>
        </div>

    </div>

    <div class="card ds-pink-table-card mb-2">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table inv-table ds-pink-table mb-0 table-stack" id="resellersTable">
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
                        $fullName = htmlspecialchars(trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''))) ?: '-';
                        $initials = strtoupper(substr($r['first_name'] ?? 'R', 0, 1) . substr($r['last_name'] ?? '', 0, 1));
                    ?>
                    <tr data-status="<?= htmlspecialchars($rStatus); ?>">
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-2" style="min-width:0;">
                                <?php if (!empty($r['profile_image'])): ?>
                                    <img src="<?= BASE_URL . htmlspecialchars($r['profile_image']); ?>" alt="<?= $fullName; ?>" style="width:40px;height:40px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                                <?php else: ?>
                                    <div style="width:40px;height:40px;border-radius:50%;background:var(--gradient-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;">
                                        <?= $initials; ?>
                                    </div>
                                <?php endif; ?>
                                <div style="min-width:0;">
                                    <div class="fw-semibold" style="font-size:13px;color:#1a1a2e;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= $fullName; ?></div>
                                    <?php if (!empty($r['business_name'])): ?>
                                        <div style="font-size:11px;color:#8a94ad;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($r['business_name']); ?></div>
                                    <?php else: ?>
                                        <div style="font-size:11px;color:#8a94ad;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($r['email'] ?? ''); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size:13px;"><?= htmlspecialchars($r['email'] ?? '-'); ?></div>
                            <?php if (!empty($r['contact_number'])): ?>
                                <div style="font-size:11px;color:#8a94ad;"><?= htmlspecialchars($r['contact_number']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-end fw-semibold" style="font-size:13px;">
                            ₱<?= number_format($r['commission_balance'] ?? 0, 2); ?>
                        </td>
                        <td class="text-center">
                            <span class="badge-status badge-<?= $rStatus === 'active' ? 'active' : ($rStatus === 'rejected' ? 'rejected' : 'inactive'); ?>"><?= ucfirst($rStatus); ?></span>
                        </td>
                        <td class="text-center pe-3">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="<?= site_url('admin/reseller/view/' . $r['reseller_id']); ?>"
                                   class="ds-action-btn" title="View">
                                    <i class="fas fa-eye" style="font-size:11px;"></i>
                                </a>
                                <a href="<?= site_url('admin/reseller/edit/' . $r['reseller_id']); ?>"
                                   class="ds-action-btn" title="Edit">
                                    <i class="fas fa-edit" style="font-size:11px;"></i>
                                </a>
                                <?php if ($rStatus === 'rejected'): ?>
                                <button type="button" class="ds-action-btn btn-reapprove-reseller"
                                        data-id="<?= $r['reseller_id']; ?>" data-name="<?= $fullName; ?>"
                                        title="Re-approve">
                                    <i class="fas fa-undo" style="font-size:11px;"></i>
                                </button>
                                <?php endif; ?>
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

<!-- Re-approve Confirm Modal -->
<div class="modal" id="reapproveModal">
    <div class="modal-content modal-sm">
        <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #218838 100%); color:#fff;">
            <h3 class="modal-title">Re-approve Reseller</h3>
            <button type="button" class="modal-close" onclick="closeModal(document.getElementById('reapproveModal'))">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p id="reapproveModalBody"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" id="reapproveBtnCancel" onclick="closeModal(document.getElementById('reapproveModal'))">Cancel</button>
            <button type="button" class="btn btn-sm btn-success" id="reapproveBtnConfirm">
                <span id="reapproveBtnConfirmText">Re-approve</span>
                <span id="reapproveBtnConfirmSpinner" class="spinner-border spinner-border-sm ms-1 d-none"></span>
            </button>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.js"></script>
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

    let reapproveId = null;

    function resetReapproveBtn() {
        $('#reapproveBtnConfirm').prop('disabled', false);
        $('#reapproveBtnCancel').prop('disabled', false);
        $('#reapproveBtnConfirmText').text('Re-approve');
        $('#reapproveBtnConfirmSpinner').addClass('d-none');
    }

    $('#resellersTable').on('click', '.btn-reapprove-reseller', function () {
        reapproveId = $(this).data('id');
        const name = $(this).data('name');
        $('#reapproveModalBody').text('Re-approve ' + name + ' as a reseller?');
        resetReapproveBtn();
        openModal('reapproveModal');
    });

    $('#reapproveBtnConfirm').on('click', function () {
        if (!reapproveId) return;

        $(this).prop('disabled', true);
        $('#reapproveBtnCancel').prop('disabled', true);
        $('#reapproveBtnConfirmSpinner').removeClass('d-none');

        fetch('<?= site_url('admin/reseller/approve-registration/'); ?>' + reapproveId, { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    closeModal(document.getElementById('reapproveModal'));
                    alert(data.message || 'Action failed.');
                    resetReapproveBtn();
                    reapproveId = null;
                }
            })
            .catch(() => {
                closeModal(document.getElementById('reapproveModal'));
                alert('Request failed. Please try again.');
                resetReapproveBtn();
                reapproveId = null;
            });
    });
});
</script>
