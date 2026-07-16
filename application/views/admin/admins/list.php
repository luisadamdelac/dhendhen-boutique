<?php
// application/views/admin/admins/list.php
?>

<div class="container-fluid py-4 fade-in">

    <!-- Page Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;"><i class="fas fa-user-shield"></i> Admin Management</h4>
            <small class="text-muted">Manage administrator accounts and access.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/admins/add'); ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Add Admin
            </a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-2">
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#eef0ff;color:#4361ee;"><i class="fas fa-user-shield"></i></div>
                <div>
                    <div class="stat-label">Total Admins</div>
                    <div class="stat-value"><?= number_format($stat_total_admins); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-label">Active</div>
                    <div class="stat-value" style="color:#2e7d32;"><?= number_format($stat_active_admins); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#f0f0f8;color:#5a5a7a;"><i class="fas fa-ban"></i></div>
                <div>
                    <div class="stat-label">Inactive</div>
                    <div class="stat-value" style="color:#5a5a7a;"><?= number_format($stat_inactive_admins); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== ADMINS SECTION ===================== -->
    <div class="page-section">
        <span class="section-title"><i class="fas fa-user-shield me-2 text-primary"></i>Admins</span>
        <hr>
        <span class="text-muted small" style="white-space:nowrap;"><?= number_format($total); ?> result<?= $total != 1 ? 's' : ''; ?></span>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-4 col-sm-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search admin name or email" value="<?= htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="<?= site_url('admin/admins'); ?>" class="btn btn-sm btn-outline-secondary" title="Clear filters">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Admins Table -->
    <div class="card border-0 shadow-sm mb-2" style="border-radius:12px;overflow:hidden;">
        <div class="table-responsive">
            <table class="table inv-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Admin Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th class="text-center pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($admins)): ?>
                        <?php foreach ($admins as $admin): ?>
                            <?php $isActive = $admin['status'] === 'active'; ?>
                            <tr>
                                <td class="ps-3"><span class="fw-semibold" style="font-size:13px;color:#1a1a2e;"><?= htmlspecialchars(trim($admin['first_name'] . ' ' . $admin['last_name'])); ?></span></td>
                                <td><?= htmlspecialchars($admin['email']); ?></td>
                                <td><?= htmlspecialchars($admin['contact_number'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge-status <?= $isActive ? 'badge-active' : 'badge-inactive'; ?>">
                                        <?= ucfirst($admin['status']); ?>
                                    </span>
                                </td>
                                <td class="text-center pe-3">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="<?= site_url('admin/admins/view/' . $admin['admin_id']); ?>"
                                           class="btn btn-sm btn-outline-info" title="View" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-eye" style="font-size:11px;"></i>
                                        </a>
                                        <a href="<?= site_url('admin/admins/edit/' . $admin['admin_id']); ?>"
                                           class="btn btn-sm btn-outline-warning" title="Edit" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-edit" style="font-size:11px;"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger delete-admin" data-id="<?= $admin['admin_id']; ?>" title="Delete"
                                                style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-trash" style="font-size:11px;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="fas fa-user-shield fa-2x text-muted mb-2 d-block"></i>
                                <span class="text-muted">No admins found.</span>
                                <a href="<?= site_url('admin/admins/add'); ?>">Create one</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <small class="text-muted">Showing <?= count($admins); ?> of <?= $total; ?> admins</small>
</div>

<script>
document.querySelectorAll('.delete-admin').forEach(btn => {
    btn.addEventListener('click', function() {
        const adminId = this.dataset.id;
        customConfirm('Are you sure you want to delete this admin?', function() {
            fetch('<?= site_url('admin/admins/delete/'); ?>' + adminId, {
                method: 'POST'
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) location.reload();
                else alert(d.message);
            });
        }, { title: 'Delete Admin' });
    });
});
</script>
