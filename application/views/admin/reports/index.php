<?php
$page_title = 'Reports';
$current_page = 'reports';
?>
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Reports</h4>
            <small class="text-muted">Sales, reseller activity, inventory, financial, commission, and refund reports.</small>
        </div>
    </div>

<div class="row g-3">
    <div class="col-6 col-md-4">
        <a href="<?= site_url('admin/reports/sales'); ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px;text-align:center;padding:24px 16px;">
                <div class="stat-icon mx-auto mb-3" style="background:#ede7f6;color:#5e35b1;">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="fw-bold mb-2" style="font-size:16px;color:#1a1a2e;">Sales Performance</h3>
                <p class="text-muted small mb-0">Revenue, orders, and top products over time</p>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="<?= site_url('admin/reports/reseller_activity'); ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px;text-align:center;padding:24px 16px;">
                <div class="stat-icon mx-auto mb-3" style="background:#e8f5e9;color:#2e7d32;">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="fw-bold mb-2" style="font-size:16px;color:#1a1a2e;">Reseller Activity</h3>
                <p class="text-muted small mb-0">Per-reseller sales and commission performance</p>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="<?= site_url('admin/reports/inventory'); ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px;text-align:center;padding:24px 16px;">
                <div class="stat-icon mx-auto mb-3" style="background:#fff8e1;color:#f57f17;">
                    <i class="fas fa-boxes"></i>
                </div>
                <h3 class="fw-bold mb-2" style="font-size:16px;color:#1a1a2e;">Inventory Status</h3>
                <p class="text-muted small mb-0">Stock levels by branch and category</p>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="<?= site_url('admin/reports/financial'); ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px;text-align:center;padding:24px 16px;">
                <div class="stat-icon mx-auto mb-3" style="background:#e3f2fd;color:#1565c0;">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <h3 class="fw-bold mb-2" style="font-size:16px;color:#1a1a2e;">Financial Records</h3>
                <p class="text-muted small mb-0">Revenue, commissions, withdrawals, and refunds</p>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="<?= site_url('admin/reports/commission'); ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px;text-align:center;padding:24px 16px;">
                <div class="stat-icon mx-auto mb-3" style="background:#e0f2f1;color:#00695c;">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <h3 class="fw-bold mb-2" style="font-size:16px;color:#1a1a2e;">Commission Report</h3>
                <p class="text-muted small mb-0">Reseller commissions by status and by reseller</p>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="<?= site_url('admin/reports/refund'); ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px;text-align:center;padding:24px 16px;">
                <div class="stat-icon mx-auto mb-3" style="background:#ffebee;color:#c62828;">
                    <i class="fas fa-undo"></i>
                </div>
                <h3 class="fw-bold mb-2" style="font-size:16px;color:#1a1a2e;">Refund Report</h3>
                <p class="text-muted small mb-0">Refund requests, approvals, and amounts</p>
            </div>
        </a>
    </div>
</div>
</div><!-- /container-fluid -->
