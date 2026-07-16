<div class="row">
    <div class="col-md-12">
        <h2 class="mb-4"><i class="fas fa-calendar-times text-warning"></i> Expiring Products</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-list"></i> Already Expired or Expiring Within 30 Days</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-secondary">
                            <tr>
                                <th>SKU</th>
                                <th>Product Name</th>
                                <th>Expiry Date</th>
                                <th>Stock (this branch)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $product): ?>
                                <?php $daysLeft = (int) ceil((strtotime($product['expiry_date']) - strtotime(date('Y-m-d'))) / 86400); ?>
                                <tr class="<?php echo $daysLeft < 0 ? 'table-danger' : 'table-warning'; ?>">
                                    <td><code><?php echo htmlspecialchars($product['sku']); ?></code></td>
                                    <td><strong><?php echo htmlspecialchars($product['product_name']); ?></strong></td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($product['expiry_date'])); ?><br>
                                        <span style="font-size:11px;font-weight:600;color:<?php echo $daysLeft <= 30 ? '#dc3545' : '#8a94ad'; ?>;">
                                            <?php echo $daysLeft < 0 ? 'Expired' : $daysLeft . ' days left'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo (int) $product['stock']; ?> units
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-success">
                                    <i class="fas fa-check-circle"></i> No products expiring soon!
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
