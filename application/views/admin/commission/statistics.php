<?php
$page_title = 'Commission Statistics';
$current_page = 'commission';
?>
<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Commission Statistics</h4>
            <small class="text-muted">Breakdown of commission totals by status and top-earning resellers.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/commission'); ?>" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </div>

    <div class="row g-3 mb-2">
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff8e1;color:#f57f17;"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="stat-label">Pending</div>
                    <div class="stat-value"><?= number_format($by_status['pending']['count'] ?? 0); ?></div>
                    <div class="small text-muted mt-1">₱<?= number_format($by_status['pending']['total'] ?? 0, 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-label">Paid</div>
                    <div class="stat-value"><?= number_format($by_status['released']['count'] ?? 0); ?></div>
                    <div class="small text-muted mt-1">₱<?= number_format($by_status['released']['total'] ?? 0, 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e3f2fd;color:#1565c0;"><i class="fas fa-hand-holding-usd"></i></div>
                <div>
                    <div class="stat-label">On Hold</div>
                    <div class="stat-value"><?= number_format($by_status['withdrawn']['count'] ?? 0); ?></div>
                    <div class="small text-muted mt-1">₱<?= number_format($by_status['withdrawn']['total'] ?? 0, 2); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-section">
        <span class="section-title"><i class="fas fa-trophy me-2 text-primary"></i>Top Earners</span>
        <hr>
    </div>
    <div class="card border-0 shadow-sm mb-2" style="border-radius:12px;overflow:hidden;">
        <div class="table-responsive">
            <table class="table inv-table mb-0">
                <thead>
                    <tr><th class="ps-3">Reseller</th><th>Transactions</th><th>Total Earned</th></tr>
                </thead>
                <tbody>
                    <?php if (!empty($top_earners)): ?>
                        <?php foreach ($top_earners as $earner): ?>
                            <tr>
                                <td class="ps-3"><?= htmlspecialchars(trim($earner['first_name'] . ' ' . $earner['last_name'])); ?></td>
                                <td><?= (int)($earner['total_transactions'] ?? 0); ?></td>
                                <td><strong style="color: var(--primary-pink);">₱<?= number_format($earner['total_earned'] ?? 0, 2); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <i class="fas fa-trophy fa-2x text-muted mb-2 d-block"></i>
                                <span class="text-muted">No commission data</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
