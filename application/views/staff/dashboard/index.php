<div class="container-fluid py-4 fade-in">

    <div class="ds-hero-card mb-3">

        <div class="ds-hero-banner">
            <svg class="ds-hero-wave" viewBox="0 0 1440 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,110 C240,170 480,50 720,90 C960,130 1200,50 1440,100 L1440,200 L0,200 Z" fill="rgba(255,105,180,0.16)"></path>
                <path d="M0,140 C280,80 560,180 840,120 C1080,70 1280,140 1440,130 L1440,200 L0,200 Z" fill="rgba(233,30,99,0.22)"></path>
            </svg>
            <div class="ds-hero-banner-content">
                <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Staff Dashboard</h4>
                <small class="text-muted">Welcome back, <strong><?php echo htmlspecialchars($user_full_name ?? 'Staff'); ?></strong>: order and inventory summary.</small>
            </div>
        </div>

        <div class="ds-hero-stats">
            <!-- Order Stats -->
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="ds-stat-tile">
                        <div class="ds-stat-tile-icon" style="background:#eef0ff;color:#4361ee;"><i class="fas fa-shopping-cart"></i></div>
                        <div>
                            <div class="ds-stat-tile-label">Total Orders</div>
                            <div class="ds-stat-tile-value"><?php echo number_format($order_stats['total_orders'] ?? 0); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="ds-stat-tile">
                        <div class="ds-stat-tile-icon" style="background:#fff3e0;color:#e65100;"><i class="fas fa-clock"></i></div>
                        <div>
                            <div class="ds-stat-tile-label">Pending</div>
                            <div class="ds-stat-tile-value"><?php echo number_format($order_stats['pending_orders'] ?? 0); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="ds-stat-tile">
                        <div class="ds-stat-tile-icon" style="background:#e3f2fd;color:#1565c0;"><i class="fas fa-cog"></i></div>
                        <div>
                            <div class="ds-stat-tile-label">Processing</div>
                            <div class="ds-stat-tile-value"><?php echo number_format($order_stats['processing_orders'] ?? 0); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="ds-stat-tile">
                        <div class="ds-stat-tile-icon" style="background:#ede7f6;color:#5e35b1;"><i class="fas fa-truck"></i></div>
                        <div>
                            <div class="ds-stat-tile-label">To Ship</div>
                            <div class="ds-stat-tile-value"><?php echo number_format($order_stats['to_ship_orders'] ?? 0); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Stats -->
            <div class="row g-3 mt-1">
                <div class="col-6 col-md-4">
                    <div class="ds-stat-tile">
                        <div class="ds-stat-tile-icon" style="background:#e0f7fa;color:#00838f;"><i class="fas fa-boxes"></i></div>
                        <div>
                            <div class="ds-stat-tile-label">Total Products</div>
                            <div class="ds-stat-tile-value"><?php echo number_format($product_stats['total_products'] ?? 0); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="ds-stat-tile">
                        <div class="ds-stat-tile-icon" style="background:#fff3e0;color:#e65100;"><i class="fas fa-exclamation-triangle"></i></div>
                        <div>
                            <div class="ds-stat-tile-label">Low Stock</div>
                            <div class="ds-stat-tile-value"><?php echo number_format($product_stats['low_stock_products'] ?? 0); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="ds-stat-tile">
                        <div class="ds-stat-tile-icon" style="background:#ffebee;color:#c62828;"><i class="fas fa-times-circle"></i></div>
                        <div>
                            <div class="ds-stat-tile-label">Out of Stock</div>
                            <div class="ds-stat-tile-value"><?php echo number_format($product_stats['out_of_stock_products'] ?? 0); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="card ds-pink-table-card mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-shopping-cart"></i> Recent Orders</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($recent_orders)): ?>
                <div class="table-responsive">
                    <table class="table ds-pink-table">
                        <thead><tr><th>Order #</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                    <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No orders yet.</p>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>staff/orders" class="btn btn-primary mt-2">View All Orders</a>
        </div>
    </div>

</div>
