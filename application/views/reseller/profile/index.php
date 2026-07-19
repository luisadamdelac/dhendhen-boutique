<div class="profile-container">
    <div class="profile-main">
        <!-- Profile Header -->
        <div class="card profile-header-card">
            <div class="profile-header-content">
                <div class="profile-avatar-large">
                    <?php
                        $avatarImage = $user['profile_image'] ?? '';
                        $avatarSrc = !empty($avatarImage)
                            ? BASE_URL . $avatarImage
                            : BASE_URL . default_avatar_url();
                    ?>
                    <img id="currentPhoto" src="<?php echo $avatarSrc; ?>" alt="Profile Photo"
                         style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                    <p style="color: var(--gray-600); margin-top: 0.25rem;">
                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                    </p>
                    <div style="margin-top: 0.75rem;">
                        <span class="badge badge-<?php
                            echo match($reseller['approval_status']) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'secondary'
                            };
                        ?>" style="font-size: 0.875rem; padding: 0.4rem 0.75rem;">
                            <i class="fas fa-<?php echo $reseller['approval_status'] === 'approved' ? 'check-circle' : ($reseller['approval_status'] === 'pending' ? 'clock' : 'times-circle'); ?>"></i>
                            <?php echo ucfirst($reseller['approval_status']); ?>
                        </span>
                        <span style="margin-left: 1rem; color: var(--gray-600);">
                            <i class="fas fa-calendar"></i> Member since <?php echo date('M Y', strtotime($user['created_at'])); ?>
                        </span>
                    </div>
                    <form method="POST" action="<?php echo BASE_URL; ?>reseller/profile/update_photo" enctype="multipart/form-data" id="photoForm">
                        <input type="file" id="photoInput" name="photo" accept="image/*" style="display: none;" onchange="previewAndUpload(this)">
                        <div style="margin-top: 0.75rem; display: flex; gap: 10px;">
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('photoInput').click();">
                                <i class="fas fa-upload"></i> Upload Photo
                            </button>
                            <?php if (!empty($avatarImage)): ?>
                            <a href="<?php echo BASE_URL; ?>reseller/profile/delete_photo" class="btn btn-sm btn-outline"
                               onclick="return confirmRemovePhoto(event, this, 'Remove your profile photo?');">
                                <i class="fas fa-trash"></i> Remove
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-user-edit"></i> Edit Profile Information</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>reseller/profile/update">
                    <div class="form-section">
                        <h4 class="section-title"><i class="fas fa-user"></i> Personal Information</h4>
                        
                        <div class="form-row-grid">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> First Name *</label>
                                <input type="text" name="first_name" class="form-control"
                                       value="<?php echo htmlspecialchars($reseller['first_name'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Middle Name</label>
                                <input type="text" name="middle_name" class="form-control"
                                       value="<?php echo htmlspecialchars($reseller['middle_name'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Last Name *</label>
                                <input type="text" name="last_name" class="form-control"
                                       value="<?php echo htmlspecialchars($reseller['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="form-row-grid">
                            <div class="form-group">
                                <label><i class="fas fa-envelope"></i> Email Address</label>
                                <input type="email" class="form-control"
                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                                       disabled style="background: var(--gray-100); cursor: not-allowed;">
                                <small style="color: var(--gray-600);">Email cannot be changed</small>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-phone"></i> Phone Number *</label>
                                <input type="text" name="phone" class="form-control"
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                       placeholder="09XXXXXXXXX" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4 class="section-title"><i class="fas fa-briefcase"></i> Business Information</h4>

                        <div class="form-group">
                            <label><i class="fas fa-store"></i> Business Name *</label>
                            <input type="text" name="business_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($reseller['business_name'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-map-marked-alt"></i> Business Address</label>
                            <textarea name="business_address" class="form-control" rows="3" 
                                      placeholder="Complete business address..."><?php echo htmlspecialchars($reseller['business_address'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4 class="section-title"><i class="fas fa-wallet"></i> GCash Information (For Payouts)</h4>

                        <div class="form-row-grid">
                            <div class="form-group">
                                <label><i class="fas fa-mobile-alt"></i> GCash Number *</label>
                                <input type="text" name="gcash_number" class="form-control" 
                                       value="<?php echo htmlspecialchars($reseller['gcash_number'] ?? ''); ?>" 
                                       placeholder="09XXXXXXXXX" required>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-id-card"></i> GCash Registered Name</label>
                                <input type="text" name="gcash_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($reseller['gcash_name'] ?? ''); ?>"
                                       placeholder="Name as registered in GCash">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="profile-sidebar">
        <!-- Change Password -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-key"></i> Change Password</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>reseller/profile/change_password" id="resellerChangePasswordForm">
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Current Password</label>
                        <input type="password" name="current_password" class="form-control" 
                               placeholder="Enter current password" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control"
                               placeholder="Enter new password" required minlength="12">
                        <div class="password-reqs" id="pwd-reqs">
                            <div class="req" id="r-len"><i class="fas fa-circle"></i> At least 12 characters</div>
                            <div class="req" id="r-upper"><i class="fas fa-circle"></i> Uppercase letter (A&ndash;Z)</div>
                            <div class="req" id="r-lower"><i class="fas fa-circle"></i> Lowercase letter (a&ndash;z)</div>
                            <div class="req" id="r-num"><i class="fas fa-circle"></i> Number (0&ndash;9)</div>
                            <div class="req" id="r-special"><i class="fas fa-circle"></i> Special character (!@#$%^&amp;*)</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                               placeholder="Confirm new password" required minlength="12">
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="resellerChangePasswordBtn">
                        <i class="fas fa-check"></i> Update Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Account Info -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-info-circle"></i> Account Details</h3>
            </div>
            <div class="card-body">
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-hashtag"></i> Reseller ID</span>
                    <span class="info-value">#<?php echo str_pad($reseller['reseller_id'], 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-calendar-check"></i> Registered</span>
                    <span class="info-value"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-sign-in-alt"></i> Last Login</span>
                    <span class="info-value"><?php echo $user['last_login'] ? date('M d, Y h:i A', strtotime($user['last_login'])) : 'Never'; ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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

.profile-container {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 1.5rem;
}

.profile-header-card {
    margin-bottom: 1.5rem;
}

.profile-header-content {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.5rem;
}

.profile-avatar-large {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid var(--primary-pink-light, #fce4ec);
    flex-shrink: 0;
}

.profile-info h2 {
    margin: 0;
    font-size: 1.75rem;
    color: var(--gray-800);
}

.form-section {
    padding: 1.5rem 0;
    border-bottom: 1px solid var(--gray-200);
}

.form-section:last-child {
    border-bottom: none;
}

.section-title {
    color: var(--primary-pink);
    font-size: 1.125rem;
    margin-bottom: 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-row-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    color: var(--gray-700);
    margin-bottom: 0.5rem;
}

.form-group label i {
    color: var(--primary-pink);
    font-size: 0.875rem;
}

.form-actions {
    padding-top: 1.5rem;
    display: flex;
    gap: 1rem;
}

/* Stats Card */
.stats-card .card-body {
    padding: 1rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--gray-50);
    border-radius: 0.5rem;
    margin-bottom: 0.75rem;
}

.stat-item:last-child {
    margin-bottom: 0;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.stat-details {
    display: flex;
    flex-direction: column;
}

.stat-label {
    font-size: 0.75rem;
    color: var(--gray-600);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value {
    font-size: 1.125rem;
    font-weight: bold;
    color: var(--gray-800);
    margin-top: 0.25rem;
}

/* Info items */
.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--gray-200);
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-size: 0.875rem;
    color: var(--gray-600);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-label i {
    color: var(--primary-pink);
    width: 16px;
}

.info-value {
    font-weight: 600;
    color: var(--gray-800);
}

@media (max-width: 1024px) {
    .profile-container {
        grid-template-columns: 1fr;
    }
    
    .form-row-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .profile-header-content {
        flex-direction: column;
        text-align: center;
    }
    
    .profile-info {
        width: 100%;
    }
}
</style>

<script>
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

document.getElementById('resellerChangePasswordForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (!/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%^&*]).{12,}$/.test(newPassword)) {
        e.preventDefault();
        alert('Password must be at least 12 characters and include an uppercase letter, lowercase letter, number, and special character (!@#$%^&*).');
        return false;
    }

    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('New password and confirmation do not match');
        return false;
    }
});
</script>
