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
</style>

<div class="page-header-section">
    <div>
        <h1 class="page-title">Welcome back, <?php echo htmlspecialchars(get_user_first_name() ?: 'Reseller'); ?>! <span aria-hidden="true">👋</span></h1>
        <p class="text-muted mb-0" style="font-size:.9rem;">Here's what's happening with your store today.</p>
    </div>
</div>

<div class="row g-3 mt-3">
    <?php foreach ($statCards as $card): ?>
        <?php
            $pct = $trend[$card['key']] ?? 0;
            $trendClass = $pct > 0.05 ? 'up' : ($pct < -0.05 ? 'down' : 'flat');
            $trendIcon = $trendClass === 'up' ? 'fa-arrow-up' : ($trendClass === 'down' ? 'fa-arrow-down' : 'fa-minus');
            $sparkValues = array_column($this_week, $card['field']);
        ?>
        <div class="col-6 col-md-4 col-xl">
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
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-3 mt-1">
    <div class="col-12 col-xl-8">
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
    <div class="col-12 col-xl-4">
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

<div class="row g-3 mt-1">
    <div class="col-12 col-xl-5">
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
                    <p class="text-center text-muted" style="padding:20px 0;">No orders yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
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

    <div class="col-12 col-xl-3">
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
                    <p class="text-center text-muted" style="padding:20px 0;">No sales yet.</p>
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
