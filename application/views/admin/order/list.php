<?php
    $orders = is_array($orders ?? null) ? $orders : [];
    $order_stats = $order_stats ?? [
        'total_orders' => 0,
        'pending_orders' => 0,
        'processing_orders' => 0,
        'shipped_orders' => 0,   // used for "To Ship" (legacy 'shipped' or new 'to_ship')
        'delivered_orders' => 0,
        'cancelled_orders' => 0,
        'refund_return_orders' => 0,
        'paid_orders' => 0,
        'total_sales' => 0,
        'average_order_value' => 0
    ];
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>public/vendor/datatables/buttons.bootstrap5.min.css">
<style>
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid var(--border); border-radius: var(--radius-md);
    padding: .4rem .75rem; font-size: .85rem; margin-left: .5rem;
}
.dataTables_wrapper .dataTables_filter input:focus { outline: none; border-color: var(--primary-pink); }
table.dataTable thead th { position: relative; }
table.dataTable thead th.sorting:hover { color: var(--primary-pink); cursor: pointer; }
.dt-buttons .btn {
    background: #fff; border: 1px solid var(--border); color: var(--text);
    font-size: .82rem; padding: .45rem .9rem; margin-right: 6px;
}
.dt-buttons .btn:hover { border-color: var(--primary-pink); color: var(--primary-pink); }
</style>

<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Orders</h4>
            <small class="text-muted">Review and manage customer orders.</small>
        </div>
    </div>

<?php include __DIR__ . '/../partials/order_tabs.php'; ?>

    <!-- Order Management -->

    <!-- Order Statistics -->
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff8e1;color:#f57f17;"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="stat-label">Pending Orders</div>
                    <div class="stat-value"><?php echo number_format($order_stats['pending_orders'] ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e3f2fd;color:#1565c0;"><i class="fas fa-cog fa-spin"></i></div>
                <div>
                    <div class="stat-label">Processing</div>
                    <div class="stat-value"><?php echo number_format($order_stats['processing_orders'] ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8eaf6;color:#3949ab;"><i class="fas fa-truck"></i></div>
                <div>
                    <div class="stat-label">To Ship</div>
                    <div class="stat-value"><?php echo number_format($order_stats['shipped_orders'] ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-label">Delivered</div>
                    <div class="stat-value"><?php echo number_format($order_stats['delivered_orders'] ?? 0); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Status & Payment Statistics -->
    <div class="row g-3 mt-1">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-wallet"></i></div>
                <div>
                    <div class="stat-label">Paid</div>
                    <div class="stat-value"><?php echo number_format($order_stats['paid_orders'] ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ffebee;color:#c62828;"><i class="fas fa-times-circle"></i></div>
                <div>
                    <div class="stat-label">Cancelled</div>
                    <div class="stat-value"><?php echo number_format($order_stats['cancelled_orders'] ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff8e1;color:#f57f17;"><i class="fas fa-undo-alt"></i></div>
                <div>
                    <div class="stat-label">Refund/Return</div>
                    <div class="stat-value"><?php echo number_format($order_stats['refund_return_orders'] ?? 0); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="page-section">
        <span class="section-title"><i class="fas fa-shopping-bag me-2"></i>All Orders</span>
        <hr>
        <span class="text-muted small" style="white-space:nowrap;"><?php echo number_format($total); ?> result<?php echo $total != 1 ? 's' : ''; ?></span>
        <div class="section-actions">
            <div id="ordersExportButtons" class="d-inline-block"></div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="row g-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label class="form-label small text-muted mb-1">Status</label>
                <select id="filterStatus" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="processing">Processing</option>
                    <option value="to_ship">To Ship</option>
                    <option value="delivered">Delivered</option>
                    <option value="return_refund">Return/Refund</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label small text-muted mb-1">Payment Method</label>
                <select id="filterPaymentMethod" class="form-select form-select-sm">
                    <option value="" selected>All Payment Methods</option>
                    <option value="GCash">GCash</option>
                    <option value="Cash">Cash</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label small text-muted mb-1">Date From</label>
                <input type="date" id="filterDateFrom" class="form-control form-control-sm">
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label small text-muted mb-1">Date To</label>
                <input type="date" id="filterDateTo" class="form-control form-control-sm">
            </div>
            <div class="col-md-2 d-flex">
                <button type="button" id="clearFiltersBtn" class="btn btn-sm btn-outline-secondary flex-fill">
                    <i class="fas fa-times"></i> Clear Filters
                </button>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-2" style="border-radius:12px;overflow:hidden;">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table inv-table mb-0 table-stack" id="ordersDataTable">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Total Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order):
                            $orderId = isset($order['order_id']) ? $order['order_id'] : ($order['id'] ?? 0);
                            $orderStatus = $order['order_status'] ?? $order['status'] ?? 'pending';
                            $ptStatus = $order['payment_tx_status'] ?? 'pending';
                            $hasPayment = !empty($order['payment_method']);
                            $orderDate = $order['created_at'] ?? 'now';
                        ?>
                            <tr data-status="<?php echo htmlspecialchars($orderStatus); ?>"
                                data-payment-method="<?php echo $hasPayment ? htmlspecialchars($order['payment_method']) : 'none'; ?>"
                                data-date="<?php echo date('Y-m-d', strtotime($orderDate)); ?>">
                                <td>
                                    <strong style="color: var(--primary-pink);">
                                        #<?php echo htmlspecialchars($order['order_number'] ?? 'N/A'); ?>
                                    </strong>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></strong>
                                    <p style="margin: 0; font-size: 12px; color: var(--gray);">
                                        <?php echo htmlspecialchars($order['customer_email'] ?? ''); ?>
                                    </p>
                                </td>
                                <td><?php echo htmlspecialchars($order['customer_phone'] ?? 'N/A'); ?></td>
                                <td>
                                    <strong style="color: var(--primary-pink);">
                                        ₱<?php echo number_format($order['total_amount'] ?? 0, 2); ?>
                                    </strong>
                                </td>
                                <td>
                                    <?php
                                        $badgeColor = $ptStatus === 'completed' ? '#28a745' : ($ptStatus === 'failed' ? '#dc3545' : ($ptStatus === 'refunded' ? '#6c757d' : '#f57f17'));
                                        $badgeIcon = $ptStatus === 'completed' ? 'fa-check-circle' : ($ptStatus === 'failed' ? 'fa-times-circle' : ($ptStatus === 'refunded' ? 'fa-undo' : 'fa-clock'));
                                        $badgeLabel = ucfirst($ptStatus);
                                        $isCash = $hasPayment && $order['payment_method'] === 'Cash';
                                        $methodColor = $isCash ? '#28a745' : '#007DFF';
                                        $methodIcon = $isCash ? 'fa-money-bill-wave' : 'fa-mobile-alt';
                                        if ($hasPayment):
                                    ?>
                                        <span style="display:inline-flex; align-items:center; gap:4px; background:<?= $methodColor; ?>15; color:<?= $methodColor; ?>; padding:4px 10px; border-radius:15px; font-size:11px; font-weight:600;">
                                            <i class="fas <?= $methodIcon; ?>"></i> <?= htmlspecialchars($order['payment_method']); ?>
                                        </span>
                                        <br><span style="font-size:10px; color:<?= $badgeColor; ?>;"><i class="fas <?= $badgeIcon; ?>"></i> <?= $badgeLabel; ?></span>
                                    <?php else: ?>
                                        <span style="color:#888; font-size:12px;">No payment yet</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $sBadge = '';
                                        $sLabel = ucfirst($orderStatus);
                                        switch($orderStatus) {
                                            case 'pending':
                                                $sBadge = 'badge-pending';
                                                break;
                                            case 'processing':
                                                $sBadge = 'badge-processing';
                                                break;
                                            case 'shipped': // legacy value, treat as to ship
                                            case 'to_ship':
                                                $sBadge = 'badge-to_ship';
                                                $sLabel = 'To Ship';
                                                break;
                                            case 'delivered':
                                                $sBadge = 'badge-delivered';
                                                break;
                                            case 'paid':
                                                $sBadge = 'badge-paid';
                                                // Order stage is "paid" as soon as the customer submits
                                                // GCash proof, but the payment transaction itself may
                                                // still be awaiting admin verification — label it as such
                                                // so it doesn't read as a contradiction next to the
                                                // Payment column (which can still show "Pending").
                                                $sLabel = ($ptStatus === 'completed') ? 'Paid' : 'Payment Submitted';
                                                break;
                                            case 'refund_return':
                                                $sBadge = 'badge-refund_return';
                                                $sLabel = 'Refund/Return';
                                                break;
                                            case 'cancelled':
                                                $sBadge = 'badge-cancelled';
                                                break;
                                            default:
                                                $sBadge = 'badge-pending';
                                                break;
                                        }
                                    ?>
                                    <span class="badge-status <?php echo $sBadge; ?>"><?php echo $sLabel; ?></span>
                                    <?php if (!empty($order['forwarded_at'])): ?>
                                        <br><span style="font-size:10px;color:#28a745;"><i class="fas fa-truck-loading"></i> Forwarded</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($orderDate)); ?></td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="<?php echo site_url('admin/order/view/' . $orderId); ?>"
                                           class="btn btn-sm btn-outline-info" title="View Order"
                                           style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-eye" style="font-size:11px;"></i>
                                        </a>

                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="printInvoice(<?php echo $orderId; ?>)"
                                                title="Print Invoice"
                                                style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-print" style="font-size:11px;"></i>
                                        </button>

                                        <?php if ($orderStatus === 'delivered'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-success"
                                                    onclick="openDetailsModal(<?php echo $orderId; ?>, 'commission')"
                                                    title="Commission Details"
                                                    style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                                <i class="fas fa-hand-holding-dollar" style="font-size:11px;"></i>
                                            </button>
                                        <?php elseif ($orderStatus === 'return_refund'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-warning"
                                                    onclick="openDetailsModal(<?php echo $orderId; ?>, 'refund')"
                                                    title="Refund Details"
                                                    style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                                <i class="fas fa-undo-alt" style="font-size:11px;"></i>
                                            </button>
                                        <?php elseif ($orderStatus === 'cancelled'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    onclick="openDetailsModal(<?php echo $orderId; ?>, 'cancellation')"
                                                    title="Cancellation Details"
                                                    style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                                <i class="fas fa-ban" style="font-size:11px;"></i>
                                            </button>
                                        <?php else:
                                            $paymentInfo = [
                                                'orderId'      => $orderId,
                                                'status'       => $ptStatus,
                                                'method'       => $hasPayment ? $order['payment_method'] : null,
                                                'reference'    => $order['payment_reference'] ?? null,
                                                'receiptImage' => !empty($order['receipt_image']) ? BASE_URL . $order['receipt_image'] : null,
                                                'amount'       => $order['payment_amount'] ?? ($order['total_amount'] ?? 0),
                                                'paidAt'       => $order['paid_at'] ?? null,
                                            ];
                                        ?>
                                            <button type="button" class="btn btn-sm btn-outline-success"
                                                    data-payment-info="<?php echo htmlspecialchars(json_encode($paymentInfo), ENT_QUOTES); ?>"
                                                    onclick="openPaymentModal(this)"
                                                    title="Update Payment Status"
                                                    style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                                <i class="fas fa-money-check-dollar" style="font-size:11px;"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Payment Details Modal (Pending / Paid / Processing / To Ship) -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px; box-shadow:0 8px 32px rgba(0,0,0,.15);">
                <form id="paymentForm">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="fas fa-money-check-dollar me-2"></i>Update Payment Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="text-muted mb-1" style="font-size:12px;">Payment Method</label>
                                <div class="fw-semibold" id="paymentInfoMethod">—</div>
                            </div>
                            <div class="col-6">
                                <label class="text-muted mb-1" style="font-size:12px;">Reference Number</label>
                                <div class="fw-semibold" id="paymentInfoReference">—</div>
                            </div>
                            <div class="col-6">
                                <label class="text-muted mb-1" style="font-size:12px;">Amount</label>
                                <div class="fw-semibold" id="paymentInfoAmount">—</div>
                            </div>
                            <div class="col-6">
                                <label class="text-muted mb-1" style="font-size:12px;">Payment Date</label>
                                <div class="fw-semibold" id="paymentInfoDate">—</div>
                            </div>
                            <div class="col-12">
                                <label class="text-muted mb-1" style="font-size:12px;">Current Status</label>
                                <div class="fw-semibold" id="paymentInfoCurrentStatus">—</div>
                            </div>
                            <div class="col-12" id="paymentInfoReceiptWrap" style="display:none;">
                                <label class="text-muted mb-1" style="font-size:12px;">Receipt</label>
                                <div>
                                    <a id="paymentInfoReceiptLink" href="#" target="_blank">
                                        <img id="paymentInfoReceiptImg" src="" alt="GCash Receipt" style="max-width:140px; max-height:140px; border-radius:8px; border:2px solid #007DFF;">
                                    </a>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group">
                            <label for="paymentStatusSelect">Payment Status</label>
                            <select class="form-control" id="paymentStatusSelect" name="payment_status" required>
                                <option value="pending" id="paymentStatusOptPending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="failed" id="paymentStatusOptFailed">Failed</option>
                                <option value="refunded" id="paymentStatusOptRefunded">Refunded</option>
                            </select>
                            <small class="text-muted" id="paymentStatusLockedHint" style="display:none;">
                                Already Completed — use the Refund flow to reverse this payment instead.
                            </small>
                        </div>
                        <div class="form-group" id="rejectionReasonWrap" style="display:none;">
                            <label for="rejectionReasonInput">Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejectionReasonInput" name="rejection_reason" rows="2"
                                      placeholder="e.g. Reference number doesn't match our GCash records"></textarea>
                            <small class="text-muted">Shown to the customer so they can resubmit a corrected reference number/receipt.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="paymentSubmitBtn"><i class="fas fa-check"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Read-only Details Modal (Commission / Refund / Cancellation) -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px; box-shadow:0 8px 32px rgba(0,0,0,.15);">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="detailsModalTitle"><i class="fas fa-info-circle me-2"></i>Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detailsModalBody">
                    <div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Loading…</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="<?php echo BASE_URL; ?>public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.js"></script>

<script>
function printInvoice(orderId) {
    window.open('<?php echo BASE_URL; ?>admin/order/invoice/' + orderId, '_blank');
}

/* ── Payment Details modal (editable, pre-Delivered statuses) ─── */
let paymentModalOrderId = null;
const paymentModalEl = document.getElementById('paymentModal');

function openPaymentModal(btn) {
    const info = JSON.parse(btn.dataset.paymentInfo);
    paymentModalOrderId = info.orderId;

    document.getElementById('paymentInfoMethod').textContent = info.method || '—';
    document.getElementById('paymentInfoReference').textContent = info.reference || '—';
    document.getElementById('paymentInfoAmount').textContent = info.amount
        ? ('₱' + Number(info.amount).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }))
        : '—';
    document.getElementById('paymentInfoDate').textContent = info.paidAt
        ? new Date(info.paidAt).toLocaleString('en-PH')
        : 'Not yet paid';
    document.getElementById('paymentInfoCurrentStatus').textContent = info.status
        ? info.status.charAt(0).toUpperCase() + info.status.slice(1)
        : 'Pending';

    const receiptWrap = document.getElementById('paymentInfoReceiptWrap');
    if (info.receiptImage) {
        document.getElementById('paymentInfoReceiptImg').src = info.receiptImage;
        document.getElementById('paymentInfoReceiptLink').href = info.receiptImage;
        receiptWrap.style.display = 'block';
    } else {
        receiptWrap.style.display = 'none';
    }

    document.getElementById('paymentStatusSelect').value = info.status || 'pending';

    const isCompleted = info.status === 'completed';
    ['paymentStatusOptPending', 'paymentStatusOptFailed', 'paymentStatusOptRefunded'].forEach(id => {
        document.getElementById(id).disabled = isCompleted;
    });
    document.getElementById('paymentStatusLockedHint').style.display = isCompleted ? 'block' : 'none';
    document.getElementById('paymentSubmitBtn').disabled = isCompleted;

    document.getElementById('rejectionReasonInput').value = '';
    document.getElementById('rejectionReasonWrap').style.display = document.getElementById('paymentStatusSelect').value === 'failed' ? 'block' : 'none';

    bootstrap.Modal.getOrCreateInstance(paymentModalEl).show();
}

document.getElementById('paymentStatusSelect').addEventListener('change', function() {
    document.getElementById('rejectionReasonWrap').style.display = this.value === 'failed' ? 'block' : 'none';
});

document.getElementById('paymentForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const newStatus = document.getElementById('paymentStatusSelect').value;
    const currentStatus = (document.getElementById('paymentInfoCurrentStatus').textContent || '').trim().toLowerCase();

    if (currentStatus === 'completed' && newStatus !== 'completed') {
        alert('This payment is already marked Completed and can\'t be changed back. Use the Refund flow to reverse it instead.');
        return;
    }

    const rejectionReason = document.getElementById('rejectionReasonInput').value.trim();
    if (newStatus === 'failed' && !rejectionReason) {
        alert('Please provide a reason for rejecting this payment.');
        document.getElementById('rejectionReasonInput').focus();
        return;
    }

    if (newStatus === 'completed') {
        customConfirm(
            'Once approved, this cannot be undone — it unlocks the order for staff to process.',
            function() { submitPaymentUpdate(newStatus, rejectionReason); },
            { title: 'Mark this payment as Completed?', okText: 'Mark Completed' }
        );
        return;
    }

    submitPaymentUpdate(newStatus, rejectionReason);
});

function submitPaymentUpdate(newStatus, rejectionReason) {
    const btn = document.getElementById('paymentSubmitBtn');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';

    const params = new URLSearchParams({
        payment_status: newStatus,
        rejection_reason: rejectionReason,
    });

    fetch('<?php echo BASE_URL; ?>admin/order/update_payment/' + paymentModalOrderId, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            alert(data.message || 'Failed to update payment');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        alert('Request failed');
    });
}

/* ── Read-only Details modal (Commission / Refund / Cancellation) ── */
function openDetailsModal(orderId, type) {
    const titleEl = document.getElementById('detailsModalTitle');
    const bodyEl = document.getElementById('detailsModalBody');
    const titles = { commission: 'Commission Details', refund: 'Refund Details', cancellation: 'Cancellation Details' };
    const icons = { commission: 'fa-hand-holding-dollar', refund: 'fa-undo-alt', cancellation: 'fa-ban' };
    titleEl.innerHTML = '<i class="fas ' + (icons[type] || 'fa-info-circle') + ' me-2"></i>' + (titles[type] || 'Details');
    bodyEl.innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Loading…</div>';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('detailsModal')).show();

    fetch('<?php echo BASE_URL; ?>admin/order/action_details/' + orderId)
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                bodyEl.innerHTML = '<p class="text-muted mb-0">' + (res.message || 'No details available.') + '</p>';
                return;
            }
            bodyEl.innerHTML = renderDetails(res.type, res.data);
        })
        .catch(() => {
            bodyEl.innerHTML = '<p class="text-danger mb-0">Failed to load details.</p>';
        });
}

function renderDetails(type, data) {
    function row(label, value) {
        return '<div class="d-flex justify-content-between py-2" style="border-bottom:1px dashed var(--border);">' +
               '<span class="text-muted small">' + label + '</span><strong>' + value + '</strong></div>';
    }
    if (type === 'commission') {
        if (!data) return '<p class="text-muted mb-0">No commission record found for this order.</p>';
        const name = ((data.first_name || '') + ' ' + (data.last_name || '')).trim() || '—';
        return row('Reseller', name) +
               row('Amount', '₱' + parseFloat(data.amount).toFixed(2)) +
               row('Status', data.status.charAt(0).toUpperCase() + data.status.slice(1)) +
               row('Released At', data.released_at || '—');
    }
    if (type === 'refund') {
        if (!data) return '<p class="text-muted mb-0">No refund request found for this order.</p>';
        return row('Refund #', data.refund_number || '—') +
               row('Amount', '₱' + parseFloat(data.amount).toFixed(2)) +
               row('Status', data.status.charAt(0).toUpperCase() + data.status.slice(1)) +
               row('Reason', data.reason || '—') +
               row('Admin Remarks', data.admin_remarks || '—');
    }
    if (type === 'cancellation') {
        return row('Cancelled At', data.cancelled_at || '—') +
               row('Commission', data.commission_reversed ? 'Reversed (no commission credited)' : 'Not applicable');
    }
    return '<p class="text-muted mb-0">No details available.</p>';
}

/* ── DataTable: search, sort, pagination, filters ──────────────── */
/* Excel/PDF export lives in Reports, not on this operational list page. */
$(function () {
    const table = $('#ordersDataTable').DataTable({
        order: [[6, 'desc']],
        columnDefs: [{ orderable: false, targets: [7] }],
        language: { search: '_INPUT_', searchPlaceholder: 'Search orders…' },
        drawCallback: function() { if (typeof initResponsiveTableStacking === 'function') initResponsiveTableStacking(); }
    });

    // Status / Payment Method / Date Range — custom filters read straight off each row's data-* attributes
    $.fn.dataTable.ext.search.push(function (settings, searchData, index, rowData, counter) {
        if (settings.nTable.id !== 'ordersDataTable') return true;

        const $row = $(settings.aoData[index].nTr);
        const status = $row.data('status');
        const paymentMethod = String($row.data('payment-method'));
        const date = $row.data('date');

        const statusFilter = $('#filterStatus').val();
        if (statusFilter && status !== statusFilter) return false;

        const paymentFilter = $('#filterPaymentMethod').val();
        if (paymentFilter && paymentMethod !== paymentFilter) return false;

        const dateFrom = $('#filterDateFrom').val();
        const dateTo = $('#filterDateTo').val();
        if (dateFrom && date < dateFrom) return false;
        if (dateTo && date > dateTo) return false;

        return true;
    });

    $('#filterStatus, #filterPaymentMethod, #filterDateFrom, #filterDateTo').on('change', function () {
        table.draw();
    });

    table.draw();

    $('#clearFiltersBtn').on('click', function () {
        $('#filterStatus, #filterPaymentMethod').val('');
        $('#filterDateFrom, #filterDateTo').val('');
        table.search('').draw();
    });
});
</script>
