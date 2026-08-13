<?php
/**
 * Forgot Password View — same wine/pink palette as login.php (deep pink
 * #d6006d/#b8005c accents, hot-pink #ff69b4 interactive details, soft pink
 * page backdrop, white card).
 * File: application/views/auth/forgot_password.php
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
            --ds-pink: #d6006d;
            --ds-pink-light: #ff69b4;
            --ds-pink-dark: #b8005c;
            --ds-gradient: linear-gradient(135deg, #d6006d 0%, #b8005c 100%);
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

        .forgot-wrapper { width: 100%; max-width: 460px; }

        .forgot-container {
            position: relative;
            overflow: hidden;
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 25px 70px rgba(214, 0, 109, 0.18);
            padding: 44px 40px 38px;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Decorative soft wave glow (bottom-right) */
        .forgot-container::after {
            content: "";
            position: absolute; right: -60px; bottom: -70px; width: 220px; height: 220px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255,105,180,.16) 0%, rgba(255,105,180,0) 70%);
            z-index: 0;
            pointer-events: none;
        }
        .forgot-container > * { position: relative; z-index: 1; }

        .brand-header { text-align: center; margin-bottom: 22px; }
        .brand-logo-wrap {
            width: 130px;
            height: 130px;
            margin: 0 auto;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #fff;
            box-shadow: 0 10px 26px rgba(214, 0, 109, 0.18);
        }
        .brand-logo-img { display: block; width: 100%; height: 100%; object-fit: cover; }

        .forgot-icon-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin: 22px 0 20px;
        }
        .forgot-icon-dots {
            flex: 1;
            max-width: 70px;
            height: 6px;
            background-image: radial-gradient(var(--ds-pink-light) 1.6px, transparent 1.6px);
            background-size: 10px 6px;
            opacity: .6;
        }
        .forgot-icon {
            width: 58px;
            height: 58px;
            flex-shrink: 0;
            border-radius: 50%;
            background: var(--ds-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 22px;
            box-shadow: 0 10px 24px rgba(255, 105, 180, 0.35);
        }

        .forgot-container h2 {
            font-size: 25px;
            font-weight: 700;
            color: #24202b;
            margin-bottom: 8px;
            text-align: center;
        }
        .forgot-container h2 .accent { color: var(--ds-pink); }
        .forgot-container p.subtitle {
            font-size: 13.5px;
            color: #6b7280;
            margin-bottom: 26px;
            text-align: center;
            line-height: 1.5;
        }

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

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 7px; font-weight: 600; color: #333; font-size: 12.5px; }

        .input-group { position: relative; }
        .input-group > i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--ds-pink);
            font-size: 15px;
            pointer-events: none;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1.8px solid var(--ds-pink-light);
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--ds-pink);
            box-shadow: 0 0 0 3px rgba(255, 105, 180, 0.14);
        }

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
            margin-top: 6px;
            transition: transform 220ms ease, box-shadow 220ms ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(255, 105, 180, 0.35); }
        .btn-submit:active { transform: translateY(0); }

        .or-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 22px 0;
            color: #7a3d5c;
            font-size: 12px;
            font-weight: 600;
        }
        .or-divider::before,
        .or-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: rgba(122, 61, 92, 0.35);
        }

        .btn-back {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px 20px;
            border-radius: 10px;
            border: 1.8px solid var(--ds-pink);
            background: #fff;
            color: var(--ds-pink-dark);
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            transition: background-color 0.2s, color 0.2s;
        }
        .btn-back:hover { background: var(--ds-pink); color: #fff; }

        @media (max-width: 480px) {
            .forgot-container { padding: 34px 24px; }
            .brand-logo-wrap { width: 110px; height: 110px; }
        }
    </style>
</head>
<body>
    <div class="forgot-wrapper">
        <div class="forgot-container">
            <div class="brand-header">
                <div class="brand-logo-wrap">
                    <img class="brand-logo-img" src="<?php echo base_url('public/uploads/avatars/c6e87fc1363436e5468a05c9c2a59b26.webp'); ?>" alt="Dhendhen Beauty Products and Boutique">
                </div>
            </div>

            <div class="forgot-icon-row">
                <span class="forgot-icon-dots"></span>
                <div class="forgot-icon"><i class="fas fa-key"></i></div>
                <span class="forgot-icon-dots"></span>
            </div>

            <h2>Forgot <span class="accent">Password?</span></h2>
            <p class="subtitle">No worries. Enter your email address and we'll send you a verification code to reset it.</p>

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

            <form method="POST" action="<?php echo site_url('auth/send_reset_link'); ?>" novalidate>
                <?php echo csrf_field(); ?>

                <div class="form-group">
                    <label>Email Address</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="your@email.com" required autofocus value="<?php echo htmlspecialchars($this->input->post('email', TRUE) ?: ($prefill_email ?? '')); ?>">
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Send Verification Code
                </button>
            </form>

            <div class="or-divider">OR</div>

            <a href="<?php echo site_url('auth/login'); ?>" class="btn-back">Sign In</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                if (alert.classList.contains('alert-success')) {
                    setTimeout(function() { alert.style.display = 'none'; }, 8000);
                }
            });
        });
    </script>
</body>
</html>
