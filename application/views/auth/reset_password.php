<?php
/**
 * Reset Password View — restyled to match the DropSell brand used on
 * login.php / register.php. The live requirement checklist below mirrors
 * exactly what Auth::do_reset() validates server-side (min 12 characters,
 * upper, lower, number, special character — same rule used by registration's
 * password field).
 * File: application/views/auth/reset_password.php
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛍️</text></svg>">

    <link rel="stylesheet" href="<?php echo base_url('public/vendor/poppins/poppins.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('public/vendor/fontawesome/css/all.min.css'); ?>">

    <style>
        :root {
            --ds-pink: #ff69b4;
            --ds-pink-dark: #d6006d;
            --ds-pink-light: #ff8cc5;
            --ds-purple: #b8005c;
            --ds-gradient: linear-gradient(135deg, var(--ds-pink-dark) 0%, var(--ds-purple) 100%);
            --ds-success: #22c55e;
            --ds-danger: #e53935;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: radial-gradient(circle at 50% 35%, #fdeef5 0%, #fbd9ea 55%, #f7c6de 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .reset-wrapper { width: 100%; max-width: 460px; }

        .reset-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 24px 60px rgba(133, 49, 122, 0.18);
            padding: 44px 40px;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .brand-header { text-align: center; margin-bottom: 26px; }
        .brand-logo-wrap {
            width: 90px;
            height: 90px;
            margin: 0 auto 12px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #fff;
            box-shadow: 0 10px 26px rgba(214, 0, 109, 0.28);
        }
        .brand-logo-img { display: block; width: 100%; height: 100%; object-fit: cover; }
        .brand-name { font-size: 22px; font-weight: 700; color: #24202b; }

        .reset-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 18px;
            border-radius: 50%;
            background: var(--ds-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 24px;
            box-shadow: 0 10px 24px rgba(255, 105, 180, 0.3);
        }

        .reset-container h2 { font-size: 24px; font-weight: 700; color: #24202b; margin-bottom: 8px; text-align: center; }
        .reset-container p.subtitle { font-size: 13.5px; color: #6b7280; margin-bottom: 26px; text-align: center; line-height: 1.5; }

        .alert {
            padding: 13px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            animation: fadeIn 300ms ease;
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .alert-danger { background: #fff2f4; color: #c72a3c; border: 1px solid #ffc8d8; }
        .alert-success { background: #f0fdf4; color: #1f8554; border: 1px solid #bbf7d0; }

        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 7px; font-weight: 600; color: #333; font-size: 12.5px; }

        .input-group { position: relative; }
        .input-group input {
            width: 100%;
            padding: 12px 42px 12px 15px;
            border: 1.8px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
        }
        .input-group input:focus {
            outline: none;
            border-color: #ff6ea5;
            box-shadow: 0 0 0 3px rgba(255, 105, 180, 0.14);
        }

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

        .password-toggle {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            background: none;
            border: none;
            font-size: 15px;
            padding: 4px;
            line-height: 1;
        }
        .password-toggle:hover { color: var(--ds-pink); }

        /* ---- Live strength meter ---- */
        .strength-meter { margin-top: 10px; }
        .strength-bars { display: flex; gap: 5px; margin-bottom: 8px; }
        .strength-bars span { flex: 1; height: 5px; border-radius: 3px; background: #e5e7eb; transition: background 0.3s ease; }
        .strength-label { font-size: 11.5px; font-weight: 600; color: #6b7280; }

        .password-requirements {
            background: #fff5f8;
            border: 1px solid #ffccdc;
            border-radius: 10px;
            padding: 12px 14px;
            margin-top: 10px;
        }
        .password-requirements .req {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11.5px;
            color: #8b8b8b;
            margin-bottom: 5px;
            transition: color 0.2s;
        }
        .password-requirements .req:last-child { margin-bottom: 0; }
        .password-requirements .req i { font-size: 8px; }
        .password-requirements .req.ok { color: var(--ds-success); }
        .password-requirements .req.ok i::before { content: "\f00c"; font-size: 10px; }

        .match-message {
            font-size: 11.5px;
            margin-top: 6px;
            display: none;
            align-items: center;
            gap: 5px;
        }
        .match-message.show-error { display: flex; color: var(--ds-danger); }
        .match-message.show-ok { display: flex; color: var(--ds-success); }

        .btn-submit {
            background: var(--ds-gradient);
            border: none;
            color: white;
            padding: 13px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            width: 100%;
            cursor: pointer;
            margin-top: 8px;
            transition: transform 220ms ease, box-shadow 220ms ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(255, 105, 180, 0.3); }
        .btn-submit:active { transform: translateY(0); }

        .back-link { text-align: center; margin-top: 20px; }
        .back-link a {
            color: var(--ds-pink-dark);
            text-decoration: none;
            font-weight: 600;
            font-size: 13.5px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .back-link a:hover { color: var(--ds-pink-light); }

        @media (max-width: 480px) {
            .reset-container { padding: 34px 24px; }
        }
    </style>
</head>
<body>
    <div class="reset-wrapper">
        <div class="reset-container">
            <div class="brand-header">
                <div class="brand-logo-wrap">
                    <img class="brand-logo-img" src="<?php echo base_url('public/uploads/avatars/c6e87fc1363436e5468a05c9c2a59b26.png'); ?>" alt="Dhendhen Beauty Products and Boutique">
                </div>
                <div class="brand-name">DropSell</div>
            </div>

            <div class="reset-icon"><i class="fas fa-lock"></i></div>

            <h2>Create New Password</h2>
            <p class="subtitle">Choose a new password for your account below.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo site_url('auth/do_reset'); ?>" novalidate>
                <?php echo csrf_field(); ?>
                <?php echo form_hidden('token', $token); ?>

                <div class="form-group">
                    <label>New Password</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" placeholder="Enter new password" required autofocus autocomplete="new-password">
                        <button type="button" class="password-toggle" onclick="togglePwd('password', this)" tabindex="-1" aria-label="Show password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <div class="strength-meter" aria-hidden="true">
                        <div class="strength-bars">
                            <span id="bar-1"></span><span id="bar-2"></span><span id="bar-3"></span><span id="bar-4"></span><span id="bar-5"></span>
                        </div>
                        <div class="strength-label" id="strengthLabel">Password strength</div>
                    </div>

                    <div class="password-requirements">
                        <div class="req" id="r-len"><i class="fas fa-circle"></i> At least 12 characters</div>
                        <div class="req" id="r-upper"><i class="fas fa-circle"></i> Uppercase letter (A–Z)</div>
                        <div class="req" id="r-lower"><i class="fas fa-circle"></i> Lowercase letter (a–z)</div>
                        <div class="req" id="r-num"><i class="fas fa-circle"></i> Number (0–9)</div>
                        <div class="req" id="r-special"><i class="fas fa-circle"></i> Special character (!@#$%^&amp;*)</div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <div class="input-group">
                        <input type="password" id="password_confirm" name="password_confirm" placeholder="Confirm new password" required autocomplete="new-password">
                        <button type="button" class="password-toggle" onclick="togglePwd('password_confirm', this)" tabindex="-1" aria-label="Show password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="match-message" id="matchError"><i class="fas fa-circle-exclamation"></i> Passwords do not match.</div>
                    <div class="match-message" id="matchOk"><i class="fas fa-check-circle"></i> Passwords match.</div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-check"></i> Update Password
                </button>
            </form>

            <div class="back-link">
                <a href="<?php echo site_url('auth/login'); ?>"><i class="fas fa-arrow-left"></i> Back to Login</a>
            </div>
        </div>
    </div>

    <script>
        function togglePwd(id, btn) {
            var inp = document.getElementById(id);
            var icon = btn.querySelector('i');
            if (inp.type === 'password') {
                inp.type = 'text';
                icon.className = 'fas fa-eye-slash';
                btn.setAttribute('aria-label', 'Hide password');
            } else {
                inp.type = 'password';
                icon.className = 'fas fa-eye';
                btn.setAttribute('aria-label', 'Show password');
            }
        }

        (function() {
            var pwd = document.getElementById('password');
            var confirmPwd = document.getElementById('password_confirm');
            var matchError = document.getElementById('matchError');
            var matchOk = document.getElementById('matchOk');

            var checks = {
                'r-len':     function(v) { return v.length >= 12; },
                'r-upper':   function(v) { return /[A-Z]/.test(v); },
                'r-lower':   function(v) { return /[a-z]/.test(v); },
                'r-num':     function(v) { return /[0-9]/.test(v); },
                'r-special': function(v) { return /[!@#$%^&*]/.test(v); }
            };

            var bars = ['bar-1', 'bar-2', 'bar-3', 'bar-4', 'bar-5'].map(function(id) { return document.getElementById(id); });
            var strengthLabel = document.getElementById('strengthLabel');
            var strengthColors = ['#e53935', '#fb8c00', '#fdd835', '#7cb342', '#22c55e'];
            var strengthText = ['Weak', 'Weak', 'Fair', 'Good', 'Strong'];

            function evaluatePassword() {
                var val = pwd.value;
                var passCount = 0;

                Object.keys(checks).forEach(function(id) {
                    var ok = checks[id](val);
                    var el = document.getElementById(id);
                    if (el) el.classList.toggle('ok', ok);
                    if (ok) passCount++;
                });

                bars.forEach(function(bar, i) {
                    bar.style.background = (val.length > 0 && i < passCount) ? strengthColors[Math.max(passCount - 1, 0)] : '';
                });

                strengthLabel.textContent = val.length === 0 ? 'Password strength' : strengthText[Math.max(passCount - 1, 0)];
                strengthLabel.style.color = val.length === 0 ? '' : strengthColors[Math.max(passCount - 1, 0)];

                evaluateMatch();
            }

            function evaluateMatch() {
                if (confirmPwd.value === '') {
                    matchError.classList.remove('show-error');
                    matchOk.classList.remove('show-ok');
                    return;
                }
                var matches = confirmPwd.value === pwd.value;
                matchError.classList.toggle('show-error', !matches);
                matchOk.classList.toggle('show-ok', matches);
            }

            pwd.addEventListener('input', evaluatePassword);
            confirmPwd.addEventListener('input', evaluateMatch);
        })();
    </script>
</body>
</html>
