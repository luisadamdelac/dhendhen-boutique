<div class="ds-hero-card mb-3">
    <div class="ds-hero-banner">
        <svg class="ds-hero-wave" viewBox="0 0 1440 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,110 C240,170 480,50 720,90 C960,130 1200,50 1440,100 L1440,200 L0,200 Z" fill="rgba(255,105,180,0.16)"></path>
            <path d="M0,140 C280,80 560,180 840,120 C1080,70 1280,140 1440,130 L1440,200 L0,200 Z" fill="rgba(233,30,99,0.22)"></path>
        </svg>
        <div class="ds-hero-banner-content">
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;"><i class="fas fa-calendar-times text-warning"></i> Expiring Products</h4>
            <small class="text-muted">Already expired or expiring within 30 days.</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card ds-pink-table-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table inv-table ds-pink-table">
                        <thead>
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
