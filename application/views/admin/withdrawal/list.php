<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.css">

<div class="container-fluid py-4 fade-in">

    <!-- Hero + Stats + Requests header — merged into a single card -->
    <div class="ds-hero-card">

        <div class="ds-hero-banner">
            <svg class="ds-hero-wave" viewBox="0 0 1440 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,110 C240,170 480,50 720,90 C960,130 1200,50 1440,100 L1440,200 L0,200 Z" fill="rgba(255,105,180,0.16)"></path>
                <path d="M0,140 C280,80 560,180 840,120 C1080,70 1280,140 1440,130 L1440,200 L0,200 Z" fill="rgba(233,30,99,0.22)"></path>
            </svg>
            <div class="ds-hero-banner-content d-flex align-items-center gap-3">
                <div style="width:46px;height:46px;border-radius:14px;background:var(--primary-pink-dark);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
                    <i class="fas fa-wallet"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Withdrawal Requests</h4>
                    <small class="text-muted">Review and process reseller commission withdrawal requests.</small>
                </div>
            </div>
        </div>

        <!-- Stat summary -->
        <div class="ds-hero-stats">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="ds-stat-tile">
                        <div class="ds-stat-tile-icon" style="background:#ffe3ee;color:#e0559c;"><i class="fas fa-clock"></i></div>
                        <div>
                            <div class="ds-stat-tile-label">Pending Requests</div>
                            <div class="ds-stat-tile-value"><?= number_format($pending_count ?? 0); ?></div>
                            <div class="ds-stat-tile-sub text-muted">Awaiting review</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="ds-stat-tile">
                        <div class="ds-stat-tile-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-check"></i></div>
                        <div>
                            <div class="ds-stat-tile-label">Approved Requests</div>
                            <div class="ds-stat-tile-value"><?= number_format($approved_count ?? 0); ?></div>
                            <div class="ds-stat-tile-sub" style="color:#2e7d32;">Ready for payout</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="ds-stat-tile">
                        <div class="ds-stat-tile-icon" style="background:#ffebee;color:#c62828;"><i class="fas fa-times"></i></div>
                        <div>
                            <div class="ds-stat-tile-label">Rejected Requests</div>
                            <div class="ds-stat-tile-value"><?= number_format($rejected_count ?? 0); ?></div>
                            <div class="ds-stat-tile-sub" style="color:#c62828;">Declined requests</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="ds-stat-tile">
                        <div class="ds-stat-tile-icon" style="background:#eee8ff;color:#6a3fd6;"><i class="fas fa-peso-sign"></i></div>
                        <div>
                            <div class="ds-stat-tile-label">Total Requested</div>
                            <div class="ds-stat-tile-value" style="font-size: 18px;">₱<?= number_format($total_amount ?? 0, 0); ?></div>
                            <div class="ds-stat-tile-sub" style="color:#6a3fd6;">Total amount</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requests header -->
        <div class="ds-hero-section-row">
            <span class="section-title"><i class="fas fa-wallet me-2 text-primary"></i>Requests</span>
            <span class="text-muted small" style="white-space:nowrap;"><?= number_format($total ?? 0); ?> result<?= ($total ?? 0) != 1 ? 's' : ''; ?></span>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select id="filterWithdrawalStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex">
                    <button type="button" id="clearWithdrawalFiltersBtn" class="btn btn-sm ds-clear-btn flex-fill">
                        <i class="fas fa-times"></i> Clear Filters
                    </button>
                </div>
            </div>
        </div>

    </div>

    <div class="card ds-pink-table-card mb-2">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table inv-table ds-pink-table mb-0 table-stack" id="withdrawalsTable">
                    <thead>
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Reseller</th>
                            <th>Method</th>
                            <th style="text-align: right;">Amount</th>
                            <th style="text-align: center;">Status</th>
                            <th>Requested</th>
                            <th class="text-center pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($withdrawals as $w): ?>
                    <?php
                        $wStatus = $w['status'] ?? 'pending';
                        $wBadge  = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'completed' => 'info'][$wStatus] ?? 'info';
                        $wNum    = $w['withdrawal_number'] ?? ('#' . $w['withdrawal_id']);
                        $rName   = htmlspecialchars(trim(($w['first_name'] ?? '') . ' ' . ($w['last_name'] ?? '')) ?: '—');
                        $method  = $w['payment_method'] ?? $w['method'] ?? 'gcash';
                        $methodColors = ['gcash' => '#007DFF', 'bank_transfer' => '#ff9800', 'cod' => '#4caf50'];
                        $methodIcons  = ['gcash' => 'fa-mobile-alt', 'bank_transfer' => 'fa-university', 'cod' => 'fa-money-bill-wave'];
                        $mc = $methodColors[$method] ?? '#888';
                        $mi = $methodIcons[$method]  ?? 'fa-wallet';
                    ?>
                    <tr data-status="<?= htmlspecialchars($wStatus); ?>">
                        <td class="ps-3" style="font-weight: 700; color: var(--primary-pink-dark);"><?= $wNum; ?></td>
                        <td>
                            <div style="font-weight: 600;"><?= $rName; ?></div>
                            <?php if (!empty($w['email'])): ?>
                                <div style="font-size: 11px; color: var(--gray);"><?= htmlspecialchars($w['email']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:<?= $mc; ?>;background:<?= $mc; ?>15;padding:2px 8px;border-radius:20px;">
                                <i class="fas <?= $mi; ?>"></i> <?= strtoupper(str_replace('_', ' ', $method)); ?>
                            </span>
                        </td>
                        <td style="text-align: right; font-weight: 600;">
                            ₱<?= number_format($w['amount'] ?? 0, 2); ?>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge-status badge-<?= $wStatus; ?>"><?= ucfirst($wStatus); ?></span>
                        </td>
                        <td style="font-size: 12px; color: var(--gray);">
                            <?= !empty($w['created_at']) ? date('M d, Y', strtotime($w['created_at'])) : '—'; ?>
                        </td>
                        <td class="text-center pe-3">
                            <a href="<?= site_url('admin/withdrawal/view/' . $w['withdrawal_id']); ?>"
                               class="ds-action-btn" title="View">
                                <i class="fas fa-eye" style="font-size:11px;"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="<?php echo BASE_URL; ?>public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
    const table = $('#withdrawalsTable').DataTable({
        order: [[0, 'desc']],
        columnDefs: [{ orderable: false, targets: [6] }],
        language: { search: '_INPUT_', searchPlaceholder: 'Search reseller or email…', emptyTable: 'No withdrawal requests found' },
        drawCallback: function() { if (typeof initResponsiveTableStacking === 'function') initResponsiveTableStacking(); }
    });

    $.fn.dataTable.ext.search.push(function (settings, searchData, index) {
        if (settings.nTable.id !== 'withdrawalsTable') return true;
        const statusFilter = $('#filterWithdrawalStatus').val();
        if (!statusFilter) return true;
        const $row = $(settings.aoData[index].nTr);
        return $row.data('status') === statusFilter;
    });

    $('#filterWithdrawalStatus').on('change', function () { table.draw(); });
    $('#clearWithdrawalFiltersBtn').on('click', function () {
        $('#filterWithdrawalStatus').val('');
        table.search('').draw();
    });
});
</script>
