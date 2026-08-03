<style>
    .order-details-container {
        margin-top: 30px;
    }
    
    .order-header {
        background: var(--white);
        padding: 30px;
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .order-title {
        font-size: 28px;
        font-weight: bold;
        color: var(--gray-900);
        margin-bottom: 5px;
    }
    
    .order-date {
        color: var(--gray);
        font-size: 14px;
    }
    
    .status-badge {
        display: inline-block;
        padding: 10px 25px;
        border-radius: 25px;
        font-size: 15px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-processing {
        background: #cfe2ff;
        color: #084298;
    }
    
    .status-shipped {
        background: #e7d6ff;
        color: #6f42c1;
    }
    
    .status-delivered {
        background: #d1e7dd;
        color: #0f5132;
    }
    
    .status-cancelled {
        background: #f8d7da;
        color: #842029;
    }
    
    .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .detail-card {
        background: var(--white);
        padding: 25px;
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
    }
    
    .card-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .card-content {
        color: var(--gray);
        line-height: 1.8;
    }
    
    .card-content strong {
        color: var(--gray-900);
        display: inline-block;
        min-width: 120px;
    }
    
    .items-card {
        background: var(--white);
        padding: 30px;
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
        margin-bottom: 20px;
    }
    
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    
    .items-table thead {
        background: var(--light-gray);
        color: var(--gray-900);
    }
    
    .items-table th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: var(--gray-900) !important;
        border-bottom: 2px solid var(--gray-300);
    }
    
    .items-table td {
        padding: 18px 15px;
        color: var(--gray-900);
        border-bottom: 1px solid var(--gray-200);
        vertical-align: middle;
    }
    
    .item-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        background: #f0f0f0;
    }
    
    .item-name {
        font-weight: 500;
        color: var(--gray-900);
        font-size: 15px;
    }
    
    .item-price {
        font-size: 16px;
        color: var(--gray);
    }
    
    .item-quantity {
        font-size: 15px;
        color: var(--gray-900);
        text-align: center;
    }
    
    .item-total {
        font-size: 18px;
        font-weight: bold;
        color: var(--primary-pink);
        text-align: right;
    }
    
    .order-summary {
        background: var(--light-gray);
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        font-size: 15px;
    }
    
    .summary-total {
        font-size: 24px;
        font-weight: bold;
        color: var(--primary-pink);
        border-top: 2px solid var(--gray-300);
        padding-top: 15px;
        margin-top: 10px;
    }
    
    .action-buttons {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        flex-wrap: wrap;
    }
    
    /* Cancel Order now uses the shared .btn-danger component. */

    @media (max-width: 768px) {
        .items-table {
            font-size: 14px;
        }

        .item-image {
            width: 60px;
            height: 60px;
        }
    }

    @media (max-width: 600px) {
        .order-header,
        .detail-card,
        .items-card {
            padding: 16px;
        }

        .order-title {
            font-size: 21px;
        }

        .status-badge {
            padding: 8px 16px;
            font-size: 13px;
        }

        .action-buttons {
            flex-direction: column;
        }

        .action-buttons .btn {
            width: 100%;
        }
    }
</style>

<div class="order-details-container">
    <!-- Back Button -->
    <a href="<?php echo BASE_URL; ?>orders" class="btn btn-outline" style="margin-bottom: 20px; display: inline-flex; align-items: center; gap: 8px;">
        <i class="fas fa-arrow-left"></i> Back to Orders
    </a>
    
    <!-- Order Header -->
    <div class="order-header">
        <div>
            <div class="order-title">Order #<?php echo $order['order_number']; ?></div>
            <div class="order-date">
                Placed on <?php echo date('F d, Y \a\t h:i A', strtotime($order['created_at'])); ?>
            </div>
        </div>
        <div>
            <span class="status-badge status-<?php echo $order['order_status']; ?>">
                <?php echo ucfirst($order['order_status']); ?>
            </span>
        </div>
    </div>
    
    <!-- Customer & Delivery Details -->
    <div class="details-grid">
        <!-- Customer Information -->
        <div class="detail-card">
            <h2 class="card-title">
                <i class="fas fa-user"></i> Customer Information
            </h2>
            <div class="card-content">
                <div><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></div>
                <div><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></div>
                <div><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></div>
            </div>
        </div>
        
        <!-- Delivery Address -->
        <div class="detail-card">
            <h2 class="card-title">
                <i class="fas fa-map-marker-alt"></i> Delivery Address
            </h2>
            <div class="card-content">
                <div><strong>Type:</strong> <?php echo ucwords(str_replace('_', ' ', $order['delivery_type'])); ?></div>
                <div><strong>Address:</strong><br><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></div>
            </div>
        </div>
        
        <!-- Payment Information -->
        <div class="detail-card">
            <h2 class="card-title">
                <i class="fas fa-credit-card"></i> Payment Information
            </h2>
            <div class="card-content">
                <div><strong>Method:</strong>
                    <?php $pm = $order['payment']['payment_method'] ?? 'N/A'; ?>
                    <?php if ($pm === 'GCash'): ?>
                        <span style="color:#007DFF; font-weight:600;"><i class="fas fa-mobile-alt"></i> GCash</span>
                    <?php else: ?>
                        <?php echo htmlspecialchars($pm); ?>
                    <?php endif; ?>
                </div>
                <div><strong>Status:</strong>
                    <?php
                        $payStatus = $order['payment']['status'] ?? 'pending';
                        $payStatusColors = ['pending' => '#ffc107', 'completed' => '#28a745', 'failed' => '#dc3545', 'refunded' => '#6c757d'];
                        $payColor = $payStatusColors[$payStatus] ?? '#ffc107';
                    ?>
                    <span style="color: <?php echo $payColor; ?>; font-weight: 600;">
                        <?php echo ucfirst($payStatus); ?>
                        <?php if ($payStatus === 'pending' && $pm === 'GCash'): ?>
                            <span style="font-size:11px; color:#888; font-weight:normal;">(Awaiting verification)</span>
                        <?php endif; ?>
                    </span>
                </div>

                <?php if ($payStatus === 'failed'): ?>
                    <div style="margin-top:12px; background:#f8d7da; color:#842029; border:1px solid #f5c2c7; border-radius:8px; padding:12px 14px; font-size:13px;">
                        <i class="fas fa-exclamation-circle"></i> Payment rejected: <strong><?php echo htmlspecialchars($order['payment']['rejection_reason'] ?? 'Please contact support.'); ?></strong>
                        <div style="margin-top:8px;">
                            <a href="<?php echo BASE_URL; ?>checkout/resubmit_payment/<?php echo $order['order_id']; ?>" class="btn btn-danger btn-sm">
                                <i class="fas fa-redo"></i> Resubmit Payment
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($pm === 'GCash' && !empty($order['payment'])): ?>
                    <?php if (!empty($order['payment']['payment_reference'])): ?>
                        <div><strong>Ref. Number:</strong> <span style="color:#007DFF; font-weight:600;"><?php echo htmlspecialchars($order['payment']['payment_reference']); ?></span></div>
                    <?php endif; ?>
                    <?php if (!empty($order['payment']['receipt_image'])): ?>
                        <div style="margin-top:10px;">
                            <strong>Receipt:</strong><br>
                            <a href="<?php echo BASE_URL . htmlspecialchars($order['payment']['receipt_image']); ?>" target="_blank" style="display:inline-block; margin-top:8px;">
                                <img src="<?php echo BASE_URL . htmlspecialchars($order['payment']['receipt_image']); ?>"
                                     style="max-width:200px; border-radius:8px; border:2px solid #007DFF; cursor:pointer;"
                                     alt="GCash Receipt">
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if (!empty($order['notes'])): ?>
                    <div style="margin-top: 10px;"><strong>Notes:</strong><br><?php echo nl2br(htmlspecialchars($order['notes'])); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Order Items -->
    <div class="items-card">
        <h2 class="card-title">
            <i class="fas fa-box"></i> Order Items
        </h2>
        
        <div style="overflow-x: auto;">
            <table class="items-table table-stack">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th style="text-align: center;">Quantity</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <?php $itemImage = $item['product_image'] ?? $item['image'] ?? ''; ?>
                                    <?php if (!empty($itemImage)): ?>
                                        <img src="<?php echo BASE_URL . $itemImage; ?>"
                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                             class="item-image">
                                    <?php else: ?>
                                        <div class="item-image" style="display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-image" style="color: #ccc; font-size: 24px;"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="item-price">₱<?php echo number_format($item['unit_price'], 2); ?></div>
                            </td>
                            <td>
                                <div class="item-quantity"><?php echo $item['quantity']; ?></div>
                            </td>
                            <td>
                                <div class="item-total">₱<?php echo number_format($item['subtotal'], 2); ?></div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Order Summary -->
        <div class="order-summary">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span style="font-weight: 600;">₱<?php echo number_format($order['total_amount'] - $order['delivery_fee'], 2); ?></span>
            </div>
            <div class="summary-row">
                <span>Delivery Fee:</span>
                <span style="font-weight: 600;">₱<?php echo number_format($order['delivery_fee'], 2); ?></span>
            </div>
            <div class="summary-row summary-total">
                <span>Total Amount:</span>
                <span>₱<?php echo number_format($order['total_amount'], 2); ?></span>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="action-buttons">
        <?php if ($order['order_status'] == 'pending'): ?>
            <a href="<?php echo BASE_URL; ?>customer/orders/cancel/<?php echo $order['order_id']; ?>"
               class="btn btn-danger"
               onclick="return confirmNavigate(event, this, 'Are you sure you want to cancel this order?', 'Cancel Order')">
                <i class="fas fa-times-circle"></i> Cancel Order
            </a>
        <?php endif; ?>
        
        <a href="<?php echo BASE_URL; ?>orders" class="btn btn-outline">
            <i class="fas fa-list"></i> View All Orders
        </a>
    </div>
</div>
