<div class="page-header-section">
    <div>
        <h1 class="page-title"><i class="fas fa-bell"></i> Notifications</h1>
        <p class="page-subtitle">View and manage your account notifications.</p>
    </div>
    <button type="button" class="btn btn-outline btn-sm" id="mark-all-read">
        <i class="fas fa-check-double"></i> Mark All Read
    </button>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-list"></i> All Notifications</h3>
    </div>
    <div class="card-body">
        <?php if (empty($notifications)): ?>
            <p style="text-align: center; color: var(--gray-500); padding: 2rem;">
                <i class="fas fa-bell-slash" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                No notifications found
            </p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-stack">
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
                                        <span class="badge-status badge-inactive">Read</span>
                                    <?php else: ?>
                                        <span class="badge-status badge-pending">Unread</span>
                                    <?php endif; ?>
                                </td>
                                <td><small><?php echo !empty($notification['created_at']) ? date('M d, Y h:i A', strtotime($notification['created_at'])) : '-'; ?></small></td>
                                <td>
                                    <?php if (!($notification['is_read'] ?? 0)): ?>
                                        <button class="btn btn-sm" onclick="markAsRead(<?php echo $notification['notification_id']; ?>)" title="Mark as read">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm" onclick="deleteNotification(<?php echo $notification['notification_id']; ?>)" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('mark-all-read').addEventListener('click', function () {
    fetch('<?php echo site_url('reseller/notifications/mark_all_as_read'); ?>', { method: 'POST' })
        .then(r => r.json())
        .then(() => location.reload());
});

function markAsRead(id) {
    fetch('<?php echo site_url('reseller/notifications/mark_as_read/'); ?>' + id, { method: 'POST' })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); });
}

function deleteNotification(id) {
    customConfirm('Delete this notification?', function() {
        fetch('<?php echo site_url('reseller/notifications/delete/'); ?>' + id, { method: 'POST' })
            .then(r => r.json())
            .then(data => { if (data.success) location.reload(); });
    }, { title: 'Delete Notification' });
}
</script>
