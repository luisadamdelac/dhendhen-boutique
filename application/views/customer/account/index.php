<style>
    .account-container {
        margin-top: 30px;
    }
    
    .account-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .account-card {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .card-title {
        font-size: 20px;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--dark);
    }
    
    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="tel"],
    .form-group input[type="password"],
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        background: #fff;
    }

    .form-group select:disabled {
        background: #f3f4f6;
        color: #666;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary);
    }

    .has-toggle {
        position: relative;
    }

    .has-toggle input {
        padding-right: 46px !important;
    }

    .toggle-password {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: #999;
        font-size: 15px;
        padding: 0;
        line-height: 1;
    }

    .toggle-password:hover { color: var(--primary); }
    
    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }
    
    /* Submit buttons below now use the shared .btn/.btn-primary/.w-100 components. */

    .stats-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .stat-card {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
    }
    
    .stat-card:nth-child(2) {
        background: linear-gradient(135deg, #ff9800, #f57c00);
    }
    
    .stat-card:nth-child(3) {
        background: linear-gradient(135deg, #4caf50, #388e3c);
    }
    
    .stat-value {
        font-size: 36px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 14px;
        opacity: 0.9;
    }
    
    .password-section {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .password-reqs {
        background: #fff5f8;
        border: 1px solid #ffccdc;
        border-radius: 10px;
        padding: 12px 14px;
        margin-top: 10px;
    }
    .password-reqs .req {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 11.5px;
        color: #8b8b8b;
        margin-bottom: 5px;
        transition: color 0.2s;
    }
    .password-reqs .req:last-child { margin-bottom: 0; }
    .password-reqs .req i { font-size: 8px; }
    .password-reqs .req.ok { color: #28a745; }
    .password-reqs .req.ok i::before { content: "\f00c"; font-size: 10px; }

    .divider {
        height: 2px;
        background: var(--light-gray);
        margin: 30px 0;
    }
    
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .alert-success {
        background: #d1e7dd;
        color: #0f5132;
        border: 1px solid #badbcc;
    }
    
    .alert-error {
        background: #f8d7da;
        color: #842029;
        border: 1px solid #f5c2c7;
    }
    
    @media (max-width: 968px) {
        .account-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 600px) {
        .account-card,
        .password-section {
            padding: 20px 16px;
        }

        .account-profile-row {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 15px !important;
        }

        .password-fields-grid,
        .business-fields-grid {
            grid-template-columns: 1fr !important;
        }

        .eligibility-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }

        .stat-value {
            font-size: 28px;
        }
    }
</style>

<h1><i class="fas fa-user-circle"></i> My Account</h1>

<div class="account-container">
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
    
    <div class="account-grid">
        <!-- Profile Information -->
        <div class="account-card">
            <h2 class="card-title">
                <i class="fas fa-user-edit"></i> Profile Information
            </h2>

            <?php
                $avatarImage = $user['profile_image'] ?? '';
                $avatarSrc = !empty($avatarImage)
                    ? BASE_URL . $avatarImage
                    : BASE_URL . default_avatar_url();
            ?>
            <div class="account-profile-row" style="display:flex; align-items:center; gap:20px; margin-bottom:25px;">
                <div style="width:90px;height:90px;border-radius:50%;overflow:hidden;border:3px solid var(--primary-light, #eee);flex-shrink:0;">
                    <img id="currentPhoto" src="<?php echo $avatarSrc; ?>" alt="Profile Photo" style="width:100%;height:100%;object-fit:cover;">
                </div>
                <div>
                    <div style="margin-bottom: 10px;">
                        <h4 style="margin: 0 0 3px 0;"><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></h4>
                        <p style="color: #666; margin: 0;">
                            <i class="fas fa-shield-alt" style="color: var(--primary, var(--primary-pink));"></i>
                            Customer
                        </p>
                    </div>
                    <form method="POST" action="<?php echo BASE_URL; ?>account/update_photo" enctype="multipart/form-data" id="photoForm">
                        <input type="file" id="photoInput" name="photo" accept="image/*" style="display:none;" onchange="previewAndUpload(this)">
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('photoInput').click();">
                                <i class="fas fa-upload"></i> Upload Photo
                            </button>
                            <?php if (!empty($avatarImage)): ?>
                            <a href="<?php echo BASE_URL; ?>account/delete_photo" class="btn btn-outline"
                               onclick="return confirmRemovePhoto(event, this, 'Remove your profile photo?');">
                                <i class="fas fa-trash"></i> Remove
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <form method="POST" action="<?php echo BASE_URL; ?>account/update_profile">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                           placeholder="+63 XXX XXX XXXX">
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mt-2">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </form>
        </div>
        
        <!-- Account Statistics -->
        <div>
            <div class="account-card">
                <h2 class="card-title">
                    <i class="fas fa-chart-bar"></i> Statistics
                </h2>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['total_orders'] ?? 0; ?></div>
                        <div class="stat-label">Total Orders</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['pending_orders'] ?? 0; ?></div>
                        <div class="stat-label">Pending Orders</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['completed_orders'] ?? 0; ?></div>
                        <div class="stat-label">Completed Orders</div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="account-card" style="margin-top: 20px;">
                <h2 class="card-title">
                    <i class="fas fa-link"></i> Quick Links
                </h2>
                
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="<?php echo BASE_URL; ?>orders" class="btn btn-outline">
                        <i class="fas fa-receipt"></i> View Orders
                    </a>
                    <a href="<?php echo BASE_URL; ?>shop" class="btn btn-outline">
                        <i class="fas fa-shopping-bag"></i> Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Become a Reseller -->
    <div class="password-section">
        <h2 class="card-title">
            <i class="fas fa-store"></i> Become a Reseller
        </h2>

        <?php if (!empty($active_reseller)): ?>
            <!-- Already an active reseller — no more applying, just a shortcut -->
            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                <span style="display:inline-flex; align-items:center; gap:6px; background:#dcfce7; color:#16a34a; padding:6px 14px; border-radius:20px; font-weight:600; font-size:0.85rem;">
                    <i class="fas fa-check-circle"></i> Active Reseller
                </span>
                <a href="<?php echo BASE_URL; ?>reseller/dashboard" class="btn btn-primary btn-sm">
                    <i class="fas fa-arrow-right"></i> Go to Reseller Dashboard
                </a>
            </div>

        <?php else:
            $is_eligible = ($best_single_order ?? 0) >= ($minimum_spend ?? 0);
            $remaining = max(0, ($minimum_spend ?? 0) - ($best_single_order ?? 0));
            $pct = ($minimum_spend ?? 0) > 0 ? min(100, (($best_single_order ?? 0) / $minimum_spend) * 100) : 0;
        ?>

            <?php if (!empty($reseller_application) && $reseller_application['status'] !== 'rejected'): ?>
                <p>
                    Application status:
                    <span class="badge badge-<?php echo $reseller_application['status'] === 'approved' ? 'success' : 'warning'; ?>">
                        <?php echo ucfirst(htmlspecialchars($reseller_application['status'])); ?>
                    </span>
                </p>
            <?php else: ?>
                <?php if (!empty($reseller_application) && $reseller_application['status'] === 'rejected'): ?>
                    <p style="font-size:0.875rem; color:#dc2626;">
                        Your previous application was rejected.
                        <?php if (!empty($reseller_application['admin_remarks'])): ?>
                            <br><small><?php echo htmlspecialchars($reseller_application['admin_remarks']); ?></small>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>

                <!-- Eligibility progress -->
                <div class="eligibility-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:14px; margin-bottom:12px;">
                    <div>
                        <small class="text-muted d-block">Your Largest Single Order</small>
                        <strong style="font-size:1.1rem;">₱<?php echo number_format($best_single_order ?? 0, 2); ?></strong>
                    </div>
                    <div>
                        <small class="text-muted d-block">Needed in One Order</small>
                        <strong style="font-size:1.1rem;"><?php echo $is_eligible ? '₱0.00' : '₱' . number_format($remaining, 2); ?></strong>
                    </div>
                    <div>
                        <small class="text-muted d-block">Eligibility</small>
                        <?php if ($is_eligible): ?>
                            <span style="display:inline-flex; align-items:center; gap:5px; background:#dcfce7; color:#16a34a; padding:3px 12px; border-radius:20px; font-weight:600; font-size:0.8rem;">
                                <i class="fas fa-check-circle"></i> Eligible
                            </span>
                        <?php else: ?>
                            <span style="display:inline-flex; align-items:center; gap:5px; background:#fef3c7; color:#b45309; padding:3px 12px; border-radius:20px; font-weight:600; font-size:0.8rem;">
                                Not Eligible
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="width:100%; height:10px; background:#eef0ff; border-radius:6px; overflow:hidden; margin-bottom:6px;">
                    <div style="width:<?php echo $pct; ?>%; height:100%; background:<?php echo $is_eligible ? '#16a34a' : '#f59e0b'; ?>; transition:width .3s ease;"></div>
                </div>
                <p style="font-size:0.8rem; color:var(--gray-600); margin-bottom:1.25rem;">
                    ₱<?php echo number_format($best_single_order ?? 0, 2); ?> of ₱<?php echo number_format($minimum_spend ?? 0, 2); ?> in a single delivered order. Purchases across multiple orders don't add up
                </p>

                <form method="POST" action="<?php echo BASE_URL; ?>account/apply_reseller">
                    <div class="form-group">
                        <label for="business_name">Business Name *</label>
                        <input type="text" id="business_name" name="business_name" required>
                    </div>
                    <div class="form-group">
                        <label>Province</label>
                        <input type="text" value="Oriental Mindoro" readonly>
                    </div>
                    <div class="business-fields-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div class="form-group">
                            <label for="business_city">City *</label>
                            <select id="business_city" name="business_city" required>
                                <option value="">Select Municipality / City</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="business_barangay">Barangay</label>
                            <select id="business_barangay" name="business_barangay" disabled>
                                <option value="">Select Municipality first</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="business_street">Street</label>
                        <input type="text" id="business_street" name="business_street">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-2" <?php echo $is_eligible ? '' : 'disabled title="Keep shopping to reach the minimum qualifying spend"'; ?>>
                        <i class="fas fa-paper-plane"></i> <?php echo $is_eligible ? 'Submit Application' : 'Not Eligible Yet'; ?>
                    </button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Change Password -->
    <div class="password-section">
        <h2 class="card-title">
            <i class="fas fa-lock"></i> Change Password
        </h2>
        
        <form method="POST" action="<?php echo BASE_URL; ?>account/change_password">
            <div class="password-fields-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label for="current_password">Current Password *</label>
                    <div class="has-toggle">
                        <input type="password" id="current_password" name="current_password" required>
                        <button type="button" class="toggle-password" onclick="togglePwd('current_password', this)" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password *</label>
                    <div class="has-toggle">
                        <input type="password" id="new_password" name="new_password" required minlength="12">
                        <button type="button" class="toggle-password" onclick="togglePwd('new_password', this)" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-reqs" id="pwd-reqs">
                        <div class="req" id="r-len"><i class="fas fa-circle"></i> At least 12 characters</div>
                        <div class="req" id="r-upper"><i class="fas fa-circle"></i> Uppercase letter (A&ndash;Z)</div>
                        <div class="req" id="r-lower"><i class="fas fa-circle"></i> Lowercase letter (a&ndash;z)</div>
                        <div class="req" id="r-num"><i class="fas fa-circle"></i> Number (0&ndash;9)</div>
                        <div class="req" id="r-special"><i class="fas fa-circle"></i> Special character (!@#$%^&amp;*)</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <div class="has-toggle">
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="12">
                        <button type="button" class="toggle-password" onclick="togglePwd('confirm_password', this)" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 mt-2">
                <i class="fas fa-key"></i> Change Password
            </button>
        </form>
    </div>
</div>

<script>
    function togglePwd(id, btn) {
        var inp = document.getElementById(id);
        if (!inp) return;

        var icon = btn ? btn.querySelector('i') : null;
        if (inp.type === 'password') {
            inp.type = 'text';
            if (icon) icon.className = 'fas fa-eye-slash';
        } else {
            inp.type = 'password';
            if (icon) icon.className = 'fas fa-eye';
        }
    }

    // Live password requirements checklist, same policy as registration.
    (function() {
        var pwd = document.getElementById('new_password');
        if (!pwd) return;

        var checks = {
            'r-len':     function(v) { return v.length >= 12; },
            'r-upper':   function(v) { return /[A-Z]/.test(v); },
            'r-lower':   function(v) { return /[a-z]/.test(v); },
            'r-num':     function(v) { return /[0-9]/.test(v); },
            'r-special': function(v) { return /[!@#$%^&*]/.test(v); }
        };

        pwd.addEventListener('input', function() {
            var val = pwd.value;
            Object.keys(checks).forEach(function(id) {
                var el = document.getElementById(id);
                if (el) el.classList.toggle('ok', checks[id](val));
            });
        });
    })();

    // Form validation for password change
    document.querySelector('form[action*="change_password"]').addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('New password and confirm password do not match!');
            return false;
        }

        if (!/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%^&*]).{12,}$/.test(newPassword)) {
            e.preventDefault();
            alert('Password must be at least 12 characters and include an uppercase letter, lowercase letter, number, and special character (!@#$%^&*).');
            return false;
        }
    });
    
    // Phone number formatting
    document.getElementById('phone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 11) {
            value = value.slice(0, 11);
        }
        e.target.value = value;
    });

    // Profile photo preview + auto-upload
    function previewAndUpload(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];

            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Please select a valid image file (JPG, PNG, GIF, or WebP)');
                input.value = '';
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2MB');
                input.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('currentPhoto').src = e.target.result;
            };
            reader.readAsDataURL(file);

            customConfirm('Upload this photo as your profile picture?', function() {
                document.getElementById('photoForm').submit();
            }, { onCancel: function() {
                input.value = '';
                location.reload();
            } });
        }
    }

    // Become a Reseller: City -> Barangay cascading dropdowns
    (function() {
        var citySelect = document.getElementById('business_city');
        var barangaySelect = document.getElementById('business_barangay');
        if (!citySelect || !barangaySelect) return;

        var ORIENTAL_MINDORO_BARANGAYS = {
            "Calapan City": ["Balingayan","Balite","Baruyan","Batino","Bayanan I","Bayanan II","Biga","Bondoc","Bucayao","Buhuan","Bulusan","Calero","Camansihan","Camilmil","Canubing I","Canubing II","Comunal","Guinobatan","Gulod","Gutad","Ibaba East","Ibaba West","Ilaya","Lalud","Lazareto","Libis","Lumangbayan","Mahal na Pangalan","Maidlang","Malad","Malamig","Managpi","Masipit","Nag-iba I","Nag-iba II","Navotas","Pachoca","Palhi","Panggalaan","Parang","Patas","Personas","Putingtubig","Salong","San Antonio","San Vicente Central","San Vicente East","San Vicente North","San Vicente South","San Vicente West","Santa Cruz","Santa Isabel","Santa Maria Village","Santa Rita","Santo Niño","Sapul","Silonay","Suqui","Tawagan","Tawiran","Tibag","Wawa"],
            "Baco": ["Alag","Bangkatan","Baras","Bayanan","Burbuli","Catwiran I","Catwiran II","Dulangan I","Dulangan II","Lantuyang","Lumangbayan","Malapad","Mangangan I","Mangangan II","Mayabig","Pambisan","Poblacion","Pulang-Tubig","Putican-Cabulo","San Andres","San Ignacio","Santa Cruz","Santa Rosa I","Santa Rosa II","Tabon-tabon","Tagumpay","Water"],
            "Bansud": ["Alcadesma","Bato","Conrazon","Malo","Manihala","Pag-asa","Poblacion","Proper Bansud","Proper Tiguisan","Rosacara","Salcedo","Sumagui","Villa Pag-asa"],
            "Bongabong": ["Anilao","Aplaya","Bagumbayan I","Bagumbayan II","Batangan","Bukal","Camantigue","Carmundo","Cawayan","Dayhagan","Formon","Hagan","Hagupit","Ipil","Kaligtasan","Labasan","Labonan","Libertad","Lisap","Luna","Malitbog","Mapang","Masaguisi","Mina de Oro","Morente","Ogbot","Orconuma","Poblacion","Polusahi","Sagana","San Isidro","San Jose","San Juan","Santa Cruz","Sigange","Tawas"],
            "Bulalacao": ["Bagong Sikat","Balatasan","Benli","Cabugao","Cambunang","Campaasan","Maasin","Maujao","Milagrosa","Nasukob","Poblacion","San Francisco","San Isidro","San Juan","San Roque"],
            "Gloria": ["Agos","Agsalin","Alma Villa","Andres Bonifacio","Balete","Banus","Banutan","Bulaklakan","Buong Lupa","Gaudencio Antonino","Guimbonan","Kawit","Lucio Laurel","Macario Adriatico","Malamig","Malayong","Maligaya","Malubay","Manguyang","Maragooc","Mirayan","Narra","Papandungin","San Antonio","Santa Maria","Santa Theresa","Tambong"],
            "Mansalay": ["B. del Mundo","Balugo","Bonbon","Budburan","Cabalwa","Don Pedro","Maliwanag","Manaul","Panaytayan","Poblacion","Roma","Santa Brigida","Santa Maria","Santa Teresita","Villa Celestial","Wasig","Waygan"],
            "Naujan": ["Adrialuna","Andres Ilagan","Antipolo","Apitong","Arangin","Aurora","Bacungan","Bagong Buhay","Balite","Bancuro","Banuton","Barcenaga","Bayani","Buhangin","Caburo","Concepcion","Curva","Dao","Del Pilar","Estrella","Evangelista","Gamao","General Esco","Herrera","Inarawan","Kalinisan","Laguna","Mabini","Magtibay","Mahabang Parang","Malaya","Malinao","Malvar","Masagana","Masaguing","Melgar A","Melgar B","Metolza","Montelago","Montemayor","Motoderazo","Mulawin","Nag-iba I","Nag-iba II","Pagkakaisa","Paitan","Paniquian","Pinagsabangan I","Pinagsabangan II","Piñahan","Poblacion I","Poblacion II","Poblacion III","Sampaguita","San Agustin I","San Agustin II","San Andres","San Antonio","San Carlos","San Isidro","San Jose","San Luis","San Nicolas","San Pedro","Santa Cruz","Santa Isabel","Santa Maria","Santiago","Santo Niño","Tagumpay","Tigkan"],
            "Pinamalayan": ["Anoling","Bacungan","Bangbang","Banilad","Buli","Cacawan","Calingag","Del Razon","Guinhawa","Inclanay","Lumangbayan","Malaya","Maliangcog","Maningcol","Marayos","Marfrancisco","Nabuslot","Pagalagala","Palayan","Pambisan Malaki","Pambisan Munti","Panggulayan","Papandayan","Pili","Quinabigan","Ranzo","Rosario","Sabang","Santa Isabel","Santa Maria","Santa Rita","Santo Niño","Wawa","Zone I","Zone II","Zone III","Zone IV"],
            "Pola": ["Bacawan","Bacungan","Batuhan","Bayanan","Biga","Buhay na Tubig","Calima","Calubasanhon","Campamento","Casiligan","Malibago","Maluanluan","Matulatula","Misong","Pahilahan","Panikihan","Pula","Puting Cacao","Tagbakin","Tagumpay","Tiguihan","Zone I","Zone II"],
            "Puerto Galera": ["Aninuan","Baclayan","Balatero","Dulangan","Palangan","Poblacion","Sabang","San Antonio","San Isidro","Santo Niño","Sinandigan","Tabinay","Villaflor"],
            "Roxas": ["Bagumbayan","Cantil","Dangay","Happy Valley","Libertad","Libtong","Little Tanauan","Mabuhay","Maraska","Odiong","Paclasan","San Aquilino","San Isidro","San Jose","San Mariano","San Miguel","San Rafael","San Vicente","Uyao","Victoria"],
            "San Teodoro": ["Bigaan","Caagutayan","Calangatan","Calsapa","Ilag","Lumangbayan","Poblacion","Tacligan"],
            "Socorro": ["Bagsok","Batong Dalig","Bayuin","Bugtong na Tuog","Calocmoy","Calubayan","Catiningan","Fortuna","Happy Valley","Leuteboro I","Leuteboro II","Ma. Concepcion","Mabuhay I","Mabuhay II","Malugay","Matungao","Monteverde","Pasi I","Pasi II","Santo Domingo","Subaan","Villareal","Zone I","Zone II","Zone III","Zone IV"],
            "Victoria": ["Alcate","Antonino","Babangonan","Bagong Buhay","Bagong Silang","Bambanin","Bethel","Canaan","Concepcion","Duongan","Jose Leido Jr.","Loyal","Mabini","Macatoc","Malabo","Merit","Ordovilla","Pakyas","Poblacion I","Poblacion II","Poblacion III","Poblacion IV","Sampaguita","San Antonio","San Cristobal","San Gabriel","San Gelacio","San Isidro","San Juan","San Narciso","Urdaneta","Villa Cerveza"]
        };

        Object.keys(ORIENTAL_MINDORO_BARANGAYS).sort().forEach(function(name) {
            var opt = document.createElement('option');
            opt.value = name;
            opt.textContent = name;
            citySelect.appendChild(opt);
        });

        function populateBarangays() {
            var barangays = ORIENTAL_MINDORO_BARANGAYS[citySelect.value] || [];
            barangaySelect.innerHTML = '';

            if (!barangays.length) {
                barangaySelect.appendChild(new Option('Select Municipality first', ''));
                barangaySelect.disabled = true;
                return;
            }

            barangaySelect.disabled = false;
            barangaySelect.appendChild(new Option('Select Barangay', ''));
            barangays.forEach(function(b) {
                barangaySelect.appendChild(new Option(b, b));
            });
        }

        citySelect.addEventListener('change', populateBarangays);
    })();
</script>
