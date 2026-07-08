<!-- Staff Profile -->
<div class="fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">My Account</h4>
            <small class="text-muted">Welcome back, <strong><?php echo htmlspecialchars($user_full_name ?? 'Staff'); ?></strong> — manage your profile and password.</small>
        </div>
    </div>

    <div class="row">
        <!-- Profile Photo -->
        <div class="col col-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-camera"></i> Profile Photo</h3>
                </div>
                <div class="card-body text-center">
                    <?php
                        $avatarImage = $profile['profile_image'] ?? '';
                        $avatarSrc = !empty($avatarImage)
                            ? BASE_URL . $avatarImage
                            : BASE_URL . default_avatar_url();
                    ?>
                    <div style="width:140px;height:140px;margin:0 auto 16px;border-radius:50%;overflow:hidden;border:4px solid var(--primary-pink-light);">
                        <img id="currentPhoto" src="<?php echo $avatarSrc; ?>" alt="Profile Photo" style="width:100%;height:100%;object-fit:cover;">
                    </div>
                    <form method="POST" action="<?php echo site_url('staff/profile/update_photo'); ?>" enctype="multipart/form-data" id="photoForm">
                        <input type="file" id="photoInput" name="photo" accept="image/*" style="display:none;" onchange="previewAndUpload(this)">
                        <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('photoInput').click();">
                                <i class="fas fa-upload"></i> Upload Photo
                            </button>
                            <?php if (!empty($avatarImage)): ?>
                            <a href="<?php echo site_url('staff/profile/delete_photo'); ?>" class="btn btn-outline"
                               onclick="return confirm('Remove your profile photo?');">
                                <i class="fas fa-trash"></i> Remove
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Basic Info -->
        <div class="col col-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-id-badge"></i> Personal Information</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo site_url('staff/profile/update'); ?>">
                        <div class="row">
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="first_name"><i class="fas fa-user"></i> First Name</label>
                                    <input type="text" id="first_name" name="first_name" class="form-control" required
                                           value="<?php echo htmlspecialchars($profile['first_name'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="last_name"><i class="fas fa-user"></i> Last Name</label>
                                    <input type="text" id="last_name" name="last_name" class="form-control" required
                                           value="<?php echo htmlspecialchars($profile['last_name'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label><i class="fas fa-envelope"></i> Email</label>
                                    <input type="email" class="form-control" readonly
                                           value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col col-6">
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

    <div class="row">
        <div class="col col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-lock"></i> Change Password</h3>
                </div>
                <div class="card-body">
                    <div id="passwordAlert"></div>
                    <form id="changePasswordForm">
                        <div class="row">
                            <div class="col col-4">
                                <div class="form-group">
                                    <label for="current_password"><i class="fas fa-key"></i> Current Password</label>
                                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                                </div>
                            </div>
                            <div class="col col-4">
                                <div class="form-group">
                                    <label for="new_password"><i class="fas fa-lock"></i> New Password</label>
                                    <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
                                </div>
                            </div>
                            <div class="col col-4">
                                <div class="form-group">
                                    <label for="confirm_password"><i class="fas fa-lock"></i> Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
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
function previewAndUpload(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];

        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('Please select a valid image file (JPG, PNG, or GIF)');
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

        if (confirm('Upload this photo as your profile picture?')) {
            document.getElementById('photoForm').submit();
        } else {
            input.value = '';
            location.reload();
        }
    }
}

document.getElementById('changePasswordForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const alertBox = document.getElementById('passwordAlert');

    if (newPassword !== confirmPassword) {
        alertBox.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> New password and confirmation do not match</div>';
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
