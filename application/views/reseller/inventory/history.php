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
<div class="page-header-section">
    <div>
        <h1 class="page-title"><i class="fas fa-clock"></i> Availability History</h1>
        <p class="page-subtitle"><?php echo $productName; ?></p>
    </div>
    <a href="<?php echo BASE_URL; ?>reseller/inventory" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back
    </a>
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

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3 style="margin:0;"><i class="fas fa-list"></i> Change Log</h3>
    </div>
    <div class="card-body">
        <?php if (empty($history)): ?>
            <p style="color: var(--gray-500); margin:0;">No history found for this product yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
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
