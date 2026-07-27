<style>
.comm-empty { text-align: center; padding: 3rem 1rem 2.4rem; }
.comm-empty-illustration { position: relative; width: 150px; height: 130px; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; }
.comm-empty-blob {
    position: absolute; inset: 0; border-radius: 50%;
    background: radial-gradient(circle, rgba(255, 193, 217, .55) 0%, rgba(255, 247, 250, 0) 72%);
}
.comm-empty-box { position: relative; font-size: 72px; color: #FF9EC7; z-index: 1; filter: drop-shadow(0 10px 14px rgba(255, 79, 162, .22)); }
.comm-empty-arrow {
    position: absolute; top: 6px; right: 14px; font-size: 26px; color: #FF4FA2; z-index: 2;
    transform: rotate(10deg); filter: drop-shadow(0 4px 8px rgba(255, 79, 162, .3));
}
.comm-empty-spark { position: absolute; width: 6px; height: 6px; background: #FFC1D9; transform: rotate(45deg); }
.comm-empty-spark.s1 { top: 4px; left: 18px; }
.comm-empty-spark.s2 { width: 5px; height: 5px; bottom: 20px; right: 6px; }
.comm-empty-spark.s3 { width: 4px; height: 4px; top: 30px; left: 4px; }
.comm-empty-title { margin: 0 0 6px; font-size: 1.05rem; font-weight: 700; color: #1a1a2e; }
.comm-empty-sub { margin: 0 0 18px; font-size: .86rem; color: var(--gray); }
.comm-empty-btn { display: inline-flex; align-items: center; gap: 6px; }
</style>

<div class="ds-hero-card mb-3">
    <div class="ds-hero-banner">
        <svg class="ds-hero-wave" viewBox="0 0 1440 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,110 C240,170 480,50 720,90 C960,130 1200,50 1440,100 L1440,200 L0,200 Z" fill="rgba(255,105,180,0.16)"></path>
            <path d="M0,140 C280,80 560,180 840,120 C1080,70 1280,140 1440,130 L1440,200 L0,200 Z" fill="rgba(233,30,99,0.22)"></path>
        </svg>
        <div class="ds-hero-banner-content d-flex align-items-center gap-3">
            <div style="width:46px;height:46px;border-radius:14px;background:var(--primary-pink-dark);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
                <i class="fas fa-wallet"></i>
            </div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Wallet</h4>
        </div>
    </div>

    <div class="ds-hero-stats">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="ds-stat-tile">
                    <div class="ds-stat-tile-icon" style="background:#fff8e1;color:#f57f17;"><i class="fas fa-hourglass-half"></i></div>
                    <div>
                        <div class="ds-stat-tile-label">Pending Commission</div>
                        <div class="ds-stat-tile-value">₱<?php echo number_format($wallet['pending_commission'] ?? 0, 2); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ds-stat-tile">
                    <div class="ds-stat-tile-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-unlock"></i></div>
                    <div>
                        <div class="ds-stat-tile-label">Released Commission</div>
                        <div class="ds-stat-tile-value">₱<?php echo number_format($wallet['approved_commission'] ?? 0, 2); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ds-stat-tile">
                    <div class="ds-stat-tile-icon" style="background:#eef0ff;color:#4361ee;"><i class="fas fa-money-bill-wave"></i></div>
                    <div>
                        <div class="ds-stat-tile-label">Withdrawn</div>
                        <div class="ds-stat-tile-value">₱<?php echo number_format($wallet['paid_commission'] ?? 0, 2); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ds-stat-tile">
                    <div class="ds-stat-tile-icon" style="background:#ffebee;color:#c62828;"><i class="fas fa-undo"></i></div>
                    <div>
                        <div class="ds-stat-tile-label">Pending Refunds</div>
                        <div class="ds-stat-tile-value"><?php echo number_format($wallet['pending_refunds'] ?? 0); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card ds-pink-table-card mt-3">
    <div class="card-header">
        <h3><i class="fas fa-dollar-sign"></i> My Commissions</h3>
    </div>
    <div class="card-body">
        <?php if (empty($commissions)): ?>
            <div class="comm-empty">
                <div class="comm-empty-illustration">
                    <div class="comm-empty-blob"></div>
                    <i class="fas fa-box-open comm-empty-box"></i>
                    <i class="fas fa-arrow-trend-up comm-empty-arrow"></i>
                    <span class="comm-empty-spark s1"></span>
                    <span class="comm-empty-spark s2"></span>
                    <span class="comm-empty-spark s3"></span>
                </div>
                <h5 class="comm-empty-title">No Commissions Yet</h5>
                <p class="comm-empty-sub">Commissions from your sales will appear here once you start earning.</p>
                <a href="<?php echo site_url('reseller/inventory'); ?>" class="btn btn-outline comm-empty-btn">
                    <i class="fas fa-bag-shopping"></i> Start Selling
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped ds-pink-table table-stack">
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
