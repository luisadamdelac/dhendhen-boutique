<h2 class="page-title" style="font-size: 22px; margin: 0 0 10px;"><i class="fas fa-wallet"></i> Wallet</h2>
<div class="row g-3" style="margin-bottom: 20px;">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff8e1;color:#f57f17;"><i class="fas fa-hourglass-half"></i></div>
            <div>
                <div class="stat-label">Pending Commission</div>
                <div class="stat-value">₱<?php echo number_format($wallet['pending_commission'] ?? 0, 2); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-unlock"></i></div>
            <div>
                <div class="stat-label">Released Commission</div>
                <div class="stat-value">₱<?php echo number_format($wallet['approved_commission'] ?? 0, 2); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eef0ff;color:#4361ee;"><i class="fas fa-money-bill-wave"></i></div>
            <div>
                <div class="stat-label">Withdrawn</div>
                <div class="stat-value">₱<?php echo number_format($wallet['paid_commission'] ?? 0, 2); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ffebee;color:#c62828;"><i class="fas fa-undo"></i></div>
            <div>
                <div class="stat-label">Pending Refunds</div>
                <div class="stat-value"><?php echo number_format($wallet['pending_refunds'] ?? 0); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-dollar-sign"></i> My Commissions</h3>
    </div>
    <div class="card-body">
        <?php if (empty($commissions)): ?>
            <div class="empty-state">
                <i class="fas fa-dollar-sign"></i>
                <h3>No Commissions Yet</h3>
                <p>Commissions from your sales will appear here.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-stack">
                    <thead>
                        <tr>
                            <th>Commission #</th>
                            <th>Order</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Released</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commissions as $c): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($c['commission_number']); ?></strong></td>
                                <td>#<?php echo htmlspecialchars($c['order_id']); ?></td>
                                <td>₱<?php echo number_format($c['amount'], 2); ?></td>
                                <td>
                                    <span class="badge-status badge-<?php echo $c['status']; ?>">
                                        <?php echo ucfirst($c['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($c['created_at'])); ?></td>
                                <td><?php echo !empty($c['released_at']) ? date('M d, Y', strtotime($c['released_at'])) : '—'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
