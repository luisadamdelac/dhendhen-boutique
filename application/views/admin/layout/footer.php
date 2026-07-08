        </main>
    </div>
    
    <!-- User Dropdown Menu (Hidden by default) -->
    <div id="userDropdown" style="display: none; position: absolute; top: 60px; right: 20px; background: white; border-radius: 12px; box-shadow: var(--shadow-lg); padding: 10px; min-width: 200px; z-index: 9999;">
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
        // Sidebar Toggle (desktop vs mobile)
        const menuToggle = document.getElementById('menuToggle');
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
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
        
        // Global Search
        const globalSearch = document.getElementById('globalSearch');
        if (globalSearch) {
            globalSearch.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                console.log('Searching for:', searchTerm);
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
        function applySidebarResponsiveState() {
            if (window.innerWidth <= 1024) {
                document.body.classList.add('sidebar-collapsed');
            } else {
                document.body.classList.remove('sidebar-collapsed');
                document.body.classList.remove('sidebar-open');
            }
        }

        applySidebarResponsiveState();
        window.addEventListener('resize', applySidebarResponsiveState);
    </script>
    
    <!-- Define BASE_URL for JavaScript -->
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
    </script>
    
    <!-- Bootstrap 5 JS Bundle (modals, dropdowns, alerts) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="<?php echo BASE_URL; ?>public/js/admin-scripts.js?v=<?php echo time(); ?>"></script>
</body>
</html>
