<?php
$page_title = 'Notifications';
$current_page = 'notification';
?>
<div class="container-fluid py-4 fade-in">

    <!-- Page Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;"><i class="fas fa-bell me-2"></i>Notifications</h4>
            <small class="text-muted">View and manage your account notifications.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="mark-all-read">
                <i class="fas fa-check-double me-1"></i> Mark All Read
            </button>
        </div>
    </div>

    <!-- ===================== NOTIFICATIONS SECTION ===================== -->
    <div class="page-section">
        <span class="section-title"><i class="fas fa-list me-2 text-primary"></i>All Notifications</span>
        <hr>
        <span class="text-muted small" style="white-space:nowrap;"><?= number_format($total); ?> result<?= $total != 1 ? 's' : ''; ?></span>
    </div>

    <!-- Notifications Table -->
    <div class="card border-0 shadow-sm mb-2" style="border-radius:12px;overflow:hidden;">
        <div class="table-responsive">
            <table class="table inv-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Message</th>
                        <th class="text-center">Status</th>
                        <th class="pe-3">Date</th>
                    </tr>
                </thead>
                <tbody id="notificationTableBody">
                    <?php if (!empty($notifications)): ?>
                        <?php foreach ($notifications as $notification): ?>
                            <tr data-id="<?= (int) $notification['notification_id']; ?>">
                                <td class="ps-3">
                                    <div style="font-weight:600;"><?= htmlspecialchars($notification['title'] ?? 'Notification'); ?></div>
                                    <div style="font-size:12px;color:var(--gray);"><?= htmlspecialchars($notification['message'] ?? '-'); ?></div>
                                </td>
                                <td class="text-center">
                                    <?php if ($notification['is_read'] ?? 0): ?>
                                        <span class="badge-status badge-inactive read-status">Read</span>
                                    <?php else: ?>
                                        <span class="badge-status badge-pending read-status">Unread</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-3"><?= !empty($notification['created_at']) ? date('M d, Y g:i A', strtotime($notification['created_at'])) : '-'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <i class="fas fa-bell-slash fa-2x text-muted mb-2 d-block"></i>
                                <span class="text-muted">No notifications found</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
document.getElementById('mark-all-read')?.addEventListener('click', function () {
    const btn = this;
    btn.disabled = true;
    fetch('<?= site_url('admin/notification/mark_all_as_read'); ?>', { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.querySelectorAll('#notificationTableBody .read-status').forEach(function (badge) {
                    badge.textContent = 'Read';
                    badge.classList.remove('badge-pending');
                    badge.classList.add('badge-inactive');
                });
                const bellBadge = document.getElementById('notificationBadge');
                if (bellBadge) bellBadge.style.display = 'none';
            }
            btn.disabled = false;
        })
        .catch(() => { btn.disabled = false; alert('Failed to mark notifications as read.'); });
});
</script>
