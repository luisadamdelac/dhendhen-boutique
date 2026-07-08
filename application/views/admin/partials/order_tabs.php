<?php
    // Determine active tab from page_title (negotiation removed)
    $activeTab = 'orders';
    if (isset($page_title)) {
        if (strpos($page_title, 'Refund') !== false) {
            $activeTab = 'refunds';
        } elseif (strpos($page_title, 'Review') !== false) {
            $activeTab = 'reviews';
        }
    }
?>
<div class="page-tabs">
    <a href="<?php echo BASE_URL; ?>admin/order" class="page-tab <?php echo $activeTab === 'orders' ? 'active' : ''; ?>">
        <i class="fas fa-shopping-cart"></i> Orders
        <?php if(isset($pending_orders_notifications) && $pending_orders_notifications > 0): ?>
            <span class="tab-badge"><?php echo $pending_orders_notifications; ?></span>
        <?php endif; ?>
    </a>

    <a href="<?php echo BASE_URL; ?>admin/refund" class="page-tab <?php echo $activeTab === 'refunds' ? 'active' : ''; ?>">
        <i class="fas fa-undo"></i> Refunds
        <?php if(isset($pending_refunds) && $pending_refunds > 0): ?>
            <span class="tab-badge"><?php echo $pending_refunds; ?></span>
        <?php endif; ?>
    </a>
    <a href="<?php echo BASE_URL; ?>admin/reviews" class="page-tab <?php echo $activeTab === 'reviews' ? 'active' : ''; ?>">
        <i class="fas fa-star"></i> Reviews
        <?php if(isset($pending_reviews) && $pending_reviews > 0): ?>
            <span class="tab-badge"><?php echo $pending_reviews; ?></span>
        <?php endif; ?>
    </a>
</div>
