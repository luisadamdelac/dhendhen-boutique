<div class="container-fluid py-4 fade-in">
    <div class="ds-hero-card mb-3">
        <div class="ds-hero-banner">
            <svg class="ds-hero-wave" viewBox="0 0 1440 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,110 C240,170 480,50 720,90 C960,130 1200,50 1440,100 L1440,200 L0,200 Z" fill="rgba(255,105,180,0.16)"></path>
                <path d="M0,140 C280,80 560,180 840,120 C1080,70 1280,140 1440,130 L1440,200 L0,200 Z" fill="rgba(233,30,99,0.22)"></path>
            </svg>
            <div class="ds-hero-banner-content d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h4 class="fw-bold mb-0" style="color:#1a1a2e;"><i class="fas fa-bell"></i> Notifications</h4>
                    <small class="text-muted">View and manage your account notifications.</small>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="mark-all-read">
                    <i class="fas fa-check-double"></i> Mark All Read
                </button>
            </div>
        </div>
    </div>

    <div class="card ds-pink-table-card">
        <div class="card-body">
            <?php if (empty($notifications)): ?>
                <p style="text-align: center; color: var(--gray-500, #888); padding: 2rem;">
                    <i class="fas fa-bell-slash" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                    No notifications found
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table ds-pink-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notifications as $notification): ?>
                                <tr id="notif-row-<?php echo $notification['notification_id']; ?>">
                                    <td><strong><?php echo htmlspecialchars($notification['title'] ?? '-'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($notification['message'] ?? '-'); ?></td>
                                    <td>
                                        <?php if ($notification['is_read'] ?? 0): ?>
                                            <span class="badge badge-secondary">Read</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Unread</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?php echo !empty($notification['created_at']) ? date('M d, Y h:i A', strtotime($notification['created_at'])) : '-'; ?></small></td>
                                    <td>
                                        <?php if (!($notification['is_read'] ?? 0)): ?>
                                            <button class="ds-action-btn" onclick="markAsRead(<?php echo $notification['notification_id']; ?>)" title="Mark as read">
                                                <i class="fas fa-check" style="font-size:11px;"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="ds-action-btn" onclick="deleteNotification(<?php echo $notification['notification_id']; ?>)" title="Delete">
                                            <i class="fas fa-trash" style="font-size:11px;"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($pages) && $pages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $pages; $i++): ?>
                            <a class="page-link <?= $i == ($page ?? 1) ? 'active' : ''; ?>"
                               href="<?= site_url('staff/notifications?page=' . $i); ?>"><?= $i; ?></a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('mark-all-read').addEventListener('click', function () {
    fetch('<?php echo site_url('staff/notifications/mark_all_as_read'); ?>', { method: 'POST' })
        .then(r => r.json())
        .then(() => location.reload());
});

function markAsRead(id) {
    fetch('<?php echo site_url('staff/notifications/mark_as_read/'); ?>' + id, { method: 'POST' })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); });
}

function deleteNotification(id) {
    customConfirm('Delete this notification?', function() {
        fetch('<?php echo site_url('staff/notifications/delete/'); ?>' + id, { method: 'POST' })
            .then(r => r.json())
            .then(data => { if (data.success) location.reload(); });
    }, { title: 'Delete Notification' });
}
</script>
