<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-shopping-cart"></i> My Orders</h3>
    </div>
    <div class="card-body">
        <?php if (empty($orders)): ?>
            <p style="text-align: center; color: var(--gray-500); padding: 2rem;">No orders found</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total Amount</th>
                            <th>Commission</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>₱<?php echo number_format($order['commission_amount'] ?? 0, 2); ?></td>
                                <td>
                                    <span class="badge-status badge-<?php echo $order['order_status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <a href="<?php echo site_url('reseller/orders/view/' . $order['order_id']); ?>" class="btn btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
