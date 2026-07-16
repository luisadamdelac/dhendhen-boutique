<?php
$page_title = 'Commission Transactions';
$current_page = 'commission';
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.css">
<style>
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid var(--border); border-radius: var(--radius-md);
    padding: .4rem .75rem; font-size: .85rem; margin-left: .5rem;
}
.dataTables_wrapper .dataTables_filter input:focus { outline: none; border-color: var(--primary-pink); }
table.dataTable thead th { position: relative; }
table.dataTable thead th.sorting:hover { color: var(--primary-pink); cursor: pointer; }
</style>

<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Commission Transactions</h4>
            <small class="text-muted">Track reseller commission earnings and payout status.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/commission/statistics'); ?>" class="btn btn-info btn-sm"><i class="fas fa-chart-bar me-1"></i> Statistics</a>
        </div>
    </div>

    <div class="page-section">
        <span class="section-title"><i class="fas fa-hand-holding-usd me-2 text-primary"></i>Transactions</span>
        <hr>
        <span class="text-muted small" style="white-space:nowrap;"><?= number_format($total); ?> result<?= $total != 1 ? 's' : ''; ?></span>
    </div>

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
                <button type="button" id="clearCommissionFiltersBtn" class="btn btn-sm btn-outline-secondary flex-fill">
                    <i class="fas fa-times"></i> Clear Filters
                </button>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-2" style="border-radius:12px;overflow:hidden;">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table inv-table mb-0" id="commissionsTable">
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
