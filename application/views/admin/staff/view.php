<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1a1a2e;"><i class="fas fa-user"></i> <?php echo htmlspecialchars(trim($staff['first_name'] . ' ' . $staff['last_name'])); ?></h4>
            <small class="text-muted">Staff account details and recent activity.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?php echo site_url('admin/staff'); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Staff
            </a>
            <a href="<?php echo site_url('admin/staff/edit/' . $staff['staff_id']); ?>" class="btn btn-info btn-sm">
                Edit
            </a>
            <?php if ($staff['status'] === 'active'): ?>
                <button type="button" class="btn btn-warning btn-sm" onclick="setStaffStatus(<?php echo $staff['staff_id']; ?>, 'deactivate')">
                    Deactivate
                </button>
            <?php else: ?>
                <button type="button" class="btn btn-success btn-sm" onclick="setStaffStatus(<?php echo $staff['staff_id']; ?>, 'activate')">
                    Activate
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
    <div class="col col-4">
        <!-- Profile Photo Card -->
        <div class="card mb-3">
            <div class="card-body text-center">
                <?php
                    $avatarImage = $staff['profile_image'] ?? '';
                    $avatarSrc = !empty($avatarImage) ? BASE_URL . $avatarImage : BASE_URL . default_avatar_url();
                ?>
                <img src="<?php echo $avatarSrc; ?>" alt="Staff Photo" style="width:120px;height:120px;border-radius:50%;object-fit:cover;margin-bottom:15px;">
                <form method="POST" action="<?php echo site_url('admin/staff/upload_photo/' . $staff['staff_id']); ?>" enctype="multipart/form-data" id="staffPhotoForm">
                    <input type="file" id="staffPhotoInput" name="photo" accept="image/*" style="display:none;" onchange="document.getElementById('staffPhotoForm').submit();">
                    <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                        <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('staffPhotoInput').click();">
                            <i class="fas fa-upload"></i> Change Photo
                        </button>
                        <?php if (!empty($avatarImage)): ?>
                        <button type="button" class="btn btn-outline btn-sm" onclick="removeStaffPhoto(<?php echo $staff['staff_id']; ?>)">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col col-8">
        <!-- Staff Information Card -->
        <div class="card mb-3">
            <div class="card-body card-list">
                <div class="list-item">
                    <span class="item-label">Email:</span>
                    <span class="item-value"><a href="mailto:<?php echo htmlspecialchars($staff['email']); ?>"><?php echo htmlspecialchars($staff['email']); ?></a></span>
                </div>
                <div class="list-item">
                    <span class="item-label">Contact Number:</span>
                    <span class="item-value"><?php echo htmlspecialchars($staff['contact_number'] ?? 'N/A'); ?></span>
                </div>
                <div class="list-item">
                    <span class="item-label">Address:</span>
                    <span class="item-value"><?php echo htmlspecialchars(trim(implode(', ', array_filter([$staff['street'] ?? '', $staff['barangay'] ?? '', $staff['city'] ?? ''])))) ?: 'N/A'; ?></span>
                </div>
                <div class="list-item">
                    <span class="item-label">Status:</span>
                    <span class="item-value">
                        <span class="badge badge-<?php echo $staff['status'] === 'active' ? 'success' : 'secondary'; ?>">
                            <?php echo ucfirst($staff['status']); ?>
                        </span>
                    </span>
                </div>
                <div class="list-item">
                    <span class="item-label">Created:</span>
                    <span class="item-value"><?php echo date('M d, Y H:i', strtotime($staff['created_at'])); ?></span>
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
function setStaffStatus(staffId, action) {
    customConfirm(action === 'activate' ? 'Activate this staff account?' : 'Deactivate this staff account?', function() {
        fetch('<?php echo site_url('admin/staff/'); ?>' + action + '/' + staffId, { method: 'POST' })
            .then(r => r.json())
            .then(data => { alert(data.message || ''); if (data.success) location.reload(); })
            .catch(() => alert('Request failed'));
    }, { title: action === 'activate' ? 'Activate Staff' : 'Deactivate Staff' });
}

function removeStaffPhoto(staffId) {
    customConfirm('Remove this staff member\'s photo?', function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo site_url('admin/staff/remove_photo/'); ?>' + staffId;
        document.body.appendChild(form);
        form.submit();
    }, { title: 'Remove Photo' });
}
</script>
