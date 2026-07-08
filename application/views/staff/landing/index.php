<!-- Staff Portal Landing Page -->
<div class="content">
    <!-- Hero -->
    <section class="landing-hero">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-left">
                    <div class="hero-badge">
                        <i class="fas fa-shield-alt"></i>
                        <span>Staff Portal</span>
                    </div>
                    <h1 class="hero-title">Manage Inventory & Orders</h1>
                    <p class="hero-subtitle">
                        A formal, secure, and modern interface for staff operations.
                        Track inventory, update order status, and maintain your profile.
                    </p>

                    <div class="hero-actions">
                        <a class="btn btn-primary btn-lg" href="<?php echo URLROOT; ?>dashboard">
                            <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                        </a>
                        <a class="btn btn-outline" href="<?php echo URLROOT; ?>profile">
                            <i class="fas fa-user-circle"></i> My Profile
                        </a>
                    </div>

                    <div class="hero-highlights">
                        <div class="highlight">
                            <i class="fas fa-boxes"></i>
                            <div>
                                <div class="highlight-title">Inventory</div>
                                <div class="highlight-sub">View & update stock</div>
                            </div>
                        </div>
                        <div class="highlight">
                            <i class="fas fa-shopping-cart"></i>
                            <div>
                                <div class="highlight-title">Orders</div>
                                <div class="highlight-sub">Update statuses fast</div>
                            </div>
                        </div>
                        <div class="highlight">
                            <i class="fas fa-lock"></i>
                            <div>
                                <div class="highlight-title">Secure Access</div>
                                <div class="highlight-sub">Permission-based actions</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hero-right">
                    <div class="hero-card">
                        <div class="hero-card-top">
                            <div class="avatar-circle">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div>
                                <div class="hero-card-name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Staff'); ?></div>
                                <div class="hero-card-email"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></div>
                            </div>
                        </div>

                        <div class="hero-card-stats">
                            <div class="mini-stat">
                                <div class="mini-stat-icon" style="background: rgba(0,123,255,0.12); color:#007bff;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div class="mini-stat-label">Role</div>
                                    <div class="mini-stat-value">Staff</div>
                                </div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-icon" style="background: rgba(40,167,69,0.12); color:#28a745;">
                                    <i class="fas fa-check-double"></i>
                                </div>
                                <div>
                                    <div class="mini-stat-label">Status</div>
                                    <div class="mini-stat-value">Active</div>
                                </div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-icon" style="background: rgba(255,193,7,0.14); color:#ffc107;">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div>
                                    <div class="mini-stat-label">Today</div>
                                    <div class="mini-stat-value"><?php echo date('M d, Y'); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="hero-card-footer">
                            <div class="footer-note">
                                <i class="fas fa-info-circle"></i>
                                <span>Use the sidebar to manage your tasks efficiently.</span>
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

    <!-- Quick Links -->
    <section class="landing-links">
        <div class="container">
            <div class="section-title">
                <h2><i class="fas fa-bolt"></i> Quick Access</h2>
                <p>Most used actions are one click away.</p>
            </div>

            <div class="links-grid">
                <a class="link-tile" href="<?php echo URLROOT; ?>inventory">
                    <div class="tile-icon"><i class="fas fa-boxes"></i></div>
                    <div class="tile-title">Inventory</div>
                    <div class="tile-desc">Check stock and availability</div>
                </a>

                <a class="link-tile" href="<?php echo URLROOT; ?>order">
                    <div class="tile-icon"><i class="fas fa-shopping-cart"></i></div>
                    <div class="tile-title">Orders</div>
                    <div class="tile-desc">Update order statuses</div>
                </a>

                <a class="link-tile" href="<?php echo URLROOT; ?>profile">
                    <div class="tile-icon"><i class="fas fa-user-circle"></i></div>
                    <div class="tile-title">My Profile</div>
                    <div class="tile-desc">Update personal information</div>
                </a>

                <a class="link-tile" href="<?php echo URLROOT; ?>dashboard">
                    <div class="tile-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="tile-title">Dashboard</div>
                    <div class="tile-desc">View quick stats & summaries</div>
                </a>
            </div>
        </div>
    </section>
</div>

<style>
    /* Local styles for landing (so it works even if staff-style.css is minimal) */
    .landing { padding: 0; }
    .container { width: 100%; max-width: 1100px; margin: 0 auto; padding: 0 18px; }

    .landing-hero { padding: 34px 0 10px; position: relative; }

    .hero-grid {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 22px;
        align-items: start;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-radius: 999px;
        background: rgba(0,123,255,0.10);
        border: 1px solid rgba(0,123,255,0.18);
        color: #007bff;
        font-weight: 600;
        margin-bottom: 14px;
    }

    .hero-title {
        font-size: 2.3rem;
        line-height: 1.1;
        margin: 0 0 12px;
        color: #0f172a;
        letter-spacing: -0.5px;
    }

    .hero-subtitle {
        margin: 0 0 18px;
        color: #475569;
        font-size: 1rem;
        max-width: 52ch;
    }

    .hero-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 18px; }

    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 10px; border-radius: 10px; padding: 12px 16px; font-weight: 700; text-decoration: none; border: 1px solid transparent; }
    .btn-lg { padding: 13px 18px; font-size: 1rem; }
    .btn-primary { background: linear-gradient(135deg, #ff69b4 0%, #ee82ee 45%, #9370db 100%); color: #fff; }
    .btn-outline { background: rgba(255,255,255,0.7); border-color: rgba(148,163,184,0.35); color: #0f172a; }

    .hero-highlights {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
        margin-top: 12px;
    }

    .highlight {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        background: #fff;
        border: 1px solid rgba(148,163,184,0.35);
        border-radius: 14px;
        padding: 12px 14px;
    }

    .highlight i { color: #7c3aed; font-size: 1.1rem; margin-top: 2px; }
    .highlight-title { font-weight: 800; color: #0f172a; }
    .highlight-sub { font-size: 0.9rem; color: #64748b; margin-top: 2px; }

    .hero-right { position: relative; }

    .hero-card {
        background: rgba(255,255,255,0.88);
        border: 1px solid rgba(148,163,184,0.35);
        border-radius: 18px;
        padding: 18px;
        box-shadow: 0 18px 40px rgba(15,23,42,0.08);
        overflow: hidden;
    }

    .hero-card-top { display: flex; gap: 14px; align-items: center; margin-bottom: 14px; }

    .avatar-circle {
        width: 56px; height: 56px; border-radius: 50%;
        background: linear-gradient(135deg, rgba(255,105,180,0.25), rgba(147,112,219,0.25));
        border: 1px solid rgba(147,112,219,0.35);
        display: flex; align-items: center; justify-content: center;
        color: #7c3aed; font-size: 1.5rem;
    }

    .hero-card-name { font-weight: 900; color: #0f172a; }
    .hero-card-email { color: #64748b; font-size: 0.92rem; margin-top: 2px; }

    .hero-card-stats {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
        margin-top: 10px;
    }

    .mini-stat {
        display: flex;
        gap: 12px;
        align-items: center;
        padding: 12px 12px;
        border-radius: 14px;
        background: rgba(248,250,252,0.75);
        border: 1px solid rgba(148,163,184,0.25);
    }

    .mini-stat-icon {
        width: 40px; height: 40px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        border: 1px solid rgba(148,163,184,0.22);
    }

    .mini-stat-label { font-size: 0.82rem; color: #64748b; font-weight: 700; }
    .mini-stat-value { font-weight: 900; color: #0f172a; }

    .hero-card-footer { margin-top: 14px; }
    .footer-note { display: flex; gap: 10px; align-items: center; color: #334155; font-weight: 700; background: rgba(0,123,255,0.06); border: 1px solid rgba(0,123,255,0.12); border-radius: 14px; padding: 10px 12px; }

    .landing-decoration { position: absolute; inset: -40px -40px auto auto; pointer-events: none; }
    .blob { position: absolute; border-radius: 999px; filter: blur(0px); opacity: 0.45; }
    .blob-1 { width: 120px; height: 120px; right: 20px; top: 20px; background: #ff69b4; }
    .blob-2 { width: 90px; height: 90px; right: 120px; top: 120px; background: #9370db; }
    .blob-3 { width: 140px; height: 140px; right: 40px; top: 190px; background: #7c3aed; }

    .landing-links { padding: 18px 0 42px; }

    .section-title h2 { font-size: 1.45rem; color: #0f172a; margin-bottom: 6px; }
    .section-title p { color: #64748b; margin-bottom: 18px; }

    .links-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
    }

    .link-tile {
        background: rgba(255,255,255,0.9);
        border: 1px solid rgba(148,163,184,0.35);
        border-radius: 16px;
        padding: 16px;
        text-decoration: none;
        color: inherit;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        box-shadow: 0 8px 22px rgba(15,23,42,0.06);
    }

    .link-tile:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 34px rgba(15,23,42,0.10);
        border-color: rgba(147,112,219,0.55);
    }

    .tile-icon {
        width: 46px; height: 46px; border-radius: 14px;
        background: rgba(147,112,219,0.12);
        border: 1px solid rgba(147,112,219,0.22);
        display: flex; align-items: center; justify-content: center;
        color: #7c3aed;
        margin-bottom: 12px;
        font-size: 1.2rem;
    }

    .tile-title { font-weight: 950; color: #0f172a; margin-bottom: 6px; }
    .tile-desc { color: #64748b; font-weight: 700; font-size: 0.9rem; }

    @media (max-width: 1024px) {
        .hero-grid { grid-template-columns: 1fr; }
        .links-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 560px) {
        .links-grid { grid-template-columns: 1fr; }
        .hero-title { font-size: 1.85rem; }
    }
</style>

