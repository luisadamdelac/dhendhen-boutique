<?php
$summary = $salesData['summary'] ?? ['total_sales' => 0, 'total_orders' => 0, 'total_customers' => 0, 'average_order_value' => 0, 'total_sales_change' => 0, 'total_orders_change' => 0, 'total_customers_change' => 0, 'average_order_value_change' => 0];
$chartNotes = $salesData['chartNotes'] ?? ['highest' => null, 'lowest' => null, 'growth_pct' => 0, 'average_monthly' => 0];

$trendPill = function ($pct) {
    $pct = (float) $pct;
    if (abs($pct) < 0.05) {
        return ['icon' => 'fa-minus', 'color' => '#8a94ad', 'text' => '0% vs last 6 months'];
    }
    $isUp = $pct > 0;
    return [
        'icon' => $isUp ? 'fa-arrow-up' : 'fa-arrow-down',
        'color' => $isUp ? '#16a34a' : '#dc2626',
        'text' => number_format(abs($pct), 1) . '% vs last 6 months',
    ];
};
?>
<style>
.sr-hero-illustration { position: absolute; right: 24px; bottom: 0; display: flex; align-items: flex-end; gap: 8px; opacity: .5; z-index: 0; pointer-events: none; }
.sr-hero-illustration .bar { width: 16px; border-radius: 4px 4px 0 0; background: var(--primary-pink-dark); }

.sr-trend { font-size: .74rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; margin-top: 2px; }

.sr-chart-card .card-header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; }
.sr-range-select {
    display: inline-flex; align-items: center; gap: 8px; border: 1px solid var(--primary-pink-dark);
    color: var(--primary-pink-dark); background: #fff; border-radius: 999px; padding: .45rem 1rem;
    font-size: .8rem; font-weight: 700;
}
.sr-range-select i.fa-chevron-down { font-size: 10px; opacity: .7; }

.sr-chart-notes { display: flex; flex-wrap: wrap; background: #fdf1f6; border-top: 1px solid var(--border); }
.sr-chart-note { flex: 1 1 220px; display: flex; align-items: center; gap: 10px; padding: 14px 20px; border-right: 1px solid rgba(226,232,240,.6); }
.sr-chart-note:last-child { border-right: none; }
.sr-chart-note .sr-note-icon { width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 13px; }
.sr-chart-note .sr-note-label { font-size: .72rem; font-weight: 700; color: var(--gray); }
.sr-chart-note .sr-note-value { font-size: .84rem; font-weight: 700; color: #1a1a2e; }

.sr-topproducts-card .card-header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; }
.sr-empty-state { text-align: center; padding: 3rem 1rem; }
.sr-empty-state .sr-empty-icon {
    width: 56px; height: 56px; border-radius: 50%; background: var(--primary-pink-light); color: var(--primary-pink-dark);
    display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin: 0 auto 14px;
}
.sr-empty-state h5 { font-size: .95rem; font-weight: 700; color: #1a1a2e; margin-bottom: 4px; }
.sr-empty-state p { font-size: .82rem; color: var(--gray); margin: 0; }
</style>

<div class="ds-hero-card mb-3">
    <div class="ds-hero-banner" style="position:relative;overflow:hidden;">
        <svg class="ds-hero-wave" viewBox="0 0 1440 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,110 C240,170 480,50 720,90 C960,130 1200,50 1440,100 L1440,200 L0,200 Z" fill="rgba(255,105,180,0.16)"></path>
            <path d="M0,140 C280,80 560,180 840,120 C1080,70 1280,140 1440,130 L1440,200 L0,200 Z" fill="rgba(233,30,99,0.22)"></path>
        </svg>
        <div class="sr-hero-illustration">
            <div class="bar" style="height:26px;"></div>
            <div class="bar" style="height:42px;"></div>
            <div class="bar" style="height:18px;"></div>
        </div>
        <div class="ds-hero-banner-content d-flex align-items-center gap-3">
            <div style="width:46px;height:46px;border-radius:14px;background:var(--primary-pink-dark);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
                <i class="fas fa-chart-column"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Sales Report</h4>
                <small class="text-muted">Overview of your sales performance and top products.</small>
            </div>
        </div>
    </div>
</div>

<!-- Summary stat cards -->
<div class="row g-3 mb-3">
    <?php
        $statCards = [
            ['icon' => 'fa-dollar-sign', 'bg' => '#ffe3ee', 'fg' => '#e0559c', 'label' => 'Total Sales', 'value' => '₱' . number_format($summary['total_sales'], 2), 'change' => $summary['total_sales_change']],
            ['icon' => 'fa-bag-shopping', 'bg' => '#ffe3ee', 'fg' => '#e0559c', 'label' => 'Total Orders', 'value' => number_format($summary['total_orders']), 'change' => $summary['total_orders_change']],
            ['icon' => 'fa-users', 'bg' => '#ffe3ee', 'fg' => '#e0559c', 'label' => 'Total Customers', 'value' => number_format($summary['total_customers']), 'change' => $summary['total_customers_change']],
            ['icon' => 'fa-arrow-trend-up', 'bg' => '#ffe3ee', 'fg' => '#e0559c', 'label' => 'Average Order Value', 'value' => '₱' . number_format($summary['average_order_value'], 2), 'change' => $summary['average_order_value_change']],
        ];
    ?>
    <?php foreach ($statCards as $card): ?>
        <?php $trend = $trendPill($card['change']); ?>
        <div class="col-6 col-md-3">
            <div class="ds-stat-tile">
                <div class="ds-stat-tile-icon" style="background:<?php echo $card['bg']; ?>;color:<?php echo $card['fg']; ?>;"><i class="fas <?php echo $card['icon']; ?>"></i></div>
                <div>
                    <div class="ds-stat-tile-label"><?php echo $card['label']; ?></div>
                    <div class="ds-stat-tile-value"><?php echo $card['value']; ?></div>
                    <div class="sr-trend" style="color:<?php echo $trend['color']; ?>;"><i class="fas <?php echo $trend['icon']; ?>"></i> <?php echo $trend['text']; ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Monthly Sales chart -->
<div class="card sr-chart-card mb-3">
    <div class="card-header">
        <h3 class="mb-0"><i class="fas fa-calendar-alt" style="color:var(--primary-pink-dark);"></i> Monthly Sales (Last 6 Months)</h3>
        <span class="sr-range-select"><i class="fas fa-calendar"></i> Last 6 Months <i class="fas fa-chevron-down"></i></span>
    </div>
    <div class="card-body">
        <?php if (!empty($salesData['monthly'])): ?>
            <div class="chart-container" style="height:280px;">
                <canvas id="monthlySalesChart"></canvas>
            </div>
        <?php else: ?>
            <div class="sr-empty-state">
                <div class="sr-empty-icon"><i class="fas fa-chart-line"></i></div>
                <h5>No sales data available</h5>
                <p>Your monthly sales chart will appear here once you have orders.</p>
            </div>
        <?php endif; ?>
    </div>
    <?php if (!empty($salesData['monthly'])): ?>
        <div class="sr-chart-notes">
            <div class="sr-chart-note">
                <div class="sr-note-icon" style="background:#ffe3ee;color:#e0559c;"><i class="fas fa-arrow-trend-up"></i></div>
                <div>
                    <div class="sr-note-label">Highest Month</div>
                    <div class="sr-note-value"><?php echo date('F', strtotime($chartNotes['highest']['month'] . '-01')); ?> (₱<?php echo number_format($chartNotes['highest']['amount'], 2); ?>)</div>
                </div>
            </div>
            <div class="sr-chart-note">
                <div class="sr-note-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-arrow-trend-down"></i></div>
                <div>
                    <div class="sr-note-label">Lowest Month</div>
                    <div class="sr-note-value"><?php echo date('F', strtotime($chartNotes['lowest']['month'] . '-01')); ?> (₱<?php echo number_format($chartNotes['lowest']['amount'], 2); ?>)</div>
                </div>
            </div>
            <div class="sr-chart-note">
                <div class="sr-note-icon" style="background:#eef0ff;color:#4361ee;"><i class="fas fa-chart-line"></i></div>
                <div>
                    <div class="sr-note-label">Total Growth</div>
                    <div class="sr-note-value"><i class="fas <?php echo $chartNotes['growth_pct'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'; ?>" style="font-size:10px;"></i> <?php echo number_format(abs($chartNotes['growth_pct']), 1); ?>% vs last 6 months</div>
                </div>
            </div>
            <div class="sr-chart-note">
                <div class="sr-note-icon" style="background:#fff8e1;color:#f57f17;"><i class="fas fa-calculator"></i></div>
                <div>
                    <div class="sr-note-label">Average Monthly Sales</div>
                    <div class="sr-note-value">₱<?php echo number_format($chartNotes['average_monthly'], 2); ?></div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Top Selling Products -->
<div class="card ds-pink-table-card sr-topproducts-card">
    <div class="card-header">
        <h3 class="mb-0"><i class="fas fa-trophy" style="color:var(--primary-pink-dark);"></i> Top Selling Products</h3>
        <a href="<?php echo BASE_URL; ?>reseller/inventory" class="btn-icon btn-outline-primary" title="View All Products" aria-label="View All Products"><i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($salesData['topProducts'])): ?>
            <div class="table-responsive">
                <table class="table ds-pink-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:70px;">Rank</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th class="text-center">Quantity Sold</th>
                            <th class="text-end pe-4">Total Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($salesData['topProducts'] as $i => $product): ?>
                            <tr>
                                <td><span class="badge badge-primary">#<?php echo $i + 1; ?></span></td>
                                <td><strong><?php echo htmlspecialchars($product['product_name']); ?></strong></td>
                                <td><span class="text-muted"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></span></td>
                                <td class="text-center"><?php echo number_format($product['total_sold']); ?></td>
                                <td class="text-end pe-4"><strong>₱<?php echo number_format($product['revenue'], 2); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="sr-empty-state">
                <div class="sr-empty-icon"><i class="fas fa-box-open"></i></div>
                <h5>No product sales data available.</h5>
                <p>Top selling products will appear here once data is available.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($salesData['monthly'])): ?>
<script>
(function () {
    const labels = <?php echo json_encode(array_map(fn($m) => date('M', strtotime($m['month'] . '-01')), $salesData['monthly'])); ?>;
    const sales = <?php echo json_encode(array_map(fn($m) => (float) $m['total_sales'], $salesData['monthly'])); ?>;

    new Chart(document.getElementById('monthlySalesChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Sales',
                data: sales,
                borderColor: '#EC4899',
                backgroundColor: 'rgba(236, 72, 153, 0.12)',
                borderWidth: 2.5,
                tension: 0.35,
                fill: true,
                pointBackgroundColor: '#EC4899',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#fff', titleColor: '#1a1a2e', bodyColor: '#e0559c', bodyFont: { weight: '700' },
                    borderColor: '#f0d9e8', borderWidth: 1, padding: 10, cornerRadius: 10,
                    callbacks: { label: (ctx) => '₱' + ctx.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2 }) },
                },
            },
            scales: {
                y: { beginAtZero: true, ticks: { color: '#9ca3af', callback: (v) => v >= 1000 ? (v / 1000) + 'K' : v }, grid: { color: 'rgba(226,232,240,0.6)' } },
                x: { ticks: { color: '#9ca3af' }, grid: { display: false } },
            },
        },
    });
})();
</script>
<?php endif; ?>
