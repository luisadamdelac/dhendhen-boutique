<div class="container-fluid py-4 fade-in">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Staff Dashboard</h4>
            <small class="text-muted">Welcome back, <strong><?php echo htmlspecialchars($user_full_name ?? 'Staff'); ?></strong> — order and inventory summary.</small>
        </div>
    </div>

    <!-- Order Stats -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#eef0ff;color:#4361ee;"><i class="fas fa-shopping-cart"></i></div>
                <div>
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-value"><?php echo number_format($order_stats['total_orders'] ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff3e0;color:#e65100;"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="stat-label">Pending</div>
                    <div class="stat-value"><?php echo number_format($order_stats['pending_orders'] ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e3f2fd;color:#1565c0;"><i class="fas fa-cog"></i></div>
                <div>
                    <div class="stat-label">Processing</div>
                    <div class="stat-value"><?php echo number_format($order_stats['processing_orders'] ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ede7f6;color:#5e35b1;"><i class="fas fa-truck"></i></div>
                <div>
                    <div class="stat-label">To Ship</div>
                    <div class="stat-value"><?php echo number_format($order_stats['to_ship_orders'] ?? 0); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Stats -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e0f7fa;color:#00838f;"><i class="fas fa-boxes"></i></div>
                <div>
                    <div class="stat-label">Total Products</div>
                    <div class="stat-value"><?php echo number_format($product_stats['total_products'] ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff3e0;color:#e65100;"><i class="fas fa-exclamation-triangle"></i></div>
                <div>
                    <div class="stat-label">Low Stock</div>
                    <div class="stat-value"><?php echo number_format($product_stats['low_stock_products'] ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ffebee;color:#c62828;"><i class="fas fa-times-circle"></i></div>
                <div>
                    <div class="stat-label">Out of Stock</div>
                    <div class="stat-value"><?php echo number_format($product_stats['out_of_stock_products'] ?? 0); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-shopping-cart"></i> Recent Orders</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($recent_orders)): ?>
                <div class="table-responsive">
                    <table class="table">
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
