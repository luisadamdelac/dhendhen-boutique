<?php
    $activeTab = 'general';
    if (isset($page_title)) {
        if (strpos($page_title, 'Email Logs') !== false) {
            $activeTab = 'email_logs';
        } elseif (strpos($page_title, 'Email') !== false) {
            $activeTab = 'email';
        } elseif (strpos($page_title, 'Payment') !== false) {
            $activeTab = 'payment';
        } elseif (strpos($page_title, 'Shipping') !== false) {
            $activeTab = 'shipping';
        } elseif (strpos($page_title, 'Social') !== false) {
            $activeTab = 'social';
        } elseif (strpos($page_title, 'Activity Log') !== false) {
            $activeTab = 'activity_log';
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
    <a href="<?php echo site_url('admin/settings/shipping'); ?>" class="page-tab <?php echo $activeTab === 'shipping' ? 'active' : ''; ?>">
        <i class="fas fa-truck"></i> Shipping Fees
    </a>
    <a href="<?php echo site_url('admin/settings/social'); ?>" class="page-tab <?php echo $activeTab === 'social' ? 'active' : ''; ?>">
        <i class="fas fa-share-alt"></i> Social Media
    </a>
    <a href="<?php echo site_url('admin/settings/activity_log'); ?>" class="page-tab <?php echo $activeTab === 'activity_log' ? 'active' : ''; ?>">
        <i class="fas fa-history"></i> Activity Log
    </a>
</div>
