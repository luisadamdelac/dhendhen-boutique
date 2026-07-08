<?php
$page_title = 'Refund Details';
$current_page = 'refund';
?>
<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1a1a2e;"><i class="fas fa-undo"></i> Refund Details</h4>
            <small class="text-muted">Review refund request and take action.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/refund'); ?>" class="btn btn-outline">Back</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col col-8">
                    <h4 style="margin: 0 0 10px 0;">Refund #<?= htmlspecialchars($refund['refund_number'] ?? $refund['refund_id']); ?></h4>
                    <p style="margin-bottom: 6px;"><strong>Order:</strong> #<?= htmlspecialchars($refund['order_id']); ?></p>
                    <p style="margin-bottom: 6px;"><strong>Customer:</strong> <?= htmlspecialchars(trim(($refund['customer_first_name'] ?? '') . ' ' . ($refund['customer_last_name'] ?? '')) ?: '-'); ?></p>
                    <p style="margin-bottom: 6px;"><strong>Email:</strong> <?= htmlspecialchars($refund['email'] ?? '-'); ?></p>
                    <p style="margin-bottom: 6px;"><strong>Reason:</strong> <?= htmlspecialchars($refund['reason'] ?? '-'); ?></p>
                    <?php if (!empty($refund['images'])): ?>
                        <p style="margin-bottom: 6px;"><strong>Evidence:</strong></p>
                        <a href="<?= BASE_URL . htmlspecialchars($refund['images']); ?>" target="_blank">
                            <img src="<?= BASE_URL . htmlspecialchars($refund['images']); ?>" alt="Refund evidence" style="max-width: 240px; max-height: 240px; border-radius: var(--radius-lg); border: 1px solid var(--border);">
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($refund['admin_remarks'])): ?>
                        <p style="margin-bottom: 6px;"><strong>Admin Remarks:</strong> <?= htmlspecialchars($refund['admin_remarks']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col col-4">
                    <div style="border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 15px;">
                        <small style="color: var(--gray);">Amount</small>
                        <h4 style="color: var(--success); margin: 5px 0;">₱<?= number_format($refund['amount'] ?? 0, 2); ?></h4>
                        <span class="badge badge-warning"><?= ucfirst($refund['status'] ?? 'pending'); ?></span>
                    </div>
                </div>
            </div>

            <?php if (($refund['status'] ?? '') === 'pending'): ?>
                <hr style="margin: 20px 0; border: none; border-top: 1px solid var(--border);">
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn btn-success" onclick="refundAction('approve')">Approve &amp; Restock</button>
                    <button type="button" class="btn btn-danger" onclick="refundAction('reject')">Reject</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal" id="refundActionModal">
    <div class="modal-content modal-sm">
        <div class="modal-header" id="refundActionModalHeader">
            <h3 class="modal-title" id="refundActionModalTitle"></h3>
            <button type="button" class="modal-close" onclick="closeModal(document.getElementById('refundActionModal'))">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p id="refundActionModalBody" style="margin:0;"></p>
            <div class="form-group" id="refundRemarksGroup" style="display:none;margin-top:10px;">
                <label for="refundAdminRemarks">Reason for rejection (optional)</label>
                <textarea class="form-control" id="refundAdminRemarks" rows="3" placeholder="Let the customer know why..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" id="refundActionBtnCancel" onclick="closeModal(document.getElementById('refundActionModal'))">Cancel</button>
            <button type="button" class="btn btn-sm" id="refundActionBtnConfirm">
                <span id="refundActionBtnConfirmText">Confirm</span>
                <span id="refundActionBtnConfirmSpinner" class="spinner-border spinner-border-sm ms-1 d-none"></span>
            </button>
        </div>
    </div>
</div>

<script>
let _pendingRefundAction = null;

function refundAction(action) {
    _pendingRefundAction = action;
    const isApprove = action === 'approve';

    document.getElementById('refundActionModalTitle').textContent = isApprove ? 'Approve & Restock' : 'Reject Refund';
    document.getElementById('refundActionModalBody').textContent = isApprove
        ? 'Approve this refund and restock the returned item(s)?'
        : 'Reject this refund request?';
    document.getElementById('refundActionModalHeader').style.background = isApprove ? '#28a745' : '#dc3545';
    document.getElementById('refundActionModalHeader').style.color = '#fff';
    document.getElementById('refundAdminRemarks').value = '';
    document.getElementById('refundRemarksGroup').style.display = isApprove ? 'none' : 'block';
    document.getElementById('refundActionBtnConfirmText').textContent = isApprove ? 'Approve' : 'Reject';
    document.getElementById('refundActionBtnConfirmSpinner').classList.add('d-none');
    document.getElementById('refundActionBtnConfirm').disabled = false;
    document.getElementById('refundActionBtnCancel').disabled = false;
    openModal('refundActionModal');
}

document.getElementById('refundActionBtnConfirm').addEventListener('click', function () {
    if (!_pendingRefundAction) return;
    const action = _pendingRefundAction;
    const btn = this;
    btn.disabled = true;
    document.getElementById('refundActionBtnCancel').disabled = true;
    document.getElementById('refundActionBtnConfirmSpinner').classList.remove('d-none');

    const remarks = action === 'reject' ? document.getElementById('refundAdminRemarks').value : '';
    const params = new URLSearchParams({ admin_remarks: remarks });
    fetch('<?= site_url('admin/refund/'); ?>' + action + '/<?= $refund['refund_id']; ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                closeModal(document.getElementById('refundActionModal'));
                alert(data.message || '');
                btn.disabled = false;
                document.getElementById('refundActionBtnCancel').disabled = false;
                document.getElementById('refundActionBtnConfirmSpinner').classList.add('d-none');
            }
        })
        .catch(() => {
            closeModal(document.getElementById('refundActionModal'));
            alert('Request failed');
            btn.disabled = false;
            document.getElementById('refundActionBtnCancel').disabled = false;
            document.getElementById('refundActionBtnConfirmSpinner').classList.add('d-none');
        });
});
</script>
