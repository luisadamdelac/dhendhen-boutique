<?php
$page_title = 'Reseller Upgrade Requests';
$current_page = 'reseller';
?>
<div class="container-fluid py-4 fade-in">

    <!-- Page Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Reseller Upgrade Requests</h4>
            <small class="text-muted">Existing customers requesting to become a reseller.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/reseller'); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Resellers
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <select class="form-select form-select-sm" onchange="location.href='<?= site_url('admin/reseller/applications'); ?>?status='+this.value">
                    <option value="pending" <?= $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?= $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?= $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-9 d-flex align-items-center justify-content-end">
                <span class="text-muted small">
                    <?= number_format($total ?? 0); ?> application<?= ($total ?? 0) != 1 ? 's' : ''; ?>
                    &nbsp;·&nbsp; Minimum qualifying spend: ₱<?= number_format($minimum_spend ?? 0, 2); ?>
                </span>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm mb-2" style="border-radius:12px;overflow:hidden;">
        <div class="table-responsive">
            <table class="table inv-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Customer</th>
                        <th>Business Name</th>
                        <th>Qualified Spend</th>
                        <th>Eligibility</th>
                        <th class="text-center">Completed Orders</th>
                        <th>Last Delivered</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($applications)): ?>
                        <?php foreach ($applications as $application): ?>
                        <?php
                            $aStatus = $application['status'] ?? 'pending';
                            $spend = (float) ($application['qualified_spend'] ?? 0);
                            $minSpend = (float) ($minimum_spend ?? 0);
                            $pct = $minSpend > 0 ? min(100, ($spend / $minSpend) * 100) : 0;
                            $isEligible = !empty($application['is_eligible']);
                        ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold"><i class="fas fa-user me-1 text-muted" style="font-size:11px;"></i> <?= htmlspecialchars(trim(($application['first_name'] ?? '') . ' ' . ($application['last_name'] ?? '')) ?: '-'); ?></div>
                                    <div style="font-size:12px;color:#8a94ad;"><i class="fas fa-envelope me-1" style="font-size:10px;"></i><?= htmlspecialchars($application['email'] ?? ''); ?></div>
                                </td>
                                <td class="fw-semibold"><?= htmlspecialchars($application['business_name'] ?? '-'); ?></td>
                                <td>
                                    <div style="font-size:12px;font-weight:600;">₱<?= number_format($spend, 2); ?> <span class="text-muted" style="font-weight:400;">/ ₱<?= number_format($minSpend, 2); ?></span></div>
                                    <div style="width:110px;height:6px;background:#eef0ff;border-radius:4px;overflow:hidden;margin-top:4px;">
                                        <div style="width:<?= $pct; ?>%;height:100%;background:<?= $isEligible ? '#28a745' : '#f0ad4e'; ?>;"></div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($isEligible): ?>
                                        <span class="badge-status badge-available"><i class="fas fa-check-circle"></i> Eligible</span>
                                    <?php else: ?>
                                        <span class="badge-status badge-inactive">Not Eligible</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= number_format((int) ($application['completed_orders'] ?? 0)); ?></td>
                                <td style="font-size:12px;color:#8a94ad;"><?= !empty($application['last_delivered_at']) ? date('M d, Y', strtotime($application['last_delivered_at'])) : '—'; ?></td>
                                <td class="text-center">
                                    <span class="badge-status badge-<?= $aStatus; ?>"><?= ucfirst($aStatus); ?></span>
                                </td>
                                <td class="text-center pe-3">
                                    <?php if ($aStatus === 'pending'): ?>
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="<?= site_url('admin/reseller/view_application/' . $application['application_id']); ?>" class="btn btn-sm btn-outline-info" title="Review" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                                <i class="fas fa-eye" style="font-size:11px;"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-success approve-btn" data-id="<?= $application['application_id']; ?>" title="Approve" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                                <i class="fas fa-check" style="font-size:11px;"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger reject-btn" data-id="<?= $application['application_id']; ?>" title="Reject" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                                <i class="fas fa-times" style="font-size:11px;"></i>
                                            </button>
                                        </div>
                                    <?php elseif ($aStatus === 'rejected'): ?>
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="<?= site_url('admin/reseller/view_application/' . $application['application_id']); ?>" class="btn btn-sm btn-outline-info" title="Review" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                                <i class="fas fa-eye" style="font-size:11px;"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-success approve-btn" data-id="<?= $application['application_id']; ?>" data-reapprove="1" title="Re-approve" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                                <i class="fas fa-undo" style="font-size:11px;"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">Completed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-file-alt fa-2x text-muted mb-2 d-block"></i>
                                <span class="text-muted">No applications found</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pages > 1): ?>
            <div class="d-flex justify-content-center py-2 border-top" style="background:#fafbff;">
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php for ($i = 1; $i <= $pages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="<?= site_url('admin/reseller/applications?page=' . $i . '&status=' . urlencode($status)); ?>"><?= $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal" id="appConfirmModal">
    <div class="modal-content modal-sm">
        <div class="modal-header" id="appModalHeader">
            <h3 class="modal-title" id="appModalTitle"></h3>
            <button type="button" class="modal-close" onclick="closeModal(document.getElementById('appConfirmModal'))">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p id="appModalBody"></p>
            <div class="form-group" id="appRemarksGroup" style="display:none;margin-top:10px;">
                <label for="appAdminRemarks">Reason for rejection (optional)</label>
                <textarea class="form-control" id="appAdminRemarks" rows="3" placeholder="Let the applicant know why..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" id="appBtnCancel" onclick="closeModal(document.getElementById('appConfirmModal'))">Cancel</button>
            <button type="button" class="btn btn-sm" id="appBtnConfirm">
                <span id="appBtnConfirmText">Confirm</span>
                <span id="appBtnConfirmSpinner" class="spinner-border spinner-border-sm ms-1 d-none"></span>
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    var pendingAction = null;

    function resetConfirmBtn(label) {
        var btn = document.getElementById('appBtnConfirm');
        btn.disabled = false;
        document.getElementById('appBtnCancel').disabled = false;
        document.getElementById('appBtnConfirmText').textContent = label;
        document.getElementById('appBtnConfirmSpinner').classList.add('d-none');
    }

    document.querySelectorAll('.approve-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var isReapprove = btn.dataset.reapprove === '1';
            pendingAction = { id: btn.dataset.id, type: 'approve' };
            document.getElementById('appModalTitle').textContent = isReapprove ? 'Re-approve Application' : 'Approve Application';
            document.getElementById('appModalBody').textContent = isReapprove
                ? 'This application was previously rejected. Re-approve it and create a reseller account?'
                : 'Approve this reseller application?';
            document.getElementById('appRemarksGroup').style.display = 'none';
            document.getElementById('appModalHeader').style.background = 'linear-gradient(135deg, #28a745 0%, #218838 100%)';
            document.getElementById('appModalHeader').style.color = '#fff';
            document.getElementById('appBtnConfirm').className = 'btn btn-sm btn-success';
            resetConfirmBtn('Approve');
            openModal('appConfirmModal');
        });
    });

    document.querySelectorAll('.reject-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            pendingAction = { id: btn.dataset.id, type: 'reject' };
            document.getElementById('appModalTitle').textContent = 'Reject Application';
            document.getElementById('appModalBody').textContent = 'Reject this reseller application?';
            document.getElementById('appAdminRemarks').value = '';
            document.getElementById('appRemarksGroup').style.display = 'block';
            document.getElementById('appModalHeader').style.background = 'linear-gradient(135deg, #dc3545 0%, #c82333 100%)';
            document.getElementById('appModalHeader').style.color = '#fff';
            document.getElementById('appBtnConfirm').className = 'btn btn-sm btn-danger';
            resetConfirmBtn('Reject');
            openModal('appConfirmModal');
        });
    });

    document.getElementById('appBtnConfirm').addEventListener('click', function () {
        if (!pendingAction) return;

        var btn = this;
        btn.disabled = true;
        document.getElementById('appBtnCancel').disabled = true;
        document.getElementById('appBtnConfirmSpinner').classList.remove('d-none');

        var url = pendingAction.type === 'approve'
            ? '<?= site_url('admin/reseller/approve_application/'); ?>' + pendingAction.id
            : '<?= site_url('admin/reseller/reject_application/'); ?>' + pendingAction.id;

        var options = { method: 'POST' };
        if (pendingAction.type === 'reject') {
            var remarks = document.getElementById('appAdminRemarks').value;
            options.headers = { 'Content-Type': 'application/x-www-form-urlencoded' };
            options.body = new URLSearchParams({ admin_remarks: remarks }).toString();
        }

        fetch(url, options)
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    location.reload();
                } else {
                    closeModal(document.getElementById('appConfirmModal'));
                    alert(data.message || 'Action failed.');
                    resetConfirmBtn(pendingAction.type === 'approve' ? 'Approve' : 'Reject');
                    pendingAction = null;
                }
            })
            .catch(function () {
                closeModal(document.getElementById('appConfirmModal'));
                alert('Request failed. Please try again.');
                resetConfirmBtn(pendingAction.type === 'approve' ? 'Approve' : 'Reject');
                pendingAction = null;
            });
    });
})();
</script>
