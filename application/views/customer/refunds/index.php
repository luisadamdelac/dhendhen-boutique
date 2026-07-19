<style>
    .refunds-container { margin: 30px 0; }
    .request-card {
        background: white; border-radius: 12px; padding: 25px; margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .request-card h3 { margin: 0 0 15px; }
    .request-form { display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
    .request-form .field { display: flex; flex-direction: column; gap: 5px; flex: 1; min-width: 220px; }
    .request-form select, .request-form textarea { padding: 10px 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 14px; font-family: inherit; }
    .refund-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }
    .refund-info h4 { margin: 0 0 5px; color: var(--dark); }
    .refund-info p { margin: 0; font-size: 13px; color: var(--gray); }
    .refund-amount { font-size: 20px; font-weight: 700; color: var(--primary); }
    .refund-status {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-approved { background: #d4edda; color: #155724; }
    .status-rejected { background: #f8d7da; color: #721c24; }
    .status-completed { background: #cce5ff; color: #004085; }
    /* .empty-state now comes from the shared public/css/style.css component. */
    .page-title { font-size: 28px; margin-bottom: 25px; }
</style>

<div class="refunds-container">
    <h2 class="page-title"><i class="fas fa-undo"></i> My Refund Requests</h2>

    <?php if (!empty($eligible_orders)): ?>
        <div class="request-card">
            <h3><i class="fas fa-paper-plane"></i> Request a Refund</h3>
            <form method="POST" action="<?php echo BASE_URL; ?>refund/submit" class="request-form" enctype="multipart/form-data">
                <div class="field">
                    <label>Order</label>
                    <select name="order_id" required>
                        <option value="">Select a delivered order</option>
                        <?php foreach ($eligible_orders as $order): ?>
                            <option value="<?php echo $order['order_id']; ?>">
                                #<?php echo htmlspecialchars($order['order_number']); ?> — ₱<?php echo number_format($order['total_amount'], 2); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field" style="flex: 2;">
                    <label>Reason</label>
                    <textarea name="reason" rows="2" maxlength="500" required placeholder="Why are you requesting a refund?"></textarea>
                </div>
                <div class="field">
                    <label>Evidence <span style="color:var(--gray); font-weight:400;">(photo required)</span></label>
                    <input type="file" name="evidence" accept="image/*" required>
                </div>
                <div class="field" style="flex: 0;">
                    <button type="submit" class="btn">
                        <i class="fas fa-paper-plane"></i> Submit
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php if (!empty($refunds)): ?>
        <?php foreach ($refunds as $refund): ?>
            <div class="refund-card">
                <div class="refund-info">
                    <h4>Refund #<?php echo htmlspecialchars($refund['refund_number']); ?></h4>
                    <p>
                        <i class="fas fa-shopping-bag"></i> Order #<?php echo htmlspecialchars($refund['order_number'] ?? 'N/A'); ?>
                        &nbsp;|&nbsp;
                        <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($refund['created_at'])); ?>
                    </p>
                    <p style="margin-top: 5px;">
                        <strong>Reason:</strong> <?php echo htmlspecialchars($refund['reason']); ?>
                    </p>
                    <?php if (!empty($refund['admin_remarks'])): ?>
                        <p style="margin-top: 5px;">
                            <strong>Admin Remarks:</strong> <?php echo htmlspecialchars($refund['admin_remarks']); ?>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($refund['images'])): ?>
                        <p style="margin-top: 5px;">
                            <a href="<?php echo BASE_URL . htmlspecialchars($refund['images']); ?>" target="_blank">
                                <i class="fas fa-image"></i> View uploaded evidence
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
                <div style="text-align: right;">
                    <div class="refund-amount">₱<?php echo number_format($refund['amount'], 2); ?></div>
                    <?php
                        $status = $refund['status'] ?? 'pending';
                        $statusIcons = [
                            'pending' => 'fa-clock', 'approved' => 'fa-check-circle',
                            'rejected' => 'fa-times-circle', 'completed' => 'fa-check-double',
                            'cancelled' => 'fa-ban',
                        ];
                    ?>
                    <span class="refund-status status-<?php echo $status; ?>">
                        <i class="fas <?php echo $statusIcons[$status] ?? 'fa-clock'; ?>"></i>
                        <?php echo ucfirst($status); ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php elseif (empty($eligible_orders)): ?>
        <div class="empty-state">
            <i class="fas fa-undo"></i>
            <h3>No Refund Requests</h3>
            <p>You haven't submitted any refund requests yet. Refunds can be requested once an order is marked delivered.</p>
            <a href="<?php echo BASE_URL; ?>orders" class="btn" style="margin-top: 20px;">
                <i class="fas fa-box"></i> View My Orders
            </a>
        </div>
    <?php endif; ?>
</div>
