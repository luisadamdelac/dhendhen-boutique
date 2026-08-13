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
                    <p style="margin-bottom: 6px;"><strong>Customer GCash Number:</strong> <?= htmlspecialchars($refund['gcash_number'] ?? '-'); ?></p>
                    <p style="margin-bottom: 6px;"><strong>Customer GCash Name:</strong> <?= htmlspecialchars($refund['gcash_name'] ?? '-'); ?></p>
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
                    <?php
                        $refundStatus = $refund['status'] ?? 'pending';
                        $statusBadgeClass = [
                            'pending'   => 'badge-warning',
                            'approved'  => 'badge-info',
                            'completed' => 'badge-success',
                            'rejected'  => 'badge-danger',
                            'cancelled' => 'badge-danger',
                        ][$refundStatus] ?? 'badge-warning';
                    ?>
                    <div style="border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 15px;">
                        <small style="color: var(--gray);">Amount</small>
                        <h4 style="color: var(--success); margin: 5px 0;">₱<?= number_format($refund['amount'] ?? 0, 2); ?></h4>
                        <span class="badge <?= $statusBadgeClass; ?>"><?= ucfirst($refundStatus); ?></span>
                    </div>
                </div>
            </div>

            <?php if (($refund['status'] ?? '') === 'pending'): ?>
                <hr style="margin: 20px 0; border: none; border-top: 1px solid var(--border);">
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn btn-success" onclick="refundAction('approve')">Approve</button>
                    <button type="button" class="btn btn-danger" onclick="refundAction('reject')">Reject</button>
                </div>
            <?php elseif (($refund['status'] ?? '') === 'approved'): ?>
                <hr style="margin: 20px 0; border: none; border-top: 1px solid var(--border);">
                <div class="card" style="box-shadow:none; border:1px solid var(--border);">
                    <div style="padding:15px;">
                        <p style="margin:0 0 4px; font-weight:600;">Mark as Refunded</p>
                        <p style="margin:0 0 12px; font-size:13px; color:var(--gray);">
                            Send to <strong><?= htmlspecialchars($refund['gcash_number'] ?? '-'); ?></strong>
                            (<?= htmlspecialchars($refund['gcash_name'] ?? '-'); ?>)
                        </p>
                        <label style="display:flex; align-items:center; gap:8px; margin-bottom:12px; cursor:pointer;">
                            <input type="checkbox" id="itemReceivedCheck" style="width:16px;height:16px;">
                            Item received back in good condition (restocks the item once confirmed)
                        </label>
                        <div class="row" style="align-items: center;">
                            <div class="col col-6">
                                <input type="text" id="refund_payment_reference" class="form-control" placeholder="13-digit GCash reference number" inputmode="numeric" maxlength="13">
                            </div>
                            <div class="col col-6">
                                <label class="form-label small text-muted mb-1" for="refund_payment_proof">Proof of payment (screenshot)</label>
                                <input type="file" id="refund_payment_proof" class="form-control" accept="image/jpeg,image/png,image/webp">
                            </div>
                        </div>
                        <div style="margin-top:12px;">
                            <button type="button" class="btn btn-primary" onclick="refundAction('complete')">
                                <i class="fas fa-check-double"></i> Mark as Refunded
                            </button>
                        </div>
                    </div>
                </div>
            <?php elseif (($refund['status'] ?? '') === 'completed'): ?>
                <hr style="margin: 20px 0; border: none; border-top: 1px solid var(--border);">
                <div style="background:#d4edda; color:#155724; border-radius:var(--radius-lg); padding:15px;">
                    <p style="margin:0 0 4px;"><i class="fas fa-check-circle"></i> <strong>Refunded</strong>: item received back and payment sent.</p>
                    <p style="margin:0; font-size:13px;">GCash Reference: <strong><?= htmlspecialchars($refund['payment_reference'] ?? '-'); ?></strong>
                        <?php if (!empty($refund['completed_at'])): ?>
                            &middot; Paid on <?= date('F j, Y g:i A', strtotime($refund['completed_at'])); ?>
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($refund['payment_proof'])): ?>
                        <p style="margin:10px 0 4px;font-size:13px;">Proof of payment:</p>
                        <a href="<?= BASE_URL . htmlspecialchars($refund['payment_proof']); ?>" target="_blank">
                            <img src="<?= BASE_URL . htmlspecialchars($refund['payment_proof']); ?>" alt="Proof of payment" style="max-width:200px;max-height:200px;border-radius:var(--radius-lg);border:1px solid var(--border);">
                        </a>
                    <?php endif; ?>
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

const REFUND_ACTION_META = {
    approve:  { title: 'Approve', body: 'Approve this refund request? Stock is restocked separately once the returned item is confirmed received.', label: 'Approve', color: '#28a745' },
    reject:   { title: 'Reject Refund', body: 'Reject this refund request?', label: 'Reject', color: '#dc3545' },
    complete: { title: 'Mark as Refunded', body: 'Confirm the item was received back in good condition and the refund payment was sent via GCash? This will restock the item.', label: 'Mark as Refunded', color: '#4361ee' }
};

function refundAction(action) {
    // Same 13-digit GCash reference format as checkout/withdrawal — and the
    // item-received confirmation — required before this can even open the
    // confirm modal, not just checked after the fact.
    if (action === 'complete') {
        if (!document.getElementById('itemReceivedCheck').checked) {
            alert('Please confirm the item was received back in good condition.');
            return;
        }
        if (!/^\d{13}$/.test(document.getElementById('refund_payment_reference').value)) {
            alert('Please enter the 13-digit GCash Reference Number.');
            return;
        }
        if (!document.getElementById('refund_payment_proof').files.length) {
            alert('Please attach proof of payment (a screenshot of the GCash send confirmation).');
            return;
        }
    }

    _pendingRefundAction = action;
    const meta = REFUND_ACTION_META[action];

    document.getElementById('refundActionModalTitle').textContent = meta.title;
    document.getElementById('refundActionModalBody').textContent = meta.body;
    document.getElementById('refundActionModalHeader').style.background = meta.color;
    document.getElementById('refundActionModalHeader').style.color = '#fff';
    // .modal-title has its own color rule that otherwise wins over the
    // header's inherited white, and the confirm button ships with no color
    // class at all (just .btn.btn-sm) — both need to be set here explicitly
    // or they render as barely-visible dark-on-light text.
    document.getElementById('refundActionModalTitle').style.color = '#fff';
    document.getElementById('refundAdminRemarks').value = '';
    document.getElementById('refundRemarksGroup').style.display = action === 'reject' ? 'block' : 'none';
    document.getElementById('refundActionBtnConfirmText').textContent = meta.label;
    document.getElementById('refundActionBtnConfirmSpinner').classList.add('d-none');
    document.getElementById('refundActionBtnConfirm').disabled = false;
    document.getElementById('refundActionBtnConfirm').style.background = meta.color;
    document.getElementById('refundActionBtnConfirm').style.borderColor = meta.color;
    document.getElementById('refundActionBtnConfirm').style.color = '#fff';
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
    const formData = new FormData();
    formData.set('admin_remarks', remarks);
    if (action === 'complete') {
        formData.set('item_received', document.getElementById('itemReceivedCheck').checked ? '1' : '0');
        formData.set('payment_reference', document.getElementById('refund_payment_reference').value);
        formData.set('payment_proof', document.getElementById('refund_payment_proof').files[0]);
    }
    fetch('<?= site_url('admin/refund/'); ?>' + action + '/<?= $refund['refund_id']; ?>', {
        method: 'POST',
        body: formData
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

(function() {
    const refInput = document.getElementById('refund_payment_reference');
    if (!refInput) return;
    refInput.addEventListener('input', function() {
        const digitsOnly = this.value.replace(/\D/g, '').slice(0, 13);
        if (digitsOnly !== this.value) this.value = digitsOnly;
    });
})();
</script>
