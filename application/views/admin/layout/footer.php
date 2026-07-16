        </main>
    </div>
    
    <!-- User Dropdown Menu (Hidden by default) — position: fixed (not
         absolute) so it stays anchored to the fixed header instead of
         scrolling away with the page content underneath it. -->
    <div id="userDropdown" style="display: none; position: fixed; top: var(--header-height); right: 20px; background: white; border-radius: 12px; box-shadow: var(--shadow-lg); padding: 10px; min-width: 200px; z-index: 9999;">
        <a href="<?php echo site_url('profile'); ?>" style="display: block; padding: 10px 15px; color: var(--dark-gray); text-decoration: none; border-radius: 8px; transition: var(--transition-base);">
            <i class="fas fa-user"></i> Profile
        </a>
        <a href="<?php echo site_url('admin/settings'); ?>" style="display: block; padding: 10px 15px; color: var(--dark-gray); text-decoration: none; border-radius: 8px; transition: var(--transition-base);">
            <i class="fas fa-cog"></i> Settings
        </a>
        <hr style="margin: 8px 0; border: none; border-top: 1px solid var(--secondary-lavender);">
        <a href="<?php echo site_url('auth/logout/admin'); ?>" style="display: block; padding: 10px 15px; color: var(--danger); text-decoration: none; border-radius: 8px; transition: var(--transition-base);">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
    
    <!-- Custom JavaScript -->
    <script>
        // Sidebar Toggle — below 1200px (Tablet/Mobile) the sidebar is an
        // off-canvas drawer; at 1200px+ (Desktop/Laptop) it's a manual
        // expanded/icon-only collapse toggle for the fixed sidebar.
        const menuToggle = document.getElementById('menuToggle');
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                if (window.innerWidth < 1200) {
                    document.body.classList.toggle('sidebar-open');
                } else {
                    document.body.classList.toggle('sidebar-collapsed');
                }
            });
        }
        
        // User Menu Toggle
        const userMenuToggle = document.getElementById('userMenuToggle');
        const userDropdown = document.getElementById('userDropdown');
        if (userMenuToggle && userDropdown) {
            userMenuToggle.addEventListener('click', function() {
                userDropdown.style.display = userDropdown.style.display === 'none' ? 'block' : 'none';
            });

            document.addEventListener('click', function(event) {
                if (!userMenuToggle.contains(event.target) && !userDropdown.contains(event.target)) {
                    userDropdown.style.display = 'none';
                }
            });
        }
        
        // Search toggle (small phones — expands the collapsed search icon
        // into a full-width bar instead of hiding search entirely)
        const searchToggle = document.getElementById('searchToggle');
        const headerSearchBox = document.getElementById('headerSearchBox');
        if (searchToggle && headerSearchBox) {
            searchToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                headerSearchBox.classList.toggle('search-active');
                if (headerSearchBox.classList.contains('search-active')) {
                    headerSearchBox.querySelector('input').focus();
                }
            });
            document.addEventListener('click', function(event) {
                if (!headerSearchBox.contains(event.target) && !searchToggle.contains(event.target)) {
                    headerSearchBox.classList.remove('search-active');
                }
            });
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 300);
            });
        }, 5000);
        
        // Responsive sidebar default state
        // - Desktop/Laptop (>=1200px): fixed expanded sidebar; "collapsed"
        //   only applies if the user manually toggled it via the hamburger.
        // - Tablet/Mobile (<1200px): sidebar is off-canvas; collapsed must
        //   NOT apply here or its higher-specificity width rule fights the
        //   off-canvas transform and leaves a stuck icon strip on screen.
        function applySidebarResponsiveState() {
            if (window.innerWidth < 1200) {
                document.body.classList.remove('sidebar-collapsed');
            } else {
                document.body.classList.remove('sidebar-open');
            }
        }

        applySidebarResponsiveState();
        window.addEventListener('resize', applySidebarResponsiveState);

        // Tap-outside-to-close for the mobile off-canvas sidebar
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');
        if (sidebarBackdrop) {
            sidebarBackdrop.addEventListener('click', function() {
                document.body.classList.remove('sidebar-open');
            });
        }

        // Close the off-canvas drawer when a nav link is tapped
        document.querySelectorAll('.sidebar .nav-link').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth < 1200) {
                    document.body.classList.remove('sidebar-open');
                }
            });
        });
    </script>
    
    <!-- Define BASE_URL for JavaScript -->
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
    </script>
    
    <!-- Bootstrap 5 JS Bundle (self-hosted — modals, dropdowns, alerts) -->
    <script src="<?php echo BASE_URL; ?>public/vendor/bootstrap/bootstrap.bundle.min.js"></script>

    <script src="<?php echo BASE_URL; ?>public/js/admin-scripts.js?v=<?php echo @filemtime(FCPATH . 'public/js/admin-scripts.js') ?: time(); ?>"></script>
</body>
</html>
