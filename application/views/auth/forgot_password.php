<?php
/**
 * Forgot Password View — restyled to match the DropSell brand used on
 * login.php / register.php (same gradient, fonts, card, and button style).
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

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --ds-pink: #ff69b4;
            --ds-pink-dark: #e0559c;
            --ds-violet: #ee82ee;
            --ds-purple: #9370db;
            --ds-gradient: linear-gradient(135deg, var(--ds-pink) 0%, var(--ds-violet) 50%, var(--ds-purple) 100%);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #fce4ec 0%, #f3e5f5 50%, #e1bee7 100%);
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
        .brand-logo {
            font-size: 44px;
            margin-bottom: 8px;
            display: inline-block;
            animation: pulse 2.2s ease-in-out infinite;
        }
        @media (prefers-reduced-motion: reduce) { .brand-logo { animation: none; } }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.08); } }
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

        .back-link { text-align: center; margin-top: 22px; }
        .back-link a {
            color: var(--ds-pink-dark);
            text-decoration: none;
            font-weight: 600;
            font-size: 13.5px;
            transition: color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .back-link a:hover { color: var(--ds-violet); }

        @media (max-width: 480px) {
            .forgot-container { padding: 34px 24px; }
        }
    </style>
</head>
<body>
    <div class="forgot-wrapper">
        <div class="forgot-container">
            <div class="brand-header">
                <div class="brand-logo">🛍️</div>
                <div class="brand-name">DropSell</div>
            </div>

            <div class="forgot-icon"><i class="fas fa-key"></i></div>

            <h2>Forgot Password?</h2>
            <p class="subtitle">No worries — enter your email address and we'll send you a verification code to reset it.</p>

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

            <div class="back-link">
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
