<div class="page-header-section">
    <div>
        <h1 class="page-title"><i class="fas fa-wallet"></i> My Withdrawals</h1>
    </div>
</div>

<div class="row g-3">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-coins"></i></div>
            <div>
                <div class="stat-label">Available Balance</div>
                <div class="stat-value">₱<?php echo number_format($available_balance ?? 0, 2); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eef0ff;color:#4361ee;"><i class="fas fa-arrow-down"></i></div>
            <div>
                <div class="stat-label">Total Withdrawn</div>
                <div class="stat-value">₱<?php echo number_format($total_withdrawn ?? 0, 2); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3><i class="fas fa-paper-plane"></i> Request a Withdrawal</h3>
    </div>
    <div class="card-body">
        <p style="color: var(--gray-600); font-size: 13px; margin: 0 0 15px;">
            <i class="fas fa-calendar-check"></i> Next scheduled processing date: <strong><?php echo date('F j, Y', strtotime($next_schedule_date ?? 'now')); ?></strong>
        </p>
        <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
            <div class="form-group" style="margin:0;">
                <label>Amount (₱)</label>
                <input type="number" id="req_amount" class="form-control" min="1" step="0.01" max="<?php echo $available_balance ?? 0; ?>">
            </div>
            <div class="form-group" style="margin:0;">
                <label>GCash Number</label>
                <input type="text" id="req_gcash_number" class="form-control" maxlength="20" placeholder="09XXXXXXXXX" value="<?php echo htmlspecialchars($saved_gcash_number ?? ''); ?>">
            </div>
            <div class="form-group" style="margin:0;">
                <label>GCash Account Name</label>
                <input type="text" id="req_gcash_name" class="form-control" maxlength="100" value="<?php echo htmlspecialchars($saved_gcash_name ?? ''); ?>">
            </div>
            <div class="form-group" style="margin:0;">
                <button type="button" class="btn btn-primary" onclick="submitWithdrawalRequest()">
                    <i class="fas fa-paper-plane"></i> Submit Request
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3><i class="fas fa-history"></i> Withdrawal History</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($withdrawals)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-stack">
                    <thead>
                        <tr>
                            <th>Withdrawal #</th>
                            <th>GCash</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($withdrawals as $w): ?>
                            <?php
                                $status = $w['status'] ?? 'pending';
                                $display_status = ($status === 'pending' && !(int) $w['otp_verified']) ? 'awaiting_otp' : $status;
                                $labels = ['pending' => 'Pending Approval', 'awaiting_otp' => 'Awaiting OTP', 'approved' => 'Approved', 'completed' => 'Completed', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'];
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($w['withdrawal_number']); ?></strong>
                                    <?php if ($status === 'pending' && !(int) $w['otp_verified']): ?>
                                        <div class="otp-form">
                                            <input type="text" id="otp_<?php echo $w['withdrawal_id']; ?>" class="form-control" maxlength="6" placeholder="000000">
                                            <button class="btn btn-sm btn-primary" onclick="verifyOTP(<?php echo $w['withdrawal_id']; ?>)">
                                                <i class="fas fa-check"></i> Verify OTP
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($w['gcash_number'] ?? 'N/A'); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($w['gcash_name'] ?? ''); ?></small>
                                </td>
                                <td><strong>₱<?php echo number_format($w['amount'], 2); ?></strong></td>
                                <td>
                                    <span class="badge-status badge-<?php echo $display_status; ?>">
                                        <?php echo $labels[$display_status] ?? ucfirst($display_status); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($w['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-wallet"></i>
                <h3>No Withdrawal Requests</h3>
                <p>You haven't submitted any withdrawal requests yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.otp-form { margin-top: 10px; display: flex; gap: 8px; align-items: center; }
.otp-form input { width: 120px; text-align: center; font-size: 15px; letter-spacing: 4px; }
</style>

<!-- Info Modal (shared .modal component) -->
<div class="modal" id="infoModal">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h3 class="modal-title" id="infoModalTitle">Notice</h3>
            <button type="button" class="modal-close" onclick="closeModal(document.getElementById('infoModal'))">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p id="infoModalBody" style="margin:0;"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary btn-sm" id="infoModalOkBtn">OK</button>
        </div>
    </div>
</div>

<script>
let _infoModalOnOk = null;
function showInfoModal(title, message, onOk) {
    document.getElementById('infoModalTitle').textContent = title;
    document.getElementById('infoModalBody').textContent = message;
    _infoModalOnOk = onOk || null;
    openModal('infoModal');
}
document.getElementById('infoModalOkBtn').addEventListener('click', function () {
    closeModal(document.getElementById('infoModal'));
    if (_infoModalOnOk) {
        const cb = _infoModalOnOk;
        _infoModalOnOk = null;
        cb();
    }
});

function submitWithdrawalRequest() {
    const amount = document.getElementById('req_amount').value;
    const gcashNumber = document.getElementById('req_gcash_number').value;
    const gcashName = document.getElementById('req_gcash_name').value;

    if (!amount || Number(amount) <= 0) {
        showInfoModal('Invalid Amount', 'Please enter a valid amount.');
        return;
    }
    if (!gcashNumber || !gcashName) {
        showInfoModal('Missing Information', 'Please enter your GCash number and account name.');
        return;
    }

    fetch('<?php echo site_url('reseller/withdrawals/request_withdrawal'); ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'amount=' + encodeURIComponent(amount) + '&gcash_number=' + encodeURIComponent(gcashNumber) + '&gcash_name=' + encodeURIComponent(gcashName)
    })
    .then(r => r.json())
    .then(data => {
        showInfoModal(data.success ? 'Request Submitted' : 'Request Failed',
            data.message || (data.success ? 'Request submitted' : 'Request failed'),
            data.success ? () => location.reload() : null);
    })
    .catch(() => showInfoModal('Error', 'Failed to submit withdrawal request.'));
}

function verifyOTP(withdrawalId) {
    const otp = document.getElementById('otp_' + withdrawalId).value;
    if (!otp || otp.length !== 6) {
        showInfoModal('Invalid OTP', 'Please enter a valid 6-digit OTP.');
        return;
    }
    fetch('<?php echo site_url('reseller/withdrawals/verify_withdrawal_otp'); ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'withdrawal_id=' + withdrawalId + '&otp=' + otp
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showInfoModal('Success', 'OTP verified successfully! Your withdrawal is now awaiting admin approval.', () => location.reload());
        } else {
            showInfoModal('Error', data.message || 'Invalid OTP');
        }
    })
    .catch(() => showInfoModal('Error', 'Failed to verify OTP.'));
}
</script>
