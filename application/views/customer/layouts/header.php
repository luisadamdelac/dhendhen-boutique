<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (!defined('SITE_NAME')) { define('SITE_NAME', 'DropSell'); } ?>
    <title><?php echo $page_title ?? 'Shop'; ?> - <?php echo SITE_NAME; ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container" style="margin: 0 auto;">
            <div>
                <i class="fas fa-phone"></i> +63 123 456 7890
                <span style="margin-left: 20px;"><i class="fas fa-envelope"></i> info@dhendhen.com</span>
            </div>
            <div>
                <i class="fas fa-shipping-fast"></i> Free Shipping on Orders Over ₱1000
            </div>
        </div>
    </div>
    
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="<?php echo BASE_URL; ?>shop" class="logo">
                <i class="fas fa-heart"></i>
                <?php echo SITE_NAME; ?>
            </a>
            
            <div class="search-bar">
                <form action="<?php echo BASE_URL; ?>shop" method="GET">
                    <input type="text" name="search" placeholder="Search products..." value="<?php echo $_GET['search'] ?? ''; ?>">
                </form>
            </div>
            
            <div class="header-actions">
                <?php if (isset($_SESSION['user_account_id']) && (($_SESSION['user_type'] ?? '') === 'customer')): ?>
                    <a href="<?php echo BASE_URL; ?>account" class="header-btn">
                        <i class="fas fa-user"></i>
                        <span><?php echo $_SESSION['full_name'] ?? $_SESSION['email'] ?? 'Account'; ?></span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>orders" class="header-btn">
                        <i class="fas fa-box"></i>
                        <span>Orders</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>refund" class="header-btn">
                        <i class="fas fa-undo"></i>
                        <span>Refunds</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>cart" class="header-btn">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Cart</span>
                        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                            <span class="cart-badge"><?php echo array_sum($_SESSION['cart']); ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>settings" class="header-btn" title="Settings">
                        <i class="fas fa-cog"></i>
                    </a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>auth/login" class="header-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login</span>
                    </a>
                <?php endif; ?>
            </div>
            
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>
    
    <!-- Navigation -->
    <nav class="nav">
        <div class="nav-content">
            <a href="<?php echo BASE_URL; ?>shop" class="nav-link <?php echo (!isset($_GET['url']) || $_GET['url'] === 'shop') ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="<?php echo BASE_URL; ?>shop?category=all" class="nav-link">
                <i class="fas fa-th"></i> All Products
            </a>
            <?php if (isset($_SESSION['user_account_id']) && (($_SESSION['user_type'] ?? '') === 'customer')): ?>
                <a href="<?php echo BASE_URL; ?>orders" class="nav-link">
                    <i class="fas fa-box"></i> My Orders
                </a>
                <a href="<?php echo BASE_URL; ?>refund" class="nav-link">
                    <i class="fas fa-undo"></i> Refunds
                </a>
                <a href="<?php echo BASE_URL; ?>account" class="nav-link">
                    <i class="fas fa-user-circle"></i> My Account
                </a>
                <a href="<?php echo BASE_URL; ?>settings" class="nav-link">
                    <i class="fas fa-cog"></i> Settings
                </a>
            <?php endif; ?>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
