<div class="page-header-section">
    <div>
        <h1 class="page-title"><i class="fas fa-receipt"></i> Order #<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></h1>
        <p class="page-subtitle">Ordered on <?php echo date('F d, Y \a\t h:i A', strtotime($order['created_at'])); ?></p>
    </div>
    <div style="text-align:right; display:flex; flex-direction:column; align-items:flex-end; gap:10px;">
        <a href="<?php echo BASE_URL; ?>reseller/orders" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <span class="badge-status badge-<?php echo $order['order_status']; ?>" style="font-size:13px; padding:6px 16px;">
            <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
        </span>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-user"></i> Customer Information</h3></div>
            <div class="card-body">
                <div class="detail-item">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['customer_email']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['customer_phone'] ?? 'N/A'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-map-marker-alt"></i> Delivery Address</h3></div>
            <div class="card-body">
                <span class="detail-value"><?php echo nl2br(htmlspecialchars($order['delivery_address'] ?? 'N/A')); ?></span>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-credit-card"></i> Payment Information</h3></div>
            <div class="card-body">
                <div class="detail-item">
                    <span class="detail-label">Method:</span>
                    <span class="detail-value"><?php echo strtoupper($order['payment_method'] ?? 'N/A'); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status:</span>
                    <span class="badge badge-<?php echo ($order['payment_status'] === 'completed') ? 'success' : 'warning'; ?>">
                        <?php echo ucfirst($order['payment_status'] ?? 'pending'); ?>
                    </span>
                </div>
                <?php if (!empty($order['payment_proof'])): ?>
                <div class="detail-item">
                    <span class="detail-label">Proof:</span>
                    <a href="<?php echo BASE_URL . 'uploads/payments/' . $order['payment_proof']; ?>" target="_blank" class="text-primary-pink">
                        <i class="fas fa-external-link-alt"></i> View
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header"><h3><i class="fas fa-shopping-cart"></i> Order Items</h3></div>
    <div class="card-body">
        <?php if (!empty($order['items'])): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['items'] as $item): ?>
                            <?php $itemImage = $item['product_image'] ?? $item['image'] ?? ''; ?>
                            <tr>
                                <td style="display:flex; align-items:center; gap:12px;">
                                    <img src="<?php echo !empty($itemImage) ? (BASE_URL . $itemImage) : (BASE_URL . 'public/images/no-image.jpg'); ?>"
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                         style="width:50px;height:50px;object-fit:cover;border-radius:8px;flex-shrink:0;"
                                         onerror="this.src='<?php echo BASE_URL; ?>public/images/no-image.jpg'">
                                    <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                </td>
                                <td>₱<?php echo number_format($item['unit_price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><strong>₱<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: var(--gray-500); padding: 20px;">No items found.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header"><h3><i class="fas fa-receipt"></i> Order Summary</h3></div>
    <div class="card-body">
        <div class="detail-item">
            <span class="detail-label">Subtotal:</span>
            <span class="detail-value">₱<?php echo number_format($order['total_amount'], 2); ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Delivery Fee:</span>
            <span class="detail-value">₱<?php echo number_format($order['delivery_fee'] ?? 0, 2); ?></span>
        </div>
        <div class="detail-item" style="background: var(--gradient-card); margin: 10px -1.5rem; padding: 12px 1.5rem;">
            <span class="detail-label"><i class="fas fa-award"></i> Your Commission:</span>
            <span class="detail-value text-primary-pink"><strong>₱<?php echo number_format($order['commission_amount'] ?? 0, 2); ?></strong></span>
        </div>
        <div class="detail-item" style="border-top: 2px solid var(--gray-800); border-bottom:none; font-size: 18px;">
            <span class="detail-label"><strong>Total:</strong></span>
            <span class="detail-value"><strong>₱<?php echo number_format($order['total_amount'] + ($order['delivery_fee'] ?? 0), 2); ?></strong></span>
        </div>
    </div>
</div>

<style>
.detail-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid var(--gray-200); gap: 10px; }
.detail-item:last-child { border-bottom: none; }
.detail-label { color: var(--gray-600); font-size: 14px; }
.detail-value { color: var(--gray-800); font-weight: 500; font-size: 14px; }
</style>
