<?php
/**
 * Public Landing Page
 */
?>

<div class="landing">
    <div class="landing-back">
        <a class="landing-back-link" href="<?php echo BASE_URL; ?>shop">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <section class="landing-hero">

        <div class="landing-container">
            <div class="landing-hero-grid">
                <div class="landing-hero-left">
                    <div class="hero-badge">
                        <i class="fas fa-cubes"></i>
                        <span>DropSell Dropshipping System</span>
                    </div>

                    <h1 class="hero-title">Run Your Dropshipping Business, Simplified</h1>
                    <p class="hero-subtitle">
                        One platform for Admins, Staff, Resellers, and Customers. DropSell brings inventory,
                        orders, commissions, and multi-branch management together in a single, easy-to-use
                        dashboard, so every role on your team knows exactly what to do next.
                    </p>

                    <div class="hero-actions">
                        <a class="btn btn-primary btn-lg" href="<?php echo BASE_URL; ?>auth/register">
                            <i class="fas fa-rocket"></i> Create Free Account
                        </a>
                        <a class="btn btn-outline btn-lg" href="<?php echo BASE_URL; ?>auth/login">
                            <i class="fas fa-sign-in-alt"></i> Log In
                        </a>
                    </div>
                </div>

                <div class="landing-hero-right">
                    <div class="hero-card">
                        <div class="hero-card-top">
                            <div class="hero-card-icon">
                                <i class="fas fa-gem"></i>
                            </div>
                            <div>
                                <div class="hero-card-name">For Everyone</div>
                                <div class="hero-card-email">Pick your role to continue</div>
                            </div>
                        </div>

                        <div class="role-grid">
                            <a class="role-tile" href="<?php echo BASE_URL; ?>auth/login">
                                <div class="role-icon" style="background: rgba(147,112,219,0.12); color:#9370db;">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <div class="role-title">Admin</div>
                                <div class="role-desc">Full control over branches, products, and orders</div>
                            </a>

                            <a class="role-tile" href="<?php echo BASE_URL; ?>auth/login">
                                <div class="role-icon" style="background: rgba(23,162,184,0.12); color:#17a2b8;">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div class="role-title">Staff</div>
                                <div class="role-desc">Manage inventory and process orders</div>
                            </a>

                            <a class="role-tile" href="<?php echo BASE_URL; ?>auth/register_reseller">
                                <div class="role-icon" style="background: rgba(40,167,69,0.12); color:#28a745;">
                                    <i class="fas fa-hand-holding-usd"></i>
                                </div>
                                <div class="role-title">Reseller</div>
                                <div class="role-desc">Sell products and track your commissions</div>
                            </a>

                            <a class="role-tile" href="<?php echo BASE_URL; ?>auth/register">
                                <div class="role-icon" style="background: rgba(236,72,153,0.12); color:#EC4899;">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div class="role-title">Customer</div>
                                <div class="role-desc">Shop products and track your orders</div>
                            </a>
                        </div>
                    </div>

                    <div class="landing-decoration">
                        <div class="blob blob-1"></div>
                        <div class="blob blob-2"></div>
                        <div class="blob blob-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section">
        <div class="landing-container">
            <div class="section-title">
                <h2><i class="fas fa-layer-group"></i> Everything you need, in one place</h2>
                <p>Built to keep every part of your dropshipping operation in sync.</p>
            </div>

            <div class="cards-grid">
                <div class="card">
                    <h3><i class="fas fa-boxes-stacked"></i> Inventory &amp; Branches</h3>
                    <div class="card-body">
                        Track stock across multiple branches in real time and avoid overselling.
                    </div>
                </div>

                <div class="card">
                    <h3><i class="fas fa-cart-shopping"></i> Orders &amp; Refunds</h3>
                    <div class="card-body">
                        From checkout to delivery, manage the full order lifecycle with ease.
                    </div>
                </div>

                <div class="card">
                    <h3><i class="fas fa-chart-line"></i> Reseller Commissions</h3>
                    <div class="card-body">
                        Automatically calculate and track reseller earnings and withdrawals.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section">
        <div class="landing-container">
            <div class="hero-card" style="text-align:center;">
                <h2 style="margin:0 0 8px;">Ready to grow your business?</h2>
                <p style="color:#64748b;font-weight:600;margin:0 0 18px;">Join DropSell today and manage your entire dropshipping operation from one dashboard.</p>
                <div class="hero-actions" style="justify-content:center;">
                    <a href="<?php echo BASE_URL; ?>auth/register" class="btn btn-primary btn-lg">
                        <i class="fas fa-user-plus"></i> Sign Up Free
                    </a>
                    <a href="<?php echo BASE_URL; ?>auth/login" class="btn btn-outline btn-lg">
                        <i class="fas fa-sign-in-alt"></i> Log In
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/landing.css?v=<?php echo @filemtime(FCPATH . 'public/css/landing.css') ?: time(); ?>">

