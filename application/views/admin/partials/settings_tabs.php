<?php
    $activeTab = 'general';
    if (isset($page_title)) {
        if (strpos($page_title, 'Email Logs') !== false) {
            $activeTab = 'email_logs';
        } elseif (strpos($page_title, 'Email') !== false) {
            $activeTab = 'email';
        } elseif (strpos($page_title, 'PayMongo') !== false) {
            $activeTab = 'paymongo';
        } elseif (strpos($page_title, 'Payment') !== false) {
            $activeTab = 'payment';
        } elseif (strpos($page_title, 'Shipping') !== false) {
            $activeTab = 'shipping';
        }
    }
?>
<div class="page-tabs">
    <a href="<?php echo site_url('admin/settings'); ?>" class="page-tab <?php echo $activeTab === 'general' ? 'active' : ''; ?>">
        <i class="fas fa-store"></i> General
    </a>
    <a href="<?php echo site_url('admin/settings/email'); ?>" class="page-tab <?php echo $activeTab === 'email' ? 'active' : ''; ?>">
        <i class="fas fa-envelope"></i> Email
    </a>
    <a href="<?php echo site_url('admin/settings/email_logs'); ?>" class="page-tab <?php echo $activeTab === 'email_logs' ? 'active' : ''; ?>">
        <i class="fas fa-list"></i> Email Logs
    </a>
    <a href="<?php echo site_url('admin/settings/payment'); ?>" class="page-tab <?php echo $activeTab === 'payment' ? 'active' : ''; ?>">
        <i class="fas fa-credit-card"></i> Payment
    </a>
    <a href="<?php echo site_url('admin/settings/paymongo'); ?>" class="page-tab <?php echo $activeTab === 'paymongo' ? 'active' : ''; ?>">
        <i class="fas fa-shield-halved"></i> PayMongo
    </a>
    <a href="<?php echo site_url('admin/settings/shipping'); ?>" class="page-tab <?php echo $activeTab === 'shipping' ? 'active' : ''; ?>">
        <i class="fas fa-truck"></i> Shipping Fees
    </a>
</div>
