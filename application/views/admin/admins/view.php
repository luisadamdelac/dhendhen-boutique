<?php
// application/views/admin/admins/view.php
?>
<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1a1a2e;"><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars(trim($admin['first_name'] . ' ' . $admin['last_name'])); ?></h4>
            <small class="text-muted">Admin account details and recent activity.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?php echo site_url('admin/admins'); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Admins
            </a>
            <a href="<?php echo site_url('admin/admins/edit/' . $admin['admin_id']); ?>" class="btn btn-info btn-sm">
                Edit
            </a>
            <?php if ($admin['status'] === 'active'): ?>
                <button type="button" class="btn btn-warning btn-sm" onclick="setAdminStatus(<?php echo $admin['admin_id']; ?>, 'deactivate')">
                    Deactivate
                </button>
            <?php else: ?>
                <button type="button" class="btn btn-success btn-sm" onclick="setAdminStatus(<?php echo $admin['admin_id']; ?>, 'activate')">
                    Activate
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
    <div class="col col-8">
        <!-- Admin Information Card -->
        <div class="card mb-3">
            <div class="card-body card-list">
                <div class="list-item">
                    <span class="item-label">Email:</span>
                    <span class="item-value"><a href="mailto:<?php echo htmlspecialchars($admin['email']); ?>"><?php echo htmlspecialchars($admin['email']); ?></a></span>
                </div>
                <div class="list-item">
                    <span class="item-label">Contact Number:</span>
                    <span class="item-value"><?php echo htmlspecialchars($admin['contact_number'] ?? 'N/A'); ?></span>
                </div>
                <div class="list-item">
                    <span class="item-label">Address:</span>
                    <span class="item-value"><?php echo htmlspecialchars(trim(implode(', ', array_filter([$admin['street'] ?? '', $admin['barangay'] ?? '', $admin['city'] ?? ''])))) ?: 'N/A'; ?></span>
                </div>
                <div class="list-item">
                    <span class="item-label">Status:</span>
                    <span class="item-value">
                        <span class="badge badge-<?php echo $admin['status'] === 'active' ? 'success' : 'secondary'; ?>">
                            <?php echo ucfirst($admin['status']); ?>
                        </span>
                    </span>
                </div>
                <div class="list-item">
                    <span class="item-label">Created:</span>
                    <span class="item-value"><?php echo date('M d, Y H:i', strtotime($admin['created_at'])); ?></span>
                </div>
            </div>
        </div>

        <!-- Recent Activity Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history"></i> Recent Activity
                </h3>
            </div>
            <div class="card-body">
                <?php if (!empty($activity)): ?>
                <div class="activity-list">
                    <?php foreach ($activity as $entry): ?>
                    <div class="activity-item">
                        <div class="activity-info">
                            <h6 class="activity-title"><?php echo ucfirst(str_replace('_', ' ', $entry['action'])); ?></h6>
                            <small class="activity-detail"><?php echo htmlspecialchars($entry['details'] ?? ''); ?></small>
                        </div>
                        <small class="activity-time"><?php echo date('M d, H:i', strtotime($entry['created_at'])); ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted mb-0">No activity recorded</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>

<script>
function setAdminStatus(adminId, action) {
    customConfirm(action === 'activate' ? 'Activate this admin account?' : 'Deactivate this admin account?', function() {
        fetch('<?php echo site_url('admin/admins/'); ?>' + action + '/' + adminId, { method: 'POST' })
            .then(r => r.json())
            .then(data => { alert(data.message || ''); if (data.success) location.reload(); })
            .catch(() => alert('Request failed'));
    }, { title: action === 'activate' ? 'Activate Admin' : 'Deactivate Admin' });
}
</script>
