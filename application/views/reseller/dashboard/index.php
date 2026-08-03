<?php
$stats = $stats ?? [];
$this_week = $this_week ?? [];
$last_week = $last_week ?? [];
$trend = $trend ?? [];
$commission = $commission ?? ['total_earned' => 0, 'pending' => 0, 'approved' => 0, 'paid' => 0];
$recent_orders = $recent_orders ?? [];
$top_products = $top_products ?? [];

$avatarPalette = ['#7C3AED', '#2563EB', '#D97706', '#DB2777', '#0891B2'];

/** icon, bg/fg colors, label, value, stat key (for trend/sparkline lookup) */
$statCards = [
    ['icon' => 'fa-peso-sign', 'bg' => '#fce4ec', 'fg' => '#b91c5a', 'label' => 'Total Sales', 'value' => '₱' . number_format($stats['total_sales'] ?? 0, 2), 'key' => 'total_sales', 'field' => 'sales'],
    ['icon' => 'fa-shopping-bag', 'bg' => '#eef0ff', 'fg' => '#4361ee', 'label' => 'Total Orders', 'value' => number_format($stats['total_orders'] ?? 0), 'key' => 'total_orders', 'field' => 'orders'],
    ['icon' => 'fa-clock', 'bg' => '#fff8e1', 'fg' => '#f57f17', 'label' => 'Pending Orders', 'value' => number_format($stats['pending_orders'] ?? 0), 'key' => 'pending_orders', 'field' => 'pending'],
    ['icon' => 'fa-truck', 'bg' => '#ede7f6', 'fg' => '#5e35b1', 'label' => 'To Ship', 'value' => number_format($stats['to_ship_orders'] ?? 0), 'key' => 'to_ship_orders', 'field' => 'to_ship'],
    ['icon' => 'fa-check-circle', 'bg' => '#e8f5e9', 'fg' => '#2e7d32', 'label' => 'Delivered', 'value' => number_format($stats['delivered_orders'] ?? 0), 'key' => 'delivered_orders', 'field' => 'delivered'],
];

$totalOrdersForDonut = max(1, (int) ($stats['total_orders'] ?? 0));
$completionPct = round((($stats['delivered_orders'] ?? 0) / $totalOrdersForDonut) * 100);
$statusBreakdown = [
    ['label' => 'Delivered', 'color' => '#22C55E', 'count' => $stats['delivered_orders'] ?? 0],
    ['label' => 'To Ship', 'color' => '#7C3AED', 'count' => $stats['to_ship_orders'] ?? 0],
    ['label' => 'Processing', 'color' => '#3B82F6', 'count' => $stats['processing_orders'] ?? 0],
    ['label' => 'Pending', 'color' => '#F59E0B', 'count' => $stats['pending_orders'] ?? 0],
    ['label' => 'Cancelled', 'color' => '#EC4899', 'count' => $stats['cancelled_orders'] ?? 0],
];
?>
<style>
/* ── Stat cards with a trend line + sparkline (Dashboard-only layout) ── */
.stat-card-trend { flex-direction: column; align-items: stretch; gap: .85rem; min-height: auto; padding: 1.1rem 1.15rem .95rem; }
.stat-card-trend-top { display: flex; align-items: center; gap: .85rem; }
.stat-card-trend-bottom { display: flex; align-items: center; justify-content: space-between; gap: .5rem; border-top: 1px solid var(--border); padding-top: .6rem; }
.stat-trend { font-size: 11.5px; font-weight: 700; white-space: nowrap; display: flex; align-items: center; gap: 4px; }
.stat-trend.up { color: #16A34A; }
.stat-trend.down { color: #DC2626; }
.stat-trend.flat { color: var(--gray); }
.stat-sparkline-wrap { width: 84px; height: 28px; flex-shrink: 0; }

/* Stat card grid built with plain CSS Grid instead of Bootstrap
   row/col-6/col-md-4/col-xl — grid's own box is exactly the available
   width with no negative-margin/padding gutter compensation to keep in
   sync, so its left/right edges always match the hero card above it. */
.rdb-stats-grid,
.rdb-row-8-4,
.rdb-row-5-4-3 {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

/* Grid items default to min-width:auto, i.e. they refuse to shrink below
   the natural width of their content (a Chart.js canvas, nowrap text like
   "Last 7 Days") — without this, that content pushes the column (and the
   whole row) wider than the viewport instead of fitting inside it. */
.rdb-stats-grid > *,
.rdb-row-8-4 > *,
.rdb-row-5-4-3 > * {
    min-width: 0;
}
@media (min-width: 576px) {
    .rdb-stats-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 768px) {
    .rdb-stats-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (min-width: 1200px) {
    .rdb-stats-grid { grid-template-columns: repeat(5, 1fr); }
}

/* These two rows only ever split into columns at the xl breakpoint
   (Bootstrap's old col-12 col-xl-N pattern), so they stay single-column
   until then — see the shared base rule above. */
@media (min-width: 1200px) {
    .rdb-row-8-4 { grid-template-columns: 2fr 1fr; }
    .rdb-row-5-4-3 { grid-template-columns: 5fr 4fr 3fr; }
}

.order-row { display: flex; align-items: center; gap: .85rem; padding: 12px 4px; border-bottom: 1px solid var(--border); }
.order-row:last-child { border-bottom: none; }
.order-avatar { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 13px; flex-shrink: 0; }
.order-row-info { flex: 1; min-width: 0; }
.order-row-title { font-weight: 600; font-size: var(--font-size-sm); color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.order-row-meta { font-size: var(--font-size-xs); color: var(--gray); }
.order-row-amount { text-align: right; flex-shrink: 0; }
.order-row-amount strong { font-size: var(--font-size-sm); color: var(--text); display: block; margin-bottom: 4px; }

.commission-total { font-size: 2rem; font-weight: 800; color: var(--text); letter-spacing: -0.5px; }
.commission-breakdown-row { display: flex; justify-content: space-between; align-items: center; font-size: var(--font-size-sm); font-weight: 600; margin-bottom: 4px; }
.commission-progress { height: 6px; border-radius: var(--radius-full); background: var(--page-bg); overflow: hidden; margin-bottom: 14px; }
.commission-progress-fill { height: 100%; border-radius: var(--radius-full); }

.perf-legend-row { display: flex; align-items: center; justify-content: space-between; padding: 7px 0; font-size: var(--font-size-sm); }
.perf-legend-row .dot { width: 9px; height: 9px; border-radius: 50%; display: inline-block; margin-right: 8px; }

/* ── Hero: decorative shopping bags + leaves, faded into the banner ── */
.rdb-hero-text { position: relative; z-index: 1; max-width: 62%; }
.rdb-hero-illustration {
    position: absolute; right: 2rem; top: 50%; transform: translateY(-50%);
    width: 260px; height: 150px; z-index: 1; pointer-events: none;
    -webkit-mask-image: linear-gradient(to left, #000 40%, transparent 92%);
            mask-image: linear-gradient(to left, #000 40%, transparent 92%);
}
.rdb-hero-leaves { position: absolute; top: -8px; right: 6px; width: 110px; height: 110px; opacity: .55; }
.rdb-bag {
    position: absolute; border-radius: 6px 6px 10px 10px;
    clip-path: polygon(12% 0%, 88% 0%, 100% 100%, 0% 100%);
    box-shadow: 0 12px 20px rgba(43, 22, 38, .12);
}
.rdb-bag::before {
    content: ""; position: absolute; top: -18px; left: 50%; transform: translateX(-50%);
    width: 26px; height: 22px; border: 3px solid; border-bottom: none; border-radius: 50% 50% 0 0;
}
.rdb-bag--a { width: 80px; height: 94px; right: 18px; bottom: 4px; background: linear-gradient(160deg, #FFD6E8, #FF8CC5); }
.rdb-bag--a::before { border-color: #FF4FA2; }
.rdb-bag--b { width: 60px; height: 72px; right: 92px; bottom: 0; background: linear-gradient(160deg, #FFFFFF, #FFE6F0); }
.rdb-bag--b::before { border-color: #FFB8D9; }

@media (max-width: 767px) {
    .rdb-hero-text { max-width: 100%; }
    .rdb-hero-illustration { display: none; }
}

/* ── Illustrated empty states (Recent Orders / Top Selling Products) ── */
.rdb-empty { text-align: center; padding: 2.2rem 1rem 1.4rem; }
.rdb-empty-illustration { position: relative; width: 96px; height: 80px; margin: 0 auto 16px; }
.rdb-empty-icon {
    position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
    font-size: 56px;
    background: linear-gradient(135deg, #FF8CC5 0%, #FF4FA2 100%);
    -webkit-background-clip: text; background-clip: text; color: transparent;
    filter: drop-shadow(0 8px 12px rgba(255, 79, 162, .22));
}
.rdb-empty-spark { position: absolute; width: 6px; height: 6px; background: #FFC1D9; transform: rotate(45deg); }
.rdb-empty-spark.s1 { top: 0; left: 14px; }
.rdb-empty-spark.s2 { width: 4px; height: 4px; top: 10px; right: 4px; }
.rdb-empty-spark.s3 { width: 5px; height: 5px; bottom: 8px; left: 0; }
.rdb-empty-title { margin: 0 0 4px; font-size: .92rem; font-weight: 700; color: #1a1a2e; }
.rdb-empty-sub { margin: 0; font-size: .8rem; color: var(--gray); }
</style>

<div class="ds-hero-card mb-3">
    <div class="ds-hero-banner">
        <svg class="ds-hero-wave" viewBox="0 0 1440 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,110 C240,170 480,50 720,90 C960,130 1200,50 1440,100 L1440,200 L0,200 Z" fill="rgba(255,105,180,0.16)"></path>
            <path d="M0,140 C280,80 560,180 840,120 C1080,70 1280,140 1440,130 L1440,200 L0,200 Z" fill="rgba(233,30,99,0.22)"></path>
        </svg>
        <div class="rdb-hero-illustration" aria-hidden="true">
            <svg class="rdb-hero-leaves" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <g fill="#E0559C" opacity="0.18">
                    <path d="M10 10 C40 0 60 20 50 50 C25 45 5 30 10 10 Z"></path>
                    <path d="M55 5 C75 0 90 18 82 38 C62 35 48 20 55 5 Z"></path>
                </g>
            </svg>
            <div class="rdb-bag rdb-bag--b"></div>
            <div class="rdb-bag rdb-bag--a"></div>
        </div>

        <div class="ds-hero-banner-content">
            <div class="rdb-hero-text">
                <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Welcome back, <?php echo htmlspecialchars(get_user_first_name() ?: 'Reseller'); ?>! <span aria-hidden="true">👋</span></h4>
                <small class="text-muted">Here's what's happening with your store today.</small>
            </div>
        </div>
    </div>
</div>

<div class="rdb-stats-grid mt-3">
    <?php foreach ($statCards as $card): ?>
        <?php
            $pct = $trend[$card['key']] ?? 0;
            $trendClass = $pct > 0.05 ? 'up' : ($pct < -0.05 ? 'down' : 'flat');
            $trendIcon = $trendClass === 'up' ? 'fa-arrow-up' : ($trendClass === 'down' ? 'fa-arrow-down' : 'fa-minus');
            $sparkValues = array_column($this_week, $card['field']);
        ?>
        <div class="stat-card stat-card-trend">
            <div class="stat-card-trend-top">
                <div class="stat-icon" style="background:<?php echo $card['bg']; ?>;color:<?php echo $card['fg']; ?>;"><i class="fas <?php echo $card['icon']; ?>"></i></div>
                <div>
                    <div class="stat-label"><?php echo $card['label']; ?></div>
                    <div class="stat-value"><?php echo $card['value']; ?></div>
                </div>
            </div>
            <div class="stat-card-trend-bottom">
                <span class="stat-trend <?php echo $trendClass; ?>">
                    <i class="fas <?php echo $trendIcon; ?>"></i> <?php echo number_format(abs($pct), 1); ?>% vs last 7 days
                </span>
                <div class="stat-sparkline-wrap"><canvas class="stat-sparkline" data-values='<?php echo json_encode($sparkValues); ?>' data-color="<?php echo $card['fg']; ?>"></canvas></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="rdb-row-8-4 mt-1">
    <div>
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-area"></i> Sales Overview</h3>
                <span class="text-muted" style="font-size:.8rem;">Last 7 Days</span>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height:260px;">
                    <canvas id="salesOverviewChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div>
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bullseye"></i> Performance Summary</h3>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height:190px;position:relative;">
                    <canvas id="performanceChart"></canvas>
                    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none;">
                        <div style="font-size:22px;font-weight:800;color:var(--text);"><?php echo $completionPct; ?>%</div>
                        <div style="font-size:10.5px;color:var(--gray);text-transform:uppercase;letter-spacing:.04em;">Order Completion</div>
                    </div>
                </div>
                <div style="margin-top:12px;">
                    <?php foreach ($statusBreakdown as $s): ?>
                        <?php $pct = round(($s['count'] / $totalOrdersForDonut) * 100, 1); ?>
                        <div class="perf-legend-row">
                            <span><span class="dot" style="background:<?php echo $s['color']; ?>;"></span><?php echo $s['label']; ?></span>
                            <strong><?php echo (int) $s['count']; ?> (<?php echo $pct; ?>%)</strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="rdb-row-5-4-3 mt-1">
    <div>
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-receipt"></i> Recent Orders</h3>
                <a href="<?php echo BASE_URL; ?>reseller/orders" style="font-size:.85rem;font-weight:600;">View All Orders</a>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_orders)): ?>
                    <?php foreach ($recent_orders as $i => $o): ?>
                        <?php
                            $custName = trim(($o['first_name'] ?? '') . ' ' . ($o['last_name'] ?? '')) ?: 'Customer';
                            $initial = strtoupper(substr($custName, 0, 1));
                            $avatarColor = $avatarPalette[$i % count($avatarPalette)];
                            $location = $o['municipality'] ?? $o['delivery_city'] ?? '';
                        ?>
                        <div class="order-row">
                            <div class="order-avatar" style="background:<?php echo $avatarColor; ?>;"><?php echo htmlspecialchars($initial); ?></div>
                            <div class="order-row-info">
                                <div class="order-row-title">#<?php echo htmlspecialchars($o['order_number']); ?></div>
                                <div class="order-row-meta"><?php echo date('M j, Y \a\t g:i A', strtotime($o['created_at'])); ?></div>
                                <div class="order-row-meta"><?php echo htmlspecialchars($custName); ?><?php echo $location ? ' • ' . htmlspecialchars($location) : ''; ?></div>
                            </div>
                            <div class="order-row-amount">
                                <strong>₱<?php echo number_format($o['total_amount'], 2); ?></strong>
                                <span class="badge-status badge-<?php echo $o['order_status']; ?>"><?php echo ucwords(str_replace('_', ' ', $o['order_status'])); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="rdb-empty">
                        <div class="rdb-empty-illustration">
                            <i class="fas fa-box-open rdb-empty-icon"></i>
                            <span class="rdb-empty-spark s1"></span>
                            <span class="rdb-empty-spark s2"></span>
                            <span class="rdb-empty-spark s3"></span>
                        </div>
                        <p class="rdb-empty-title">No orders yet.</p>
                        <p class="rdb-empty-sub">You don't have any recent orders.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div>
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-wallet"></i> Commission Summary</h3>
            </div>
            <div class="card-body">
                <div class="commission-total">₱<?php echo number_format($commission['total_earned'], 2); ?></div>
                <p class="text-muted" style="margin-top:-4px;font-size:.8rem;">Total Commission Earned</p>

                <div style="background:var(--page-bg);border-radius:var(--radius-md);padding:14px 16px;margin-top:10px;">
                    <p style="font-weight:700;font-size:.82rem;margin-bottom:12px;"><i class="fas fa-chart-pie" style="color:var(--primary-pink);"></i> Commission Breakdown</p>
                    <?php
                        $breakdownRows = [
                            ['label' => 'Pending', 'amount' => $commission['pending'], 'color' => '#F59E0B'],
                            ['label' => 'Approved', 'amount' => $commission['approved'], 'color' => '#16A34A'],
                            ['label' => 'Paid', 'amount' => $commission['paid'], 'color' => '#3B82F6'],
                        ];
                        $breakdownMax = max(1, $commission['pending'], $commission['approved'], $commission['paid']);
                    ?>
                    <?php foreach ($breakdownRows as $row): ?>
                        <div class="commission-breakdown-row">
                            <span style="color:<?php echo $row['color']; ?>;"><?php echo $row['label']; ?></span>
                            <span>₱<?php echo number_format($row['amount'], 2); ?></span>
                        </div>
                        <div class="commission-progress">
                            <div class="commission-progress-fill" style="width:<?php echo min(100, ($row['amount'] / $breakdownMax) * 100); ?>%;background:<?php echo $row['color']; ?>;"></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <a href="<?php echo BASE_URL; ?>reseller/commission" class="btn btn-outline-primary w-100 mt-3">View Commission Details</a>
            </div>
        </div>
    </div>

    <div>
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-fire"></i> Top Selling Products</h3>
                <a href="<?php echo BASE_URL; ?>reseller/inventory" style="font-size:.85rem;font-weight:600;">View All</a>
            </div>
            <div class="card-body">
                <?php if (!empty($top_products)): ?>
                    <?php $maxSold = max(array_column($top_products, 'total_sold')); ?>
                    <?php foreach ($top_products as $p): ?>
                        <div style="display:flex;align-items:center;gap:12px;padding:10px 4px;border-bottom:1px solid var(--border);">
                            <?php if (!empty($p['product_image'])): ?>
                                <img src="<?php echo base_url($p['product_image']); ?>" alt="" style="width:40px;height:40px;border-radius:var(--radius-md);object-fit:cover;border:1px solid var(--border);flex-shrink:0;">
                            <?php else: ?>
                                <span style="width:40px;height:40px;border-radius:var(--radius-md);background:var(--page-bg);display:flex;align-items:center;justify-content:center;color:var(--gray);flex-shrink:0;"><i class="fas fa-image"></i></span>
                            <?php endif; ?>
                            <div style="flex:1;min-width:0;">
                                <div style="font-weight:600;font-size:.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($p['product_name']); ?></div>
                                <div style="font-size:.72rem;color:var(--gray);margin-bottom:4px;"><?php echo (int) $p['total_sold']; ?> sold</div>
                                <div style="height:4px;border-radius:var(--radius-full);background:var(--page-bg);overflow:hidden;">
                                    <div style="height:100%;border-radius:var(--radius-full);background:var(--gradient-primary);width:<?php echo min(100, ($p['total_sold'] / $maxSold) * 100); ?>%;"></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="rdb-empty">
                        <div class="rdb-empty-illustration">
                            <i class="fas fa-bag-shopping rdb-empty-icon"></i>
                            <span class="rdb-empty-spark s1"></span>
                            <span class="rdb-empty-spark s2"></span>
                            <span class="rdb-empty-spark s3"></span>
                        </div>
                        <p class="rdb-empty-title">No sales yet.</p>
                        <p class="rdb-empty-sub">You haven't sold any products yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const thisWeekLabels = <?php echo json_encode(array_column($this_week, 'label')); ?>;
    const thisWeekSales = <?php echo json_encode(array_column($this_week, 'sales')); ?>;
    const lastWeekSales = <?php echo json_encode(array_column($last_week, 'sales')); ?>;

    new Chart(document.getElementById('salesOverviewChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: thisWeekLabels,
            datasets: [
                {
                    label: 'This Week',
                    data: thisWeekSales,
                    borderColor: '#EC4899',
                    backgroundColor: 'rgba(236, 72, 153, 0.10)',
                    borderWidth: 2.5,
                    tension: 0.35,
                    fill: true,
                    pointBackgroundColor: '#EC4899',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                },
                {
                    label: 'Last Week',
                    data: lastWeekSales,
                    borderColor: '#9CA3AF',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    tension: 0.35,
                    pointRadius: 0,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'top', align: 'end', labels: { boxWidth: 12, usePointStyle: true } },
                tooltip: {
                    backgroundColor: '#fff', titleColor: '#1a1a2e', bodyColor: '#e0559c', bodyFont: { weight: '700' },
                    borderColor: '#f0d9e8', borderWidth: 1, padding: 10, cornerRadius: 10,
                    callbacks: { label: (ctx) => ctx.dataset.label + ': ₱' + ctx.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2 }) },
                },
            },
            scales: {
                y: { beginAtZero: true, ticks: { color: '#9ca3af', callback: (v) => '₱' + v.toLocaleString() }, grid: { color: 'rgba(226,232,240,0.6)' } },
                x: { ticks: { color: '#9ca3af' }, grid: { display: false } },
            },
        },
    });

    const statusData = <?php echo json_encode(array_map(fn($s) => (int) $s['count'], $statusBreakdown)); ?>;
    const statusColors = <?php echo json_encode(array_column($statusBreakdown, 'color')); ?>;
    const statusHasData = statusData.some(v => v > 0);

    new Chart(document.getElementById('performanceChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($statusBreakdown, 'label')); ?>,
            datasets: [{
                data: statusHasData ? statusData : [1],
                backgroundColor: statusHasData ? statusColors : ['#eee'],
                borderWidth: 3,
                borderColor: '#fff',
                hoverOffset: statusHasData ? 8 : 0,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: statusHasData,
                    backgroundColor: '#fff', titleColor: '#1a1a2e', bodyColor: '#e0559c', bodyFont: { weight: '700' },
                    borderColor: '#f0d9e8', borderWidth: 1, padding: 10, cornerRadius: 10,
                },
            },
        },
    });

    document.querySelectorAll('.stat-sparkline').forEach(canvas => {
        const values = JSON.parse(canvas.dataset.values || '[]');
        const color = canvas.dataset.color || '#EC4899';
        new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: values.map((_, i) => i),
                datasets: [{
                    data: values,
                    borderColor: color,
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    tension: 0.4,
                    pointRadius: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: { x: { display: false }, y: { display: false } },
                elements: { line: { borderJoinStyle: 'round' } },
            },
        });
    });
})();
</script>
