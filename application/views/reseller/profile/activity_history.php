<!-- Reseller Activity History -->
<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1a1a2e;"><i class="fas fa-history"></i> Activity History</h4>
            <small class="text-muted">A record of your own actions — listing changes, withdrawals, and profile edits.</small>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Action</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= htmlspecialchars($log['created_at']); ?></td>
                                    <td><?= htmlspecialchars($log['action']); ?></td>
                                    <td><?= htmlspecialchars($log['details'] ?? '—'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center text-muted py-4">No activity yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($pages) && $pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a class="page-link <?= $i == ($page ?? 1) ? 'active' : ''; ?>"
                           href="<?= site_url('reseller/profile/activity_history?page=' . $i); ?>"><?= $i; ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
