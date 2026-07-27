<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Staff Portal'; ?> - DropSell Staff</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap 5 (self-hosted; base layer — admin-style.css overrides theme portions) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/vendor/bootstrap/bootstrap.min.css">

    <!-- Admin CSS (shared look) — cache-busted so CSS fixes here take effect
         immediately instead of serving a stale cached copy -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/admin-style.css?v=<?php echo @filemtime(FCPATH . 'public/css/admin-style.css') ?: time(); ?>">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <header class="header">
            <div class="header-left">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title">
                    <?php echo $page_title ?? 'Staff Portal'; ?>
                </h1>
            </div>

            <div class="header-right">
                <?php
                    $CI = &get_instance();
                    $CI->load->model('Notification_model');
                    $unreadCount = $CI->Notification_model->get_unread_count(get_user_id(), 'staff');
                ?>
                <a href="<?php echo BASE_URL; ?>staff/notifications" class="header-icon" id="notificationIcon" title="Notifications" style="position:relative;margin-right:10px;">
                    <i class="fas fa-bell"></i>
                    <span class="badge" id="notificationBadge" style="display: <?php echo $unreadCount > 0 ? 'block' : 'none'; ?>;"><?php echo $unreadCount; ?></span>
                </a>
                <div class="user-menu" id="userMenuToggle">
                    <?php
                        $avatarImage = get_user_profile_image();
                        $avatarSrc = !empty($avatarImage)
                            ? BASE_URL . $avatarImage
                            : BASE_URL . default_avatar_url();
                    ?>
                    <img src="<?php echo $avatarSrc; ?>" alt="Staff" class="user-avatar">
                    <div class="user-info">
                        <h4><?php echo htmlspecialchars($user_full_name ?? 'Staff'); ?></h4>
                        <p>Staff</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Sidebar backdrop (mobile off-canvas overlay, tap to close) -->
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <?php $sidebarLogoImage = get_store_logo_image(); ?>
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <?php if (!empty($sidebarLogoImage)): ?>
                        <img src="<?php echo BASE_URL . $sidebarLogoImage; ?>" alt="Store logo" class="sidebar-logo-img">
                    <?php else: ?>
                        <i class="fas fa-cube"></i>
                    <?php endif; ?>
                </div>
                <div class="sidebar-brand">
                    <h2>DropSell Staff</h2>
                    <p>Staff Portal</p>
                </div>
            </div>

            <nav>
                <ul class="sidebar-nav">
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>staff/dashboard" class="nav-link <?php echo (isset($page_title) && strpos($page_title, 'Dashboard') !== false) ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>staff/inventory" class="nav-link <?php echo (isset($page_title) && (strpos($page_title, 'Inventory') !== false || strpos($page_title, 'Stock') !== false)) ? 'active' : ''; ?>">
                            <i class="fas fa-boxes"></i>
                            <span>Inventory</span>
                            <?php if (!empty($inventory_attention_count)): ?>
                                <span class="badge"><?php echo $inventory_attention_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>staff/orders" class="nav-link <?php echo (isset($page_title) && strpos($page_title, 'Order') !== false) ? 'active' : ''; ?>">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Orders</span>
                            <?php if (!empty($pending_orders_notifications)): ?>
                                <span class="badge"><?php echo $pending_orders_notifications; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>staff/profile" class="nav-link <?php echo (isset($page_title) && strpos($page_title, 'Profile') !== false) ? 'active' : ''; ?>">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>staff/profile/activity_log" class="nav-link <?php echo (isset($page_title) && strpos($page_title, 'Activity Log') !== false) ? 'active' : ''; ?>">
                            <i class="fas fa-history"></i>
                            <span>Activity Log</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>auth/logout/staff" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <?php
            $flashSuccess = $this->session->flashdata('success');
            $flashError = $this->session->flashdata('error');
            if ($flashSuccess || $flashError):
                $flashType = $flashSuccess ? 'success' : 'danger';
                $flashMessage = $flashSuccess ?: $flashError;
            ?>
                <div class="alert alert-<?php echo $flashType; ?> fade-in">
                    <i class="fas fa-<?php
                        echo $flashType == 'success' ? 'check-circle' : 'exclamation-circle';
                    ?>"></i>
                    <span><?php echo $flashMessage; ?></span>
                </div>
            <?php endif; ?>
