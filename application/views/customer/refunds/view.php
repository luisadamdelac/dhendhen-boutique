<style>
    .refund-detail { background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin: 30px 0; }
    .refund-detail h2 { margin-bottom: 25px; }
    .detail-row { display: flex; padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
    .detail-label { width: 200px; font-weight: 600; color: var(--dark); }
    .detail-value { flex: 1; color: var(--gray); }
    .status-badge { padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-approved { background: #d4edda; color: #155724; }
    .status-rejected { background: #f8d7da; color: #721c24; }
    .status-processing { background: #d1ecf1; color: #0c5460; }
    .status-completed { background: #cce5ff; color: #004085; }
</style>

<div class="refund-detail">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h2><i class="fas fa-undo"></i> Refund Request Details</h2>
        <a href="<?php echo BASE_URL; ?>refund" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Refunds
        </a>
    </div>

    <div class="detail-row">
        <div class="detail-label">Request #</div>
        <div class="detail-value"><?php echo htmlspecialchars($refund['request_number'] ?? $refund['id']); ?></div>
    </div>
    <div class="detail-row">
        <div class="detail-label">Order #</div>
        <div class="detail-value"><?php echo htmlspecialchars($refund['order_number'] ?? 'N/A'); ?></div>
    </div>
    <div class="detail-row">
        <div class="detail-label">Refund Amount</div>
        <div class="detail-value" style="font-weight: 700; color: var(--primary); font-size: 18px;">
            ₱<?php echo number_format($refund['refund_amount'], 2); ?>
        </div>
    </div>
    <div class="detail-row">
        <div class="detail-label">Status</div>
        <div class="detail-value">
            <?php $status = $refund['status'] ?? 'pending'; ?>
            <span class="status-badge status-<?php echo $status; ?>">
                <?php echo ucfirst($status); ?>
            </span>
        </div>
    </div>
    <div class="detail-row">
        <div class="detail-label">Reason</div>
        <div class="detail-value"><?php echo nl2br(htmlspecialchars($refund['reason'])); ?></div>
    </div>
    <?php if (!empty($refund['admin_notes'])): ?>
    <div class="detail-row">
        <div class="detail-label">Admin Notes</div>
        <div class="detail-value"><?php echo nl2br(htmlspecialchars($refund['admin_notes'])); ?></div>
    </div>
    <?php endif; ?>
    <div class="detail-row">
        <div class="detail-label">Date Submitted</div>
        <div class="detail-value"><?php echo date('M d, Y h:i A', strtotime($refund['created_at'])); ?></div>
    </div>
    <?php if (!empty($refund['updated_at'])): ?>
    <div class="detail-row">
        <div class="detail-label">Last Updated</div>
        <div class="detail-value"><?php echo date('M d, Y h:i A', strtotime($refund['updated_at'])); ?></div>
    </div>
    <?php endif; ?>
</div>
