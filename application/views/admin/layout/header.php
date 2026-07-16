<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>DropSell Admin</title>
    
    <!-- Favicon with Data URI (no 404 errors) -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛍️</text></svg>">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome (self-hosted — see public/vendor/) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/vendor/fontawesome/css/all.min.css">

    <!-- Bootstrap 5 (self-hosted; base layer — admin-style.css overrides theme portions) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/vendor/bootstrap/bootstrap.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/admin-style.css?v=<?php echo @filemtime(FCPATH . 'public/css/admin-style.css') ?: time(); ?>">

    <!-- Chart.js (self-hosted) -->
    <script src="<?php echo BASE_URL; ?>public/vendor/chartjs/chart.umd.min.js"></script>

    <!-- jQuery (self-hosted) -->
    <script src="<?php echo BASE_URL; ?>public/vendor/jquery/jquery-3.7.0.min.js"></script>
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
                    <?php echo isset($page_title) ? $page_title : 'Dashboard'; ?>
                </h1>
            </div>
            
            <div class="header-right">
                <!-- Search -->
                <div class="header-search" id="headerSearchBox">
                    <form action="<?php echo BASE_URL; ?>admin/product" method="GET" style="display:contents;">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Search products…" id="globalSearch" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </form>
                </div>

                <!-- Icons -->
                <div class="header-icons">
                    <?php
                    $CI = &get_instance();
                    $CI->load->model('Notification_model');
                    $adminId = get_user_id();
                    $unreadCount = $CI->Notification_model->get_unread_count($adminId, 'admin');
                    $messageCount = $CI->Notification_model->get_unread_count_by_type($adminId, 'admin', 'order');
                    ?>
                    <!-- Notifications -->
                    <div class="header-icon-dropdown" style="position:relative;display:inline-block;">
                        <a href="javascript:void(0);" class="header-icon" id="notificationIcon" title="Notifications">
                            <i class="fas fa-bell"></i>
                            <span class="badge" id="notificationBadge" style="display: <?php echo $unreadCount > 0 ? 'block' : 'none'; ?>;">
                                <?php echo $unreadCount; ?>
                            </span>
                        </a>
                        <div id="notificationPanel" class="notif-dropdown-panel" style="display:none;">
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-bottom:1px solid #f0f0f0;">
                                <strong style="font-size:14px;">Notifications</strong>
                                <button type="button" id="markAllReadBtn" style="background:none;border:none;color:var(--primary-pink,#ff69b4);font-size:12px;font-weight:600;cursor:pointer;">
                                    <i class="fas fa-check-double"></i> Mark all read
                                </button>
                            </div>
                            <div id="notificationList"><div style="padding:20px;text-align:center;color:#999;font-size:13px;">Loading…</div></div>
                            <div style="padding:8px 16px;border-top:1px solid #f0f0f0;text-align:center;">
                                <a href="<?php echo site_url('admin/notification'); ?>" style="font-size:12px;color:var(--primary-pink,#ff69b4);text-decoration:none;font-weight:600;">View all notifications</a>
                            </div>
                        </div>
                    </div>

                    <!-- Messages / Order Notifications -->
                    <div class="header-icon-dropdown" style="position:relative;display:inline-block;">
                        <a href="javascript:void(0);" class="header-icon" id="messageIcon" title="Order Alerts">
                            <i class="fas fa-envelope"></i>
                            <span class="badge" id="messageBadge" style="display: <?php echo $messageCount > 0 ? 'block' : 'none'; ?>;">
                                <?php echo $messageCount; ?>
                            </span>
                        </a>
                        <div id="messagePanel" class="notif-dropdown-panel" style="display:none;">
                            <div style="padding:12px 16px;border-bottom:1px solid #f0f0f0;">
                                <strong style="font-size:14px;">Order Alerts</strong>
                            </div>
                            <div id="messageList"><div style="padding:20px;text-align:center;color:#999;font-size:13px;">Loading…</div></div>
                            <div style="padding:8px 16px;border-top:1px solid #f0f0f0;text-align:center;">
                                <a href="<?php echo site_url('admin/order'); ?>" style="font-size:12px;color:var(--primary-pink,#ff69b4);text-decoration:none;font-weight:600;">View all orders</a>
                            </div>
                        </div>
                    </div>

                </div>
                
                <!-- User Menu -->
                <div class="user-menu" id="userMenuToggle">
                    <?php 
                        $avatarImage = get_user_profile_image();
                        $avatarSrc = !empty($avatarImage)
                            ? BASE_URL . $avatarImage
                            : BASE_URL . default_avatar_url();
                    ?>
                    <img src="<?php echo $avatarSrc; ?>" 
                         alt="Admin" class="user-avatar" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 200 200%22%3E%3Ccircle cx=%22100%22 cy=%22100%22 r=%22100%22 fill=%22%23667eea%22/%3E%3C/svg%3E'">
                    <div class="user-info">
                        <h4><?php echo get_user_full_name() ?: 'Administrator'; ?></h4>
                        <p>Administrator</p>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </header>

        <!-- Sidebar backdrop (mobile off-canvas overlay, tap to close) -->
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-store"></i>
                </div>
                <div class="sidebar-brand">
                    <h2>DropSell</h2>
                    <p>Dhendhen Beauty</p>
                </div>
            </div>

            <nav>
                <ul class="sidebar-nav">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="<?php echo site_url('admin/dashboard'); ?>" class="nav-link <?php echo (isset($page_title) && $page_title == 'Dashboard') ? 'active' : ''; ?>">
                            <i class="fas fa-th-large"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <!-- Inventory Management -->
                    <li class="nav-item">
                        <a href="<?php echo site_url('admin/product'); ?>" class="nav-link <?php echo (isset($page_title) && strpos($page_title, 'Inventory') !== false) ? 'active' : ''; ?>">
                            <i class="fas fa-boxes"></i>
                            <span>Inventory</span>
                            <?php if (!empty($inventory_attention_count)): ?>
                                <span class="badge" title="<?php echo count($low_stock_products ?? []); ?> low stock, <?php echo count($expiring_products ?? []); ?> expiring soon"><?php echo $inventory_attention_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- Orders -->
                    <?php
                        $ordersActive = isset($page_title) && (strpos($page_title, 'Order') !== false || strpos($page_title, 'Refund') !== false || strpos($page_title, 'Review') !== false);
                        $totalOrderBadge = (isset($pending_orders_notifications) ? (int)$pending_orders_notifications : 0);
                    ?>
                    <li class="nav-item">
                        <a href="<?php echo site_url('admin/order'); ?>" class="nav-link <?php echo $ordersActive ? 'active' : ''; ?>">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Orders</span>
                            <?php if($totalOrderBadge > 0): ?>
                                <span class="badge"><?php echo $totalOrderBadge; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- Resellers -->
                    <?php
                        $pendingResellersScalar = isset($pending_resellers) ? (int)$pending_resellers : 0;
                        $pendingWithdrawalsScalar = isset($pending_withdrawals) ? (int)$pending_withdrawals : 0;
                    ?>
                    <li class="nav-item">
                        <a href="<?php echo site_url('admin/reseller'); ?>" class="nav-link <?php echo (isset($page_title) && strpos($page_title, 'Reseller') !== false) ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i>
                            <span>Resellers</span>
                            <?php if($pendingResellersScalar > 0): ?>
                                <span class="badge"><?php echo $pendingResellersScalar; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- Commissions -->
                    <li class="nav-item">
                        <a href="<?php echo site_url('admin/commission'); ?>" class="nav-link <?php echo (isset($page_title) && strpos($page_title, 'Commission') !== false) ? 'active' : ''; ?>">
                            <i class="fas fa-hand-holding-usd"></i>
                            <span>Commissions</span>
                        </a>
                    </li>

                    <!-- Withdrawals -->
                    <li class="nav-item">
                        <a href="<?php echo site_url('admin/withdrawal'); ?>" class="nav-link <?php echo (isset($page_title) && strpos($page_title, 'Withdrawal') !== false) ? 'active' : ''; ?>">
                            <i class="fas fa-wallet"></i>
                            <span>Withdrawals</span>
                            <?php if($pendingWithdrawalsScalar > 0): ?>
                                <span class="badge"><?php echo $pendingWithdrawalsScalar; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- Reports -->
                    <li class="nav-item">
                        <a href="<?php echo site_url('admin/reports'); ?>" class="nav-link <?php echo (isset($page_title) && strpos($page_title, 'Report') !== false) ? 'active' : ''; ?>">
                            <i class="fas fa-chart-line"></i>
                            <span>Reports</span>
                        </a>
                    </li>

                    <!-- Staff Management -->
                    <li class="nav-item">
                        <a href="<?php echo site_url('admin/staff'); ?>" class="nav-link <?php echo (isset($page_title) && strpos($page_title, 'Staff') !== false) ? 'active' : ''; ?>">
                            <i class="fas fa-user-tie"></i>
                            <span>Staff</span>
                        </a>
                    </li>

                    <!-- Settings -->
                    <li class="nav-item">
                        <a href="<?php echo site_url('admin/settings'); ?>" class="nav-link <?php echo (isset($page_title) && strpos($page_title, 'Settings') !== false) ? 'active' : ''; ?>">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>

                    <!-- Logout -->
                    <li class="nav-item">
                        <a href="<?php echo site_url('auth/logout/admin'); ?>" class="nav-link">
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
