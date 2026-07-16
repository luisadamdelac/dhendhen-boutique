<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;"><i class="fas fa-bell"></i> Notifications</h4>
            <small class="text-muted">View and manage your account notifications.</small>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="mark-all-read">
            <i class="fas fa-check-double"></i> Mark All Read
        </button>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
        <div class="card-body">
            <?php if (empty($notifications)): ?>
                <p style="text-align: center; color: var(--gray-500, #888); padding: 2rem;">
                    <i class="fas fa-bell-slash" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                    No notifications found
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
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
                                            <button class="btn btn-sm btn-outline-secondary" onclick="markAsRead(<?php echo $notification['notification_id']; ?>)" title="Mark as read">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteNotification(<?php echo $notification['notification_id']; ?>)" title="Delete">
                                            <i class="fas fa-trash"></i>
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
