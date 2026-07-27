<?php
    $productName = htmlspecialchars($product['product_name'] ?? 'Product');
    if (isset($product['status'])) {
        $isAvailable = ($product['status'] === 'active');
    } elseif (isset($product['is_active'])) {
        $isAvailable = ((int)$product['is_active'] === 1);
    } else {
        $isAvailable = true;
    }
?>

<!-- Page Header -->
<div class="ds-hero-card mb-3">
    <div class="ds-hero-banner">
        <svg class="ds-hero-wave" viewBox="0 0 1440 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,110 C240,170 480,50 720,90 C960,130 1200,50 1440,100 L1440,200 L0,200 Z" fill="rgba(255,105,180,0.16)"></path>
            <path d="M0,140 C280,80 560,180 840,120 C1080,70 1280,140 1440,130 L1440,200 L0,200 Z" fill="rgba(233,30,99,0.22)"></path>
        </svg>
        <div class="ds-hero-banner-content d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h4 class="fw-bold mb-0" style="color:#1a1a2e;"><i class="fas fa-clock"></i> Availability History</h4>
                <small class="text-muted"><?php echo $productName; ?></small>
            </div>
            <a href="<?php echo BASE_URL; ?>reseller/inventory" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
        <h3 style="margin:0;"><i class="fas fa-box"></i> Product</h3>
        <span class="badge badge-<?php echo $isAvailable ? 'success' : 'secondary'; ?>">
            <?php echo $isAvailable ? 'Available' : 'Unavailable'; ?>
        </span>
    </div>
    <div class="card-body">
        <div style="margin-bottom: 10px;"><strong>Name:</strong> <?php echo $productName; ?></div>
        <?php if (!empty($product['sku'])): ?>
            <div style="margin-bottom: 10px;"><strong>SKU:</strong> <?php echo htmlspecialchars($product['sku']); ?></div>
        <?php endif; ?>
    </div>
</div>

<div class="card ds-pink-table-card" style="margin-top: 20px;">
    <div class="card-header">
        <h3 style="margin:0;"><i class="fas fa-list"></i> Change Log</h3>
    </div>
    <div class="card-body">
        <?php if (empty($history)): ?>
            <p style="color: var(--gray-500); margin:0;">No history found for this product yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped ds-pink-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Change</th>
                            <th>From</th>
                            <th>To</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $row): ?>
                            <tr>
                                <td>
                                    <?php
                                        $created = $row['created_at'] ?? '';
                                        echo $created ? date('M d, Y h:i A', strtotime($created)) : '—';
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars(ucfirst($row['transaction_type'] ?? '—')); ?></td>
                                <td><?php echo ((int) ($row['quantity_changed'] ?? 0)) > 0 ? '+' : ''; echo (int) ($row['quantity_changed'] ?? 0); ?></td>
                                <td><?php echo htmlspecialchars((string) ($row['previous_quantity'] ?? '—')); ?></td>
                                <td><?php echo htmlspecialchars((string) ($row['new_quantity'] ?? '—')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
