<?php
/**
 * Verify OTP View — same brand style as forgot_password.php / login.php.
 * File: application/views/auth/verify_otp.php
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

        .forgot-wrapper { width: 100%; max-width: 440px; }

        .forgot-container {
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

        .forgot-icon {
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

        .forgot-container h2 {
            font-size: 24px;
            font-weight: 700;
            color: #24202b;
            margin-bottom: 8px;
            text-align: center;
        }
        .forgot-container p.subtitle {
            font-size: 13.5px;
            color: #6b7280;
            margin-bottom: 26px;
            text-align: center;
            line-height: 1.5;
        }
        .forgot-container p.subtitle strong { color: #24202b; }

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
            border: 1.8px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: #ff6ea5;
            box-shadow: 0 0 0 3px rgba(255, 105, 180, 0.14);
        }

        #otp_code {
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 10px;
            padding-left: 15px;
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
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(255, 105, 180, 0.3); }
        .btn-submit:active { transform: translateY(0); }

        .back-link { text-align: center; margin-top: 22px; display: flex; flex-direction: column; gap: 10px; }
        .back-link a {
            color: var(--ds-pink-dark);
            text-decoration: none;
            font-weight: 600;
            font-size: 13.5px;
            transition: color 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .back-link a:hover { color: var(--ds-pink-light); }

        @media (max-width: 480px) {
            .forgot-container { padding: 34px 24px; }
        }
    </style>
</head>
<body>
    <div class="forgot-wrapper">
        <div class="forgot-container">
            <div class="brand-header">
                <div class="brand-logo-wrap">
                    <img class="brand-logo-img" src="<?php echo base_url('public/uploads/avatars/c6e87fc1363436e5468a05c9c2a59b26.png'); ?>" alt="Dhendhen Beauty Products and Boutique">
                </div>
                <div class="brand-name">DropSell</div>
            </div>

            <div class="forgot-icon"><i class="fas fa-shield-halved"></i></div>

            <h2>Enter Verification Code</h2>
            <p class="subtitle">We sent a 6-digit code to <strong><?php echo htmlspecialchars($email); ?></strong>. Enter it below to continue.</p>

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

            <form method="POST" action="<?php echo site_url('auth/do_verify_otp'); ?>" novalidate>
                <?php echo csrf_field(); ?>
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">

                <div class="form-group">
                    <label>Verification Code</label>
                    <input type="text" name="otp_code" id="otp_code" placeholder="••••••" maxlength="6" inputmode="numeric" pattern="\d{6}" required autofocus>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-check"></i> Verify Code
                </button>
            </form>

            <div class="back-link">
                <a href="<?php echo site_url('auth/forgot_password') . '?email=' . urlencode($email); ?>"><i class="fas fa-rotate"></i> Resend Code</a>
                <a href="<?php echo site_url('auth/login'); ?>"><i class="fas fa-arrow-left"></i> Back to Login</a>
            </div>
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
