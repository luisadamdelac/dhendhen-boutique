<?php $stats = $stats ?? []; ?>

<div class="page-header-section">
    <div>
        <h1 class="page-title"><i class="fas fa-home"></i> Dashboard</h1>
    </div>
</div>


<div class="row g-3 mt-3">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fce4ec;color:#b91c5a;"><i class="fas fa-peso-sign"></i></div>
            <div>
                <div class="stat-label">Total Sales</div>
                <div class="stat-value">₱<?php echo number_format($stats['total_sales'] ?? 0, 2); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eef0ff;color:#4361ee;"><i class="fas fa-shopping-bag"></i></div>
            <div>
                <div class="stat-label">Total Orders</div>
                <div class="stat-value"><?php echo number_format($stats['total_orders'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff8e1;color:#f57f17;"><i class="fas fa-clock"></i></div>
            <div>
                <div class="stat-label">Pending Orders</div>
                <div class="stat-value"><?php echo number_format($stats['pending_orders'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e3f2fd;color:#1565c0;"><i class="fas fa-cog fa-spin"></i></div>
            <div>
                <div class="stat-label">Processing</div>
                <div class="stat-value"><?php echo number_format($stats['processing_orders'] ?? 0); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ede7f6;color:#5e35b1;"><i class="fas fa-truck"></i></div>
            <div>
                <div class="stat-label">To Ship</div>
                <div class="stat-value"><?php echo number_format($stats['to_ship_orders'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="stat-label">Delivered</div>
                <div class="stat-value"><?php echo number_format($stats['delivered_orders'] ?? 0); ?></div>
            </div>
        </div>
    </div>
</div>

