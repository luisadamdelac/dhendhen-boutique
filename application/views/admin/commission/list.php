<?php
$page_title = 'Commission Transactions';
$current_page = 'commission';
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.css">

<div class="container-fluid py-4 fade-in">

    <!-- Hero + Transactions header — merged into a single card -->
    <div class="ds-hero-card">

        <div class="ds-hero-banner">
            <svg class="ds-hero-wave" viewBox="0 0 1440 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,110 C240,170 480,50 720,90 C960,130 1200,50 1440,100 L1440,200 L0,200 Z" fill="rgba(255,105,180,0.16)"></path>
                <path d="M0,140 C280,80 560,180 840,120 C1080,70 1280,140 1440,130 L1440,200 L0,200 Z" fill="rgba(233,30,99,0.22)"></path>
            </svg>
            <div class="ds-hero-banner-content d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:46px;height:46px;border-radius:14px;background:var(--primary-pink-dark);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Commission Transactions</h4>
                        <small class="text-muted">Track reseller commission earnings and payout status.</small>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?= site_url('admin/commission/statistics'); ?>" class="btn btn-info btn-sm"><i class="fas fa-chart-bar me-1"></i> Statistics</a>
                </div>
            </div>
        </div>

        <!-- Transactions header -->
        <div class="ds-hero-section-row">
            <span class="section-title"><i class="fas fa-hand-holding-usd me-2 text-primary"></i>Transactions</span>
            <span class="text-muted small" style="white-space:nowrap;"><?= number_format($total); ?> result<?= $total != 1 ? 's' : ''; ?></span>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small text-muted mb-1">Reseller</label>
                    <select id="filterCommissionReseller" class="form-select form-select-sm">
                        <option value="">All Resellers</option>
                        <?php foreach ($resellers as $reseller): ?>
                            <option value="<?= $reseller['reseller_id']; ?>"><?= htmlspecialchars(trim($reseller['first_name'] . ' ' . $reseller['last_name'])); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex">
                    <button type="button" id="clearCommissionFiltersBtn" class="btn btn-sm ds-clear-btn flex-fill">
                        <i class="fas fa-times"></i> Clear Filters
                    </button>
                </div>
            </div>
        </div>

    </div>

    <div class="card ds-pink-table-card mb-2">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table inv-table ds-pink-table mb-0 table-stack" id="commissionsTable">
                    <thead>
                        <tr><th class="ps-3">Transaction ID</th><th>Reseller</th><th>Amount</th><th>Status</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr data-reseller-id="<?= (int) $transaction['reseller_id']; ?>">
                                <td class="ps-3"><a href="<?= site_url('admin/commission/view/' . $transaction['commission_id']); ?>">#<?= htmlspecialchars($transaction['commission_id']); ?></a></td>
                                <td><?= htmlspecialchars(trim(($transaction['first_name'] ?? '') . ' ' . ($transaction['last_name'] ?? '')) ?: '-'); ?></td>
                                <td>₱<?= number_format($transaction['amount'] ?? 0, 2); ?></td>
                                <td><span class="badge-status badge-<?= $transaction['status'] ?? 'pending'; ?>"><?= ucfirst($transaction['status'] ?? 'pending'); ?></span></td>
                                <td><?= !empty($transaction['created_at']) ? date('M d, Y', strtotime($transaction['created_at'])) : '-'; ?></td>
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
    const table = $('#commissionsTable').DataTable({
        order: [[0, 'desc']],
        language: { search: '_INPUT_', searchPlaceholder: 'Search reseller…', emptyTable: 'No commission transactions found' }
    });

    $.fn.dataTable.ext.search.push(function (settings, searchData, index) {
        if (settings.nTable.id !== 'commissionsTable') return true;
        const resellerFilter = $('#filterCommissionReseller').val();
        if (!resellerFilter) return true;
        const $row = $(settings.aoData[index].nTr);
        return String($row.data('reseller-id')) === resellerFilter;
    });

    $('#filterCommissionReseller').on('change', function () { table.draw(); });
    $('#clearCommissionFiltersBtn').on('click', function () {
        $('#filterCommissionReseller').val('');
        table.search('').draw();
    });
});
</script>
