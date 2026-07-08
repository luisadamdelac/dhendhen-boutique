<?php
/**
 * DropSell Landing Page
 * Modern, elegant, responsive (pink/white theme)
 * Multi-user login (Admin/Staff/Reseller/Customer) via existing auth/login
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>DropSell – Dropshipping Management System</title>
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛍️</text></svg>">

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    :root{
      --pink:#ff69b4;
      --pink-light:#ffb6c1;
      --pink-dark:#db7093;
      --violet:#ee82ee;
      --purple:#9370db;
      --white:#ffffff;
      --text:#2b2b2b;
      --muted:rgba(43,43,43,0.65);
      --glass:rgba(255,255,255,0.18);
      --glass-strong:rgba(255,255,255,0.35);
      --shadow: 0 18px 50px rgba(219,112,147,0.22);
      --shadow-soft: 0 10px 30px rgba(219,112,147,0.18);
      --radius-xl:22px;
      --radius-lg:16px;
      --radius-md:12px;
      --radius-sm:10px;
      --ring: 0 0 0 4px rgba(255,105,180,0.18);
    }

    *{ box-sizing:border-box; margin:0; padding:0; }
    html,body{ height:100%; }

    body{
      font-family:'Poppins',sans-serif;
      color:var(--text);
      background:
        radial-gradient(1200px 600px at 10% 10%, rgba(255,105,180,0.25), transparent 60%),
        radial-gradient(900px 500px at 80% 20%, rgba(238,130,238,0.18), transparent 55%),
        radial-gradient(900px 500px at 30% 90%, rgba(147,112,219,0.15), transparent 55%),
        linear-gradient(135deg, #fff 0%, #fff 35%, #ffe6f2 70%, #f3e5f5 100%);
      overflow-x:hidden;
    }

    a{ color:inherit; text-decoration:none; }
    .container{ width:100%; max-width:1100px; margin:0 auto; padding:0 18px; }

    /* Glass helpers */
    .glass{
      background:var(--glass);
      border:1px solid rgba(255,255,255,0.32);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
    }
    .glass-strong{
      background:var(--glass-strong);
      border:1px solid rgba(255,255,255,0.45);
      backdrop-filter: blur(18px);
      -webkit-backdrop-filter: blur(18px);
    }

    /* Navbar */
    .navbar{
      position:sticky;
      top:0;
      z-index:100;
      padding:14px 0;
      background:rgba(255,255,255,0.55);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border-bottom:1px solid rgba(255,105,180,0.12);
    }
    .nav-inner{ display:flex; align-items:center; justify-content:space-between; gap:16px; }
    .brand{
      display:flex; align-items:center; gap:10px; font-weight:900;
    }
    .brand-badge{
      width:42px; height:42px; border-radius:14px;
      display:flex; align-items:center; justify-content:center;
      background:linear-gradient(135deg, var(--pink) 0%, var(--violet) 50%, var(--purple) 100%);
      box-shadow: var(--shadow-soft);
      color:white;
    }
    .brand-name{ font-size:16px; letter-spacing:0.2px; }

    .nav-links{ display:flex; align-items:center; gap:22px; flex-wrap:wrap; justify-content:center; }
    .nav-links a{
      font-weight:600;
      font-size:14px;
      color:rgba(43,43,43,0.8);
      position:relative;
      padding:6px 0;
    }
    .nav-links a:after{
      content:"";
      position:absolute;
      left:0; bottom:0;
      width:0; height:2px;
      background:linear-gradient(90deg, var(--pink) 0%, var(--violet) 55%, var(--purple) 100%);
      transition:width 0.25s ease;
    }
    .nav-links a:hover:after{ width:100%; }

    .nav-ctas{ display:flex; align-items:center; gap:10px; }

    /* Buttons */
    .btn{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:10px;
      padding:10px 16px;
      border-radius:999px;
      font-weight:800;
      font-size:14px;
      border:1px solid rgba(255,105,180,0.22);
      transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease, border-color 0.18s ease;
      white-space:nowrap;
    }
    .btn:hover{ transform: translateY(-2px); box-shadow: var(--shadow-soft); }

    .btn-primary{
      border:none;
      color:white;
      background:linear-gradient(135deg, var(--pink) 0%, var(--violet) 45%, var(--purple) 100%);
    }
    .btn-ghost{
      background:rgba(255,255,255,0.55);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
    }

    /* Sections */
    .section{ padding:70px 0; }
    .section-head{ text-align:center; max-width:640px; margin:0 auto 40px; }
    .section-eyebrow{
      display:inline-flex; align-items:center; gap:8px;
      padding:6px 14px; border-radius:999px;
      background:rgba(255,105,180,0.12);
      color:var(--pink-dark);
      font-weight:800; font-size:12px; letter-spacing:0.4px;
      text-transform:uppercase;
      margin-bottom:14px;
    }
    .section-title{ font-size:32px; font-weight:900; letter-spacing:0.2px; }
    .section-desc{ margin-top:12px; color:var(--muted); font-size:15px; line-height:1.7; }

    /* Hero */
    .hero{ padding:55px 0 40px; }
    .hero-grid{ display:grid; grid-template-columns: 1.2fr 0.8fr; gap:26px; align-items:center; }

    .hero-card{
      border-radius: var(--radius-xl);
      padding:34px;
      box-shadow: var(--shadow);
      position:relative;
      overflow:hidden;
    }
    .hero-card:before{
      content:"";
      position:absolute;
      inset:-2px;
      background: radial-gradient(600px 220px at 20% 10%, rgba(255,105,180,0.35), transparent 55%),
                  radial-gradient(520px 200px at 90% 10%, rgba(238,130,238,0.25), transparent 50%),
                  radial-gradient(420px 180px at 40% 100%, rgba(147,112,219,0.20), transparent 55%);
      pointer-events:none;
    }
    .hero-card > *{ position:relative; z-index:1; }

    .hero-title{ font-size:54px; line-height:1.05; font-weight:950; letter-spacing:0.6px; }
    .hero-sub{ font-size:18px; font-weight:800; color:rgba(43,43,43,0.75); margin-top:12px; }
    .hero-desc{ margin-top:16px; color:var(--muted); font-size:15px; line-height:1.75; max-width:58ch; }

    .hero-actions{ display:flex; gap:12px; flex-wrap:wrap; margin-top:26px; }

    .hero-badges{ display:grid; gap:12px; }
    .mini-card{
      padding:16px;
      border-radius:var(--radius-lg);
      box-shadow: var(--shadow-soft);
      display:flex;
      align-items:center;
      gap:14px;
      transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }
    .mini-card:hover{
      transform: translateY(-3px);
      box-shadow: var(--shadow);
      border-color: rgba(255,105,180,0.4);
    }
    .mini-icon{
      width:44px; height:44px; border-radius:14px; flex-shrink:0;
      display:flex; align-items:center; justify-content:center;
      background:linear-gradient(135deg, var(--pink) 0%, var(--violet) 50%, var(--purple) 100%);
      color:white; font-size:18px;
    }
    .mini-title{ font-weight:900; font-size:15px; }
    .mini-sub{ font-size:12.5px; color:var(--muted); font-weight:600; margin-top:2px; }

    /* Features */
    .features-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:22px; }
    .feature-card{
      padding:28px 24px;
      border-radius:var(--radius-lg);
      box-shadow: var(--shadow-soft);
      transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .feature-card:hover{ transform: translateY(-4px); box-shadow: var(--shadow); }
    .feature-icon{
      width:52px; height:52px; border-radius:16px;
      display:flex; align-items:center; justify-content:center;
      background:rgba(255,105,180,0.12);
      color:var(--pink-dark);
      font-size:22px;
      margin-bottom:16px;
    }
    .feature-title{ font-size:17px; font-weight:900; margin-bottom:8px; }
    .feature-desc{ font-size:14px; color:var(--muted); line-height:1.65; }

    /* CTA band */
    .cta-band{
      border-radius:var(--radius-xl);
      padding:48px;
      text-align:center;
      background:linear-gradient(135deg, var(--pink) 0%, var(--violet) 50%, var(--purple) 100%);
      color:white;
      box-shadow: var(--shadow);
    }
    .cta-band h2{ font-size:28px; font-weight:900; }
    .cta-band p{ margin-top:10px; opacity:0.92; font-size:15px; }
    .cta-actions{ display:flex; gap:12px; justify-content:center; flex-wrap:wrap; margin-top:24px; }
    .cta-band .btn-ghost{ background:rgba(255,255,255,0.2); border-color:rgba(255,255,255,0.4); color:white; }
    .cta-band .btn-primary{ background:white; color:var(--pink-dark); }

    /* Footer */
    .footer{
      padding:30px 0;
      border-top:1px solid rgba(255,105,180,0.15);
      text-align:center;
      color:var(--muted);
      font-size:13px;
      font-weight:600;
    }
    .footer a{ color:var(--pink-dark); font-weight:800; }

    @media (max-width: 900px){
      .hero-grid{ grid-template-columns:1fr; }
      .features-grid{ grid-template-columns:1fr; }
      .hero-title{ font-size:38px; }
      .nav-links{ display:none; }
    }
  </style>
</head>
<body>

  <header class="navbar">
    <div class="container nav-inner">
      <a href="<?php echo site_url('landing'); ?>" class="brand">
        <span class="brand-badge"><i class="fas fa-store"></i></span>
        <span class="brand-name">DropSell</span>
      </a>

      <nav class="nav-links">
        <a href="#features">Features</a>
        <a href="#roles">For Everyone</a>
        <a href="#contact">Contact</a>
      </nav>

      <div class="nav-ctas">
        <a href="<?php echo site_url('login'); ?>" class="btn btn-ghost">Log In</a>
        <a href="<?php echo site_url('register'); ?>" class="btn btn-primary">Get Started</a>
      </div>
    </div>
  </header>

  <section class="hero">
    <div class="container hero-grid">
      <div class="hero-card glass-strong">
        <h1 class="hero-title">Run Your Dropshipping Business, Simplified</h1>
        <p class="hero-sub">One platform for Admins, Staff, Resellers, and Customers.</p>
        <p class="hero-desc">
          DropSell brings inventory, orders, commissions, and multi-branch management
          together in a single, easy-to-use dashboard — so every role on your team
          knows exactly what to do next.
        </p>
        <div class="hero-actions">
          <a href="<?php echo site_url('register'); ?>" class="btn btn-primary">
            <i class="fas fa-rocket"></i> Create Free Account
          </a>
          <a href="<?php echo site_url('login'); ?>" class="btn btn-ghost">
            <i class="fas fa-sign-in-alt"></i> Log In
          </a>
        </div>
      </div>

      <div class="hero-badges" id="roles">
        <a href="<?php echo site_url('login'); ?>" class="mini-card glass">
          <span class="mini-icon"><i class="fas fa-user-shield"></i></span>
          <span>
            <span class="mini-title">Admin</span>
            <span class="mini-sub">Full control over branches, products, and orders</span>
          </span>
        </a>
        <a href="<?php echo site_url('login'); ?>" class="mini-card glass">
          <span class="mini-icon"><i class="fas fa-user-tie"></i></span>
          <span>
            <span class="mini-title">Staff</span>
            <span class="mini-sub">Manage inventory and process orders</span>
          </span>
        </a>
        <a href="<?php echo site_url('register-reseller'); ?>" class="mini-card glass">
          <span class="mini-icon"><i class="fas fa-hand-holding-usd"></i></span>
          <span>
            <span class="mini-title">Reseller</span>
            <span class="mini-sub">Sell products and track your commissions</span>
          </span>
        </a>
        <a href="<?php echo site_url('register'); ?>" class="mini-card glass">
          <span class="mini-icon"><i class="fas fa-shopping-bag"></i></span>
          <span>
            <span class="mini-title">Customer</span>
            <span class="mini-sub">Shop products and track your orders</span>
          </span>
        </a>
      </div>
    </div>
  </section>

  <section class="section" id="features">
    <div class="container">
      <div class="section-head">
        <span class="section-eyebrow">Why DropSell</span>
        <h2 class="section-title">Everything you need, in one place</h2>
        <p class="section-desc">Built to keep every part of your dropshipping operation in sync.</p>
      </div>

      <div class="features-grid">
        <div class="feature-card glass">
          <div class="feature-icon"><i class="fas fa-boxes-stacked"></i></div>
          <div class="feature-title">Inventory &amp; Branches</div>
          <div class="feature-desc">Track stock across multiple branches in real time and avoid overselling.</div>
        </div>
        <div class="feature-card glass">
          <div class="feature-icon"><i class="fas fa-cart-shopping"></i></div>
          <div class="feature-title">Orders &amp; Refunds</div>
          <div class="feature-desc">From checkout to delivery, manage the full order lifecycle with ease.</div>
        </div>
        <div class="feature-card glass">
          <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
          <div class="feature-title">Reseller Commissions</div>
          <div class="feature-desc">Automatically calculate and track reseller earnings and withdrawals.</div>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="cta-band">
        <h2>Ready to grow your business?</h2>
        <p>Join DropSell today and manage your entire dropshipping operation from one dashboard.</p>
        <div class="cta-actions">
          <a href="<?php echo site_url('register'); ?>" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Sign Up Free
          </a>
          <a href="<?php echo site_url('login'); ?>" class="btn btn-ghost">
            <i class="fas fa-sign-in-alt"></i> Log In
          </a>
        </div>
      </div>
    </div>
  </section>

  <footer class="footer" id="contact">
    <div class="container">
      &copy; <?php echo date('Y'); ?> <a href="<?php echo site_url('landing'); ?>">DropSell</a>. All rights reserved.
    </div>
  </footer>

</body>
</html>
