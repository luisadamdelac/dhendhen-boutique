<!-- Dashboard Overview -->
<div class="container-fluid py-4 fade-in">

<?php
$is_staff_view = $is_staff_view ?? false;
$is_reseller_view = $is_reseller_view ?? false;
$is_admin_only = !$is_staff_view && !$is_reseller_view;
?>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Admin Dashboard</h4>
            <small class="text-muted">Welcome back, <strong><?= htmlspecialchars($user_full_name ?? get_user_full_name() ?? 'Administrator'); ?></strong> — summary of store activity and performance metrics.</small>
        </div>
    </div>

    <!-- Statistics Cards Row -->
    <div class="row g-3">
        <?php if ($is_admin_only): ?>
        <!-- Total Sales - Admin Only -->
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-dollar-sign"></i></div>
                    <div>
                        <div class="stat-label">Total Sales</div>
                        <div class="stat-value">₱<?php echo number_format($order_stats['total_sales'] ?? 0, 2); ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Total Orders -->
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#eef0ff;color:#4361ee;"><i class="fas fa-shopping-cart"></i></div>
                <div>
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-value"><?php echo number_format($order_stats['total_orders'] ?? 0); ?></div>
                </div>
            </div>
        </div>

        <?php if ($is_admin_only): ?>
        <!-- Active Resellers - Admin Only -->
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ede7f6;color:#5e35b1;"><i class="fas fa-users"></i></div>
                <div>
                    <div class="stat-label">Active Resellers</div>
                    <div class="stat-value"><?php echo number_format($reseller_stats['approved_count'] ?? 0); ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Total Products -->
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e0f7fa;color:#00838f;"><i class="fas fa-boxes"></i></div>
                <div>
                    <div class="stat-label">Total Products</div>
                    <div class="stat-value"><?php echo number_format($product_stats['total_products'] ?? 0); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Status Cards -->
    <div class="row g-3 mt-1">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff8e1;color:#f57f17;"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="stat-label">Pending Orders</div>
                    <div class="stat-value"><?php echo number_format($order_stats['pending_orders'] ?? 0); ?></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e3f2fd;color:#1565c0;"><i class="fas fa-cog fa-spin"></i></div>
                <div>
                    <div class="stat-label">Processing</div>
                    <div class="stat-value"><?php echo number_format($order_stats['processing_orders'] ?? 0); ?></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8eaf6;color:#3949ab;"><i class="fas fa-truck"></i></div>
                <div>
                    <div class="stat-label">To Ship</div>
                    <div class="stat-value"><?php echo number_format($order_stats['shipped_orders'] ?? 0); ?></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-label">Delivered</div>
                    <div class="stat-value"><?php echo number_format($order_stats['delivered_orders'] ?? 0); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Status Cards -->
    <div class="row g-3 mt-1">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff8e1;color:#f57f17;"><i class="fas fa-undo"></i></div>
                <div>
                    <div class="stat-label">Return / Refund</div>
                    <div class="stat-value"><?php echo number_format($refund_stats['pending_requests'] ?? 0); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-wallet"></i></div>
                <div>
                    <div class="stat-label">Paid Orders</div>
                    <div class="stat-value"><?php echo number_format($order_stats['paid_orders'] ?? 0); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ffebee;color:#c62828;"><i class="fas fa-times-circle"></i></div>
                <div>
                    <div class="stat-label">Cancelled Orders</div>
                    <div class="stat-value"><?php echo number_format($order_stats['cancelled_orders'] ?? 0); ?></div>
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
                    <div class="chart-container" style="height: 250px;">
                        <canvas id="commissionChart"></canvas>
                    </div>
                    <div style="margin-top: 20px;">
                        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--secondary-lavender);">
                            <span><i class="fas fa-circle" style="color: #ff69b4;"></i> Pending</span>
                            <strong>₱<?php echo number_format($commission_stats['pending_amount'] ?? 0, 2); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--secondary-lavender);">
                            <span><i class="fas fa-circle" style="color: #ee82ee;"></i> Approved</span>
                            <strong>₱<?php echo number_format($commission_stats['approved_amount'] ?? 0, 2); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 10px 0;">
                            <span><i class="fas fa-circle" style="color: #9370db;"></i> Paid</span>
                            <strong>₱<?php echo number_format($commission_stats['paid_amount'] ?? 0, 2); ?></strong>
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
                </div>
                <div class="card-body">
                    <?php if (!empty($top_products)): ?>
                        <?php foreach ($top_products as $index => $product): ?>
                            <div style="display: flex; align-items: center; padding: 15px 0; border-bottom: 1px solid var(--secondary-lavender);">
                                <div style="width: 40px; height: 40px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; margin-right: 15px;">
                                    <?php echo $index + 1; ?>
                                </div>
<div style="flex: 1;">
                                    <?php echo htmlspecialchars($product['product_name'] ?? $product['name'] ?? 'N/A'); ?>
                                    <p style="margin: 0; font-size: 12px; color: var(--gray);">
                                        <?php echo $product['total_sold'] ?? 0; ?> sold
                                    </p>
                                </div>
                                <div style="text-align: right;">
                                    <strong style="color: var(--primary-pink);">₱<?php echo number_format($product['price'] ?? 0, 2); ?></strong>
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
                </div>
                <div class="card-body">
                    <?php if (!empty($top_resellers)): ?>
                        <?php foreach ($top_resellers as $index => $reseller): ?>
                            <div style="display: flex; align-items: center; padding: 15px 0; border-bottom: 1px solid var(--secondary-lavender);">
                                <div style="width: 40px; height: 40px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; margin-right: 15px;">
                                    <?php echo $index + 1; ?>
                                </div>
                                <div style="flex: 1;">
                                    <h4 style="margin: 0; font-size: 14px; font-weight: 600;"><?php echo htmlspecialchars($reseller['full_name'] ?? 'N/A'); ?></h4>
                                    <p style="margin: 0; font-size: 12px; color: var(--gray);">
                                        <?php echo htmlspecialchars($reseller['email'] ?? 'N/A'); ?>
                                    </p>
                                </div>
                                <div style="text-align: right;">
                                    <strong style="color: var(--primary-pink); font-size: 16px;">₱<?php echo number_format($reseller['total_sales'] ?? 0, 2); ?></strong>
                                    <p style="margin: 0; font-size: 12px; color: var(--gray);">
                                        Commission: ₱<?php echo number_format($reseller['total_commission'] ?? 0, 2); ?>
                                    </p>
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

    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: salesLabels,
            datasets: [{
                label: 'Sales (₱)',
                data: salesValues,
                borderColor: '#ff69b4',
                backgroundColor: 'rgba(255, 105, 180, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#ff69b4',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 105, 180, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#ff69b4',
                    borderWidth: 2,
                    padding: 12,
                    displayColors: false,
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
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    },
                    grid: {
                        color: 'rgba(230, 230, 250, 0.5)'
                    }
                },
                x: {
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

    new Chart(commissionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Approved', 'Paid'],
            datasets: [{
                data: [
                    parseFloat(commissionStats.pending_amount || 0),
                    parseFloat(commissionStats.approved_amount || 0),
                    parseFloat(commissionStats.paid_amount || 0)
                ],
                backgroundColor: [
                    '#ff69b4',
                    '#ee82ee',
                    '#9370db'
                ],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 105, 180, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#ff69b4',
                    borderWidth: 2,
                    padding: 12,
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

