<style>
    .notif-container { margin: 30px 0; }
    .notif-header-row {
        display: flex; align-items: center; justify-content: space-between;
        gap: 15px; flex-wrap: wrap; margin-bottom: 25px;
    }
    .page-title { font-size: 28px; margin: 0; }
    .notif-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 15px;
        flex-wrap: wrap;
    }
    .notif-card.unread { border-left: 4px solid var(--primary-pink); }
    .notif-info h4 { margin: 0 0 5px; color: var(--dark); font-size: 15px; }
    .notif-info p { margin: 0; font-size: 13.5px; color: var(--gray); }
    .notif-info .notif-date { margin-top: 6px; font-size: 12px; color: var(--gray); }
    .notif-actions { display: flex; gap: 8px; flex-shrink: 0; }
    .notif-actions button {
        background: none; border: 1.5px solid var(--gray-200, #ddd); border-radius: 8px;
        width: 32px; height: 32px; cursor: pointer; color: var(--gray-600, #666);
        display: inline-flex; align-items: center; justify-content: center;
        transition: var(--transition-base, all .2s);
    }
    .notif-actions button:hover { background: var(--primary-pink-soft, #fff0f5); color: var(--primary-pink); border-color: var(--primary-pink); }
    /* .empty-state comes from the shared public/css/style.css component. */
</style>

<div class="notif-container">
    <div class="notif-header-row">
        <h2 class="page-title"><i class="fas fa-bell"></i> Notifications</h2>
        <?php if (!empty($notifications)): ?>
            <button type="button" class="btn btn-outline" id="markAllReadBtn">
                <i class="fas fa-check-double"></i> Mark All Read
            </button>
        <?php endif; ?>
    </div>

    <?php if (!empty($notifications)): ?>
        <?php foreach ($notifications as $notification): ?>
            <div class="notif-card <?php echo empty($notification['is_read']) ? 'unread' : ''; ?>" id="notif-row-<?php echo $notification['notification_id']; ?>">
                <div class="notif-info">
                    <h4><?php echo htmlspecialchars($notification['title'] ?? 'Notification'); ?></h4>
                    <p><?php echo htmlspecialchars($notification['message'] ?? ''); ?></p>
                    <div class="notif-date">
                        <i class="fas fa-clock"></i>
                        <?php echo !empty($notification['created_at']) ? date('M d, Y g:i A', strtotime($notification['created_at'])) : ''; ?>
                    </div>
                </div>
                <div class="notif-actions">
                    <?php if (empty($notification['is_read'])): ?>
                        <button type="button" onclick="markNotifRead(<?php echo $notification['notification_id']; ?>)" title="Mark as read">
                            <i class="fas fa-check"></i>
                        </button>
                    <?php endif; ?>
                    <button type="button" onclick="deleteNotif(<?php echo $notification['notification_id']; ?>)" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if ($pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="page-btn active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>" class="page-btn"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-bell-slash"></i>
            <h3>No Notifications</h3>
            <p>You're all caught up. We'll let you know here when there's something new.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function markNotifRead(id) {
    fetch('<?php echo BASE_URL; ?>notifications/mark_as_read/' + id, { method: 'POST' })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); });
}

function deleteNotif(id) {
    const doDelete = () => {
        fetch('<?php echo BASE_URL; ?>notifications/delete/' + id, { method: 'POST' })
            .then(r => r.json())
            .then(data => { if (data.success) location.reload(); });
    };
    if (typeof customConfirm === 'function') {
        customConfirm('Delete this notification?', doDelete, { title: 'Delete Notification' });
    } else if (confirm('Delete this notification?')) {
        doDelete();
    }
}

document.getElementById('markAllReadBtn')?.addEventListener('click', function () {
    fetch('<?php echo BASE_URL; ?>notifications/mark_all_as_read', { method: 'POST' })
        .then(r => r.json())
        .then(() => location.reload());
});
</script>
