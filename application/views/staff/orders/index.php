<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

<div class="ds-hero-card mb-3">
    <div class="ds-hero-banner">
        <svg class="ds-hero-wave" viewBox="0 0 1440 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,110 C240,170 480,50 720,90 C960,130 1200,50 1440,100 L1440,200 L0,200 Z" fill="rgba(255,105,180,0.16)"></path>
            <path d="M0,140 C280,80 560,180 840,120 C1080,70 1280,140 1440,130 L1440,200 L0,200 Z" fill="rgba(233,30,99,0.22)"></path>
        </svg>
        <div class="ds-hero-banner-content">
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Order Management</h4>
            <small class="text-muted">Welcome back, <strong><?php echo htmlspecialchars($user_full_name ?? 'Staff'); ?></strong> — review and fulfill customer orders.</small>
        </div>
    </div>

    <div class="ds-hero-section-row">
        <span class="section-title"><i class="fas fa-list me-2 text-primary"></i>All Orders</span>
        <span class="text-muted small" style="white-space:nowrap;"><?php echo number_format(count($orders)); ?> result<?php echo count($orders) != 1 ? 's' : ''; ?></span>
    </div>
</div>

<div class="row">
    <div class="col col-12">
        <div class="card ds-pink-table-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table inv-table ds-pink-table table-stack" id="staffOrdersTable">
                        <thead>
                            <tr>
                                <th>Order #</th><th>Customer</th><th>Total</th><th>Delivery</th><th>Status</th><th>Date</th><th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr id="order-row-<?php echo $order['order_id']; ?>">
                                    <td><strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                    <td>#<?php echo (int) $order['customer_id']; ?></td>
                                    <td><?php echo CURRENCY . number_format($order['total_amount'], 2); ?></td>
                                    <td><?php echo ucfirst($order['delivery_method']); ?></td>
                                    <?php if ($order['order_status'] === 'pending'): ?>
                                        <td>
                                            <span class="badge bg-warning text-dark" style="font-size:.75rem;white-space:normal;">
                                                <i class="fas fa-hourglass-half"></i> Awaiting payment verification by admin
                                            </span>
                                        </td>
                                        <td><small><?php echo date('M d, Y', strtotime($order['created_at'])); ?></small></td>
                                        <td><span class="text-muted small">Not yet actionable</span></td>
                                    <?php else: ?>
                                        <td>
                                            <select class="form-control form-select form-select-sm" id="status-<?php echo $order['order_id']; ?>">
                                                <?php foreach (['processing', 'to_ship', 'delivered'] as $status): ?>
                                                    <option value="<?php echo $status; ?>" <?php echo $order['order_status'] === $status ? 'selected' : ''; ?>>
                                                        <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td><small><?php echo date('M d, Y', strtotime($order['created_at'])); ?></small></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="updateOrderStatus(<?php echo $order['order_id']; ?>)">
                                                <i class="fas fa-save"></i> Update
                                            </button>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal" id="statusConfirmModal">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h3 class="modal-title">Update Order Status</h3>
            <button type="button" class="modal-close" onclick="closeModal(document.getElementById('statusConfirmModal'))">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p id="statusConfirmBody" style="margin:0;"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" id="statusBtnCancel" onclick="closeModal(document.getElementById('statusConfirmModal'))">Cancel</button>
            <button type="button" class="btn btn-primary btn-sm" id="statusBtnConfirm">
                <span id="statusBtnConfirmText">Update</span>
                <span id="statusBtnConfirmSpinner" class="spinner-border spinner-border-sm ms-1 d-none"></span>
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
    $('#staffOrdersTable').DataTable({
        order: [[5, 'desc']],
        columnDefs: [{ orderable: false, targets: [4, 6] }],
        language: { search: '_INPUT_', searchPlaceholder: 'Search order or customer…', emptyTable: 'No orders yet' },
        drawCallback: function() { if (typeof initResponsiveTableStacking === 'function') initResponsiveTableStacking(); }
    });
});

let _pendingOrderId = null;

function updateOrderStatus(orderId) {
    const status = document.getElementById('status-' + orderId).value;
    _pendingOrderId = orderId;

    document.getElementById('statusConfirmBody').textContent = 'Update this order to "' + status.replace('_', ' ') + '"?';
    document.getElementById('statusBtnConfirmText').textContent = 'Update';
    document.getElementById('statusBtnConfirmSpinner').classList.add('d-none');
    document.getElementById('statusBtnConfirm').disabled = false;
    document.getElementById('statusBtnCancel').disabled = false;
    openModal('statusConfirmModal');
}

document.getElementById('statusBtnConfirm').addEventListener('click', function () {
    if (!_pendingOrderId) return;
    const orderId = _pendingOrderId;
    const status = document.getElementById('status-' + orderId).value;

    const btn = this;
    btn.disabled = true;
    document.getElementById('statusBtnCancel').disabled = true;
    document.getElementById('statusBtnConfirmSpinner').classList.remove('d-none');

    fetch('<?php echo site_url("staff/orders/update_status/"); ?>' + orderId, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'order_status=' + encodeURIComponent(status)
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                closeModal(document.getElementById('statusConfirmModal'));
                alert(data.message || 'Failed to update order status.');
                btn.disabled = false;
                document.getElementById('statusBtnCancel').disabled = false;
                document.getElementById('statusBtnConfirmText').textContent = 'Update';
                document.getElementById('statusBtnConfirmSpinner').classList.add('d-none');
                _pendingOrderId = null;
            }
        })
        .catch(() => {
            closeModal(document.getElementById('statusConfirmModal'));
            alert('Request failed');
            btn.disabled = false;
            document.getElementById('statusBtnCancel').disabled = false;
            document.getElementById('statusBtnConfirmText').textContent = 'Update';
            document.getElementById('statusBtnConfirmSpinner').classList.add('d-none');
            _pendingOrderId = null;
        });
});
</script>
