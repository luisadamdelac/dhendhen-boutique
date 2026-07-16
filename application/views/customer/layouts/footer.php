    </div>
    
    <?php if (!defined('SITE_NAME')) { define('SITE_NAME', 'DropSell'); } ?>
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>
                    <i class="fas fa-heart"></i> <?php echo SITE_NAME; ?>
                </h4>
                <p>
                    Your trusted online beauty products and boutique shop.
                    Quality products at affordable prices.
                </p>
                <?php
                    $CI =& get_instance();
                    $CI->load->model('Settings_model');
                    $socialLinks = [
                        'facebook-f' => $CI->Settings_model->get('social_facebook'),
                        'instagram' => $CI->Settings_model->get('social_instagram'),
                        'tiktok' => $CI->Settings_model->get('social_tiktok'),
                    ];
                    $socialLinks = array_filter($socialLinks);
                ?>
                <?php if (!empty($socialLinks)): ?>
                <div class="social-links">
                    <?php foreach ($socialLinks as $icon => $url): ?>
                        <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener"><i class="fab fa-<?php echo $icon; ?>"></i></a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="footer-section">
                <h4>Quick Links</h4>
                <a href="<?php echo BASE_URL; ?>shop"><i class="fas fa-chevron-right"></i> Shop</a>
                <a href="<?php echo BASE_URL; ?>shop?category=all"><i class="fas fa-chevron-right"></i> All Products</a>
                <a href="<?php echo BASE_URL; ?>cart"><i class="fas fa-chevron-right"></i> Cart</a>
                <?php if (isset($_SESSION['user_account_id']) && (($_SESSION['user_type'] ?? '') === 'customer')): ?>
                    <a href="<?php echo BASE_URL; ?>account"><i class="fas fa-chevron-right"></i> My Account</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>auth/login"><i class="fas fa-chevron-right"></i> Login</a>
                    <a href="<?php echo BASE_URL; ?>auth/register"><i class="fas fa-chevron-right"></i> Register</a>
                <?php endif; ?>
            </div>

            <div class="footer-section">
                <h4>Customer Service</h4>
                <?php if (isset($_SESSION['user_account_id']) && (($_SESSION['user_type'] ?? '') === 'customer')): ?>
                    <a href="<?php echo BASE_URL; ?>orders"><i class="fas fa-chevron-right"></i> My Orders</a>
                    <a href="<?php echo BASE_URL; ?>refund"><i class="fas fa-chevron-right"></i> Refunds</a>
                    <a href="<?php echo BASE_URL; ?>addresses"><i class="fas fa-chevron-right"></i> Addresses</a>
                    <a href="<?php echo BASE_URL; ?>settings"><i class="fas fa-chevron-right"></i> Settings</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>auth/login"><i class="fas fa-chevron-right"></i> Track My Orders</a>
                    <a href="<?php echo BASE_URL; ?>auth/login"><i class="fas fa-chevron-right"></i> Refunds</a>
                    <a href="<?php echo BASE_URL; ?>auth/register_reseller"><i class="fas fa-chevron-right"></i> Become a Reseller</a>
                <?php endif; ?>
            </div>

            <div class="footer-section">
                <h4>Contact Us</h4>
                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($CI->Settings_model->get_company_phone() ?: '+63 123 456 7890'); ?></p>
                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($CI->Settings_model->get_company_email()); ?></p>
                <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($CI->Settings_model->get_company_address()); ?></p>
            </div>

            <div class="footer-section">
                <h4>Newsletter</h4>
                <p>Get updates on new arrivals and exclusive offers.</p>
                <form class="footer-newsletter-form" onsubmit="event.preventDefault(); this.reset();">
                    <input type="email" placeholder="Your email address" required>
                    <button type="submit"><i class="fas fa-paper-plane"></i></button>
                </form>
                <p class="footer-newsletter-note">We respect your privacy. Unsubscribe anytime.</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Made with <span class="heart"><i class="fas fa-heart"></i></span> All rights reserved.</p>
        </div>
    </footer>

    <?php if (empty($_SESSION['user_account_id'])): ?>
    <!-- Guest decision prompt: shown when a visitor tries to buy without an account -->
    <div id="guestChoiceOverlay" class="guest-choice-overlay" onclick="if (event.target === this) hideGuestChoice();">
        <div class="guest-choice-modal">
            <button type="button" class="guest-choice-close" onclick="hideGuestChoice()" aria-label="Close">&times;</button>
            <h3><i class="fas fa-user-circle"></i> Ready to buy?</h3>
            <p>Create an account or log in to add items to your cart.</p>
            <div class="guest-choice-options">
                <a class="btn" href="<?php echo BASE_URL; ?>auth/login"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a class="btn btn-outline" href="<?php echo BASE_URL; ?>auth/register"><i class="fas fa-user-plus"></i> Register</a>
                <a class="btn btn-outline" href="<?php echo BASE_URL; ?>auth/register_reseller"><i class="fas fa-store-alt"></i> Become a Reseller</a>
                <button type="button" class="btn btn-outline" onclick="hideGuestChoice()"><i class="fas fa-shopping-bag"></i> Continue Shopping</button>
            </div>
        </div>
    </div>
    <style>
        .guest-choice-overlay {
            display: none;
            position: fixed; inset: 0; background: rgba(0,0,0,0.5);
            z-index: 10000; align-items: center; justify-content: center;
        }
        .guest-choice-overlay.show { display: flex; }
        .guest-choice-modal {
            background: white; border-radius: 14px; padding: 30px;
            max-width: 380px; width: 90%; position: relative;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .guest-choice-modal h3 { margin: 0 0 8px; }
        .guest-choice-modal p { color: var(--gray, #888); margin: 0 0 20px; font-size: 14px; }
        .guest-choice-close {
            position: absolute; top: 12px; right: 14px; background: none; border: none;
            font-size: 22px; line-height: 1; cursor: pointer; color: var(--gray, #888);
        }
        .guest-choice-options { display: flex; flex-direction: column; gap: 10px; }
        .guest-choice-options .btn { width: 100%; justify-content: center; }
    </style>
    <?php endif; ?>

    <!-- Shared modal component — same markup/classes as the admin/staff/reseller
         panels (public/css/admin-style.css + admin-scripts.js), reproduced here
         with literal values since customer pages don't load that stylesheet. -->
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(17, 24, 39, 0.5);
            z-index: 9999;
            padding: 2rem;
            overflow-y: auto;
        }
        .modal.show { display: flex; align-items: center; justify-content: center; }
        .modal-content {
            background: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 10px 26px rgba(17, 24, 39, 0.16);
            max-width: 600px;
            width: 100%;
            padding: 0;
            animation: modalFadeIn 0.25s ease-in-out;
            overflow: hidden;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .modal-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #E5E7EB;
        }
        .modal-title { font-size: 1.25rem; font-weight: 700; color: #374151; }
        .modal-close {
            background: none; border: none; font-size: 1.5rem;
            color: #6b7280; cursor: pointer; transition: 0.2s ease-in-out;
        }
        .modal-close:hover { color: #dc3545; }
        .modal-body { padding: 1.5rem 2rem; }
        .modal-footer {
            display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem;
            padding: 1rem 2rem;
            border-top: 1px solid #E5E7EB;
        }
        .modal-content.modal-sm { max-width: 400px; }
        .modal-content.modal-lg { max-width: 800px; }

        /* Tighten the overlay/header/body/footer gutters on small screens
           so the modal reads as "nearly full-width with margins" instead of
           losing most of its usable width to fixed 2rem padding. */
        @media (max-width: 767.98px) {
            .modal { padding: 1rem; }
            .modal-header, .modal-body, .modal-footer { padding: 1rem 1.25rem; }
        }
        @media (max-width: 480px) {
            .modal { padding: 0.5rem; }
        }
    </style>

    <!-- Shared JS (customConfirm and friends) — same file admin/staff/reseller
         load, cache-busted so updates to it take effect immediately instead
         of a stale cached copy silently missing newer functions. -->
    <script src="<?php echo BASE_URL; ?>public/js/admin-scripts.js?v=<?php echo @filemtime(FCPATH . 'public/js/admin-scripts.js') ?: time(); ?>"></script>

    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        }
        function closeModal(modal) {
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        }

        function showGuestChoice() {
            const overlay = document.getElementById('guestChoiceOverlay');
            if (overlay) overlay.classList.add('show');
        }
        function hideGuestChoice() {
            const overlay = document.getElementById('guestChoiceOverlay');
            if (overlay) overlay.classList.remove('show');
        }

        function toggleMobileMenu() {
            document.querySelector('.nav-content').classList.toggle('active');
        }
        
        // Update cart count dynamically
        function updateCartCount() {
            fetch('<?php echo BASE_URL; ?>cart/count')
                .then(res => res.json())
                .then(data => {
                    const badge = document.querySelector('.cart-badge');
                    if (data.count > 0) {
                        if (badge) {
                            badge.textContent = data.count;
                        } else {
                            const cartBtn = document.querySelector('.header-btn[href*="cart"]');
                            if (cartBtn) {
                                const newBadge = document.createElement('span');
                                newBadge.className = 'cart-badge';
                                newBadge.textContent = data.count;
                                cartBtn.appendChild(newBadge);
                            }
                        }
                    } else if (badge) {
                        badge.remove();
                    }
                })
                .catch(() => {});
        }

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>
