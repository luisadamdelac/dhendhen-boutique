<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.css">
<style>
/* Page-specific styling not covered by the shared design system */
.avatar-placeholder {
    width: 38px; height: 38px; border-radius: 8px;
    background: #f0f2f8; display: flex; align-items: center;
    justify-content: center; color: #b0b8d4; font-size: 16px; flex-shrink: 0;
}
</style>

<div class="container-fluid py-4">

    <!-- Hero + Stats + Staff header — merged into a single card -->
    <div class="ds-hero-card">

        <div class="ds-hero-banner">
            <svg class="ds-hero-wave" viewBox="0 0 1440 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,110 C240,170 480,50 720,90 C960,130 1200,50 1440,100 L1440,200 L0,200 Z" fill="rgba(255,105,180,0.16)"></path>
                <path d="M0,140 C280,80 560,180 840,120 C1080,70 1280,140 1440,130 L1440,200 L0,200 Z" fill="rgba(233,30,99,0.22)"></path>
            </svg>
            <div class="ds-hero-banner-content d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:46px;height:46px;border-radius:14px;background:var(--primary-pink-dark);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Staff Management</h4>
                        <small class="text-muted">Manage staff accounts and access for your store.</small>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?= site_url('admin/staff/add'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Add Staff
                    </a>
                </div>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="ds-hero-stats">
            <div class="row g-3">
                <div class="col-6 col-md-4">
                    <div class="ds-stat-tile">
                        <div class="ds-stat-tile-icon" style="background:#eef0ff;color:#4361ee;"><i class="fas fa-user-tie"></i></div>
                        <div>
                            <div class="ds-stat-tile-label">Total Staff</div>
                            <div class="ds-stat-tile-value"><?= number_format($stat_total_staff); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="ds-stat-tile">
                        <div class="ds-stat-tile-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-check-circle"></i></div>
                        <div>
                            <div class="ds-stat-tile-label">Active</div>
                            <div class="ds-stat-tile-value" style="color:#2e7d32;"><?= number_format($stat_active_staff); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="ds-stat-tile">
                        <div class="ds-stat-tile-icon" style="background:#f0f0f8;color:#5a5a7a;"><i class="fas fa-ban"></i></div>
                        <div>
                            <div class="ds-stat-tile-label">Inactive</div>
                            <div class="ds-stat-tile-value" style="color:#5a5a7a;"><?= number_format($stat_inactive_staff); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff header -->
        <div class="ds-hero-section-row">
            <span class="section-title"><i class="fas fa-user-tie me-2 text-primary"></i>Staff</span>
            <span class="text-muted small" style="white-space:nowrap;"><?= number_format($total ?? 0); ?> result<?= ($total ?? 0) != 1 ? 's' : ''; ?></span>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select id="filterStaffStatus" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex">
                    <button type="button" id="clearStaffFiltersBtn" class="btn btn-sm ds-clear-btn flex-fill">
                        <i class="fas fa-times"></i> Clear Filters
                    </button>
                </div>
            </div>
        </div>

    </div>

    <div class="card ds-pink-table-card mb-2">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table inv-table ds-pink-table mb-0" id="staffTable">
                <thead>
                    <tr>
                        <th class="ps-3">Staff Member</th>
                        <th>Contact</th>
                        <th>Branch</th>
                        <th>Status</th>
                        <th class="text-center pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                        <?php foreach ($staffs as $staff): ?>
                        <?php
                            $initials = strtoupper(substr($staff['first_name'] ?? 'S', 0, 1) . substr($staff['last_name'] ?? '', 0, 1));
                            $isActive = ($staff['status'] ?? 'active') === 'active';
                        ?>
                        <tr data-status="<?= $isActive ? 'active' : 'inactive'; ?>">
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (!empty($staff['profile_image'])): ?>
                                        <img src="<?= BASE_URL . htmlspecialchars($staff['profile_image']); ?>" alt="" style="width:38px;height:38px;border-radius:8px;object-fit:cover;flex-shrink:0;">
                                    <?php else: ?>
                                        <div class="avatar-placeholder" style="background:linear-gradient(135deg,#4e73df,#224abe);color:#fff;font-weight:700;">
                                            <?= $initials; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-semibold" style="font-size:13px;color:#1a1a2e;">
                                            <?= htmlspecialchars(trim(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? ''))); ?>
                                        </div>
                                        <div style="font-size:11px;color:#8a94ad;">
                                            <?= htmlspecialchars($staff['email'] ?? ''); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($staff['contact_number'] ?? '-'); ?></td>
                            <td>
                                <?php if (!empty($staff['branch_name'])): ?>
                                    <div style="font-size:13px;color:#1a1a2e;"><?= htmlspecialchars($staff['branch_name']); ?></div>
                                    <?php if (!empty($staff['branch_city'])): ?>
                                        <div style="font-size:11px;color:#8a94ad;"><?= htmlspecialchars($staff['branch_city']); ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge-status <?= $isActive ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?= $isActive ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="text-center pe-3">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="<?= site_url('admin/staff/view/' . $staff['staff_id']); ?>"
                                       class="ds-action-btn" title="View">
                                        <i class="fas fa-eye" style="font-size:11px;"></i>
                                    </a>
                                    <a href="<?= site_url('admin/staff/edit/' . $staff['staff_id']); ?>"
                                       class="ds-action-btn" title="Edit">
                                        <i class="fas fa-edit" style="font-size:11px;"></i>
                                    </a>
                                    <button type="button" class="ds-action-btn delete-staff"
                                            data-id="<?= $staff['staff_id']; ?>"
                                            data-name="<?= htmlspecialchars(trim(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? ''))); ?>"
                                            title="Delete">
                                        <i class="fas fa-trash" style="font-size:11px;"></i>
                                    </button>
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

<!-- Delete Confirm Modal -->
<div class="modal" id="deleteStaffModal">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-trash"></i> Delete Staff?</h3>
            <button class="modal-close" onclick="closeModal(document.getElementById('deleteStaffModal'))">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" style="text-align:center;">
            <p><strong id="deleteStaffName"></strong> will be removed permanently.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal(document.getElementById('deleteStaffModal'))">Cancel</button>
            <button class="btn btn-danger" id="confirmDeleteStaff">Delete</button>
        </div>
    </div>
</div>

<script>
var _delStaffId = null;
document.querySelectorAll('.delete-staff').forEach(function(btn) {
    btn.addEventListener('click', function() {
        _delStaffId = this.dataset.id;
        document.getElementById('deleteStaffName').textContent = this.dataset.name;
        openModal('deleteStaffModal');
    });
});
document.getElementById('confirmDeleteStaff').addEventListener('click', function() {
    if (!_delStaffId) return;
    this.disabled = true;
    this.textContent = 'Deleting…';
    fetch('<?= site_url('admin/staff/delete/'); ?>' + _delStaffId, { method: 'POST' })
        .then(r => r.json())
        .then(d => {
            closeModal(document.getElementById('deleteStaffModal'));
            if (d.success) location.reload();
            else customAlert(d.message || 'Delete failed.');
            this.disabled = false;
            this.textContent = 'Delete';
        })
        .catch(function() { customAlert('Request failed.'); });
});
</script>

<script src="<?php echo BASE_URL; ?>public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
    const table = $('#staffTable').DataTable({
        columnDefs: [{ orderable: false, targets: [4] }],
        language: { search: '_INPUT_', searchPlaceholder: 'Search name or email…', emptyTable: 'No staff found' }
    });

    $.fn.dataTable.ext.search.push(function (settings, searchData, index) {
        if (settings.nTable.id !== 'staffTable') return true;
        const statusFilter = $('#filterStaffStatus').val();
        if (!statusFilter) return true;
        const $row = $(settings.aoData[index].nTr);
        return $row.data('status') === statusFilter;
    });

    $('#filterStaffStatus').on('change', function () { table.draw(); });
    $('#clearStaffFiltersBtn').on('click', function () {
        $('#filterStaffStatus').val('');
        table.search('').draw();
    });
});
</script>
