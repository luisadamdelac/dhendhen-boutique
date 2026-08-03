<!-- User Profile -->
<style>
.has-toggle {
    position: relative;
}
.has-toggle input {
    padding-right: 44px !important;
}
/* Hide Edge/IE's built-in password reveal icon so it doesn't double up
   with our own toggle button. */
.has-toggle input::-ms-reveal,
.has-toggle input::-ms-clear {
    display: none;
}
.toggle-password {
    position: absolute;
    right: 4px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #999;
    font-size: 15px;
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}
.toggle-password:hover {
    color: var(--primary-pink, #EC4899);
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
.profile-card {
    border-radius: 14px;
    border: 1px solid #eef0f8;
    box-shadow: 0 1px 10px rgba(15, 23, 42, .06);
}
.profile-card .card-header {
    background: #fff;
    border-bottom: 1px solid #f1f3fb;
    padding: 1.25rem 1.5rem;
}
.profile-card .card-body {
    background: #fff;
    padding: 1.5rem;
}
.profile-avatar {
    width: 200px;
    height: 200px;
    margin: 0 auto 20px;
    border-radius: 50%;
    overflow: hidden;
    border: 5px solid #f9e5ff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}
.profile-summary {
    background: #f8f9ff;
    border-radius: 14px;
    padding: 18px;
    margin-top: 20px;
}
.profile-summary h6 {
    margin-bottom: 12px;
    color: #d946ef;
}
.profile-summary ul {
    margin: 0;
    padding-left: 20px;
    color: #475569;
    font-size: 13px;
}

@media (max-width: 576px) {
    .profile-card .card-header {
        padding: 1rem 1.25rem;
    }
    .profile-card .card-body {
        padding: 1.25rem;
    }
    .profile-avatar {
        width: 130px;
        height: 130px;
        border-width: 3px;
        margin-bottom: 14px;
    }
    .profile-form-actions {
        flex-direction: column;
        align-items: stretch;
    }
    .profile-form-actions .btn {
        width: 100%;
    }
}

/* Profile Photo + Profile Information side-by-side on desktop — built as a
   plain flex row instead of Bootstrap's .row/.col-xl-4/.col-xl-8 so both
   cards share the exact same edges on mobile (no row negative-margin +
   col gutter-padding math to keep in sync with the rest of the page). */
.profile-top-grid {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
@media (min-width: 1200px) {
    .profile-top-grid {
        flex-direction: row;
        align-items: flex-start;
    }
    .profile-top-grid > .profile-photo-col {
        flex: 0 0 33.3333%;
        max-width: 33.3333%;
    }
    .profile-top-grid > .profile-info-col {
        flex: 1 1 0;
        min-width: 0;
    }
}
</style>
<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1a1a2e;"><i class="fas fa-user-circle"></i> My Profile</h4>
            <small class="text-muted">Manage your account settings and profile information.</small>
        </div>
    </div>

    <div class="profile-top-grid">
        <!-- Profile Photo Card -->
        <div class="profile-photo-col">
            <div class="card profile-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-camera"></i> Profile Photo
                    </h5>
                </div>
                <div class="card-body text-center">
                    <!-- Current Photo -->
                    <div style="margin-bottom: 20px;">
                        <?php 
                            $currentPhoto = !empty($user['profile_image'])
                                ? BASE_URL . $user['profile_image']
                                : BASE_URL . default_avatar_url();
                        ?>
                        <div class="profile-avatar">
                            <img id="currentPhoto" src="<?php echo $currentPhoto; ?>" alt="Profile Photo" 
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <h4 style="margin: 10px 0 5px 0;"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                        <p style="color: #666; margin: 0;">
                            <i class="fas fa-shield-alt" style="color: var(--primary-pink);"></i>
                            <?php echo ucfirst($user['role']); ?>
                        </p>
                    </div>
                    
                    <!-- Photo Upload Form -->
                    <form method="POST" action="<?php echo site_url('admin/profile/update_photo'); ?>" enctype="multipart/form-data" id="photoForm">
                        <input type="file" id="photoInput" name="photo" accept="image/*" style="display: none;" onchange="previewAndUpload(this)">

                        <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('photoInput').click();">
                                <i class="fas fa-upload"></i> Upload Photo
                            </button>

                            <?php if (!empty($user['profile_image'])): ?>
                            <a href="<?php echo site_url('admin/profile/delete_photo'); ?>"
                               class="btn btn-outline"
                               onclick="return confirmRemovePhoto(event, this, 'Are you sure you want to remove your profile photo?');">
                                <i class="fas fa-trash"></i> Remove
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                    
                    <div style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; text-align: left;">
                        <h6 style="margin: 0 0 10px 0; color: var(--primary-pink);">
                            <i class="fas fa-info-circle"></i> Photo Requirements
                        </h6>
                        <ul style="margin: 0; padding-left: 20px; font-size: 13px; color: #666;">
                            <li>Maximum file size: 2MB</li>
                            <li>Supported formats: JPG, PNG, GIF, WebP</li>
                            <li>Recommended size: 200x200px</li>
                            <li>Square images work best</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Information Card -->
        <div class="profile-info-col">
            <div class="card profile-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-user-edit"></i> Profile Information
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo site_url('admin/profile/update'); ?>" id="profileForm">
                        <div class="row">
                            <!-- Last Name -->
                            <div class="col col-4">
                                <div class="form-group">
                                    <label for="last_name">
                                        <i class="fas fa-user"></i> Last Name <span style="color: red;">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <!-- First Name -->
                            <div class="col col-4">
                                <div class="form-group">
                                    <label for="first_name">
                                        <i class="fas fa-user"></i> First Name <span style="color: red;">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <!-- Middle Name -->
                            <div class="col col-4">
                                <div class="form-group">
                                    <label for="middle_name">
                                        <i class="fas fa-user"></i> Middle Name
                                    </label>
                                    <input type="text" class="form-control" id="middle_name" name="middle_name" 
                                           value="<?php echo htmlspecialchars($user['middle_name'] ?? ''); ?>">
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="email">
                                        <i class="fas fa-envelope"></i> Email <span style="color: red;">*</span>
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="phone">
                                        <i class="fas fa-phone"></i> Phone Number
                                    </label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                           placeholder="09XXXXXXXXX">
                                </div>
                            </div>

                            <!-- Role (Read-only) -->
                            <div class="col col-6">
                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-shield-alt"></i> Role
                                    </label>
                                    <input type="text" class="form-control" 
                                           value="<?php echo ucfirst($user['role']); ?>" readonly 
                                           style="background: #f8f9fa;">
                                </div>
                            </div>

                            <!-- Account Status (Read-only) -->
                            <div class="col col-6">
                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-circle"></i> Status
                                    </label>
                                    <input type="text" class="form-control" 
                                           value="<?php echo ucfirst($user['status']); ?>" readonly 
                                           style="background: #f8f9fa;">
                                </div>
                            </div>

                            <!-- Member Since (Read-only) -->
                            <div class="col col-6">
                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-calendar-alt"></i> Member Since
                                    </label>
                                    <input type="text" class="form-control" 
                                           value="<?php echo date('M d, Y', strtotime($user['created_at'])); ?>" readonly 
                                           style="background: #f8f9fa;">
                                </div>
                            </div>
                        </div>

                        <hr style="margin: 30px 0; border-color: #eee;">

                        <h5 style="margin-bottom: 20px; color: var(--primary-pink);">
                            <i class="fas fa-lock"></i> Change Password
                        </h5>
                        <p style="color: #666; font-size: 14px; margin-bottom: 20px;">
                            Leave blank if you don't want to change your password
                        </p>

                        <div class="row">
                            <!-- Current Password -->
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="current_password">
                                        <i class="fas fa-lock"></i> Current Password
                                    </label>
                                    <div class="has-toggle">
                                        <input type="password" class="form-control" id="current_password" name="current_password"
                                               placeholder="Enter your current password">
                                        <button type="button" class="toggle-password" onclick="togglePassword('current_password')" tabindex="-1">
                                            <i class="fas fa-eye" id="current_password_icon"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Required to change password</small>
                                </div>
                            </div>

                            <!-- New Password -->
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="new_password">
                                        <i class="fas fa-key"></i> New Password
                                    </label>
                                    <div class="has-toggle">
                                        <input type="password" class="form-control" id="new_password" name="new_password"
                                               placeholder="Enter new password" minlength="12">
                                        <button type="button" class="toggle-password" onclick="togglePassword('new_password')" tabindex="-1">
                                            <i class="fas fa-eye" id="new_password_icon"></i>
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
                            </div>

                            <!-- Confirm Password -->
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="confirm_password">
                                        <i class="fas fa-lock"></i> Confirm Password
                                    </label>
                                    <div class="has-toggle">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                               placeholder="Confirm new password">
                                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')" tabindex="-1">
                                            <i class="fas fa-eye" id="confirm_password_icon"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-group profile-form-actions" style="margin-top: 30px; display: flex; flex-wrap: wrap; gap: 10px;">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                            <a href="<?php echo site_url('admin/dashboard'); ?>" class="btn btn-outline btn-lg">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
// Preview and auto-upload photo
function previewAndUpload(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Please select a valid image file (JPG, PNG, GIF, or WebP)');
            input.value = '';
            return;
        }
        
        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB');
            input.value = '';
            return;
        }
        
        // Preview image
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('currentPhoto').src = e.target.result;
        }
        reader.readAsDataURL(file);
        
        // Auto-submit form
        customConfirm('Upload this photo as your profile picture?', function() {
            document.getElementById('photoForm').submit();
        }, { onCancel: function() {
            input.value = '';
            // Reload current photo
            location.reload();
        } });
    }
}

// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
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

// Form validation
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (newPassword || confirmPassword) {
        if (!/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%^&*]).{12,}$/.test(newPassword)) {
            e.preventDefault();
            alert('Password must be at least 12 characters and include an uppercase letter, lowercase letter, number, and special character (!@#$%^&*).');
            return false;
        }

        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match');
            return false;
        }
    }

    return true;
});
</script>

<style>
.form-control[readonly] {
    cursor: not-allowed;
    opacity: 0.7;
}

.card-body ul li {
    margin-bottom: 5px;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>
