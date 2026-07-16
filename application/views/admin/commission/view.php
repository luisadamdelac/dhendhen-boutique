<?php
$page_title = 'Commission Transaction Details';
$current_page = 'commission';

$cStatus = $transaction['status'] ?? 'pending';
$cBadge  = ['pending' => 'pending', 'released' => 'released', 'withdrawn' => 'withdrawn'][$cStatus] ?? 'pending';
?>
<div class="container-fluid py-4 fade-in">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1a1a2e;"><i class="fas fa-hand-holding-usd"></i> Commission Transaction Details</h4>
            <small class="text-muted">Reseller commission earned from an order.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/commission'); ?>" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col col-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Transaction #<?= htmlspecialchars($transaction['commission_number'] ?? $transaction['commission_id']); ?>
                    </h3>
                    <span class="badge-status badge-<?= $cBadge; ?>"><?= ucfirst($cStatus); ?></span>
                </div>
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--secondary-lavender);">
                        <span style="color: var(--gray);">Reseller</span>
                        <strong><?= htmlspecialchars(trim(($transaction['first_name'] ?? '') . ' ' . ($transaction['last_name'] ?? '')) ?: '-'); ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--secondary-lavender);">
                        <span style="color: var(--gray);">Business Name</span>
                        <strong><?= htmlspecialchars($transaction['business_name'] ?? '-'); ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--secondary-lavender);">
                        <span style="color: var(--gray);">Email</span>
                        <strong><?= htmlspecialchars($transaction['email'] ?? '-'); ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--secondary-lavender);">
                        <span style="color: var(--gray);">Order</span>
                        <strong>
                            <?php if (!empty($transaction['order_id'])): ?>
                                <a href="<?= site_url('admin/order/view/' . $transaction['order_id']); ?>">#<?= str_pad($transaction['order_id'], 6, '0', STR_PAD_LEFT); ?></a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; <?= !empty($transaction['withdrawal_id']) || !empty($transaction['released_at']) ? 'border-bottom: 1px solid var(--secondary-lavender);' : ''; ?>">
                        <span style="color: var(--gray);">Created</span>
                        <strong><?= !empty($transaction['created_at']) ? date('F j, Y g:i A', strtotime($transaction['created_at'])) : '-'; ?></strong>
                    </div>
                    <?php if (!empty($transaction['released_at'])): ?>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; <?= !empty($transaction['withdrawal_id']) ? 'border-bottom: 1px solid var(--secondary-lavender);' : ''; ?>">
                        <span style="color: var(--gray);">Released</span>
                        <strong><?= date('F j, Y g:i A', strtotime($transaction['released_at'])); ?></strong>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($transaction['withdrawal_id'])): ?>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0;">
                        <span style="color: var(--gray);">Withdrawal</span>
                        <strong>
                            <a href="<?= site_url('admin/withdrawal/view/' . $transaction['withdrawal_id']); ?>">#<?= (int) $transaction['withdrawal_id']; ?></a>
                        </strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col col-4">
            <div class="card stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);">
                    <i class="fas fa-peso-sign"></i>
                </div>
                <div class="stat-value">₱<?= number_format($transaction['amount'] ?? 0, 2); ?></div>
                <div class="stat-label">Commission Amount</div>
            </div>
        </div>
    </div>
</div>
