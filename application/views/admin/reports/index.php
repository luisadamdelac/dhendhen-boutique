<?php
$page_title = 'Reports';
$current_page = 'reports';

$reportCards = [
    ['url' => 'admin/reports/sales',            'icon' => 'fa-arrow-trend-up',   'bg' => '#ffe3ee', 'fg' => '#e0559c', 'wave' => '224,85,156',  'title' => 'Sales Performance',   'desc' => 'Track revenue, orders, and top performing products over time.'],
    ['url' => 'admin/reports/reseller_activity', 'icon' => 'fa-users',            'bg' => '#e8f5e9', 'fg' => '#2e7d32', 'wave' => '46,125,50',   'title' => 'Reseller Activity',   'desc' => 'Monitor reseller sales, performance, and engagement metrics.'],
    ['url' => 'admin/reports/inventory',         'icon' => 'fa-box',              'bg' => '#fff3e0', 'fg' => '#f57c00', 'wave' => '245,124,0',   'title' => 'Inventory Status',    'desc' => 'Check stock levels by branch and product category.'],
    ['url' => 'admin/reports/financial',         'icon' => 'fa-file-invoice-dollar', 'bg' => '#e3f2fd', 'fg' => '#1565c0', 'wave' => '21,101,192',  'title' => 'Financial Records',   'desc' => 'View revenue, payouts, withdrawals, and financial summaries.'],
    ['url' => 'admin/reports/commission',        'icon' => 'fa-hand-holding-usd', 'bg' => '#e0f2f1', 'fg' => '#00695c', 'wave' => '0,105,92',    'title' => 'Commission Report',   'desc' => 'Analyze reseller commissions by status and by reseller.'],
    ['url' => 'admin/reports/refund',            'icon' => 'fa-rotate-left',      'bg' => '#ffebee', 'fg' => '#c62828', 'wave' => '198,40,40',   'title' => 'Refund Report',       'desc' => 'Review refund requests, approvals, and refund amounts.'],
];
?>
<style>
.rp-report-card {
    position: relative;
    overflow: hidden;
    border: none;
    border-radius: 20px;
    box-shadow: 0 4px 18px rgba(43, 22, 38, .07);
    padding: 30px 28px 34px;
    min-height: 200px;
    height: 100%;
    background: #fff;
    transition: transform .18s ease, box-shadow .18s ease;
}
a.rp-report-link { text-decoration: none; display: block; height: 100%; }
a.rp-report-link:hover .rp-report-card { transform: translateY(-3px); box-shadow: 0 10px 26px rgba(43, 22, 38, .12); }

.rp-report-top { display: flex; align-items: center; gap: 16px; position: relative; z-index: 1; }
.rp-report-icon {
    width: 60px; height: 60px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 1.4rem;
}
.rp-report-title { font-size: 1.25rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.rp-report-desc { font-size: .95rem; color: var(--gray, #8a94ad); margin: 16px 0 0; position: relative; z-index: 1; line-height: 1.6; }

.rp-report-wave {
    position: absolute; left: 0; right: 0; bottom: -1px; width: 100%; height: 90px;
    z-index: 0; pointer-events: none;
}
</style>

<div class="container-fluid py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div style="width:46px;height:46px;border-radius:14px;background:var(--primary-pink-dark);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
                <i class="fas fa-chart-line"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Reports</h4>
                <small class="text-muted">Sales, reseller activity, inventory, financial, commission, and refund reports.</small>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <?php foreach ($reportCards as $card): ?>
            <div class="col-6 col-md-4">
                <a href="<?= site_url($card['url']); ?>" class="rp-report-link">
                    <div class="rp-report-card">
                        <div class="rp-report-top">
                            <div class="rp-report-icon" style="background:<?= $card['bg']; ?>;color:<?= $card['fg']; ?>;">
                                <i class="fas <?= $card['icon']; ?>"></i>
                            </div>
                            <h3 class="rp-report-title"><?= $card['title']; ?></h3>
                        </div>
                        <p class="rp-report-desc"><?= $card['desc']; ?></p>
                        <svg class="rp-report-wave" viewBox="0 0 400 70" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M0,45 C90,10 180,55 260,32 C320,15 360,35 400,25 L400,70 L0,70 Z" fill="rgba(<?= $card['wave']; ?>,0.10)"></path>
                            <path d="M0,55 C110,30 220,60 300,40 C340,30 370,42 400,38 L400,70 L0,70 Z" fill="rgba(<?= $card['wave']; ?>,0.16)"></path>
                        </svg>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div><!-- /container-fluid -->
