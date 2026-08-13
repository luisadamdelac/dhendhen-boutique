<?php
$page_title = 'Withdrawal Details';
$current_page = 'withdrawal';
?>
<div class="container-fluid py-4 fade-in">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1a1a2e;"><i class="fas fa-wallet"></i> Withdrawal Details</h4>
            <small class="text-muted">Review reseller withdrawal request and process payment.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/withdrawal'); ?>" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <?php
        $wStatus = $withdrawal['status'] ?? 'pending';
        $wBadge  = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'completed' => 'info'][$wStatus] ?? 'info';
    ?>

    <div class="row">
        <!-- Left Column -->
        <div class="col col-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Withdrawal #<?= htmlspecialchars($withdrawal['withdrawal_number'] ?? $withdrawal['withdrawal_id']); ?>
                    </h3>
                    <span class="badge badge-<?= $wBadge; ?>"><?= ucfirst($wStatus); ?></span>
                </div>
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--secondary-lavender);">
                        <span style="color: var(--gray);">Reseller</span>
                        <strong><?= htmlspecialchars(trim(($withdrawal['first_name'] ?? '') . ' ' . ($withdrawal['last_name'] ?? '')) ?: '-'); ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--secondary-lavender);">
                        <span style="color: var(--gray);">Email</span>
                        <strong><?= htmlspecialchars($withdrawal['email'] ?? '-'); ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--secondary-lavender);">
                        <span style="color: var(--gray);">GCash Number</span>
                        <strong><?= htmlspecialchars($withdrawal['gcash_number'] ?? '-'); ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; <?= !empty($withdrawal['rejection_reason']) ? 'border-bottom: 1px solid var(--secondary-lavender);' : ''; ?>">
                        <span style="color: var(--gray);">GCash Name</span>
                        <strong><?= htmlspecialchars($withdrawal['gcash_name'] ?? '-'); ?></strong>
                    </div>
                    <?php if (!empty($withdrawal['rejection_reason'])): ?>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0;">
                        <span style="color: var(--gray);">Rejection Reason</span>
                        <strong style="color: var(--danger);"><?= htmlspecialchars($withdrawal['rejection_reason']); ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($wStatus === 'pending'): ?>
                <div class="card">
                    <div style="display: flex; gap: 10px;">
                        <button type="button" class="btn btn-success" onclick="postWithdrawalAction('approve', {})">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button type="button" class="btn btn-danger" onclick="postWithdrawalAction('reject', {admin_remarks: 'Rejected by admin'})">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </div>
                </div>
            <?php elseif ($wStatus === 'approved'):
                $scheduledDate = $withdrawal['scheduled_date'] ?? NULL;
                $isScheduleReached = !$scheduledDate || date('Y-m-d') >= $scheduledDate;
            ?>
                <div class="card">
                    <?php if (!$isScheduleReached): ?>
                        <p style="color: var(--gray); margin-bottom: 14px;">
                            <i class="fas fa-clock"></i> Scheduled for processing on
                            <strong><?= date('F j, Y', strtotime($scheduledDate)); ?></strong>, cannot be marked completed before then.
                        </p>
                    <?php endif; ?>
                    <div class="row" style="align-items: center;">
                        <div class="col col-6">
                            <input type="text" id="payment_reference" class="form-control" placeholder="13-digit GCash reference number" inputmode="numeric" maxlength="13">
                        </div>
                        <div class="col col-6">
                            <button type="button" class="btn btn-primary" <?= $isScheduleReached ? '' : 'disabled title="Not yet at the scheduled processing date"'; ?>
                                    onclick="postWithdrawalAction('mark_processed', {payment_reference: document.getElementById('payment_reference').value})">
                                <i class="fas fa-check-double"></i> Mark as Completed
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column -->
        <div class="col col-4">
            <div class="card stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);">
                    <i class="fas fa-peso-sign"></i>
                </div>
                <div class="stat-value">₱<?= number_format($withdrawal['amount'] ?? 0, 2); ?></div>
                <div class="stat-label">Amount</div>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal" id="wdActionModal">
    <div class="modal-content modal-sm">
        <div class="modal-header" id="wdActionModalHeader">
            <h3 class="modal-title" id="wdActionModalTitle"></h3>
            <button type="button" class="modal-close" onclick="closeModal(document.getElementById('wdActionModal'))">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p id="wdActionModalBody" style="margin:0;"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" id="wdActionBtnCancel" onclick="closeModal(document.getElementById('wdActionModal'))">Cancel</button>
            <button type="button" class="btn btn-sm" id="wdActionBtnConfirm">
                <span id="wdActionBtnConfirmText">Confirm</span>
                <span id="wdActionBtnConfirmSpinner" class="spinner-border spinner-border-sm ms-1 d-none"></span>
            </button>
        </div>
    </div>
</div>

<script>
let _pendingWdAction = null;

const WD_ACTION_META = {
    approve: { title: 'Approve Withdrawal', body: 'Approve this withdrawal request?', label: 'Approve', color: '#28a745' },
    reject: { title: 'Reject Withdrawal', body: 'Reject this withdrawal request?', label: 'Reject', color: '#dc3545' },
    mark_processed: { title: 'Mark as Completed', body: 'Mark this withdrawal as completed and paid out?', label: 'Mark Completed', color: '#4361ee' }
};

function postWithdrawalAction(action, extra) {
    // Same 13-digit GCash reference format as customer checkout — required
    // before the admin can confirm a payout actually went out, not just
    // optional bookkeeping text.
    if (action === 'mark_processed' && !/^\d{13}$/.test((extra && extra.payment_reference) || '')) {
        alert('Please enter the 13-digit GCash Reference Number before marking this withdrawal as completed.');
        return;
    }

    const meta = WD_ACTION_META[action] || { title: 'Confirm Action', body: 'Are you sure?', label: 'Confirm', color: '#4361ee' };
    _pendingWdAction = { action: action, extra: extra };

    document.getElementById('wdActionModalTitle').textContent = meta.title;
    document.getElementById('wdActionModalBody').textContent = meta.body;
    document.getElementById('wdActionModalHeader').style.background = meta.color;
    document.getElementById('wdActionModalHeader').style.color = '#fff';
    // .modal-title has its own color rule that otherwise wins over the
    // header's inherited white, and the confirm button ships with no
    // color class at all (just .btn.btn-sm) — both need to be set here
    // explicitly or they render as barely-visible dark-on-light text.
    document.getElementById('wdActionModalTitle').style.color = '#fff';
    document.getElementById('wdActionBtnConfirmText').textContent = meta.label;
    document.getElementById('wdActionBtnConfirmSpinner').classList.add('d-none');
    document.getElementById('wdActionBtnConfirm').disabled = false;
    document.getElementById('wdActionBtnConfirm').style.background = meta.color;
    document.getElementById('wdActionBtnConfirm').style.borderColor = meta.color;
    document.getElementById('wdActionBtnConfirm').style.color = '#fff';
    document.getElementById('wdActionBtnCancel').disabled = false;
    openModal('wdActionModal');
}

document.getElementById('wdActionBtnConfirm').addEventListener('click', function () {
    if (!_pendingWdAction) return;
    const btn = this;
    btn.disabled = true;
    document.getElementById('wdActionBtnCancel').disabled = true;
    document.getElementById('wdActionBtnConfirmSpinner').classList.remove('d-none');

    const params = new URLSearchParams(_pendingWdAction.extra);
    fetch('<?= site_url('admin/withdrawal/'); ?>' + _pendingWdAction.action + '/<?= $withdrawal['withdrawal_id']; ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            closeModal(document.getElementById('wdActionModal'));
            alert(data.message || 'Failed');
            btn.disabled = false;
            document.getElementById('wdActionBtnCancel').disabled = false;
            document.getElementById('wdActionBtnConfirmSpinner').classList.add('d-none');
        }
    })
    .catch(() => {
        closeModal(document.getElementById('wdActionModal'));
        alert('Request failed');
        btn.disabled = false;
        document.getElementById('wdActionBtnCancel').disabled = false;
        document.getElementById('wdActionBtnConfirmSpinner').classList.add('d-none');
    });
});

(function() {
    const paymentRefInput = document.getElementById('payment_reference');
    if (!paymentRefInput) return;
    paymentRefInput.addEventListener('input', function() {
        const digitsOnly = this.value.replace(/\D/g, '').slice(0, 13);
        if (digitsOnly !== this.value) this.value = digitsOnly;
    });
})();
</script>
