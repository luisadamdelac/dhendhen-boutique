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
                            <?php if(isset($low_stock_products) && count($low_stock_products) > 0): ?>
                                <span class="badge"><?php echo count($low_stock_products); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <!-- Orders -->
                    <?php 
                        // Orders section (negotiation removed from grouping/badges)
                        $ordersActive = isset($page_title) && (strpos($page_title, 'Order') !== false || strpos($page_title, 'Refund') !== false || strpos($page_title, 'Review') !== false);
                        // Orders badge: show unread order notifications if available.
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
                        $resellerActive = isset($page_title) && (strpos($page_title, 'Reseller') !== false || strpos($page_title, 'Commission') !== false || strpos($page_title, 'Withdrawal') !== false);
                        // Ensure badge values are scalars to avoid array+int TypeErrors.
                        $pendingResellersScalar = isset($pending_resellers) ? (int)$pending_resellers : 0;
                        $pendingWithdrawalsScalar = isset($pending_withdrawals) ? (int)$pending_withdrawals : 0;
                        $totalResellerBadge = $pendingResellersScalar + $pendingWithdrawalsScalar;

                    ?>
                    <li class="nav-item">
                        <a href="<?php echo site_url('admin/reseller'); ?>" class="nav-link <?php echo $resellerActive ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i>
                            <span>Resellers</span>
                            <?php if($totalResellerBadge > 0): ?>
                                <span class="badge"><?php echo $totalResellerBadge; ?></span>
                            <?php endif; ?>
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
            // Display flash messages
            if (isset($_SESSION['flash_message'])):
                $flashType = $_SESSION['flash_type'];
                $flashMessage = $_SESSION['flash_message'];
                unset($_SESSION['flash_type']);
                unset($_SESSION['flash_message']);
            ?>
                <div class="alert alert-<?php echo $flashType; ?> fade-in">
                    <i class="fas fa-<?php 
                        echo $flashType == 'success' ? 'check-circle' : 
                             ($flashType == 'danger' ? 'exclamation-circle' : 
                             ($flashType == 'warning' ? 'exclamation-triangle' : 'info-circle')); 
                    ?>"></i>
                    <span><?php echo $flashMessage; ?></span>
                </div>
            <?php endif; ?>
