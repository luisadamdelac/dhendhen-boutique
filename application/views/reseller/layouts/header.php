<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Dashboard'; ?> - DropSell Reseller</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap 5 (self-hosted; base layer — admin-style.css overrides theme portions) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/vendor/bootstrap/bootstrap.min.css">

    <!-- Admin CSS (shared look) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/admin-style.css">

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
                    <?php echo $page_title ?? 'Dashboard'; ?>
                </h1>
            </div>

            <div class="header-right">
                <div class="header-icons">
                    <a href="<?php echo BASE_URL; ?>reseller/notifications" class="header-icon" id="notificationIcon" title="Notifications">
                        <i class="fas fa-bell"></i>
                        <span class="badge" id="notificationBadge" style="display: none;">0</span>
                    </a>
                </div>

                <div class="user-menu" id="userMenuToggle">
                    <?php
                        $avatarImage = get_user_profile_image();
                        $avatarSrc = !empty($avatarImage)
                            ? BASE_URL . $avatarImage
                            : BASE_URL . default_avatar_url();
                    ?>
                    <img src="<?php echo $avatarSrc; ?>"
                         alt="Reseller" class="user-avatar" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 200 200%22%3E%3Ccircle cx=%22100%22 cy=%22100%22 r=%22100%22 fill=%22%23667eea%22/%3E%3C/svg%3E'">
                    <div class="user-info">
                        <h4><?php echo htmlspecialchars($user_full_name ?? 'Reseller'); ?></h4>
                        <p>Reseller</p>
                    </div>
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
                    <p>Reseller Portal</p>
                </div>
            </div>

            <nav>
                <ul class="sidebar-nav">
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>reseller/dashboard" class="nav-link <?php echo (isset($page_title) && strpos($page_title, 'Dashboard') !== false) ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>reseller/inventory" class="nav-link <?php echo (isset($page_title) && strpos($page_title, 'Inventory') !== false) ? 'active' : ''; ?>">
                            <i class="fas fa-box"></i>
                            <span>Inventory</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>reseller/orders" class="nav-link <?php echo (isset($page_title) && strpos($page_title, 'Order') !== false) ? 'active' : ''; ?>">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Orders</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>reseller/commission" class="nav-link <?php echo (isset($page_title) && strpos($page_title, 'Commission') !== false) ? 'active' : ''; ?>">
                            <i class="fas fa-dollar-sign"></i>
                            <span>Commissions</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>reseller/withdrawals" class="nav-link <?php echo (isset($page_title) && strpos($page_title, 'Withdrawal') !== false) ? 'active' : ''; ?>">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Withdrawals</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>reseller/sales" class="nav-link <?php echo (isset($page_title) && strpos($page_title, 'Sales') !== false) ? 'active' : ''; ?>">
                            <i class="fas fa-chart-line"></i>
                            <span>Sales Report</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>reseller/profile" class="nav-link <?php echo (isset($page_title) && strpos($page_title, 'Profile') !== false) ? 'active' : ''; ?>">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>auth/logout/reseller" class="nav-link" onclick="return confirm('Are you sure you want to logout?');">
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
