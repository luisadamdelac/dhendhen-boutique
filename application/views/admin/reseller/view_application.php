<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Review Upgrade Request</h4>
            <small class="text-muted">Full details for this customer's reseller application.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/reseller/applications'); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Requests
            </a>
        </div>
    </div>

    <?php
        $spend = (float) ($application['qualified_spend'] ?? 0);
        $minSpend = (float) ($minimum_spend ?? 0);
        $pct = $minSpend > 0 ? min(100, ($spend / $minSpend) * 100) : 0;
        $isEligible = !empty($application['is_eligible']);
        $aStatus = $application['status'] ?? 'pending';
    ?>

    <div class="row g-3">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm" style="border-radius:12px;">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="fas fa-user text-muted me-1"></i> Applicant</h6>
                    <div class="row g-2 mb-3">
                        <div class="col-6"><small class="text-muted d-block">Name</small><span class="fw-semibold"><?= htmlspecialchars(trim(($application['first_name'] ?? '') . ' ' . ($application['last_name'] ?? '')) ?: '-'); ?></span></div>
                        <div class="col-6"><small class="text-muted d-block">Email</small><span class="fw-semibold"><?= htmlspecialchars($application['email'] ?? '-'); ?></span></div>
                        <div class="col-6"><small class="text-muted d-block">Contact Number</small><span class="fw-semibold"><?= htmlspecialchars($application['contact_number'] ?? '-'); ?></span></div>
                        <div class="col-6"><small class="text-muted d-block">Submitted</small><span class="fw-semibold"><?= !empty($application['created_at']) ? date('M d, Y', strtotime($application['created_at'])) : '-'; ?></span></div>
                    </div>

                    <hr>

                    <h6 class="fw-bold mb-3"><i class="fas fa-store text-muted me-1"></i> Business Details</h6>
                    <div class="row g-2">
                        <div class="col-12"><small class="text-muted d-block">Business Name</small><span class="fw-semibold"><?= htmlspecialchars($application['business_name'] ?? '-'); ?></span></div>
                        <div class="col-12"><small class="text-muted d-block">Address</small><span class="fw-semibold"><?= htmlspecialchars(trim(implode(', ', array_filter([$application['business_street'] ?? '', $application['business_barangay'] ?? '', $application['business_city'] ?? '', $application['business_zip_code'] ?? ''])))) ?: '-'; ?></span></div>
                    </div>

                    <?php if (!empty($application['admin_remarks'])): ?>
                    <hr>
                    <h6 class="fw-bold mb-2"><i class="fas fa-comment text-muted me-1"></i> Admin Remarks</h6>
                    <p class="text-muted small mb-0"><?= htmlspecialchars($application['admin_remarks']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm" style="border-radius:12px;">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="fas fa-chart-line text-muted me-1"></i> Eligibility</h6>
                    <div style="font-size:14px;font-weight:600;">₱<?= number_format($spend, 2); ?> <span class="text-muted" style="font-weight:400;">of ₱<?= number_format($minSpend, 2); ?> required in a single order</span></div>
                    <div style="width:100%;height:8px;background:#eef0ff;border-radius:4px;overflow:hidden;margin:8px 0 12px;">
                        <div style="width:<?= $pct; ?>%;height:100%;background:<?= $isEligible ? '#28a745' : '#f0ad4e'; ?>;"></div>
                    </div>
                    <?php if ($isEligible): ?>
                        <span class="badge-status badge-available"><i class="fas fa-check-circle"></i> Eligible</span>
                    <?php else: ?>
                        <span class="badge-status badge-inactive">Not Eligible</span>
                    <?php endif; ?>

                    <hr>
                    <div class="row g-2">
                        <div class="col-6"><small class="text-muted d-block">Completed Orders</small><span class="fw-semibold"><?= number_format((int) ($application['completed_orders'] ?? 0)); ?></span></div>
                        <div class="col-6"><small class="text-muted d-block">Last Delivered</small><span class="fw-semibold"><?= !empty($application['last_delivered_at']) ? date('M d, Y', strtotime($application['last_delivered_at'])) : '-'; ?></span></div>
                    </div>

                    <hr>
                    <div><small class="text-muted d-block mb-1">Status</small><span class="badge-status badge-<?= $aStatus; ?>"><?= ucfirst($aStatus); ?></span></div>

                    <?php if ($aStatus === 'pending'): ?>
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-success flex-fill approve-btn" data-id="<?= $application['application_id']; ?>">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button class="btn btn-outline-danger flex-fill reject-btn" data-id="<?= $application['application_id']; ?>">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </div>
                    <?php elseif ($aStatus === 'rejected'): ?>
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-success flex-fill approve-btn" data-id="<?= $application['application_id']; ?>" data-reapprove="1">
                            <i class="fas fa-undo"></i> Re-approve
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
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
            // .modal-title has its own color rule that otherwise wins over
            // the header's inherited white.
            document.getElementById('appModalTitle').style.color = '#fff';
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
            document.getElementById('appModalTitle').style.color = '#fff';
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
                    location.href = '<?= site_url('admin/reseller/applications'); ?>';
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
