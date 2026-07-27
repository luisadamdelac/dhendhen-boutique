<style>
.wd-section-head { display: flex; align-items: flex-start; gap: 12px; margin-bottom: var(--spacing-lg); }
.wd-icon-badge {
    width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
    background: var(--primary-pink-light); color: var(--primary-pink-dark);
    display: flex; align-items: center; justify-content: center; font-size: 1rem;
}
.wd-section-head h3 { margin: 0; font-size: 1rem; font-weight: 700; color: #1a1a2e; }
.wd-section-head p { margin: 2px 0 0; font-size: .82rem; color: var(--gray); }

.wd-hero-row { display: flex; align-items: flex-start; gap: 14px; position: relative; z-index: 1; max-width: 62%; }
.wd-hero-icon-badge {
    width: 46px; height: 46px; border-radius: 14px; flex-shrink: 0;
    background: var(--primary-pink-dark); color: #fff;
    display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
}
.wd-hero-row h4 { margin: 0; }
.wd-hero-row small { display: block; margin-top: 2px; }

.wd-hero-illustration {
    position: absolute; right: 2rem; top: 50%; transform: translateY(-50%);
    width: 230px; height: 140px; z-index: 1; pointer-events: none;
    -webkit-mask-image: linear-gradient(to left, #000 42%, transparent 92%);
            mask-image: linear-gradient(to left, #000 42%, transparent 92%);
}
.wd-hero-leaves { position: absolute; top: -10px; right: 10px; width: 90px; height: 90px; opacity: .6; }
.wd-hero-wallet {
    position: absolute; right: 14px; bottom: 4px; font-size: 92px; color: var(--primary-pink-dark);
    filter: drop-shadow(0 14px 18px rgba(224,85,156,.28));
}
.wd-hero-coin {
    position: absolute; border-radius: 50%; background: linear-gradient(135deg,#FFD6E8,#FF9EC7);
    color: #fff; font-weight: 800; display: flex; align-items: center; justify-content: center;
    box-shadow: 0 6px 10px rgba(224,85,156,.25);
}
.wd-hero-coin.c1 { width: 30px; height: 30px; font-size: 13px; left: 6px; bottom: 6px; }
.wd-hero-coin.c2 { width: 24px; height: 24px; font-size: 11px; left: 30px; bottom: -2px; }
.wd-hero-coin.c3 { width: 20px; height: 20px; font-size: 10px; left: 4px; bottom: -8px; }

.wd-notice {
    display: flex; align-items: center; gap: 10px; background: var(--primary-pink-light);
    color: #1a1a2e; border-radius: 12px; padding: 12px 16px; font-size: .82rem; margin-bottom: 18px;
}
.wd-notice i { color: var(--primary-pink-dark); }
.wd-notice strong { color: var(--primary-pink-dark); }

@media (max-width: 767px) {
    .wd-hero-row { max-width: 100%; }
    .wd-hero-illustration { display: none; }
}
</style>

<div class="ds-hero-card mb-3">
    <div class="ds-hero-banner">
        <svg class="ds-hero-wave" viewBox="0 0 1440 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,110 C240,170 480,50 720,90 C960,130 1200,50 1440,100 L1440,200 L0,200 Z" fill="rgba(255,105,180,0.16)"></path>
            <path d="M0,140 C280,80 560,180 840,120 C1080,70 1280,140 1440,130 L1440,200 L0,200 Z" fill="rgba(233,30,99,0.22)"></path>
        </svg>

        <div class="wd-hero-illustration" aria-hidden="true">
            <svg class="wd-hero-leaves" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <g fill="#E0559C" opacity="0.18">
                    <path d="M10 10 C40 0 60 20 50 50 C25 45 5 30 10 10 Z"></path>
                    <path d="M55 5 C75 0 90 18 82 38 C62 35 48 20 55 5 Z"></path>
                </g>
            </svg>
            <span class="wd-hero-coin c3"></span>
            <span class="wd-hero-coin c2"></span>
            <span class="wd-hero-coin c1">₱</span>
            <i class="fas fa-wallet wd-hero-wallet"></i>
        </div>

        <div class="ds-hero-banner-content">
            <div class="wd-hero-row">
                <div class="wd-hero-icon-badge"><i class="fas fa-wallet"></i></div>
                <div>
                    <h4 class="fw-bold mb-0" style="color:#1a1a2e;">My Withdrawals</h4>
                    <small class="text-muted">Manage your funds and request a withdrawal securely.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="ds-hero-stats">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="ds-stat-tile">
                    <div class="ds-stat-tile-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-coins"></i></div>
                    <div>
                        <div class="ds-stat-tile-label">Available Balance</div>
                        <div class="ds-stat-tile-value">₱<?php echo number_format($available_balance ?? 0, 2); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ds-stat-tile">
                    <div class="ds-stat-tile-icon" style="background:#eef0ff;color:#4361ee;"><i class="fas fa-arrow-down"></i></div>
                    <div>
                        <div class="ds-stat-tile-label">Total Withdrawn</div>
                        <div class="ds-stat-tile-value">₱<?php echo number_format($total_withdrawn ?? 0, 2); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header" style="display:block;">
        <div class="wd-section-head" style="margin-bottom:0;">
            <div class="wd-icon-badge"><i class="fas fa-paper-plane"></i></div>
            <div>
                <h3>Request a Withdrawal</h3>
                <p>Fill in the details below to request a withdrawal to your GCash account.</p>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="wd-notice">
            <i class="fas fa-calendar-check"></i>
            <span>Next scheduled processing date: <strong><?php echo date('F j, Y', strtotime($next_schedule_date ?? 'now')); ?></strong></span>
        </div>
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
                <button type="button" class="btn btn-primary" id="btnSubmitWithdrawal" onclick="submitWithdrawalRequest()">
                    <i class="fas fa-paper-plane" id="btnSubmitWithdrawalIcon"></i> <span id="btnSubmitWithdrawalText">Submit Request</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card ds-pink-table-card mt-3">
    <div class="card-header" style="display:block;">
        <div class="wd-section-head" style="margin-bottom:0;">
            <div class="wd-icon-badge"><i class="fas fa-history"></i></div>
            <div>
                <h3>Withdrawal History</h3>
                <p>Track and view all your withdrawal requests.</p>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($withdrawals)): ?>
            <div class="table-responsive">
                <table class="table inv-table ds-pink-table table-stack">
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
                                            <button class="btn btn-sm btn-primary" onclick="verifyOTP(<?php echo $w['withdrawal_id']; ?>, this)">
                                                <i class="fas fa-check"></i> <span>Verify OTP</span>
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
            <div class="wd-empty">
                <div class="wd-empty-illustration">
                    <div class="wd-empty-blob"></div>
                    <i class="fas fa-folder-open wd-empty-folder"></i>
                    <i class="fas fa-paper-plane wd-empty-plane"></i>
                    <span class="wd-empty-spark s1"></span>
                    <span class="wd-empty-spark s2"></span>
                </div>
                <h5 class="wd-empty-title">No Withdrawal Requests</h5>
                <p class="wd-empty-sub">You haven't submitted any withdrawal requests yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.otp-form { margin-top: 10px; display: flex; gap: 8px; align-items: center; }
.otp-form input { width: 120px; text-align: center; font-size: 15px; letter-spacing: 4px; }

.wd-empty { text-align: center; padding: 3rem 1rem 2.4rem; }
.wd-empty-illustration { position: relative; width: 150px; height: 130px; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; }
.wd-empty-blob {
    position: absolute; inset: 0; border-radius: 50%;
    background: radial-gradient(circle, rgba(255, 193, 217, .55) 0%, rgba(255, 247, 250, 0) 72%);
}
.wd-empty-folder { position: relative; font-size: 72px; color: #FF9EC7; z-index: 1; filter: drop-shadow(0 10px 14px rgba(255, 79, 162, .22)); }
.wd-empty-plane {
    position: absolute; top: 8px; right: 16px; font-size: 24px; color: #FF4FA2; z-index: 2;
    transform: rotate(20deg); filter: drop-shadow(0 4px 8px rgba(255, 79, 162, .3));
}
.wd-empty-spark { position: absolute; width: 6px; height: 6px; background: #FFC1D9; transform: rotate(45deg); }
.wd-empty-spark.s1 { top: 2px; left: 20px; }
.wd-empty-spark.s2 { width: 5px; height: 5px; bottom: 22px; right: 4px; }
.wd-empty-title { margin: 0 0 6px; font-size: 1.05rem; font-weight: 700; color: #1a1a2e; }
.wd-empty-sub { margin: 0; font-size: .86rem; color: var(--gray); }
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

    const btn = document.getElementById('btnSubmitWithdrawal');
    const icon = document.getElementById('btnSubmitWithdrawalIcon');
    const text = document.getElementById('btnSubmitWithdrawalText');
    btn.disabled = true;
    icon.className = 'fas fa-spinner fa-spin';
    text.textContent = 'Submitting…';

    const resetBtn = () => {
        btn.disabled = false;
        icon.className = 'fas fa-paper-plane';
        text.textContent = 'Submit Request';
    };

    fetch('<?php echo site_url('reseller/withdrawals/request_withdrawal'); ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'amount=' + encodeURIComponent(amount) + '&gcash_number=' + encodeURIComponent(gcashNumber) + '&gcash_name=' + encodeURIComponent(gcashName)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // The OTP field for this new request only exists in the
            // server-rendered list, so it won't appear until the page
            // reloads — don't make the reseller click "OK" first (or
            // manually hit refresh) just to see it; reload on its own
            // shortly after the confirmation is shown. Button stays in its
            // loading state until then instead of resetting.
            showInfoModal('Request Submitted', data.message || 'Request submitted');
            setTimeout(() => location.reload(), 1500);
        } else {
            resetBtn();
            showInfoModal('Request Failed', data.message || 'Request failed');
        }
    })
    .catch(() => {
        resetBtn();
        showInfoModal('Error', 'Failed to submit withdrawal request.');
    });
}

function verifyOTP(withdrawalId, btn) {
    const otp = document.getElementById('otp_' + withdrawalId).value;
    if (!otp || otp.length !== 6) {
        showInfoModal('Invalid OTP', 'Please enter a valid 6-digit OTP.');
        return;
    }

    const icon = btn.querySelector('i');
    const label = btn.querySelector('span');
    btn.disabled = true;
    icon.className = 'fas fa-spinner fa-spin';
    label.textContent = 'Verifying…';
    const resetBtn = () => {
        btn.disabled = false;
        icon.className = 'fas fa-check';
        label.textContent = 'Verify OTP';
    };

    fetch('<?php echo site_url('reseller/withdrawals/verify_withdrawal_otp'); ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'withdrawal_id=' + withdrawalId + '&otp=' + otp
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showInfoModal('Success', 'OTP verified successfully! Your withdrawal is now awaiting admin approval.');
            setTimeout(() => location.reload(), 1500);
        } else {
            resetBtn();
            showInfoModal('Error', data.message || 'Invalid OTP');
        }
    })
    .catch(() => {
        resetBtn();
        showInfoModal('Error', 'Failed to verify OTP.');
    });
}
</script>
