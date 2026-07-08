<?php
    // Determine active tab from page_title
    $activeTab = 'resellers';
    if (isset($page_title)) {
        if (strpos($page_title, 'Commission') !== false) $activeTab = 'commissions';
        elseif (strpos($page_title, 'Withdrawal') !== false) $activeTab = 'withdrawals';
    }
?>
<div class="page-tabs">
    <a href="<?php echo BASE_URL; ?>admin/reseller" class="page-tab <?php echo $activeTab === 'resellers' ? 'active' : ''; ?>">
        <i class="fas fa-users"></i> Resellers
        <?php if(isset($pending_resellers) && $pending_resellers > 0): ?>
            <span class="tab-badge"><?php echo $pending_resellers; ?></span>
        <?php endif; ?>
    </a>
    <a href="<?php echo BASE_URL; ?>admin/commission" class="page-tab <?php echo $activeTab === 'commissions' ? 'active' : ''; ?>">
        <i class="fas fa-wallet"></i> Commissions
    </a>
    <a href="<?php echo BASE_URL; ?>admin/withdrawal" class="page-tab <?php echo $activeTab === 'withdrawals' ? 'active' : ''; ?>">
        <i class="fas fa-money-bill-wave"></i> Withdrawals
        <?php if(isset($pending_withdrawals) && $pending_withdrawals > 0): ?>
            <span class="tab-badge"><?php echo $pending_withdrawals; ?></span>
        <?php endif; ?>
    </a>
</div>
