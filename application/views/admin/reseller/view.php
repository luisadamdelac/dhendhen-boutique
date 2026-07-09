<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container-fluid py-4 fade-in">

    <!-- Page Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1a1a2e;"><i class="fas fa-user"></i> Reseller Profile</h4>
            <small class="text-muted">View reseller details, commission summary, and order history.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/reseller/edit/' . $reseller['reseller_id']); ?>" class="btn btn-warning btn-sm">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <a href="<?= site_url('admin/reseller'); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">

        <!-- Left Column -->
        <div class="col col-4">

            <!-- Profile Card -->
            <div class="card" style="text-align: center;">
                <?php
                    $initials = strtoupper(substr($reseller['first_name'] ?? 'R', 0, 1) . substr($reseller['last_name'] ?? '', 0, 1));
                    // r.status: this reseller's own approval state
                    // (pending/active/rejected) — not to be confused with
                    // account_status (the login account's active/inactive
                    // toggle used by Suspend/Activate below).
                    $rStatus  = $reseller['status'] ?? 'active';
                    $rBadgeStatus = $rStatus === 'active' ? 'badge-active' : ($rStatus === 'rejected' ? 'badge-rejected' : 'badge-pending');
                    $accountStatus = $reseller['account_status'] ?? 'active';
                ?>
                <div style="width:64px;height:64px;border-radius:50%;background:var(--gradient-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:22px;margin:0 auto 14px;">
                    <?= $initials; ?>
                </div>
                <h4 style="font-size:16px;margin-bottom:4px;">
                    <?= htmlspecialchars(trim(($reseller['first_name'] ?? '') . ' ' . ($reseller['last_name'] ?? ''))); ?>
                </h4>
                <?php if (!empty($reseller['business_name'])): ?>
                    <div style="font-size:12px;color:var(--gray);margin-bottom:4px;"><?= htmlspecialchars($reseller['business_name']); ?></div>
                <?php endif; ?>
                <div style="font-size:12px;color:var(--gray);margin-bottom:14px;"><?= htmlspecialchars($reseller['email'] ?? ''); ?></div>
                <span class="badge-status <?= $rBadgeStatus; ?>"><?= ucfirst($rStatus); ?></span>
            </div>

            <?php if ($rStatus === 'active'): ?>
            <!-- Commission Summary -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title" style="font-size: 15px;">Commission Summary</h3>
                </div>
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--secondary-lavender);">
                        <span style="color: var(--gray);">Current Balance</span>
                        <strong style="color: var(--success);">
                            ₱<?= number_format($reseller['commission_balance'] ?? 0, 2); ?>
                        </strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--secondary-lavender);">
                        <span style="color: var(--gray);">Total Earned</span>
                        <strong style="color: var(--primary-pink);">
                            ₱<?= number_format($reseller['total_commission_earned'] ?? 0, 2); ?>
                        </strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0;">
                        <span style="color: var(--gray);">Total Withdrawn</span>
                        <strong>
                            ₱<?= number_format($reseller['total_withdrawn'] ?? 0, 2); ?>
                        </strong>
                    </div>
                </div>
                <div class="card-footer" style="border-top: none; padding-top: 0;">
                    <?php if ($accountStatus === 'active'): ?>
                        <button class="btn btn-danger btn-sm" style="width: 100%;" id="suspendBtn"
                                data-id="<?= $reseller['reseller_id']; ?>">
                            <i class="fas fa-ban"></i> Suspend Reseller
                        </button>
                    <?php else: ?>
                        <button class="btn btn-success btn-sm" style="width: 100%;" id="activateBtn"
                                data-id="<?= $reseller['reseller_id']; ?>">
                            <i class="fas fa-check"></i> Activate Reseller
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <!-- Not yet an active reseller: commission/suspend controls don't
                 apply — surface the approval state and, if rejected, a
                 re-approve action instead. -->
            <div class="card">
                <div class="card-body" style="text-align:center;">
                    <i class="fas fa-hourglass-half" style="font-size:22px;color:var(--gray);margin-bottom:8px;display:block;"></i>
                    <?php if ($rStatus === 'rejected'): ?>
                        <p style="color:var(--gray);font-size:13px;margin-bottom:<?= !empty($reseller['admin_remarks']) ? '6px' : '14px'; ?>;">This registration was rejected. Commission and order data aren't available until it's approved.</p>
                        <?php if (!empty($reseller['admin_remarks'])): ?>
                            <p style="color:var(--gray);font-size:12px;font-style:italic;margin-bottom:14px;">"<?= htmlspecialchars($reseller['admin_remarks']); ?>"</p>
                        <?php endif; ?>
                        <button class="btn btn-success btn-sm" style="width: 100%;" id="reapproveBtn"
                                data-id="<?= $reseller['reseller_id']; ?>">
                            <i class="fas fa-undo"></i> Re-approve Reseller
                        </button>
                    <?php else: ?>
                        <p style="color:var(--gray);font-size:13px;margin-bottom:0;">This registration is still pending review. Commission and order data aren't available until it's approved.</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Right Column -->
        <div class="col col-8">

            <!-- Profile Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title" style="font-size: 15px;">Profile Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col col-6">
                            <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--secondary-lavender);">
                                <span style="color: var(--gray);">Email</span>
                                <strong><?= htmlspecialchars($reseller['email'] ?? '—'); ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--secondary-lavender);">
                                <span style="color: var(--gray);">Contact Number</span>
                                <strong><?= htmlspecialchars($reseller['contact_number'] ?? '—'); ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 10px 0;">
                                <span style="color: var(--gray);">Business Name</span>
                                <strong><?= htmlspecialchars($reseller['business_name'] ?? '—'); ?></strong>
                            </div>
                        </div>
                        <div class="col col-6">
                            <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--secondary-lavender);">
                                <span style="color: var(--gray);">Street</span>
                                <strong><?= htmlspecialchars($reseller['street'] ?? '—'); ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--secondary-lavender);">
                                <span style="color: var(--gray);">Barangay</span>
                                <strong><?= htmlspecialchars($reseller['barangay'] ?? '—'); ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 10px 0;">
                                <span style="color: var(--gray);">City</span>
                                <strong><?= htmlspecialchars($reseller['city'] ?? '—'); ?></strong>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-top: 1px solid var(--secondary-lavender); margin-top: 4px;">
                        <span style="color: var(--gray);">Member Since</span>
                        <strong>
                            <?= !empty($reseller['created_at']) ? date('F d, Y', strtotime($reseller['created_at'])) : '—'; ?>
                        </strong>
                    </div>
                </div>
            </div>

            <?php if ($rStatus === 'active'): ?>
            <!-- Recent Orders -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title" style="font-size: 15px;">Recent Orders</h3>
                    <a href="<?= site_url('admin/order?reseller_id=' . $reseller['reseller_id']); ?>"
                       class="btn btn-outline-secondary btn-sm">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th style="text-align: right;">Total</th>
                                <th style="text-align: center;">Status</th>
                                <th style="text-align: right;">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $order): ?>
                            <?php
                                $oStatus = $order['order_status'] ?? 'pending';
                                $oBadge  = ['pending' => 'warning', 'processing' => 'info', 'to_ship' => 'info', 'shipped' => 'info', 'delivered' => 'success', 'cancelled' => 'danger'][$oStatus] ?? 'info';
                            ?>
                            <tr>
                                <td style="font-weight: 600; color: var(--primary-pink-dark);">
                                    #<?= htmlspecialchars($order['order_number'] ?? $order['order_id']); ?>
                                </td>
                                <td style="text-align: right; font-weight: 600;">
                                    ₱<?= number_format($order['total_amount'] ?? 0, 2); ?>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge-status badge-<?= $oStatus; ?>">
                                        <?= ucfirst(str_replace('_', ' ', $oStatus)); ?>
                                    </span>
                                </td>
                                <td style="text-align: right; font-size: 11px; color: var(--gray);">
                                    <?= !empty($order['created_at']) ? date('M d, Y', strtotime($order['created_at'])) : '—'; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 40px 0;">
                                    <i class="fas fa-shopping-cart fa-2x" style="color: var(--gray); display: block; margin-bottom: 10px;"></i>
                                    <span style="color: var(--gray);">No orders yet.</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
(function() {
    var suspendBtn   = document.getElementById('suspendBtn');
    var activateBtn  = document.getElementById('activateBtn');
    var reapproveBtn = document.getElementById('reapproveBtn');

    function doAction(url, confirmMsg) {
        if (!confirm(confirmMsg)) return;
        fetch(url, { method: 'POST' })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) location.reload();
                else alert(d.message || 'Action failed.');
            })
            .catch(function() { alert('Request failed.'); });
    }

    if (suspendBtn) {
        suspendBtn.addEventListener('click', function() {
            doAction('<?= site_url('admin/reseller/suspend/'); ?>' + this.dataset.id, 'Suspend this reseller?');
        });
    }
    if (activateBtn) {
        activateBtn.addEventListener('click', function() {
            doAction('<?= site_url('admin/reseller/activate/'); ?>' + this.dataset.id, 'Activate this reseller?');
        });
    }
    if (reapproveBtn) {
        reapproveBtn.addEventListener('click', function() {
            doAction('<?= site_url('admin/reseller/approve-registration/'); ?>' + this.dataset.id, 'Re-approve this reseller?');
        });
    }
})();
</script>
