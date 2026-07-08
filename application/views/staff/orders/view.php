<div class="row">
    <div class="col col-12">
        <h2 class="mb-4"><i class="fas fa-shopping-cart"></i> Order Details - #<?php echo htmlspecialchars($data['order']['order_number'] ?? ''); ?></h2>
    </div>
</div>

<div class="row">
    <div class="col col-8">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-box"></i> Order Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($data['order_items'])): ?>
                                <?php foreach($data['order_items'] as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name'] ?? 'Unknown'); ?></td>
                                    <td><code><?php echo htmlspecialchars($item['sku'] ?? 'N/A'); ?></code></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo CURRENCY . number_format($item['price'] ?? 0, 2); ?></td>
                                    <td><?php echo CURRENCY . number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 0), 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if($data['can_update_status']): ?>
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-edit"></i> Update Order Status</h5>
            </div>
            <div class="card-body">
                <form id="updateStatusForm">
                    <input type="hidden" name="order_id" value="<?php echo (int) ($data['order']['order_id'] ?? 0); ?>">

                    <div class="form-group">
                        <label for="status"><strong>New Status</strong></label>
                        <?php $currentStatus = $data['order']['order_status'] ?? $data['order']['status'] ?? 'pending'; ?>
                        <select class="form-control" id="status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="processing" <?php echo $currentStatus === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="to_ship" <?php echo $currentStatus === 'to_ship' || $currentStatus === 'shipped' ? 'selected' : ''; ?>>To Ship</option>
                            <option value="delivered" <?php echo $currentStatus === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="paid" <?php echo $currentStatus === 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="cancelled" <?php echo $currentStatus === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            <option value="refund_return" <?php echo $currentStatus === 'refund_return' ? 'selected' : ''; ?>>Refund/Return</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="notes"><strong>Notes (Optional)</strong></label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Add any notes about this status change..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Update Status
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col col-4">
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Order Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Order #:</strong> <?php echo htmlspecialchars($data['order']['order_number'] ?? ''); ?></p>
                <p><strong>Date:</strong> <?php echo !empty($data['order']['created_at']) ? date('M d, Y H:i', strtotime($data['order']['created_at'])) : ''; ?></p>
                <p>
                    <strong>Status:</strong>
                    <?php 
                    $statusClass = [
                        'pending' => 'warning',
                        'processing' => 'info',
                        'to_ship' => 'secondary',
                        'shipped' => 'secondary', // legacy mapping
                        'delivered' => 'success',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        'refund_return' => 'warning'
                    ];
                    $currentStatus = $data['order']['order_status'] ?? $data['order']['status'] ?? 'pending';
                    ?>
                    <span class="badge badge-<?php echo $statusClass[$currentStatus] ?? 'secondary'; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $currentStatus)); ?>
                    </span>
                </p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-user"></i> Customer Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($data['order']['customer_name'] ?? ($data['order']['full_name'] ?? '')); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($data['order']['customer_email'] ?? ($data['order']['email'] ?? '')); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($data['order']['phone'] ?? ''); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($data['order']['address'] ?? ($data['order']['shipping_address'] ?? '')); ?></p>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-money-bill"></i> Order Summary</h5>
            </div>
            <div class="card-body">
                <p><strong>Subtotal:</strong> <?php echo CURRENCY . number_format($data['order']['subtotal'] ?? 0, 2); ?></p>
                <p><strong>Delivery Fee:</strong> <?php echo CURRENCY . number_format($data['order']['delivery_fee'] ?? 0, 2); ?></p>
                <p><strong>Discount:</strong> <?php echo CURRENCY . number_format($data['order']['discount'] ?? 0, 2); ?></p>
                <hr>
                <p class="h5"><strong>Total:</strong> <?php echo CURRENCY . number_format($data['order']['total_amount'] ?? 0, 2); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col col-12">
        <a href="<?php echo URLROOT; ?>order" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
    </div>
</div>

<script>
const updateStatusForm = document.getElementById('updateStatusForm');
if (updateStatusForm) updateStatusForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const orderId = formData.get('order_id');
    
    fetch('<?php echo URLROOT; ?>order/updateStatus/' + orderId, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('Order status updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to update order status'));
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
});
</script>
