<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?= BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.css">

<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div style="width:46px;height:46px;border-radius:14px;background:var(--primary-pink-dark);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
                <i class="fas fa-cog"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Activity Log</h4>
                <small class="text-muted">History of actions taken by admins, staff, and resellers across the system.</small>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/settings_tabs.php'; ?>

    <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
        <div class="card-body">
            <div class="row g-2 align-items-end mb-3">
                <div class="col-md-4">
                    <label class="form-label mb-1" for="activityLogSearch">Search action / details</label>
                    <input type="text" class="form-control form-control-sm" id="activityLogSearch" placeholder="e.g. approve_reseller_registration">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1" for="activityLogRole">Role</label>
                    <select id="activityLogRole" class="form-select form-select-sm">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="staff">Staff</option>
                        <option value="reseller">Reseller</option>
                        <option value="customer">Customer</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" id="activityLogClear" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table" id="activityLogTable">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Role</th>
                            <th>User ID</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr data-role="<?= htmlspecialchars($log['user_type']); ?>">
                                <td><?= htmlspecialchars($log['created_at']); ?></td>
                                <td>
                                    <span class="badge badge-<?= $log['user_type'] === 'admin' ? 'primary' : 'secondary'; ?>">
                                        <?= htmlspecialchars(ucfirst($log['user_type'])); ?>
                                    </span>
                                </td>
                                <td><?= (int) $log['user_id']; ?></td>
                                <td><?= htmlspecialchars($log['action']); ?></td>
                                <td><?= htmlspecialchars($log['details'] ?? '—'); ?></td>
                                <td><?= htmlspecialchars($log['ip_address'] ?? '—'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL; ?>public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="<?= BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
    const table = $('#activityLogTable').DataTable({
        order: [[0, 'desc']],
        language: { search: '_INPUT_', searchPlaceholder: 'Search…' },
        dom: '<"d-none"f>rtip' // hides the built-in global search box — the labeled "Search action / details" field above drives filtering instead.
    });

    // Free-text search box filters the same way DataTables' own global
    // search would (all columns), just wired to our own labeled input
    // instead of the default unlabeled one.
    $('#activityLogSearch').on('input', function () {
        table.search(this.value).draw();
    });

    // Role filter reads straight off each row's data-role attribute.
    $.fn.dataTable.ext.search.push(function (settings, searchData, index, rowData, counter) {
        if (settings.nTable.id !== 'activityLogTable') return true;
        const roleFilter = $('#activityLogRole').val();
        if (!roleFilter) return true;
        return $(settings.aoData[index].nTr).data('role') === roleFilter;
    });

    $('#activityLogRole').on('change', function () { table.draw(); });

    $('#activityLogClear').on('click', function () {
        $('#activityLogSearch').val('');
        $('#activityLogRole').val('');
        table.search('').draw();
    });
});
</script>
