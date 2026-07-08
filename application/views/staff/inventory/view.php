<?php
$product = $data['product'] ?? [];
$productName = $product['product_name'] ?? ($product['name'] ?? 'Product');
$sku = $product['sku'] ?? '';
$categoryName = $product['category_name'] ?? 'Uncategorized';
$price = (float) ($product['price'] ?? 0);
$stockQty = (int) ($product['stock_quantity'] ?? 0);
$lowThreshold = (int) ($product['low_stock_threshold'] ?? 0);
$isLow = $lowThreshold > 0 ? ($stockQty <= $lowThreshold) : false;
$canUpdate = (bool) ($data['can_update'] ?? false);
?>

<div class="row">
    <div class="col col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-box"></i> Product Details</h2>
            <a href="<?php echo URLROOT; ?>inventory" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Inventory
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($productName); ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th style="width: 220px;">SKU</th>
                                <td><code><?php echo htmlspecialchars($sku); ?></code></td>
                            </tr>
                            <tr>
                                <th>Category</th>
                                <td><?php echo htmlspecialchars($categoryName); ?></td>
                            </tr>
                            <tr>
                                <th>Price</th>
                                <td><?php echo CURRENCY . number_format($price, 2); ?></td>
                            </tr>
                            <tr>
                                <th>Current Stock</th>
                                <td>
                                    <span class="badge badge-<?php echo $isLow ? 'danger' : 'success'; ?> badge-lg">
                                        <?php echo number_format($stockQty); ?> units
                                    </span>
                                    <?php if($lowThreshold > 0): ?>
                                        <small class="text-muted" style="margin-left: 8px;">Low stock threshold: <?php echo number_format($lowThreshold); ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="badge badge-<?php echo ((int)($product['is_active'] ?? 1)) === 1 ? 'success' : 'secondary'; ?>">
                                        <?php echo ((int)($product['is_active'] ?? 1)) === 1 ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php if(!empty($product['updated_at'])): ?>
                            <tr>
                                <th>Last Updated</th>
                                <td><small><?php echo date('M d, Y h:i A', strtotime($product['updated_at'])); ?></small></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if($canUpdate): ?>
<div class="row">
    <div class="col col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-edit"></i> Update Stock</h5>
            </div>
            <div class="card-body">
                <form id="updateStockForm">
                    <input type="hidden" name="product_id" value="<?php echo (int)($product['product_id'] ?? 0); ?>">

                    <div class="row">
                        <div class="col col-4">
                            <div class="form-group">
                                <label for="action"><strong>Action</strong></label>
                                <select class="form-control" id="action" name="action" required onchange="updateActionHelper()">
                                    <option value="">Select Action</option>
                                    <option value="set">Set Exact Quantity</option>
                                    <option value="add">Add Stock</option>
                                    <option value="subtract">Remove Stock</option>
                                </select>
                                <small id="action-helper" class="form-text text-muted"></small>
                            </div>
                        </div>
                        <div class="col col-4">
                            <div class="form-group">
                                <label for="quantity"><strong>Quantity</strong></label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="0" required>
                            </div>
                        </div>
                        <div class="col col-4">
                            <div class="form-group">
                                <label for="reason"><strong>Reason</strong></label>
                                <select class="form-control" id="reason" name="reason" required>
                                    <option value="">Select Reason</option>
                                    <option value="Manual Update">Manual Update</option>
                                    <option value="Stock Count Correction">Stock Count Correction</option>
                                    <option value="Damaged Items">Damaged Items</option>
                                    <option value="Returned Items">Returned Items</option>
                                    <option value="Inventory Adjustment">Inventory Adjustment</option>
                                    <option value="New Shipment">New Shipment</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center" style="gap: 12px;">
                        <div class="text-muted" style="font-size: 0.9rem;">
                            Updates will apply immediately.
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Update Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function updateActionHelper() {
    const action = document.getElementById('action').value;
    const helper = document.getElementById('action-helper');

    const helpers = {
        'set': 'Set the product to an exact quantity',
        'add': 'Add this amount to the current stock',
        'subtract': 'Remove this amount from the stock'
    };

    helper.textContent = helpers[action] || '';
}

document.getElementById('updateStockForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const productId = formData.get('product_id');

    fetch('<?php echo URLROOT; ?>inventory/updateStock/' + productId, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.success) {
            alert('Stock updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + ((data && (data.error || data.message)) ? (data.error || data.message) : 'Failed to update stock'));
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
});
</script>
<?php endif; ?>
