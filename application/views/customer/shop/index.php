<style>
    /* .products-grid/.product-card/.product-image/.product-info/.product-name/
       .product-price/.empty-state/.filter-section/.category-filter/.category-btn/
       .pagination/.page-btn/.product-stock/.trust-bar now come from the shared
       public/css/style.css component library instead of being redeclared per page. */

    /* ── Add to Cart modal (structural .modal/.modal-content/etc come from
       the shared component in customer/layouts/footer.php) ──────────── */
    .atc-modal-header {
        display: flex;
        gap: 14px;
    }
    .atc-modal-header img {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 8px;
        flex-shrink: 0;
        background: #f0f0f0;
    }
    .atc-modal-name { font-weight: 600; font-size: 15px; margin-bottom: 4px; color: var(--dark); }
    .atc-modal-price { color: var(--primary-pink); font-weight: 700; font-size: 18px; }
    .atc-variation-label { font-size: 13px; font-weight: 600; color: #666; margin-bottom: 8px; }
    .atc-variation-group { margin-bottom: 12px; }
    .atc-variation-opt {
        display: inline-block;
        padding: 7px 14px;
        margin: 0 6px 6px 0;
        border: 2px solid #ddd;
        border-radius: 8px;
        background: white;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.15s;
    }
    .atc-variation-opt:hover { border-color: var(--primary-pink); }
    .atc-variation-opt.selected { border-color: var(--primary-pink); background: #fff5f8; font-weight: 600; }
    .atc-variation-opt.disabled { opacity: 0.4; cursor: not-allowed; text-decoration: line-through; }
    .atc-variation-error { display: none; color: #e53935; font-size: 12px; margin-top: 4px; }
    .atc-qty-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 18px 0 22px;
    }
    .atc-qty-controls { display: flex; align-items: center; gap: 12px; }
    .atc-qty-btn {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 2px solid #ddd;
        background: white;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .atc-qty-btn:hover { border-color: var(--primary-pink); color: var(--primary-pink); }
    .atc-qty-value { min-width: 24px; text-align: center; font-weight: 600; }
    .atc-modal-actions { display: flex; gap: 10px; }
    .atc-modal-actions .btn { flex: 1; }
</style>

<?php if (empty($isResellerShop) && empty($searchQuery) && empty($currentCategory) && (int) $currentPage <= 1): ?>
<section class="shop-hero" id="shopHeroSlider">
    <div class="shop-hero-content-frame">
        <div class="shop-hero-content active" data-slide-content="0">
            <span class="shop-hero-eyebrow"><i class="fas fa-heart"></i> Welcome to DropSell</span>
            <h1 class="shop-hero-title">Discover Your<br><span class="accent">Beauty Essentials</span></h1>
            <p class="shop-hero-subtitle">Browse thousands of skincare and cosmetic products.</p>
            <a href="#shop-products" class="btn btn-primary btn-lg shop-hero-cta">
                Browse Products
            </a>
        </div>
        <div class="shop-hero-content" data-slide-content="1">
            <span class="shop-hero-eyebrow"><i class="fas fa-couch"></i> New Arrivals</span>
            <h1 class="shop-hero-title">Elevate Your<br><span class="accent">Home Living</span></h1>
            <p class="shop-hero-subtitle">Stylish furniture and home decor pieces for every room.</p>
            <a href="#shop-products" class="btn btn-primary btn-lg shop-hero-cta">
                Shop Furniture
            </a>
        </div>
        <div class="shop-hero-content" data-slide-content="2">
            <span class="shop-hero-eyebrow"><i class="fas fa-cookie-bite"></i> Sweet Treats</span>
            <h1 class="shop-hero-title">Indulge in<br><span class="accent">Delicious Chocolates</span></h1>
            <p class="shop-hero-subtitle">Premium chocolates and gourmet treats delivered fresh.</p>
            <a href="#shop-products" class="btn btn-primary btn-lg shop-hero-cta">
                Shop Treats
            </a>
        </div>
    </div>
    <div class="shop-hero-decor" aria-hidden="true">
        <span class="decor-circle c1"></span>
        <span class="decor-circle c2"></span>
        <span class="decor-circle c3"></span>
        <span class="decor-circle c4"></span>
        <span class="decor-circle c5"></span>
        <i class="fas fa-star decor-star s1"></i>
        <i class="fas fa-star decor-star s2"></i>
        <i class="fas fa-star decor-star s3"></i>
        <i class="fas fa-star decor-star s4"></i>
        <i class="fas fa-star decor-star s5"></i>
    </div>
    <div class="shop-hero-art" aria-hidden="true">
        <!-- Slide 1: Beauty products -->
        <div class="shop-hero-art-slide active" data-slide-art="0">
        <svg viewBox="0 0 460 300" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <filter id="softBlur" x="-50%" y="-50%" width="200%" height="200%">
                    <feGaussianBlur stdDeviation="7"/>
                </filter>
                <filter id="dropShadow" x="-60%" y="-60%" width="220%" height="220%">
                    <feDropShadow dx="0" dy="7" stdDeviation="5" flood-color="#9D174D" flood-opacity="0.22"/>
                </filter>
                <radialGradient id="heroGlow" cx="50%" cy="52%" r="55%">
                    <stop offset="0%" stop-color="#EC4899" stop-opacity="0.18"/>
                    <stop offset="100%" stop-color="#EC4899" stop-opacity="0"/>
                </radialGradient>
                <linearGradient id="ringGrad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#FFFFFF"/>
                    <stop offset="100%" stop-color="#FBCFE8"/>
                </linearGradient>
                <linearGradient id="podiumLight" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#FFFFFF"/>
                    <stop offset="100%" stop-color="#FCE7F3"/>
                </linearGradient>
                <linearGradient id="podiumDark" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#F9A8D4"/>
                    <stop offset="100%" stop-color="#EC4899"/>
                </linearGradient>
                <linearGradient id="glassPink" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%" stop-color="#FDF2F8"/>
                    <stop offset="55%" stop-color="#FCE7F3"/>
                    <stop offset="100%" stop-color="#F9A8D4"/>
                </linearGradient>
                <linearGradient id="serumLiquid" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#F9A8D4"/>
                    <stop offset="100%" stop-color="#EC4899"/>
                </linearGradient>
                <linearGradient id="goldCap" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%" stop-color="#C98A3E"/>
                    <stop offset="45%" stop-color="#FDE9C7"/>
                    <stop offset="100%" stop-color="#C98A3E"/>
                </linearGradient>
                <linearGradient id="tubePink" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%" stop-color="#F9CEE3"/>
                    <stop offset="50%" stop-color="#FDF2F8"/>
                    <stop offset="100%" stop-color="#F9CEE3"/>
                </linearGradient>
                <linearGradient id="jarPink" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%" stop-color="#F472B6"/>
                    <stop offset="50%" stop-color="#FDF2F8"/>
                    <stop offset="100%" stop-color="#F472B6"/>
                </linearGradient>
                <symbol id="leafShape" viewBox="-12 -22 24 44">
                    <path d="M0,-20 C11,-14 11,14 0,20 C-11,14 -11,-14 0,-20 Z" fill="currentColor"/>
                    <path d="M0,-16 L0,16" stroke="rgba(157,23,77,0.30)" stroke-width="1"/>
                </symbol>
            </defs>

            <ellipse cx="235" cy="165" rx="200" ry="125" fill="url(#heroGlow)"/>
            <ellipse cx="235" cy="286" rx="130" ry="12" fill="#9D174D" opacity="0.15" filter="url(#softBlur)"/>

            <!-- leaf branches -->
            <g>
                <path d="M85,272 C68,210 102,168 76,108" stroke="#F472B6" stroke-width="2" fill="none" opacity="0.4"/>
                <use href="#leafShape" width="24" height="44" x="-12" y="-22" transform="translate(78,246) rotate(-38)" color="#F9A8D4" opacity="0.9"/>
                <use href="#leafShape" width="22" height="40" x="-11" y="-20" transform="translate(70,208) rotate(-8)" color="#F472B6" opacity="0.85"/>
                <use href="#leafShape" width="22" height="40" x="-11" y="-20" transform="translate(80,172) rotate(24)" color="#F9A8D4" opacity="0.8"/>
                <use href="#leafShape" width="18" height="32" x="-9" y="-16" transform="translate(72,132) rotate(-18)" color="#F472B6" opacity="0.7"/>

                <path d="M390,272 C407,210 373,168 399,108" stroke="#F472B6" stroke-width="2" fill="none" opacity="0.4"/>
                <use href="#leafShape" width="24" height="44" x="-12" y="-22" transform="translate(397,246) rotate(38)" color="#F9A8D4" opacity="0.9"/>
                <use href="#leafShape" width="22" height="40" x="-11" y="-20" transform="translate(405,208) rotate(8)" color="#F472B6" opacity="0.85"/>
                <use href="#leafShape" width="22" height="40" x="-11" y="-20" transform="translate(395,172) rotate(-24)" color="#F9A8D4" opacity="0.8"/>
                <use href="#leafShape" width="18" height="32" x="-9" y="-16" transform="translate(403,132) rotate(18)" color="#F472B6" opacity="0.7"/>
            </g>

            <!-- glow ring -->
            <circle cx="235" cy="150" r="118" fill="none" stroke="#F472B6" stroke-width="18" opacity="0.28" filter="url(#softBlur)"/>
            <circle cx="235" cy="150" r="110" fill="none" stroke="url(#ringGrad)" stroke-width="4" opacity="0.95"/>

            <!-- podium -->
            <ellipse cx="235" cy="270" rx="150" ry="22" fill="url(#podiumDark)"/>
            <rect x="85" y="252" width="300" height="18" fill="url(#podiumDark)"/>
            <ellipse cx="235" cy="252" rx="150" ry="22" fill="url(#podiumLight)"/>

            <!-- serum dropper bottle -->
            <g filter="url(#dropShadow)">
                <rect x="118" y="150" width="48" height="88" rx="10" fill="url(#glassPink)"/>
                <rect x="123" y="176" width="38" height="58" rx="6" fill="url(#serumLiquid)" opacity="0.85"/>
                <rect x="126" y="156" width="7" height="72" rx="3.5" fill="#ffffff" opacity="0.45"/>
                <rect x="133" y="130" width="18" height="22" fill="url(#glassPink)"/>
                <rect x="127" y="106" width="30" height="26" rx="6" fill="url(#goldCap)"/>
                <path d="M136,92 Q142,84 148,92 L148,108 Q142,112 136,108 Z" fill="#F8FAFC"/>
                <text x="142" y="200" text-anchor="middle" font-family="'Poppins', Arial, sans-serif" font-size="7" font-weight="700" fill="#9D174D" letter-spacing="0.5">GLOW</text>
                <text x="142" y="211" text-anchor="middle" font-family="'Poppins', Arial, sans-serif" font-size="6" fill="#9D174D" letter-spacing="0.5">SERUM</text>
            </g>

            <!-- cleanser tube -->
            <g filter="url(#dropShadow)">
                <rect x="184" y="112" width="64" height="126" rx="14" fill="url(#tubePink)"/>
                <path d="M199,112 L233,112 L229,92 Q216,84 203,92 Z" fill="#F5B8D6"/>
                <rect x="205" y="93" width="22" height="4" rx="2" fill="#EC4899" opacity="0.4"/>
                <text x="216" y="145" text-anchor="middle" font-family="'Poppins', Arial, sans-serif" font-size="7" font-weight="700" fill="#831843" letter-spacing="0.5">LUMINOUS</text>
                <text x="216" y="157" text-anchor="middle" font-family="'Poppins', Arial, sans-serif" font-size="5.5" fill="#831843" letter-spacing="0.3">FACIAL</text>
                <text x="216" y="167" text-anchor="middle" font-family="'Poppins', Arial, sans-serif" font-size="5.5" fill="#831843" letter-spacing="0.3">CLEANSER</text>
            </g>

            <!-- sunscreen jar (front, overlapping) -->
            <g filter="url(#dropShadow)">
                <rect x="184" y="200" width="84" height="64" rx="18" fill="url(#jarPink)"/>
                <rect x="187" y="176" width="78" height="30" rx="13" fill="url(#goldCap)"/>
                <ellipse cx="226" cy="176" rx="35" ry="6" fill="#9D5A26" opacity="0.35"/>
                <rect x="187" y="188" width="78" height="4" fill="#9D174D" opacity="0.15"/>
                <rect x="187" y="192" width="6" height="66" rx="3" fill="#9D174D" opacity="0.18"/>
                <rect x="259" y="192" width="6" height="66" rx="3" fill="#ffffff" opacity="0.35"/>
                <text x="226" y="230" text-anchor="middle" font-family="'Poppins', Arial, sans-serif" font-size="6.5" font-weight="700" fill="#9D174D" letter-spacing="0.4">SUNSCREEN</text>
                <text x="226" y="243" text-anchor="middle" font-family="'Poppins', Arial, sans-serif" font-size="5.5" fill="#9D174D" letter-spacing="0.4">SPF 50</text>
            </g>

            <!-- lipstick -->
            <g transform="rotate(9 305 220)" filter="url(#dropShadow)">
                <rect x="291" y="190" width="28" height="58" rx="6" fill="#1F2937"/>
                <rect x="295" y="194" width="5" height="48" rx="2.5" fill="#ffffff" opacity="0.12"/>
                <rect x="291" y="185" width="28" height="10" rx="3" fill="url(#goldCap)"/>
                <path d="M291,185 L319,185 L313,160 Q305,148 297,160 Z" fill="#DB2777"/>
                <path d="M297,160 Q305,150 313,160 L311,166 Q305,158 299,166 Z" fill="#F472B6" opacity="0.6"/>
            </g>

            <!-- sparkles -->
            <g fill="#F472B6" opacity="0.85">
                <path d="M110 70 l3 9 9 3 -9 3 -3 9 -3 -9 -9 -3 9 -3 z"/>
                <path d="M370 100 l2.5 7 7 2.5 -7 2.5 -2.5 7 -2.5 -7 -7 -2.5 7 -2.5 z"/>
            </g>
            <g fill="#ffffff" opacity="0.9">
                <circle cx="235" cy="45" r="3"/>
                <circle cx="360" cy="140" r="2.5"/>
                <circle cx="100" cy="130" r="2.5"/>
            </g>
        </svg>
        </div>

        <!-- Slide 2: Furniture -->
        <div class="shop-hero-art-slide" data-slide-art="1">
        <svg viewBox="0 0 460 300" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <filter id="softBlurF" x="-50%" y="-50%" width="200%" height="200%">
                    <feGaussianBlur stdDeviation="7"/>
                </filter>
                <filter id="dropShadowF" x="-60%" y="-60%" width="220%" height="220%">
                    <feDropShadow dx="0" dy="7" stdDeviation="5" flood-color="#9D174D" flood-opacity="0.22"/>
                </filter>
                <radialGradient id="heroGlowF" cx="50%" cy="52%" r="55%">
                    <stop offset="0%" stop-color="#EC4899" stop-opacity="0.18"/>
                    <stop offset="100%" stop-color="#EC4899" stop-opacity="0"/>
                </radialGradient>
                <linearGradient id="ringGradF" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#FFFFFF"/>
                    <stop offset="100%" stop-color="#FBCFE8"/>
                </linearGradient>
                <linearGradient id="podiumLightF" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#FFFFFF"/>
                    <stop offset="100%" stop-color="#FCE7F3"/>
                </linearGradient>
                <linearGradient id="podiumDarkF" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#F9A8D4"/>
                    <stop offset="100%" stop-color="#EC4899"/>
                </linearGradient>
                <linearGradient id="chairFabric" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#FBD5EA"/>
                    <stop offset="100%" stop-color="#F472B6"/>
                </linearGradient>
                <linearGradient id="woodLeg" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%" stop-color="#C98A3E"/>
                    <stop offset="100%" stop-color="#9D5A26"/>
                </linearGradient>
                <linearGradient id="lampShade" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#FDE9C7"/>
                    <stop offset="100%" stop-color="#F5C97B"/>
                </linearGradient>
                <linearGradient id="tableTop" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%" stop-color="#DBEAFE"/>
                    <stop offset="100%" stop-color="#93C5FD"/>
                </linearGradient>
                <linearGradient id="potGrad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#F9A8D4"/>
                    <stop offset="100%" stop-color="#EC4899"/>
                </linearGradient>
                <symbol id="leafShapeF" viewBox="-12 -22 24 44">
                    <path d="M0,-20 C11,-14 11,14 0,20 C-11,14 -11,-14 0,-20 Z" fill="currentColor"/>
                    <path d="M0,-16 L0,16" stroke="rgba(157,23,77,0.30)" stroke-width="1"/>
                </symbol>
            </defs>

            <ellipse cx="235" cy="165" rx="200" ry="125" fill="url(#heroGlowF)"/>
            <ellipse cx="235" cy="286" rx="130" ry="12" fill="#9D174D" opacity="0.15" filter="url(#softBlurF)"/>

            <!-- glow ring -->
            <circle cx="235" cy="150" r="118" fill="none" stroke="#F472B6" stroke-width="18" opacity="0.28" filter="url(#softBlurF)"/>
            <circle cx="235" cy="150" r="110" fill="none" stroke="url(#ringGradF)" stroke-width="4" opacity="0.95"/>

            <!-- podium -->
            <ellipse cx="235" cy="270" rx="150" ry="22" fill="url(#podiumDarkF)"/>
            <rect x="85" y="252" width="300" height="18" fill="url(#podiumDarkF)"/>
            <ellipse cx="235" cy="252" rx="150" ry="22" fill="url(#podiumLightF)"/>

            <!-- armchair -->
            <g filter="url(#dropShadowF)">
                <rect x="108" y="238" width="6" height="20" fill="url(#woodLeg)"/>
                <rect x="176" y="238" width="6" height="20" fill="url(#woodLeg)"/>
                <rect x="92" y="176" width="22" height="58" rx="11" fill="url(#chairFabric)"/>
                <rect x="176" y="176" width="22" height="58" rx="11" fill="url(#chairFabric)"/>
                <rect x="106" y="146" width="76" height="72" rx="20" fill="url(#chairFabric)"/>
                <rect x="98" y="196" width="92" height="46" rx="16" fill="url(#chairFabric)"/>
                <rect x="118" y="201" width="60" height="7" rx="3.5" fill="#ffffff" opacity="0.35"/>
                <path d="M144,150 L144,214" stroke="rgba(157,23,77,0.18)" stroke-width="1.5"/>
            </g>

            <!-- side table + lamp -->
            <g filter="url(#dropShadowF)">
                <rect x="257" y="238" width="26" height="6" rx="3" fill="url(#woodLeg)" opacity="0.6"/>
                <rect x="266" y="198" width="8" height="40" fill="url(#woodLeg)"/>
                <ellipse cx="270" cy="196" rx="42" ry="12" fill="url(#tableTop)"/>
                <ellipse cx="270" cy="192" rx="42" ry="12" fill="#ffffff" opacity="0.3"/>
                <rect x="267" y="152" width="4" height="40" fill="url(#woodLeg)"/>
                <ellipse cx="269" cy="156" rx="10" ry="3" fill="#FFF6E0" opacity="0.6"/>
                <path d="M247,156 L293,156 L282,116 Q269,108 256,116 Z" fill="url(#lampShade)"/>
                <ellipse cx="269" cy="116" rx="13" ry="4" fill="#FDE9C7" opacity="0.85"/>
            </g>

            <!-- potted plant -->
            <g filter="url(#dropShadowF)">
                <path d="M338,210 L378,210 L372,240 Q358,246 344,240 Z" fill="url(#potGrad)"/>
                <ellipse cx="358" cy="210" rx="20" ry="5" fill="#F9CEE3"/>
                <use href="#leafShapeF" width="20" height="36" x="-10" y="-18" transform="translate(358,196) rotate(-28)" color="#F9A8D4" opacity="0.9"/>
                <use href="#leafShapeF" width="20" height="36" x="-10" y="-18" transform="translate(358,192) rotate(0)" color="#F472B6" opacity="0.9"/>
                <use href="#leafShapeF" width="20" height="36" x="-10" y="-18" transform="translate(358,196) rotate(28)" color="#F9A8D4" opacity="0.9"/>
            </g>

            <!-- sparkles -->
            <g fill="#F472B6" opacity="0.85">
                <path d="M110 70 l3 9 9 3 -9 3 -3 9 -3 -9 -9 -3 9 -3 z"/>
                <path d="M370 100 l2.5 7 7 2.5 -7 2.5 -2.5 7 -2.5 -7 -7 -2.5 7 -2.5 z"/>
            </g>
            <g fill="#ffffff" opacity="0.9">
                <circle cx="235" cy="45" r="3"/>
                <circle cx="360" cy="140" r="2.5"/>
                <circle cx="100" cy="130" r="2.5"/>
            </g>
        </svg>
        </div>

        <!-- Slide 3: Chocolates -->
        <div class="shop-hero-art-slide" data-slide-art="2">
        <svg viewBox="0 0 460 300" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <filter id="softBlurC" x="-50%" y="-50%" width="200%" height="200%">
                    <feGaussianBlur stdDeviation="7"/>
                </filter>
                <filter id="dropShadowC" x="-60%" y="-60%" width="220%" height="220%">
                    <feDropShadow dx="0" dy="7" stdDeviation="5" flood-color="#9D174D" flood-opacity="0.22"/>
                </filter>
                <radialGradient id="heroGlowC" cx="50%" cy="52%" r="55%">
                    <stop offset="0%" stop-color="#EC4899" stop-opacity="0.18"/>
                    <stop offset="100%" stop-color="#EC4899" stop-opacity="0"/>
                </radialGradient>
                <linearGradient id="ringGradC" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#FFFFFF"/>
                    <stop offset="100%" stop-color="#FBCFE8"/>
                </linearGradient>
                <linearGradient id="podiumLightC" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#FFFFFF"/>
                    <stop offset="100%" stop-color="#FCE7F3"/>
                </linearGradient>
                <linearGradient id="podiumDarkC" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#F9A8D4"/>
                    <stop offset="100%" stop-color="#EC4899"/>
                </linearGradient>
                <linearGradient id="chocoDark" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#8B5A38"/>
                    <stop offset="100%" stop-color="#4A2C17"/>
                </linearGradient>
                <linearGradient id="boxPink" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%" stop-color="#F472B6"/>
                    <stop offset="50%" stop-color="#FDF2F8"/>
                    <stop offset="100%" stop-color="#F472B6"/>
                </linearGradient>
                <linearGradient id="truffleGold" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%" stop-color="#C98A3E"/>
                    <stop offset="45%" stop-color="#FDE9C7"/>
                    <stop offset="100%" stop-color="#C98A3E"/>
                </linearGradient>
            </defs>

            <ellipse cx="235" cy="165" rx="200" ry="125" fill="url(#heroGlowC)"/>
            <ellipse cx="235" cy="286" rx="130" ry="12" fill="#9D174D" opacity="0.15" filter="url(#softBlurC)"/>

            <!-- glow ring -->
            <circle cx="235" cy="150" r="118" fill="none" stroke="#F472B6" stroke-width="18" opacity="0.28" filter="url(#softBlurC)"/>
            <circle cx="235" cy="150" r="110" fill="none" stroke="url(#ringGradC)" stroke-width="4" opacity="0.95"/>

            <!-- podium -->
            <ellipse cx="235" cy="270" rx="150" ry="22" fill="url(#podiumDarkC)"/>
            <rect x="85" y="252" width="300" height="18" fill="url(#podiumDarkC)"/>
            <ellipse cx="235" cy="252" rx="150" ry="22" fill="url(#podiumLightC)"/>

            <!-- chocolate bar -->
            <g filter="url(#dropShadowC)">
                <rect x="104" y="152" width="80" height="86" rx="8" fill="url(#chocoDark)"/>
                <path d="M144,152 L104,152 L104,178 Q124,168 144,178 Z" fill="#F9A8D4"/>
                <line x1="144" y1="152" x2="144" y2="238" stroke="#3A2010" stroke-width="2"/>
                <line x1="104" y1="195" x2="184" y2="195" stroke="#3A2010" stroke-width="2"/>
                <rect x="110" y="160" width="28" height="30" rx="3" fill="#ffffff" opacity="0.08"/>
                <rect x="150" y="160" width="28" height="30" rx="3" fill="#ffffff" opacity="0.08"/>
                <rect x="110" y="201" width="28" height="30" rx="3" fill="#ffffff" opacity="0.08"/>
                <rect x="150" y="201" width="28" height="30" rx="3" fill="#ffffff" opacity="0.08"/>
            </g>

            <!-- box of chocolates -->
            <g filter="url(#dropShadowC)">
                <rect x="204" y="150" width="88" height="16" rx="4" fill="url(#boxPink)" transform="rotate(-6 248 158)" opacity="0.9"/>
                <rect x="200" y="180" width="96" height="58" rx="12" fill="url(#boxPink)"/>
                <rect x="208" y="188" width="80" height="42" rx="8" fill="#4A2C17" opacity="0.12"/>
                <circle cx="228" cy="206" r="13" fill="url(#truffleGold)"/>
                <circle cx="258" cy="206" r="13" fill="url(#chocoDark)"/>
                <circle cx="228" cy="206" r="13" fill="none" stroke="#ffffff" stroke-width="1" opacity="0.3"/>
                <circle cx="258" cy="206" r="13" fill="none" stroke="#ffffff" stroke-width="1" opacity="0.2"/>
                <path d="M240,178 q8,-14 16,0" stroke="#DB2777" stroke-width="5" fill="none" stroke-linecap="round"/>
                <circle cx="248" cy="176" r="6" fill="#DB2777"/>
            </g>

            <!-- wrapped truffle -->
            <g transform="rotate(-8 340 216)" filter="url(#dropShadowC)">
                <circle cx="340" cy="216" r="20" fill="url(#truffleGold)"/>
                <path d="M320,216 l-12,-8 4,8 -4,8 z" fill="#F9CEE3"/>
                <path d="M360,216 l12,-8 -4,8 4,8 z" fill="#F9CEE3"/>
                <circle cx="340" cy="216" r="20" fill="none" stroke="#ffffff" stroke-width="1.5" opacity="0.35"/>
            </g>

            <!-- sparkles -->
            <g fill="#F472B6" opacity="0.85">
                <path d="M110 70 l3 9 9 3 -9 3 -3 9 -3 -9 -9 -3 9 -3 z"/>
                <path d="M370 100 l2.5 7 7 2.5 -7 2.5 -2.5 7 -2.5 -7 -7 -2.5 7 -2.5 z"/>
            </g>
            <g fill="#ffffff" opacity="0.9">
                <circle cx="235" cy="45" r="3"/>
                <circle cx="360" cy="140" r="2.5"/>
                <circle cx="100" cy="130" r="2.5"/>
            </g>
        </svg>
        </div>

        <div class="hero-dots" id="heroDots">
            <span class="hero-dot active" onclick="heroGoToSlide(0)"></span>
            <span class="hero-dot" onclick="heroGoToSlide(1)"></span>
            <span class="hero-dot" onclick="heroGoToSlide(2)"></span>
        </div>
    </div>
</section>

<script>
(function() {
    var heroTotal = 3;
    var heroCurrent = 0;
    var heroTimer = null;

    window.heroGoToSlide = function(index) {
        heroCurrent = index;
        document.querySelectorAll('#shopHeroSlider [data-slide-content]').forEach(function(el) {
            el.classList.toggle('active', el.dataset.slideContent == index);
        });
        document.querySelectorAll('#shopHeroSlider [data-slide-art]').forEach(function(el) {
            el.classList.toggle('active', el.dataset.slideArt == index);
        });
        document.querySelectorAll('#heroDots .hero-dot').forEach(function(dot, i) {
            dot.classList.toggle('active', i === index);
        });
    };

    function heroNext() {
        heroGoToSlide((heroCurrent + 1) % heroTotal);
    }

    function heroStartAutoplay() {
        heroTimer = setInterval(heroNext, 6000);
    }

    var heroSection = document.getElementById('shopHeroSlider');
    if (heroSection) {
        heroStartAutoplay();
        heroSection.addEventListener('mouseenter', function() { clearInterval(heroTimer); });
        heroSection.addEventListener('mouseleave', heroStartAutoplay);
    }
})();
</script>
<?php endif; ?>

<?php if (!empty($isResellerShop) && !empty($reseller)): ?>
    <div style="background: var(--gradient-primary); color: white; padding: 20px 25px; border-radius: 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 15px;">
        <i class="fas fa-store" style="font-size: 32px;"></i>
        <div>
            <h2 style="margin: 0; font-size: 20px;">
                <?php echo htmlspecialchars(!empty($reseller['business_name']) ? $reseller['business_name'] : trim($reseller['first_name'] . ' ' . $reseller['last_name'])); ?>
            </h2>
            <p style="margin: 0; opacity: 0.9; font-size: 13px;">Shopping from this reseller's mini-shop &mdash; your purchase supports them directly.</p>
        </div>
    </div>
<?php endif; ?>

<div class="filter-section" id="shop-products">
    <h3>
        <i class="fas fa-filter"></i> Filter by Category
    </h3>
    <div class="category-filter">
        <a href="<?php echo BASE_URL; ?>shop" class="category-btn <?php echo !isset($_GET['category']) ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i> All Products
        </a>
        <?php
            $activeParent = NULL;
            foreach ($categories as $category):
                // Cosmetic-only icon guess from the category name — purely
                // decorative, falls back to a generic tag for anything else.
                $catNameLower = strtolower($category['category_name']);
                if (strpos($catNameLower, 'lip') !== false) {
                    $catIcon = 'fa-kiss-wink-heart';
                } elseif (strpos($catNameLower, 'nail') !== false) {
                    $catIcon = 'fa-hand-sparkles';
                } elseif (strpos($catNameLower, 'skin') !== false) {
                    $catIcon = 'fa-pump-soap';
                } elseif (strpos($catNameLower, 'makeup') !== false || strpos($catNameLower, 'make up') !== false) {
                    $catIcon = 'fa-paintbrush';
                } elseif (strpos($catNameLower, 'perfume') !== false || strpos($catNameLower, 'fragrance') !== false) {
                    $catIcon = 'fa-spray-can-sparkles';
                } elseif (strpos($catNameLower, 'massage') !== false) {
                    $catIcon = 'fa-spa';
                } elseif (strpos($catNameLower, 'furniture') !== false) {
                    $catIcon = 'fa-couch';
                } elseif (strpos($catNameLower, 'beauty') !== false || strpos($catNameLower, 'personal care') !== false) {
                    $catIcon = 'fa-heart';
                } else {
                    $catIcon = 'fa-tag';
                }
                $isActiveParent = isset($_GET['category']) && $_GET['category'] == $category['category_id'];
                if ($isActiveParent) {
                    $activeParent = $category;
                }
        ?>
            <a href="<?php echo BASE_URL; ?>shop?category=<?php echo $category['category_id']; ?>"
               class="category-btn <?php echo $isActiveParent ? 'active' : ''; ?>">
                <i class="fas <?php echo $catIcon; ?>"></i> <?php echo htmlspecialchars($category['category_name']); ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php if ($activeParent && !empty($activeParent['subcategories'])): ?>
        <div class="subcategory-filter">
            <span class="subcategory-filter-label"><i class="fas fa-angle-right"></i> Subcategory:</span>
            <a href="<?php echo BASE_URL; ?>shop?category=<?php echo $activeParent['category_id']; ?>"
               class="subcategory-btn <?php echo !isset($_GET['subcategory']) ? 'active' : ''; ?>">
                All <?php echo htmlspecialchars($activeParent['category_name']); ?>
            </a>
            <?php foreach ($activeParent['subcategories'] as $subcategory): ?>
                <a href="<?php echo BASE_URL; ?>shop?category=<?php echo $activeParent['category_id']; ?>&subcategory=<?php echo $subcategory['category_id']; ?>"
                   class="subcategory-btn <?php echo (isset($_GET['subcategory']) && $_GET['subcategory'] == $subcategory['category_id']) ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($subcategory['category_name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php if ($searchQuery): ?>
<div style="background: #e3f2fd; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px;">
    <i class="fas fa-search"></i> Search results for: <strong><?php echo htmlspecialchars($searchQuery); ?></strong>
    <a href="<?php echo BASE_URL; ?>shop" style="margin-left: 15px; color: var(--primary);">Clear search</a>
</div>
<?php endif; ?>

<?php if (!is_logged_in()): ?>
<div class="shop-header">
    <div class="shop-back">
        <a class="landing-back-link" href="<?php echo BASE_URL; ?>landing">
            <i class="fas fa-arrow-left"></i> Back to Landing
        </a>
    </div>
</div>
<?php endif; ?>

<div class="shop-title-row">
    <div>
        <h2 class="shop-title">
            <i class="fas fa-star"></i>
            <?php echo $searchQuery ? 'Search Results' : ($currentCategory ? 'Products' : 'Featured Products'); ?>
        </h2>
        <p class="shop-title-sub"><?php echo $searchQuery ? 'Results matching your search.' : ($currentCategory ? 'Browse products in this category.' : 'Popular products selected for you.'); ?></p>
    </div>
    <a href="<?php echo BASE_URL; ?>shop?category=all" class="btn btn-outline btn-sm shop-viewall-btn">View All Products</a>
</div>


<?php if (empty($products)): ?>
    <div class="empty-state">
        <i class="fas fa-box-open"></i>
        <h3>No products found</h3>
        <p>Try adjusting your search or filter to find what you're looking for</p>
        <a href="<?php echo BASE_URL; ?>shop" class="btn" style="margin-top: 20px;">Browse All Products</a>
    </div>
<?php else: ?>
    <div class="products-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card" onclick="location.href='<?php echo BASE_URL; ?>shop/product/<?php echo $product['product_id']; ?>'">
                <?php $productImage = $product['product_image'] ?? $product['image'] ?? ''; ?>
                <div class="product-image-wrapper">
                    <?php if (!empty($product['category_name'])): ?>
                        <?php $catColors = ['cat-pink', 'cat-purple', 'cat-orange']; ?>
                        <span class="badge-category <?php echo $catColors[((int) ($product['category_id'] ?? 0)) % 3]; ?>"><?php echo htmlspecialchars($product['category_name']); ?></span>
                    <?php endif; ?>
                    <span class="product-wishlist"><i class="far fa-heart"></i></span>
                    <?php if (!empty($productImage)): ?>
                        <img src="<?php echo BASE_URL . $productImage; ?>"
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                             class="product-image"
                             onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%23f0f0f0%22/%3E%3C/svg%3E';">
                    <?php else: ?>
                        <div class="product-image" style="display: flex; align-items: center; justify-content: center; background: #f0f0f0;">
                            <i class="fas fa-image" style="font-size: 60px; color: #ccc;"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="product-info">
                    <div class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></div>
                    <?php if (!empty($product['avg_rating'])): ?>
                    <div class="product-rating">
                        <span class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?><i class="<?php echo $i > round($product['avg_rating']) ? 'far' : 'fas'; ?> fa-star"></i><?php endfor; ?>
                        </span>
                        <span><?php echo !empty($product['review_count']) ? '(' . (int) $product['review_count'] . ')' : ''; ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="product-price-row">
                        <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>
                        <?php $soldCount = (int) ($product['total_sold'] ?? 0); ?>
                        <span class="product-sold">Sold <?php echo $soldCount >= 1000 ? number_format($soldCount / 1000, 1) . 'k' : $soldCount; ?></span>
                    </div>
                    <?php
                        $isPublished = $product['purchasable'] ?? true;
                        $canBuy = $isPublished && $product['stock'] > 0;
                        // Published by a reseller but temporarily out of stock — pre-order
                        // rather than a flat "unavailable", since it's expected to restock.
                        $isPreOrder = $isPublished && $product['stock'] <= 0;
                    ?>
                    <?php if ($isPreOrder): ?>
                        <div class="stock-indicator low-stock">Pre Order</div>
                    <?php elseif ($product['stock'] <= 0): ?>
                        <div class="stock-indicator out-of-stock">Sold Out</div>
                    <?php elseif ($product['stock'] <= 10): ?>
                        <div class="stock-indicator low-stock">Low Stock</div>
                    <?php else: ?>
                        <div class="stock-indicator in-stock">In Stock</div>
                    <?php endif; ?>
                    <?php
                        // No static "no-image" file to keep in sync — the placeholder is
                        // generated inline, same idea as the onerror fallback below, so
                        // there's no file path that can ever go stale or missing.
                        $noImageSvg = 'data:image/svg+xml,' . rawurlencode(
                            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">' .
                            '<rect width="200" height="200" fill="#f0f0f0"/>' .
                            '<circle cx="70" cy="70" r="18" fill="#ccc"/>' .
                            '<path d="M20 160 L75 100 L110 135 L140 100 L180 160 Z" fill="#ccc"/>' .
                            '</svg>'
                        );
                        $atcPayload = htmlspecialchars(json_encode([
                            'id' => (int) $product['product_id'],
                            'name' => $product['product_name'],
                            'image' => !empty($productImage) ? BASE_URL . $productImage : $noImageSvg,
                            'price' => (float) $product['price'],
                            'stock' => (int) $product['stock'],
                            'hasVariations' => !empty($product['has_variations']),
                        ]), ENT_QUOTES);
                    ?>
                    <?php $alreadyPreordered = !empty($product['already_preordered']); ?>
                    <div class="product-actions-row" style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;">
                        <button class="btn btn-sm w-100" style="background: var(--gradient-hover, var(--primary-pink)); white-space: nowrap;" <?php echo $canBuy ? '' : 'disabled'; ?>
                            onclick="event.stopPropagation(); <?php echo $canBuy ? 'openAddToCartModal(' . $atcPayload . ", 'buy')" : ''; ?>">
                            <i class="fas fa-bolt"></i> Buy Now
                        </button>
                        <?php if (!$isPublished): ?>
                            <button class="btn btn-outline btn-sm w-100" style="white-space: nowrap;"
                                data-product-id="<?php echo (int) $product['product_id']; ?>"
                                <?php echo $alreadyPreordered ? 'disabled' : ''; ?>
                                title="<?php echo $alreadyPreordered ? "You'll be notified when this becomes available" : 'Not currently available from a reseller yet. Click to get notified when it is'; ?>"
                                onclick="event.stopPropagation(); submitPreorder(this);">
                                <i class="fas fa-<?php echo $alreadyPreordered ? 'check' : 'bell'; ?>"></i> <?php echo $alreadyPreordered ? "You'll Be Notified" : 'Notify Me'; ?>
                            </button>
                        <?php else: ?>
                            <button class="btn btn-outline btn-sm w-100" style="white-space: nowrap;" <?php echo $canBuy ? '' : 'disabled title="Currently out of stock. Check back soon"'; ?>
                                onclick="event.stopPropagation(); <?php echo $canBuy ? 'openAddToCartModal(' . $atcPayload . ", 'cart')" : ''; ?>">
                                <i class="fas fa-cart-plus"></i> <?php echo $canBuy ? 'Add to Cart' : 'Pre Order'; ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="?page=<?php echo $currentPage - 1; ?><?php echo $searchQuery ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo $currentCategory ? '&category=' . $currentCategory : ''; ?><?php echo $currentSubcategory ? '&subcategory=' . $currentSubcategory : ''; ?>" class="page-btn">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i === $currentPage): ?>
                    <span class="page-btn active"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?><?php echo $searchQuery ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo $currentCategory ? '&category=' . $currentCategory : ''; ?><?php echo $currentSubcategory ? '&subcategory=' . $currentSubcategory : ''; ?>" class="page-btn">
                        <?php echo $i; ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?php echo $currentPage + 1; ?><?php echo $searchQuery ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo $currentCategory ? '&category=' . $currentCategory : ''; ?><?php echo $currentSubcategory ? '&subcategory=' . $currentSubcategory : ''; ?>" class="page-btn">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="trust-bar">
    <div class="trust-item">
        <span class="trust-icon"><i class="fas fa-truck-fast"></i></span>
        <span>
            <span class="trust-title">Fast Delivery</span>
            <span class="trust-sub">Quick &amp; reliable delivery nationwide</span>
        </span>
    </div>
    <div class="trust-item">
        <span class="trust-icon"><i class="fas fa-shield-halved"></i></span>
        <span>
            <span class="trust-title">100% Authentic</span>
            <span class="trust-sub">All products are original and verified</span>
        </span>
    </div>
    <div class="trust-item">
        <span class="trust-icon"><i class="fas fa-headset"></i></span>
        <span>
            <span class="trust-title">Customer Support</span>
            <span class="trust-sub">We're here to help you 24/7</span>
        </span>
    </div>
    <div class="trust-item">
        <span class="trust-icon"><i class="fas fa-award"></i></span>
        <span>
            <span class="trust-title">Best Quality</span>
            <span class="trust-sub">Premium quality beauty products</span>
        </span>
    </div>
</div>

<!-- Add to Cart / Buy Now modal (shared .modal component) -->
<div class="modal" id="addToCartModal">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h3 class="modal-title">Choose Options</h3>
            <button type="button" class="modal-close" onclick="closeAddToCartModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="atc-modal-header">
                <img id="atcImage" src="" alt="">
                <div>
                    <div class="atc-modal-name" id="atcName"></div>
                    <div class="atc-modal-price" id="atcPrice"></div>
                    <div class="atc-modal-stock" id="atcStock" style="font-size:13px;color:var(--gray,#888);margin-top:2px;"><i class="fas fa-box"></i> <span id="atcStockText"></span></div>
                </div>
            </div>

            <div id="atcVariationSection" style="display:none;margin-top:16px;">
                <div id="atcVariationGroups"></div>
                <div class="atc-variation-error" id="atcVariationError">
                    <i class="fas fa-circle-exclamation"></i> Please select an option.
                </div>
            </div>

            <div class="atc-qty-row">
                <span style="font-size:13px;font-weight:600;color:#666;">Quantity:</span>
                <div class="atc-qty-controls">
                    <button type="button" class="atc-qty-btn" onclick="atcChangeQty(-1)">-</button>
                    <span class="atc-qty-value" id="atcQtyValue">1</span>
                    <button type="button" class="atc-qty-btn" onclick="atcChangeQty(1)">+</button>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline btn-sm" onclick="closeAddToCartModal()">Cancel</button>
            <button type="button" class="btn btn-sm" style="background:var(--gradient-hover, var(--primary-pink));color:#fff;" id="atcConfirmBtn" onclick="confirmAddToCart()">Add to Cart</button>
        </div>
    </div>
</div>

<script>
let atcState = { id: null, mode: 'cart', hasVariations: false, basePrice: 0, variations: [], combinations: [], selectedVariationId: null, selectedVariantId: null, selectedAxisValues: {}, qty: 1, stock: 0 };

function submitPreorder(btn) {
    <?php if (($_SESSION['user_type'] ?? '') !== 'customer'): ?>
        showGuestChoice();
        return;
    <?php endif; ?>

    btn.disabled = true;
    const productId = btn.dataset.productId;
    fetch('<?php echo BASE_URL; ?>shop/preorder', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + encodeURIComponent(productId)
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                btn.innerHTML = '<i class="fas fa-check"></i> You\'ll Be Notified';
                btn.title = "You'll be notified when this becomes available";
            } else {
                btn.disabled = false;
                if (data.needs_login) { showGuestChoice(); return; }
                if (typeof customAlert === 'function') customAlert(data.message || 'Something went wrong. Please try again.');
                else alert(data.message || 'Something went wrong. Please try again.');
            }
        })
        .catch(() => {
            btn.disabled = false;
            if (typeof customAlert === 'function') customAlert('Something went wrong. Please try again.');
            else alert('Something went wrong. Please try again.');
        });
}

function openAddToCartModal(product, mode) {
    <?php if (($_SESSION['user_type'] ?? '') !== 'customer'): ?>
        showGuestChoice();
        return;
    <?php endif; ?>

    atcState = {
        id: product.id,
        mode: mode,
        hasVariations: !!product.hasVariations,
        basePrice: product.price,
        variations: [],
        combinations: [],
        selectedVariationId: null,
        selectedVariantId: null,
        selectedAxisValues: {},
        qty: 1,
        stock: product.stock
    };

    atcState.baseImage = product.image;
    document.getElementById('atcImage').src = product.image;
    document.getElementById('atcName').textContent = product.name;
    document.getElementById('atcPrice').textContent = '₱' + product.price.toFixed(2);
    document.getElementById('atcQtyValue').textContent = '1';
    document.getElementById('atcConfirmBtn').textContent = mode === 'buy' ? 'Buy Now' : 'Add to Cart';
    document.getElementById('atcVariationError').style.display = 'none';
    updateAtcStockDisplay();

    const variationSection = document.getElementById('atcVariationSection');
    if (atcState.hasVariations) {
        variationSection.style.display = 'block';
        document.getElementById('atcVariationGroups').innerHTML = '<div style="font-size:13px;color:#888;">Loading options…</div>';
        fetch('<?php echo BASE_URL; ?>shop/variations/' + product.id)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    atcState.variations = data.variations;
                    atcState.combinations = data.combinations || [];
                    renderAtcVariationGroups();
                }
            });
    } else {
        variationSection.style.display = 'none';
    }

    openModal('addToCartModal');
}

function closeAddToCartModal() {
    closeModal(document.getElementById('addToCartModal'));
}

function updateAtcStockDisplay() {
    const stockEl = document.getElementById('atcStock');
    const textEl = document.getElementById('atcStockText');
    if (!stockEl || !textEl) return;
    const stock = atcState.stock || 0;
    textEl.textContent = stock > 0 ? stock + ' in stock' : 'Out of stock';
    stockEl.style.color = stock > 0 ? 'var(--gray, #888)' : '#c62828';
}

function renderAtcVariationGroups() {
    const groups = {};
    atcState.variations.forEach(v => {
        if (!groups[v.variation_type]) groups[v.variation_type] = [];
        groups[v.variation_type].push(v);
    });

    const combinationsMode = atcState.combinations.length > 0;
    let html = '';
    let axisIndex = 0;
    Object.keys(groups).forEach(type => {
        axisIndex++;
        html += '<div class="atc-variation-group" data-axis="' + axisIndex + '" data-type="' + escapeHtml(type) + '"><div class="atc-variation-label">' + escapeHtml(type) + '</div>';
        groups[type].forEach(v => {
            if (combinationsMode) {
                html += '<span class="atc-variation-opt" data-value="' + escapeHtml(v.variation_value) + '" onclick="atcSelectAxisValue(' + axisIndex + ', this)">' +
                    escapeHtml(v.variation_value) + '</span>';
            } else {
                const outOfStock = v.stock <= 0;
                html += '<span class="atc-variation-opt' + (outOfStock ? ' disabled' : '') + '" data-variation-id="' + v.variation_id + '" data-image="' + escapeHtml(v.image_url || '') + '"' +
                    (outOfStock ? '' : ' onclick="atcSelectVariation(this, ' + v.variation_id + ', ' + v.stock + ', ' + v.price_adjustment + ')"') + '">' +
                    escapeHtml(v.variation_value) + (outOfStock ? ' (out of stock)' : '') + '</span>';
            }
        });
        html += '</div>';
    });
    document.getElementById('atcVariationGroups').innerHTML = html;
}

function atcSelectVariation(el, variationId, stock, priceAdjustment) {
    document.getElementById('atcVariationError').style.display = 'none';

    // Clicking an already-selected option deselects it instead of re-selecting.
    if (atcState.selectedVariationId === variationId) {
        atcState.selectedVariationId = null;
        atcState.stock = 0;

        document.querySelectorAll('.atc-variation-opt').forEach(opt => {
            opt.classList.remove('selected');
        });

        document.getElementById('atcPrice').textContent = '₱' + atcState.basePrice.toFixed(2);
        document.getElementById('atcImage').src = atcState.baseImage;
        updateAtcStockDisplay();
        return;
    }

    atcState.selectedVariationId = variationId;
    atcState.stock = stock;

    document.querySelectorAll('.atc-variation-opt').forEach(opt => {
        opt.classList.toggle('selected', opt.dataset.variationId == variationId);
    });

    document.getElementById('atcPrice').textContent = '₱' + (atcState.basePrice + priceAdjustment).toFixed(2);
    if (el.dataset.image) document.getElementById('atcImage').src = el.dataset.image;
    updateAtcStockDisplay();

    if (atcState.qty > stock) {
        atcState.qty = Math.max(1, stock);
        document.getElementById('atcQtyValue').textContent = atcState.qty;
    }
}

/* ── Combination mode: cascading per-axis selection, mirrors shop/product.php ── */
function atcSelectAxisValue(axisIndex, el) {
    if (el.classList.contains('disabled')) return;
    document.getElementById('atcVariationError').style.display = 'none';

    const group = el.closest('.atc-variation-group');
    const type = group.dataset.type;
    const value = el.dataset.value;

    if (atcState.selectedAxisValues[axisIndex] && atcState.selectedAxisValues[axisIndex].value === value) {
        delete atcState.selectedAxisValues[axisIndex];
    } else {
        atcState.selectedAxisValues[axisIndex] = { type: type, value: value };
    }

    group.querySelectorAll('.atc-variation-opt').forEach(opt => {
        const active = atcState.selectedAxisValues[axisIndex] && opt.dataset.value === atcState.selectedAxisValues[axisIndex].value;
        opt.classList.toggle('selected', !!active);
    });

    atcFilterOtherAxes(axisIndex);
    atcResolveVariant();
}

function atcFilterOtherAxes(changedAxisIndex) {
    const groups = Array.from(document.querySelectorAll('.atc-variation-group'));
    if (groups.length < 2) return;

    // A candidate is pairable if some combination simultaneously matches it
    // AND every other axis currently selected (not just the axis that just
    // changed) — required once there can be 3+ axes at once.
    const otherPicks = groups
        .map(g => parseInt(g.dataset.axis, 10))
        .filter(idx => idx !== changedAxisIndex)
        .map(idx => atcState.selectedAxisValues[idx])
        .filter(Boolean);

    groups.forEach(group => {
        const axisIndex = parseInt(group.dataset.axis, 10);
        if (axisIndex === changedAxisIndex) return;

        group.querySelectorAll('.atc-variation-opt').forEach(opt => {
            const candidateValue = opt.dataset.value;
            const candidateType = group.dataset.type;
            const required = otherPicks.concat([{ type: candidateType, value: candidateValue }]);
            const pairable = atcState.combinations.some(c =>
                required.every(req => c.axes.some(a => a.type === req.type && a.value === req.value))
            );
            opt.classList.toggle('disabled', !pairable);
        });
    });
}

function atcFindMatchingCombination() {
    const groups = Array.from(document.querySelectorAll('.atc-variation-group'));
    const picks = groups.map(g => atcState.selectedAxisValues[parseInt(g.dataset.axis, 10)]).filter(Boolean);
    if (picks.length !== groups.length) return null;

    return atcState.combinations.find(c => {
        return picks.every(p => c.axes.some(v => v.type === p.type && v.value === p.value))
            && c.axes.every(v => picks.some(p => p.type === v.type && p.value === v.value));
    }) || null;
}

function atcResolveVariant() {
    const combo = atcFindMatchingCombination();
    if (!combo) {
        atcState.selectedVariantId = null;
        atcState.stock = 0;
        document.getElementById('atcPrice').textContent = '₱' + atcState.basePrice.toFixed(2);
        // Nothing resolved yet (still picking options) — don't claim "Out of
        // stock" prematurely before both axes are chosen.
        const textEl = document.getElementById('atcStockText');
        if (textEl) textEl.textContent = '';
        return;
    }

    atcState.selectedVariantId = combo.variant_id;
    atcState.stock = parseInt(combo.total_stock, 10) || 0;
    document.getElementById('atcPrice').textContent = '₱' + (atcState.basePrice + (parseFloat(combo.price_adjustment) || 0)).toFixed(2);
    if (combo.image_url) document.getElementById('atcImage').src = combo.image_url;
    updateAtcStockDisplay();

    if (atcState.qty > atcState.stock) {
        atcState.qty = Math.max(1, atcState.stock);
        document.getElementById('atcQtyValue').textContent = atcState.qty;
    }
}

function atcChangeQty(delta) {
    const max = atcState.stock > 0 ? atcState.stock : 1;
    let next = atcState.qty + delta;
    if (next < 1) next = 1;
    if (next > max) next = max;
    atcState.qty = next;
    document.getElementById('atcQtyValue').textContent = next;
}

function confirmAddToCart() {
    const combinationsMode = atcState.combinations.length > 0;
    if (atcState.hasVariations) {
        if (combinationsMode) {
            const groups = Array.from(document.querySelectorAll('.atc-variation-group'));
            const allPicked = groups.every(g => atcState.selectedAxisValues[parseInt(g.dataset.axis, 10)]);
            if (!allPicked || !atcState.selectedVariantId) {
                document.getElementById('atcVariationError').style.display = 'block';
                return;
            }
            if (atcState.stock <= 0) {
                document.getElementById('atcVariationError').textContent = 'This combination is currently out of stock.';
                document.getElementById('atcVariationError').style.display = 'block';
                return;
            }
        } else if (!atcState.selectedVariationId) {
            document.getElementById('atcVariationError').style.display = 'block';
            return;
        }
    }

    const resellerId = <?php echo (int) ($shopResellerId ?? 0); ?>;
    const btn = document.getElementById('atcConfirmBtn');
    const originalLabel = btn.textContent;
    btn.disabled = true;

    fetch('<?php echo BASE_URL; ?>cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'product_id=' + atcState.id + '&quantity=' + atcState.qty +
            (combinationsMode && atcState.selectedVariantId ? '&variant_id=' + atcState.selectedVariantId : '') +
            (!combinationsMode && atcState.selectedVariationId ? '&variation_id=' + atcState.selectedVariationId : '') +
            (resellerId ? '&reseller_id=' + resellerId : '')
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeAddToCartModal();
            if (atcState.mode === 'buy') {
                window.location.href = '<?php echo BASE_URL; ?>checkout';
            } else {
                showToast('Product added to cart!', 'success');
                updateCartCount();
            }
        } else {
            showToast(data.message || 'Failed to add product', 'error');
        }
    })
    .catch(() => showToast('Something went wrong', 'error'))
    .finally(() => {
        btn.disabled = false;
        btn.textContent = originalLabel;
    });
}

function escapeHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function showToast(message, type) {
    const existing = document.querySelector('.cart-toast');
    if (existing) existing.remove();
    
    const toast = document.createElement('div');
    toast.className = 'cart-toast cart-toast-' + type;
    toast.innerHTML = '<i class="fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + '"></i> ' + message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>

<style>
.cart-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 25px;
    border-radius: 10px;
    color: white;
    font-weight: 500;
    font-size: 14px;
    z-index: 9999;
    transform: translateX(120%);
    transition: transform 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    gap: 10px;
}
.cart-toast.show { transform: translateX(0); }
.cart-toast-success { background: #28a745; }
.cart-toast-error { background: #dc3545; }
</style>
