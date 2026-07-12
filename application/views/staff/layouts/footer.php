        </main>
    </div>

    <!-- User Dropdown Menu (Staff) -->
    <div id="userDropdown" style="display: none; position: absolute; top: 60px; right: 20px; background: white; border-radius: 12px; box-shadow: var(--shadow-lg); padding: 10px; min-width: 200px; z-index: 9999;">
		<a href="<?php echo BASE_URL; ?>staff/profile" style="display: block; padding: 10px 15px; color: var(--dark-gray); text-decoration: none; border-radius: 8px; transition: var(--transition-base);">
			<i class="fas fa-user"></i> Profile
		</a>
		<hr style="margin: 8px 0; border: none; border-top: 1px solid var(--secondary-lavender);">
		<a href="<?php echo BASE_URL; ?>auth/logout/staff" style="display: block; padding: 10px 15px; color: var(--danger); text-decoration: none; border-radius: 8px; transition: var(--transition-base);">
			<i class="fas fa-sign-out-alt"></i> Logout
		</a>
	</div>

    <!-- Define BASE_URL for JavaScript -->
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
    </script>

    <!-- Bootstrap 5 JS Bundle (self-hosted — modals, dropdowns, alerts) -->
    <script src="<?php echo BASE_URL; ?>public/vendor/bootstrap/bootstrap.bundle.min.js"></script>

    <script src="<?php echo BASE_URL; ?>public/js/admin-scripts.js"></script>
    <script>
        // Sidebar Toggle — below 1200px (Tablet/Mobile) the sidebar is an
        // off-canvas drawer; at 1200px+ (Desktop/Laptop) it's a manual
        // expanded/icon-only collapse toggle for the fixed sidebar. This
        // threshold must match admin-style.css's off-canvas breakpoint
        // (max-width: 1199.98px) exactly, or the hamburger silently toggles
        // a class with no visible effect in the mismatched range.
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

        // Responsive sidebar default state
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

        // User Menu Toggle (Profile / Logout)
        const userMenuToggle = document.getElementById('userMenuToggle');
        const userDropdown = document.getElementById('userDropdown');
        if (userMenuToggle && userDropdown) {
            userMenuToggle.addEventListener('click', function(event) {
                event.stopPropagation();
                userDropdown.style.display = userDropdown.style.display === 'none' ? 'block' : 'none';
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!userMenuToggle.contains(event.target) && !userDropdown.contains(event.target)) {
                    userDropdown.style.display = 'none';
                }
            });
        }

        // Notification badge — polls this staff member's own unread count
        // (admin-scripts.js's checkNotifications() is hardcoded to scope=admin
        // and would read the wrong session here, so this is a separate,
        // staff-scoped poll instead of reusing that function).
        function checkStaffNotifications() {
            fetch(BASE_URL + 'index.php/api/notifications?scope=staff')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (!data || !data.success) return;
                    const badge = document.getElementById('notificationBadge');
                    if (!badge) return;
                    badge.textContent = data.count;
                    badge.style.display = data.count > 0 ? 'block' : 'none';
                })
                .catch(function() { /* fail silently */ });
        }
        checkStaffNotifications();
        setInterval(checkStaffNotifications, 30000);
    </script>
</body>
</html>
