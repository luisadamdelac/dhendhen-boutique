<div class="row">
    <div class="col-md-12">
        <h2 class="mb-4"><i class="fas fa-exclamation-triangle text-danger"></i> Low Stock Items</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-list"></i> Products at 10 Units or Below</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-secondary">
                            <tr>
                                <th>SKU</th>
                                <th>Product Name</th>
                                <th>Current Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $product): ?>
                                <tr class="table-danger">
                                    <td><code><?php echo htmlspecialchars($product['sku']); ?></code></td>
                                    <td><strong><?php echo htmlspecialchars($product['product_name']); ?></strong></td>
                                    <td>
                                        <span class="badge bg-danger">
                                            <?php echo (int) $product['stock']; ?> units
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-success">
                                    <i class="fas fa-check-circle"></i> All products are above the low stock threshold!
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

<div class="row mt-3">
    <div class="col-md-12">
        <a href="<?php echo site_url('staff/inventory'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Inventory
        </a>
    </div>
</div>
