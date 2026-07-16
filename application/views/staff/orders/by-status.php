<div class="row">
    <div class="col col-12">
        <h2 class="mb-4"><i class="fas fa-shopping-cart"></i> Orders - <?php echo ucfirst($data['status'] ?? ''); ?></h2>
    </div>
</div>

<div class="row">
    <div class="col col-12 mb-3">
        <div class="order-status-filters" role="group" aria-label="Order status filters">
            <a href="<?php echo URLROOT; ?>order" class="btn btn-primary">
                <i class="fas fa-list"></i> All Orders
            </a>
            <a href="<?php echo URLROOT; ?>order/status/pending" class="btn btn-warning <?php echo $data['status'] === 'pending' ? 'active' : ''; ?>">
                <i class="fas fa-hourglass-half"></i> Pending
            </a>
            <a href="<?php echo URLROOT; ?>order/status/processing" class="btn btn-info <?php echo $data['status'] === 'processing' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> Processing
            </a>
            <a href="<?php echo URLROOT; ?>order/status/to_ship" class="btn btn-secondary <?php echo $data['status'] === 'to_ship' ? 'active' : ''; ?>">
                <i class="fas fa-truck"></i> To Ship
            </a>
            <a href="<?php echo URLROOT; ?>order/status/delivered" class="btn btn-success <?php echo $data['status'] === 'delivered' ? 'active' : ''; ?>">
                <i class="fas fa-check-circle"></i> Delivered
            </a>
            <a href="<?php echo URLROOT; ?>order/status/paid" class="btn btn-success <?php echo $data['status'] === 'paid' ? 'active' : ''; ?>">
                <i class="fas fa-money-bill-wave"></i> Paid
            </a>
            <a href="<?php echo URLROOT; ?>order/status/refund_return" class="btn btn-warning <?php echo $data['status'] === 'refund_return' ? 'active' : ''; ?>">
                <i class="fas fa-undo"></i> Refund/Return
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-list"></i> <?php echo ucfirst($data['status']); ?> Orders</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-stack">
                        <thead class="table-secondary">
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($data['orders'])): ?>
                                <?php foreach($data['orders'] as $order): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($order['customer_name'] ?? 'Unknown'); ?>
                                    </td>
                                    <td>
                                        <?php $itemCount = $order['item_count'] ?? $order['total_items'] ?? 0; ?>
                                        <span class="badge badge-info"><?php echo (int) $itemCount; ?> items</span>
                                    </td>
                                    <td>
                                        <?php echo CURRENCY . number_format($order['total_amount'] ?? 0, 2); ?>
                                    </td>
                                    <td>
                                        <small><?php echo date('M d, Y', strtotime($order['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <a href="<?php echo URLROOT; ?>order/show/<?php echo $order['order_id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    <i class="fas fa-inbox"></i> No <?php echo htmlspecialchars($data['status']); ?> orders found
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
