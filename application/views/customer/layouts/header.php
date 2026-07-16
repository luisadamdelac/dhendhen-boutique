<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (!defined('SITE_NAME')) { define('SITE_NAME', 'DropSell'); } ?>
    <title><?php echo $page_title ?? 'Shop'; ?> - <?php echo SITE_NAME; ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS — cache-busted so CSS fixes here take effect immediately
         instead of serving a stale cached copy -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css?v=<?php echo @filemtime(FCPATH . 'public/css/style.css') ?: time(); ?>">
    
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
            <div class="top-bar-promo">
                <i class="fas fa-star"></i> New Arrivals Every Week
            </div>
        </div>
    </div>
    
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="<?php echo BASE_URL; ?>shop" class="logo">
                <span class="logo-badge"><i class="fas fa-shopping-bag"></i></span>
                <?php echo SITE_NAME; ?>
            </a>
            
            <div class="search-bar">
                <form action="<?php echo BASE_URL; ?>shop" method="GET">
                    <input type="text" name="search" placeholder="Search products..." value="<?php echo $_GET['search'] ?? ''; ?>">
                    <button type="submit" class="search-bar-submit" aria-label="Search">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            
            <div class="header-actions">
                <?php if (isset($_SESSION['user_account_id']) && (($_SESSION['user_type'] ?? '') === 'customer')): ?>
                    <?php
                        $headerAvatarImage = get_user_profile_image();
                        $headerAvatarSrc = !empty($headerAvatarImage)
                            ? BASE_URL . $headerAvatarImage
                            : BASE_URL . default_avatar_url();
                    ?>
                    <a href="<?php echo BASE_URL; ?>account" class="header-btn">
                        <img src="<?php echo $headerAvatarSrc; ?>" alt="" style="width:22px;height:22px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                        <span><?php echo $_SESSION['full_name'] ?? $_SESSION['email'] ?? 'Account'; ?></span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>cart" class="header-btn">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Cart</span>
                        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                            <span class="cart-badge" id="cartBadgeCount"><?php echo array_sum(array_column($_SESSION['cart'], 'qty')); ?></span>
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
