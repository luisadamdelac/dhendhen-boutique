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

                    <h1 class="hero-title">Beauty Products. Simple Fulfillment. Fast Growth.</h1>
                    <p class="hero-subtitle">
                        Shop quality items online—or manage inventory and orders through dedicated portals.
                        One platform for customers, resellers, admin, and staff.
                    </p>

                    <div class="hero-actions">
                        <a class="btn btn-primary btn-lg" href="<?php echo BASE_URL; ?>shop">
                            <i class="fas fa-store"></i> Shop Now
                        </a>
                        <a class="btn btn-outline btn-lg" href="<?php echo BASE_URL; ?>auth/login">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </div>

                    <div class="hero-highlights">
                        <div class="highlight">
                            <i class="fas fa-truck"></i>
                            <div>
                                <div class="highlight-title">Fast Delivery</div>
                                <div class="highlight-sub">Streamlined fulfillment process</div>
                            </div>
                        </div>
                        <div class="highlight">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <div class="highlight-title">Secure Access</div>
                                <div class="highlight-sub">Permission-based portals</div>
                            </div>
                        </div>
                        <div class="highlight">
                            <i class="fas fa-recycle"></i>
                            <div>
                                <div class="highlight-title">Easy Refunds</div>
                                <div class="highlight-sub">Request & track status</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="landing-hero-right">
                    <div class="hero-card">
                        <div class="hero-card-top">
                            <div class="hero-card-icon">
                                <i class="fas fa-gem"></i>
                            </div>
                            <div>
                                <div class="hero-card-name">Start Your Journey</div>
                                <div class="hero-card-email">Choose your role & continue</div>
                            </div>
                        </div>

                        <div class="role-grid">
                            <a class="role-tile" href="<?php echo BASE_URL; ?>auth/login">
                                <div class="role-icon" style="background: rgba(255,105,180,0.12); color:#ff69b4;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="role-title">Customer</div>
                                <div class="role-desc">Shop, pay, and track orders</div>
                            </a>

                            <a class="role-tile" href="<?php echo BASE_URL; ?>auth/login">
                                <div class="role-icon" style="background: rgba(147,112,219,0.12); color:#9370db;">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <div class="role-title">Admin</div>
                                <div class="role-desc">Manage system operations</div>
                            </a>

                            <a class="role-tile" href="<?php echo BASE_URL; ?>auth/login">
                                <div class="role-icon" style="background: rgba(40,167,69,0.12); color:#28a745;">
                                    <i class="fas fa-store-alt"></i>
                                </div>
                                <div class="role-title">Reseller</div>
                                <div class="role-desc">Offer products via your store</div>
                            </a>

                            <a class="role-tile" href="<?php echo BASE_URL; ?>auth/login">
                                <div class="role-icon" style="background: rgba(23,162,184,0.12); color:#17a2b8;">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div class="role-title">Staff</div>
                                <div class="role-desc">Handle inventory & orders</div>
                            </a>
                        </div>

                        <div class="hero-card-footer">
                            <div class="footer-note">
                                <i class="fas fa-info-circle"></i>
                                <span>Use the Shop button to explore products instantly.</span>
                            </div>
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
                <h2><i class="fas fa-bolt"></i> Why DropSell?</h2>
                <p>Designed to look consistent across portals while keeping the user experience clean.</p>
            </div>

            <div class="cards-grid">
                <div class="card">
                    <h3><i class="fas fa-bullseye"></i> One Platform</h3>
                    <div class="card-body">
                        Customer shopping experience, and internal portals for admin/staff/resellers.
                    </div>
                </div>

                <div class="card">
                    <h3><i class="fas fa-bolt"></i> Fast Navigation</h3>
                    <div class="card-body">
                        Clear entry points and quick access to the right section.
                    </div>
                </div>

                <div class="card">
                    <h3><i class="fas fa-lock"></i> Role-Based</h3>
                    <div class="card-body">
                        Each portal shows only relevant actions based on permissions.
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/landing.css">

