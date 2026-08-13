<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
@media (max-width: 600px) {
    .email-log-detail-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div style="width:46px;height:46px;border-radius:14px;background:var(--primary-pink-dark);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
                <i class="fas fa-cog"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Email Logs</h4>
                <small class="text-muted">History of every queued outgoing email and its delivery status.</small>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/settings_tabs.php'; ?>

    <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
        <div class="card-body">
            <form method="get" class="row g-2 align-items-end mb-3">
                <div class="col-md-4">
                    <label class="form-label mb-1" for="search">Search recipient / subject</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search" value="<?= htmlspecialchars($search ?? ''); ?>" placeholder="email or subject">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1" for="status">Status</label>
                    <select id="status" name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="sent" <?= ($status ?? '') === 'sent' ? 'selected' : ''; ?>>Sent</option>
                        <option value="failed" <?= ($status ?? '') === 'failed' ? 'selected' : ''; ?>>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search"></i> Filter</button>
                </div>
                <?php if (!empty($search) || !empty($status)): ?>
                    <div class="col-md-2">
                        <a href="<?= site_url('admin/settings/email_logs'); ?>" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                <?php endif; ?>
            </form>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Recipient</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Attempts</th>
                            <th>Created</th>
                            <th>Last Attempt</th>
                            <th>Error</th>
                            <th class="text-center">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= htmlspecialchars($log['recipient_email']); ?></td>
                                    <td><?= htmlspecialchars($log['subject']); ?></td>
                                    <td>
                                        <span class="badge badge-<?= $log['status'] === 'sent' ? 'success' : ($log['status'] === 'failed' ? 'danger' : 'warning'); ?>">
                                            <?= htmlspecialchars(ucfirst($log['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?= (int) $log['attempts']; ?></td>
                                    <td><?= htmlspecialchars($log['created_at']); ?></td>
                                    <td><?= htmlspecialchars($log['last_attempt'] ?? '-'); ?></td>
                                    <td>
                                        <?php if (!empty($log['error_message'])): ?>
                                            <?= htmlspecialchars(mb_strimwidth($log['error_message'], 0, 40, '…')); ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-info view-email-log-btn"
                                                data-recipient="<?= htmlspecialchars($log['recipient_email']); ?>"
                                                data-subject="<?= htmlspecialchars($log['subject']); ?>"
                                                data-status="<?= htmlspecialchars(ucfirst($log['status'])); ?>"
                                                data-attempts="<?= (int) $log['attempts']; ?>"
                                                data-created="<?= htmlspecialchars($log['created_at']); ?>"
                                                data-last-attempt="<?= htmlspecialchars($log['last_attempt'] ?? '-'); ?>"
                                                data-error="<?= htmlspecialchars($log['error_message'] ?? ''); ?>"
                                                title="View full details">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No emails logged yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($pages) && $pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a class="page-link <?= $i == ($page ?? 1) ? 'active' : ''; ?>"
                           href="<?= site_url('admin/settings/email_logs?page=' . $i . '&search=' . urlencode($search ?? '') . '&status=' . urlencode($status ?? '')); ?>"><?= $i; ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Email Log Details Modal -->
<div id="emailLogModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:14px; max-width:640px; width:92%; margin:auto; padding:28px; box-shadow:0 20px 60px rgba(0,0,0,0.3); position:relative; top:50%; transform:translateY(-50%); max-height:85vh; overflow-y:auto;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="fas fa-envelope-open-text me-2 text-primary"></i>Email Log Details</h5>
            <button type="button" onclick="closeEmailLogModal()" style="background:none;border:none;font-size:20px;color:#888;cursor:pointer;">&times;</button>
        </div>
        <div class="email-log-detail-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:10px 20px; font-size:13px; margin-bottom:16px;">
            <div><span class="text-muted">Recipient:</span> <strong id="logDetailRecipient"></strong></div>
            <div><span class="text-muted">Status:</span> <strong id="logDetailStatus"></strong></div>
            <div><span class="text-muted">Subject:</span> <strong id="logDetailSubject"></strong></div>
            <div><span class="text-muted">Attempts:</span> <strong id="logDetailAttempts"></strong></div>
            <div><span class="text-muted">Created:</span> <strong id="logDetailCreated"></strong></div>
            <div><span class="text-muted">Last Attempt:</span> <strong id="logDetailLastAttempt"></strong></div>
        </div>
        <div>
            <span class="text-muted" style="font-size:13px;">Error Details:</span>
            <pre id="logDetailError" style="background:#f8f9fa; border:1px solid #eee; border-radius:8px; padding:14px; margin-top:6px; font-size:12px; white-space:pre-wrap; word-break:break-word; max-height:320px; overflow-y:auto;"></pre>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.view-email-log-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.getElementById('logDetailRecipient').textContent = this.dataset.recipient;
        document.getElementById('logDetailSubject').textContent = this.dataset.subject;
        document.getElementById('logDetailStatus').textContent = this.dataset.status;
        document.getElementById('logDetailAttempts').textContent = this.dataset.attempts;
        document.getElementById('logDetailCreated').textContent = this.dataset.created;
        document.getElementById('logDetailLastAttempt').textContent = this.dataset.lastAttempt;
        document.getElementById('logDetailError').textContent = this.dataset.error || 'No error recorded.';
        document.getElementById('emailLogModal').style.display = 'flex';
    });
});

function closeEmailLogModal() {
    document.getElementById('emailLogModal').style.display = 'none';
}

document.getElementById('emailLogModal').addEventListener('click', function (e) {
    if (e.target === this) closeEmailLogModal();
});
</script>
