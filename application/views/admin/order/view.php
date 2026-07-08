<?php
$page_title = 'Order Details';
$current_page = 'order';

$orderStatus = $order['order_status'] ?? 'pending';
$sLabel = ucfirst($orderStatus);
switch ($orderStatus) {
    case 'pending':
        $sBadge = 'badge-warning';
        break;
    case 'processing':
        $sBadge = 'badge-info';
        break;
    case 'shipped':
    case 'to_ship':
        $sBadge = 'badge-primary';
        $sLabel = 'To Ship';
        break;
    case 'delivered':
        $sBadge = 'badge-success';
        break;
    case 'paid':
        $sBadge = 'badge-success';
        $sLabel = 'Paid';
        break;
    case 'refund_return':
        $sBadge = 'badge-warning';
        $sLabel = 'Refund/Return';
        break;
    case 'cancelled':
        $sBadge = 'badge-danger';
        break;
    default:
        $sBadge = 'badge-warning';
        break;
}
?>
<div class="container-fluid py-4 fade-in">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1a1a2e;"><i class="fas fa-shopping-bag"></i> Order Details</h4>
            <small class="text-muted">View order information and line items.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/order/invoice/' . $order['order_id']); ?>" class="btn btn-success btn-sm"><i class="fas fa-file-invoice"></i> Invoice</a>
            <a href="<?= site_url('admin/order'); ?>" class="btn btn-secondary btn-sm">Back</a>
        </div>
    </div>

    <div class="card">
        <div class="row">
            <div class="col col-8">
                <h4 style="margin-bottom: var(--spacing-sm);">Order #<?= htmlspecialchars($order['order_id']); ?></h4>
                <p style="color: var(--gray);">Customer: <?= htmlspecialchars($order['customer_name'] ?? '-'); ?></p>
                <p style="color: var(--gray);">Reseller: <?= htmlspecialchars(trim(($order['reseller_first_name'] ?? '') . ' ' . ($order['reseller_last_name'] ?? '')) ?: '-'); ?></p>
            </div>
            <div class="col col-4">
                <div class="card stat-card" style="margin-bottom: 0;">
                    <span style="font-size: var(--font-size-sm); color: var(--gray);">Status</span>
                    <h5 style="margin: 4px 0 12px;">
                        <span class="badge <?= $sBadge; ?>"><?= $sLabel; ?></span>
                        <?php if (!empty($order['forwarded_at'])): ?>
                            <span class="badge badge-success" title="Forwarded by reseller"><i class="fas fa-truck-loading"></i> Forwarded</span>
                        <?php endif; ?>
                    </h5>
                    <span style="font-size: var(--font-size-sm); color: var(--gray);">Total</span>
                    <h4 style="color: var(--success); margin-top: 4px;">₱<?= number_format($order['total_amount'] ?? 0, 2); ?></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Items</h3></div>
        <div class="card-body">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr><th>Item</th><th>Qty</th><th>Price</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($items)): ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['product_name'] ?? '-'); ?></td>
                                    <td><?= (int)($item['quantity'] ?? 0); ?></td>
                                    <td>₱<?= number_format($item['unit_price'] ?? 0, 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center" style="padding: 30px 0; color: var(--gray);">No items found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
