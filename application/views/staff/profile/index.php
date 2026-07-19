<!-- Staff Profile -->
<style>
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

.has-toggle { position: relative; }
.has-toggle input { padding-right: 42px !important; }

.toggle-password {
    position: absolute;
    right: 10px;
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
.toggle-password:hover { color: var(--ds-pink, #ff69b4); }

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

input[type="password"] {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}
input[type="password"]::-ms-reveal,
input[type="password"]::-ms-clear,
input[type="password"]::-webkit-textfield-decoration-button,
input[type="password"]::-webkit-textfield-decoration-container,
input[type="password"]::-webkit-password-preview-button,
input[type="password"]::-webkit-clear-button {
    display: none !important;
    width: 0;
    height: 0;
}
</style>
<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1a1a2e;"><i class="fas fa-user-circle"></i> My Account</h4>
            <small class="text-muted">Welcome back, <strong><?php echo htmlspecialchars($user_full_name ?? 'Staff'); ?></strong> — manage your profile and password.</small>
        </div>
    </div>

    <div class="row g-4">
        <!-- Profile Photo -->
        <div class="col col-12 col-xl-4">
            <div class="card profile-card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-camera"></i> Profile Photo</h5>
                </div>
                <div class="card-body text-center">
                    <?php
                        $avatarImage = $profile['profile_image'] ?? '';
                        $avatarSrc = !empty($avatarImage)
                            ? BASE_URL . $avatarImage
                            : BASE_URL . default_avatar_url();
                    ?>
                    <div class="profile-avatar">
                        <img id="currentPhoto" src="<?php echo $avatarSrc; ?>" alt="Profile Photo" style="width:100%;height:100%;object-fit:cover;">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <h4 style="margin: 10px 0 5px 0;"><?php echo htmlspecialchars(trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? ''))); ?></h4>
                        <p style="color: #666; margin: 0;">
                            <i class="fas fa-shield-alt" style="color: var(--primary-pink);"></i>
                            Staff
                        </p>
                    </div>

                    <form method="POST" action="<?php echo site_url('staff/profile/update_photo'); ?>" enctype="multipart/form-data" id="photoForm">
                        <input type="file" id="photoInput" name="photo" accept="image/*" style="display:none;" onchange="previewAndUpload(this)">
                        <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('photoInput').click();">
                                <i class="fas fa-upload"></i> Upload Photo
                            </button>
                            <?php if (!empty($avatarImage)): ?>
                            <a href="<?php echo site_url('staff/profile/delete_photo'); ?>" class="btn btn-outline"
                               onclick="return confirmRemovePhoto(event, this, 'Remove your profile photo?');">
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
                            <li>Square images work best</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Basic Info -->
        <div class="col col-12 col-xl-8">
            <div class="card profile-card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-id-badge"></i> Personal Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo site_url('staff/profile/update'); ?>">
                        <div class="row">
                            <div class="col col-12 col-md-6">
                                <div class="form-group">
                                    <label for="first_name"><i class="fas fa-user"></i> First Name</label>
                                    <input type="text" id="first_name" name="first_name" class="form-control" required
                                           value="<?php echo htmlspecialchars($profile['first_name'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col col-12 col-md-6">
                                <div class="form-group">
                                    <label for="last_name"><i class="fas fa-user"></i> Last Name</label>
                                    <input type="text" id="last_name" name="last_name" class="form-control" required
                                           value="<?php echo htmlspecialchars($profile['last_name'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col col-12 col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-envelope"></i> Email</label>
                                    <input type="email" class="form-control" readonly
                                           value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col col-12 col-md-6">
                                <div class="form-group">
                                    <label for="contact_number"><i class="fas fa-phone"></i> Phone</label>
                                    <input type="text" id="contact_number" name="contact_number" class="form-control" required
                                           value="<?php echo htmlspecialchars($profile['contact_number'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col col-12">
            <div class="card profile-card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-lock"></i> Change Password</h5>
                </div>
                <div class="card-body">
                    <div id="passwordAlert"></div>
                    <form id="changePasswordForm">
                        <div class="row">
                            <div class="col col-12 col-md-4">
                                <div class="form-group">
                                    <label for="current_password"><i class="fas fa-key"></i> Current Password</label>
                                    <div class="has-toggle">
                                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                                        <button type="button" class="toggle-password" onclick="togglePwd('current_password', this)" tabindex="-1">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col col-12 col-md-4">
                                <div class="form-group">
                                    <label for="new_password"><i class="fas fa-lock"></i> New Password</label>
                                    <div class="has-toggle">
                                        <input type="password" id="new_password" name="new_password" class="form-control" required minlength="12">
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
                            </div>
                            <div class="col col-12 col-md-4">
                                <div class="form-group">
                                    <label for="confirm_password"><i class="fas fa-lock"></i> Confirm New Password</label>
                                    <div class="has-toggle">
                                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="12">
                                        <button type="button" class="toggle-password" onclick="togglePwd('confirm_password', this)" tabindex="-1">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group" style="margin-top: 10px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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
            const container = document.getElementById('currentPhoto').parentElement;
            container.innerHTML = '<img id="currentPhoto" src="' + e.target.result + '" alt="Profile Photo" style="width:100%;height:100%;object-fit:cover;">';
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

document.getElementById('changePasswordForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const alertBox = document.getElementById('passwordAlert');

    if (newPassword !== confirmPassword) {
        alertBox.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> New password and confirmation do not match</div>';
        return;
    }

    if (!/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%^&*]).{12,}$/.test(newPassword)) {
        alertBox.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Password must be at least 12 characters and include an uppercase letter, lowercase letter, number, and special character (!@#$%^&*).</div>';
        return;
    }

    const formData = new FormData(this);

    fetch('<?php echo site_url('staff/profile/change_password'); ?>', {
        method: 'POST',
        body: formData
    })
        .then(r => r.json())
        .then(data => {
            alertBox.innerHTML = '<div class="alert alert-' + (data.success ? 'success' : 'danger') + '"><i class="fas fa-' +
                (data.success ? 'check-circle' : 'exclamation-circle') + '"></i> ' + data.message + '</div>';
            if (data.success) {
                document.getElementById('changePasswordForm').reset();
            }
        })
        .catch(() => {
            alertBox.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Request failed</div>';
        });
});
</script>
