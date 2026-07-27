<!-- Dashboard Overview -->
<div class="container-fluid py-4 fade-in">

<?php
$is_staff_view = $is_staff_view ?? false;
$is_reseller_view = $is_reseller_view ?? false;
$is_admin_only = !$is_staff_view && !$is_reseller_view;
?>

    <?php
        $hour = (int) date('G');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        $adminDisplayName = htmlspecialchars($user_full_name ?? get_user_full_name() ?? 'Administrator');

        // Renders a stat card's trend line: an up/down arrow with a percentage
        // when a comparable prior period is available, or a flat "No change"
        // otherwise (used for cards with no historical baseline to compare).
        $render_stat_change = function ($pct = null) {
            if ($pct === null || (float) $pct === 0.0) {
                echo '<div class="stat-change neutral"><i class="fas fa-minus"></i> No change</div>';
                return;
            }
            $isPositive = $pct > 0;
            $cssClass = $isPositive ? 'positive' : 'negative';
            $icon = $isPositive ? 'fa-arrow-up' : 'fa-arrow-down';
            echo '<div class="stat-change ' . $cssClass . '"><i class="fas ' . $icon . '"></i> ' . number_format(abs($pct), 1) . '% vs last 30 days</div>';
        };
    ?>
    <div class="ds-hero-card mb-4">
        <div class="ds-hero-banner">
            <svg class="ds-hero-wave" viewBox="0 0 1440 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,110 C240,170 480,50 720,90 C960,130 1200,50 1440,100 L1440,200 L0,200 Z" fill="rgba(255,105,180,0.16)"></path>
                <path d="M0,140 C280,80 560,180 840,120 C1080,70 1280,140 1440,130 L1440,200 L0,200 Z" fill="rgba(233,30,99,0.22)"></path>
            </svg>
            <div class="ds-hero-banner-content dashboard-greeting-row">
                <div class="dashboard-greeting">
                    <h4 class="fw-bold mb-0" style="color:#1a1a2e;"><?= $greeting; ?>, <?= $adminDisplayName; ?>! <span aria-hidden="true">👋</span></h4>
                    <small class="text-muted">Here's what's happening with your store today.</small>
                </div>
                <?php if ($is_admin_only): ?>
                <div class="dashboard-header-actions">
                    <span class="date-badge"><i class="fas fa-calendar"></i> <?php echo date('F j, Y'); ?></span>
                    <a href="<?php echo site_url('admin/reports/export/sales/pdf'); ?>" class="btn-export">
                        <i class="fas fa-download"></i> Export Report
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Statistics Cards Row -->
    <div class="row g-3">
        <?php if ($is_admin_only): ?>
        <!-- Total Sales - Admin Only -->
            <div class="col-12 col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#ffd9ec;color:#e0559c;"><i class="fas fa-dollar-sign"></i></div>
                    <div>
                        <div class="stat-label">Total Sales</div>
                        <div class="stat-value">₱<?php echo number_format($order_stats['total_sales'] ?? 0, 2); ?></div>
                        <?php $render_stat_change($order_stats['sales_change_pct'] ?? null); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Total Orders -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e3f2fd;color:#1565c0;"><i class="fas fa-shopping-cart"></i></div>
                <div>
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-value"><?php echo number_format($order_stats['total_orders'] ?? 0); ?></div>
                    <?php $render_stat_change($order_stats['orders_change_pct'] ?? null); ?>
                </div>
            </div>
        </div>

        <?php if ($is_admin_only): ?>
        <!-- Active Resellers - Admin Only -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ffd9ec;color:#e0559c;"><i class="fas fa-users"></i></div>
                <div>
                    <div class="stat-label">Active Resellers</div>
                    <div class="stat-value"><?php echo number_format($reseller_stats['approved_count'] ?? 0); ?></div>
                    <?php $render_stat_change($reseller_stats['change_pct'] ?? null); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Total Products -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e3f2fd;color:#1565c0;"><i class="fas fa-boxes"></i></div>
                <div>
                    <div class="stat-label">Total Products</div>
                    <div class="stat-value"><?php echo number_format($product_stats['total_products'] ?? 0); ?></div>
                    <?php $render_stat_change(); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Status Cards -->
    <div class="row g-3 mt-1">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ffd9ec;color:#e0559c;"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="stat-label">Pending Orders</div>
                    <div class="stat-value"><?php echo number_format($order_stats['pending_orders'] ?? 0); ?></div>
                    <?php $render_stat_change(); ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e3f2fd;color:#1565c0;"><i class="fas fa-cog fa-spin"></i></div>
                <div>
                    <div class="stat-label">Processing</div>
                    <div class="stat-value"><?php echo number_format($order_stats['processing_orders'] ?? 0); ?></div>
                    <?php $render_stat_change(); ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ffd9ec;color:#e0559c;"><i class="fas fa-truck"></i></div>
                <div>
                    <div class="stat-label">To Ship</div>
                    <div class="stat-value"><?php echo number_format($order_stats['shipped_orders'] ?? 0); ?></div>
                    <?php $render_stat_change(); ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e3f2fd;color:#1565c0;"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-label">Delivered</div>
                    <div class="stat-value"><?php echo number_format($order_stats['delivered_orders'] ?? 0); ?></div>
                    <?php $render_stat_change(); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Status Cards -->
    <div class="row g-3 mt-1">
        <div class="col-12 col-md-6 col-xl-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ffd9ec;color:#e0559c;"><i class="fas fa-undo"></i></div>
                <div>
                    <div class="stat-label">Return / Refund</div>
                    <div class="stat-value"><?php echo number_format($refund_stats['pending_requests'] ?? 0); ?></div>
                    <?php $render_stat_change(); ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e3f2fd;color:#1565c0;"><i class="fas fa-wallet"></i></div>
                <div>
                    <div class="stat-label">Paid Orders</div>
                    <div class="stat-value"><?php echo number_format($order_stats['paid_orders'] ?? 0); ?></div>
                    <?php $render_stat_change(); ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ffd9ec;color:#e0559c;"><i class="fas fa-times-circle"></i></div>
                <div>
                    <div class="stat-label">Cancelled Orders</div>
                    <div class="stat-value"><?php echo number_format($order_stats['cancelled_orders'] ?? 0); ?></div>
                    <?php $render_stat_change(); ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$is_staff_view): ?>
    <!-- Charts Row -->
    <div class="row">
        <!-- Sales Chart -->
        <div class="col col-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-area"></i> Sales Overview
                    </h3>
                    <div>
                        <?php $selected_period = $selected_period ?? 30; ?>
                        <select id="salesPeriod" class="form-control" style="width: auto; display: inline-block;" onchange="location.href = '<?php echo current_url(); ?>?period=' + this.value;">
                            <option value="7" <?php echo $selected_period === 7 ? 'selected' : ''; ?>>Last 7 Days</option>
                            <option value="30" <?php echo $selected_period === 30 ? 'selected' : ''; ?>>Last 30 Days</option>
                            <option value="90" <?php echo $selected_period === 90 ? 'selected' : ''; ?>>Last 90 Days</option>
                            <option value="365" <?php echo $selected_period === 365 ? 'selected' : ''; ?>>Last Year</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Commission Statistics -->
        <div class="col col-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-wallet"></i> Commission Stats
                    </h3>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 250px; position: relative;">
                        <canvas id="commissionChart"></canvas>
                        <div id="commissionChartCenter" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; pointer-events: none;">
                            <div style="font-size: 20px; font-weight: 700; color: var(--dark-gray, #333);" id="commissionChartTotal">₱0.00</div>
                            <div style="font-size: 11px; color: var(--gray, #888); text-transform: uppercase; letter-spacing: .04em;">Total</div>
                        </div>
                    </div>
                    <div style="margin-top: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 4px; border-bottom: 1px solid var(--border);">
                            <span style="display:flex;align-items:center;gap:8px;font-weight:600;color:var(--text);font-size:var(--font-size-sm);"><i class="fas fa-circle" style="color: #ff69b4; font-size: 10px;"></i> Pending</span>
                            <strong style="color:var(--primary-pink-dark);">₱<?php echo number_format($commission_stats['pending_amount'] ?? 0, 2); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 4px;">
                            <span style="display:flex;align-items:center;gap:8px;font-weight:600;color:var(--text);font-size:var(--font-size-sm);"><i class="fas fa-circle" style="color: #1565c0; font-size: 10px;"></i> Approved</span>
                            <strong style="color:var(--primary-pink-dark);">₱<?php echo number_format($commission_stats['approved_amount'] ?? 0, 2); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- (Removed Data Tables Row: Recent Orders + Pending Reseller Applications) -->

    <!-- Top Products and Resellers -->
    <div class="row">
        <!-- Top Products -->
        <div class="col col-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-fire"></i> Top Selling Products
                    </h3>
                    <a href="<?php echo site_url('admin/product'); ?>" class="card-link-more">View All Products <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="card-body">
                    <?php if (!empty($top_products)): ?>
                        <?php foreach ($top_products as $index => $product): ?>
                            <div class="leaderboard-row">
                                <div class="leaderboard-rank"><?php echo $index + 1; ?></div>
                                <?php if (!empty($product['product_image'])): ?>
                                    <img class="leaderboard-thumb" src="<?php echo base_url($product['product_image']); ?>" alt="<?php echo htmlspecialchars($product['product_name'] ?? ''); ?>">
                                <?php else: ?>
                                    <span class="leaderboard-thumb leaderboard-thumb-placeholder"><i class="fas fa-image"></i></span>
                                <?php endif; ?>
                                <div class="leaderboard-info">
                                    <div class="leaderboard-title"><?php echo htmlspecialchars($product['product_name'] ?? $product['name'] ?? 'N/A'); ?></div>
                                    <div class="leaderboard-meta"><?php echo $product['total_sold'] ?? 0; ?> sold</div>
                                </div>
                                <div class="leaderboard-value">
                                    <strong>₱<?php echo number_format($product['price'] ?? 0, 2); ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center">No products data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top Resellers -->
        <div class="col col-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-trophy"></i> Top Performing Resellers
                    </h3>
                    <a href="<?php echo site_url('admin/reseller'); ?>" class="card-link-more">View All Resellers <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="card-body">
                    <?php if (!empty($top_resellers)): ?>
                        <?php foreach ($top_resellers as $index => $reseller): ?>
                            <div class="leaderboard-row">
                                <div class="leaderboard-rank"><?php echo $index + 1; ?></div>
                                <?php if (!empty($reseller['profile_image'])): ?>
                                    <img class="leaderboard-thumb leaderboard-thumb-round" src="<?php echo base_url($reseller['profile_image']); ?>" alt="<?php echo htmlspecialchars($reseller['full_name'] ?? ''); ?>">
                                <?php else: ?>
                                    <span class="leaderboard-thumb leaderboard-thumb-round leaderboard-thumb-placeholder"><i class="fas fa-user"></i></span>
                                <?php endif; ?>
                                <div class="leaderboard-info">
                                    <div class="leaderboard-title"><?php echo htmlspecialchars($reseller['full_name'] ?? 'N/A'); ?></div>
                                    <div class="leaderboard-meta"><?php echo htmlspecialchars($reseller['email'] ?? 'N/A'); ?></div>
                                </div>
                                <div class="leaderboard-value">
                                    <strong>₱<?php echo number_format($reseller['total_sales'] ?? 0, 2); ?></strong>
                                    <p>Commission: ₱<?php echo number_format($reseller['total_commission'] ?? 0, 2); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center">No reseller data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($is_admin_only): ?>
    <!-- Quick Actions -->
    <div class="quick-actions-banner">
        <div class="quick-actions-text">
            <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
            <p>Manage your store efficiently with these shortcuts.</p>
        </div>
        <div class="quick-actions-buttons">
            <a href="<?php echo site_url('admin/product/add'); ?>" class="quick-action-btn">
                <i class="fas fa-box"></i> Add Product
            </a>
            <a href="<?php echo site_url('admin/reseller/applications'); ?>" class="quick-action-btn">
                <i class="fas fa-user-plus"></i> Reseller Applications
            </a>
            <a href="<?php echo site_url('admin/order'); ?>" class="quick-action-btn">
                <i class="fas fa-clipboard-list"></i> View Orders
            </a>
            <a href="<?php echo site_url('admin/reports'); ?>" class="quick-action-btn">
                <i class="fas fa-chart-line"></i> Generate Report
            </a>
        </div>
    </div>
    <?php endif; ?>

</div>


<?php if (!$is_staff_view): ?>
<!-- JavaScript for Charts -->
<script>
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesData = <?php echo json_encode($daily_sales ?? []); ?>;

    const salesLabels = salesData.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });
    const salesValues = salesData.map(item => parseFloat(item.sales));

    const salesFillGradient = salesCtx.createLinearGradient(0, 0, 0, 280);
    salesFillGradient.addColorStop(0, 'rgba(255, 105, 180, 0.28)');
    salesFillGradient.addColorStop(1, 'rgba(255, 105, 180, 0.02)');

    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: salesLabels,
            datasets: [{
                label: 'Sales (₱)',
                data: salesValues,
                borderColor: '#ff69b4',
                backgroundColor: salesFillGradient,
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#ff69b4',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 0,
                pointHoverRadius: 6,
                pointHoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#fff',
                    titleColor: '#1a1a2e',
                    bodyColor: '#e0559c',
                    bodyFont: { weight: '700' },
                    borderColor: '#f0d9e8',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: false,
                    cornerRadius: 10,
                    boxPadding: 4,
                    callbacks: {
                        label: function(context) {
                            return '₱' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#9ca3af',
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    },
                    grid: {
                        color: 'rgba(226, 232, 240, 0.6)'
                    }
                },
                x: {
                    ticks: { color: '#9ca3af' },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Commission Chart (Doughnut)
    const commissionCtx = document.getElementById('commissionChart').getContext('2d');
    const commissionStats = <?php echo json_encode($commission_stats ?? []); ?>;

    const commissionPending = parseFloat(commissionStats.pending_amount || 0);
    const commissionApproved = parseFloat(commissionStats.approved_amount || 0);
    const commissionTotal = commissionPending + commissionApproved;

    document.getElementById('commissionChartTotal').textContent =
        '₱' + commissionTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // With no commissions at all yet, a single real-color slice would just
    // render as a full ring for whichever category happens to be nonzero —
    // show a flat neutral ring instead so an empty state doesn't look like
    // a stuck/broken chart.
    const commissionHasData = commissionTotal > 0;

    new Chart(commissionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Approved'],
            datasets: [{
                data: commissionHasData ? [commissionPending, commissionApproved] : [1],
                backgroundColor: commissionHasData ? [
                    '#ff69b4',
                    '#1565c0'
                ] : ['#eee'],
                borderWidth: 3,
                borderColor: '#fff',
                hoverOffset: commissionHasData ? 10 : 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: commissionHasData,
                    backgroundColor: '#fff',
                    titleColor: '#1a1a2e',
                    bodyColor: '#e0559c',
                    bodyFont: { weight: '700' },
                    borderColor: '#f0d9e8',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 10,
                    boxPadding: 4,
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ₱' + context.parsed.toLocaleString('en-US', {minimumFractionDigits: 2});
                        }
                    }
                }
            }
        }
    });
</script>
<?php endif; ?>

