<?php
$page_title = 'Reseller Commission Summary';
$current_page = 'commission';
?>
<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Reseller Commission Summary</h4>
            <small class="text-muted">Commission performance for <?= htmlspecialchars(trim($reseller['first_name'] . ' ' . $reseller['last_name'])); ?>.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/commission'); ?>" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </div>

    <div class="row g-3 mb-2">
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-wallet"></i></div>
                <div>
                    <div class="stat-label">Total Earned</div>
                    <div class="stat-value" style="color:#2e7d32;">₱<?= number_format($total_earned ?? 0, 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff8e1;color:#f57f17;"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="stat-label">Pending Commission</div>
                    <div class="stat-value" style="color:#f57f17;">₱<?= number_format($pending_commission ?? 0, 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ede7f6;color:#5e35b1;"><i class="fas fa-coins"></i></div>
                <div>
                    <div class="stat-label">Current Balance</div>
                    <div class="stat-value">₱<?= number_format($reseller['commission_balance'] ?? 0, 2); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-section">
        <span class="section-title"><i class="fas fa-list me-2 text-primary"></i>Recent Transactions</span>
        <hr>
    </div>
    <div class="card border-0 shadow-sm mb-2" style="border-radius:12px;overflow:hidden;">
        <div class="table-responsive">
            <table class="table inv-table mb-0">
                <thead>
                    <tr><th class="ps-3">Transaction ID</th><th>Amount</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_transactions)): ?>
                        <?php foreach ($recent_transactions as $transaction): ?>
                            <tr>
                                <td class="ps-3">#<?= htmlspecialchars($transaction['commission_id']); ?></td>
                                <td>₱<?= number_format($transaction['amount'] ?? 0, 2); ?></td>
                                <td><span class="badge-status badge-<?= $transaction['status'] ?? 'pending'; ?>"><?= ucfirst($transaction['status'] ?? 'pending'); ?></span></td>
                                <td><?= !empty($transaction['created_at']) ? date('M d, Y', strtotime($transaction['created_at'])) : '-'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="fas fa-list fa-2x text-muted mb-2 d-block"></i>
                                <span class="text-muted">No transactions found</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
