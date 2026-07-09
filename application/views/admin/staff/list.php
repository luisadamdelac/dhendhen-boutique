<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.css">
<style>
/* Page-specific styling not covered by the shared design system */
.avatar-placeholder {
    width: 38px; height: 38px; border-radius: 8px;
    background: #f0f2f8; display: flex; align-items: center;
    justify-content: center; color: #b0b8d4; font-size: 16px; flex-shrink: 0;
}
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid var(--border); border-radius: var(--radius-md);
    padding: .4rem .75rem; font-size: .85rem; margin-left: .5rem;
}
.dataTables_wrapper .dataTables_filter input:focus { outline: none; border-color: var(--primary-pink); }
table.dataTable thead th { position: relative; }
table.dataTable thead th.sorting:hover { color: var(--primary-pink); cursor: pointer; }
</style>

<div class="container-fluid py-4">

    <!-- Page Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;"><i class="fas fa-user-tie"></i> Staff Management</h4>
            <small class="text-muted">Manage staff accounts and access for your store.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/staff/add'); ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Add Staff
            </a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-2">
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#eef0ff;color:#4361ee;"><i class="fas fa-user-tie"></i></div>
                <div>
                    <div class="stat-label">Total Staff</div>
                    <div class="stat-value"><?= number_format($stat_total_staff); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-label">Active</div>
                    <div class="stat-value" style="color:#2e7d32;"><?= number_format($stat_active_staff); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#f0f0f8;color:#5a5a7a;"><i class="fas fa-ban"></i></div>
                <div>
                    <div class="stat-label">Inactive</div>
                    <div class="stat-value" style="color:#5a5a7a;"><?= number_format($stat_inactive_staff); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== STAFF SECTION ===================== -->
    <div class="page-section">
        <span class="section-title"><i class="fas fa-user-tie me-2 text-primary"></i>Staff</span>
        <hr>
        <span class="text-muted small" style="white-space:nowrap;"><?= number_format($total ?? 0); ?> result<?= ($total ?? 0) != 1 ? 's' : ''; ?></span>
    </div>

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
                <button type="button" id="clearStaffFiltersBtn" class="btn btn-sm btn-outline-secondary flex-fill">
                    <i class="fas fa-times"></i> Clear Filters
                </button>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-2" style="border-radius:12px;overflow:hidden;">
        <div class="table-responsive">
            <table class="table inv-table mb-0" id="staffTable">
                <thead>
                    <tr>
                        <th class="ps-3">Staff Member</th>
                        <th>Contact</th>
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
                                    <div class="avatar-placeholder" style="background:linear-gradient(135deg,#4e73df,#224abe);color:#fff;font-weight:700;">
                                        <?= $initials; ?>
                                    </div>
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
                            <td><?= htmlspecialchars($staff['contact_number'] ?? '—'); ?></td>
                            <td>
                                <span class="badge-status <?= $isActive ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?= $isActive ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="text-center pe-3">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="<?= site_url('admin/staff/view/' . $staff['staff_id']); ?>"
                                       class="btn btn-sm btn-outline-info" title="View" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-eye" style="font-size:11px;"></i>
                                    </a>
                                    <a href="<?= site_url('admin/staff/edit/' . $staff['staff_id']); ?>"
                                       class="btn btn-sm btn-outline-warning" title="Edit" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-edit" style="font-size:11px;"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-staff"
                                            data-id="<?= $staff['staff_id']; ?>"
                                            data-name="<?= htmlspecialchars(trim(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? ''))); ?>"
                                            title="Delete" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
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
            else alert(d.message || 'Delete failed.');
            this.disabled = false;
            this.textContent = 'Delete';
        })
        .catch(function() { alert('Request failed.'); });
});
</script>

<script src="<?php echo BASE_URL; ?>public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
    const table = $('#staffTable').DataTable({
        columnDefs: [{ orderable: false, targets: [3] }],
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
