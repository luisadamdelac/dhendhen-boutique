<?php
/**
 * Login View - Beautiful Split Panel Design
 * For all 4 roles: Admin, Staff, Reseller, Customer
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DropSell</title>
    
    <!-- Storage Access API for Safari ITP and tracking prevention -->
    <meta http-equiv="origin-trial" content="">
    
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛍️</text></svg>">
    
    <link rel="stylesheet" href="<?php echo base_url('public/vendor/poppins/poppins.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('public/vendor/fontawesome/css/all.min.css'); ?>">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            overflow-x: hidden;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: radial-gradient(circle at 50% 35%, #fdeef5 0%, #fbd9ea 55%, #f7c6de 100%);
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 1080px;
        }

        .login-container {
            position: relative;
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 70px rgba(214, 0, 109, 0.18);
            overflow: hidden;
            display: flex;
            min-height: 640px;
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Left Panel - Brand / Wine Gradient */
        .login-left {
            position: relative;
            flex: 0 0 50%;
            background:
                radial-gradient(circle, rgba(255,255,255,0.16) 1.5px, transparent 1.5px) 0 0/20px 20px,
                linear-gradient(150deg, #6b0a3d 0%, #c2185b 45%, #8e0e4e 100%);
            padding: 50px 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            overflow: hidden;
            z-index: 1;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -30%;
            width: 90%;
            height: 140%;
            background: linear-gradient(135deg, rgba(255,255,255,0.06) 0%, transparent 55%);
            transform: rotate(8deg);
            pointer-events: none;
        }

        .brand-section {
            margin-bottom: 30px;
            position: relative;
        }

        .brand-logo-wrap {
            width: 170px;
            height: 170px;
            margin: 0 auto 20px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
        }

        .brand-logo-img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .brand-name {
            font-size: 40px;
            font-weight: 800;
            margin-bottom: 6px;
            letter-spacing: 1px;
        }

        .brand-title {
            font-size: 16px;
            font-weight: 300;
            opacity: 0.9;
        }

        .brand-divider {
            width: 56px;
            height: 3px;
            background: #ffca28;
            border-radius: 2px;
            margin: 14px auto;
        }

        .brand-tagline {
            font-size: 13px;
            opacity: 0.9;
            margin-bottom: 34px;
            max-width: 380px;
        }

        .features {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            position: relative;
        }

        .feature {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            animation: slideInLeft 0.6s ease backwards;
        }

        .feature:nth-child(1) { animation-delay: 0.1s; }
        .feature:nth-child(2) { animation-delay: 0.2s; }
        .feature:nth-child(3) { animation-delay: 0.3s; }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .feature-icon {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, #ff4f93, #ff85b3);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #ffca28;
            flex-shrink: 0;
        }

        .feature-text {
            text-align: center;
        }

        .feature-text h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .feature-text p {
            font-size: 12px;
            opacity: 0.85;
            line-height: 1.4;
        }

        /* Right Panel - Form */
        .login-right {
            position: relative;
            flex: 0 0 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: white;
            z-index: 1;
            animation: slideInRight 0.6s ease;
        }
        
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .login-box {
            width: 100%;
            max-width: 380px;
        }
        
        .login-box h2 {
            font-size: 34px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .login-box h2 .welcome-dark {
            color: #1a1a2e;
        }

        .login-box h2 .welcome-pink {
            color: #d6006d;
        }

        .login-subtitle {
            font-size: 14px;
            color: #888;
            margin-bottom: 25px;
            font-weight: 400;
        }
        
        .form-group {
            margin-bottom: 18px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .input-group {
            position: relative;
            width: 100%;
            display: block;
            margin-bottom: .5rem;
        }
        
        .input-group > i.field-icon,
        .login-right .input-group > i.field-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #ff69b4 !important;
            font-size: 16px;
            pointer-events: none;
            z-index: 2;
            line-height: 1;
        }

        .input-group input,
        .login-right .input-group input {
            display: block;
            width: 100% !important;
            padding: 14px 16px 14px 50px !important;
            border: 2px solid #f5cee0 !important;
            border-radius: 12px !important;
            font-size: 14px !important;
            font-family: 'Poppins', sans-serif !important;
            transition: border-color 0.3s, box-shadow 0.3s !important;
            background: #fff !important;
            box-sizing: border-box !important;
            margin: 0 !important;
        }

        .has-toggle input {
            padding-right: 46px !important;
        }

        .input-group.no-left-icon > i.field-icon {
            display: none !important;
        }

        .input-group.no-left-icon input {
            padding-left: 16px !important;
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
        .toggle-password:hover { color: #ff69b4; }

        .form-group input {
            width: 100%;
            padding: 14px 15px 14px 50px;
            border: 2px solid #f5cee0;
            border-radius: 12px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s, box-shadow 0.3s;
            background: #fff;
            box-sizing: border-box;
        }

        .form-group input:focus {
            border-color: #ff69b4;
            outline: none;
            box-shadow: 0 0 0 4px rgba(255, 105, 180, 0.1);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #666;
            margin: 15px 0;
        }
        
        .checkbox-group input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #ff69b4;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #d6006d 0%, #b8005c 100%);
            border: none;
            color: white;
            padding: 13px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            width: 100%;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(214, 0, 109, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-danger {
            background-color: #fff5f5;
            color: #d32f2f;
            border: 1px solid #ffcdd2;
        }
        
        .alert-success {
            background-color: #f0fdf4;
            color: #22c55e;
            border: 1px solid #bbf7d0;
        }
        
        .login-links {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #666;
        }

        .signup-buttons {
            display: flex;
            gap: 12px;
        }

        .btn-outline {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: linear-gradient(135deg, #d6006d 0%, #b8005c 100%);
            border: none;
            color: white;
            padding: 9px 8px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 12px;
            white-space: nowrap;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-outline:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(214, 0, 109, 0.4);
        }

        .divider {
            text-align: center;
            margin: 20px 0;
            font-size: 13px;
            color: #999;
        }

        .forgot-link {
            text-align: center;
            margin-top: 15px;
        }

        .forgot-link a {
            color: #2563eb;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }

        .forgot-link a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                min-height: auto;
            }
            
            .login-left {
                flex: 0 0 auto;
                padding: 40px 30px;
                min-height: 280px;
            }
            
            .login-right {
                flex: 0 0 auto;
                padding: 30px 20px;
            }
            
            .brand-logo-wrap {
                width: 120px;
                height: 120px;
            }

            .brand-name {
                font-size: 32px;
            }

            .brand-section {
                margin-bottom: 20px;
            }

            .features {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .login-box h2 {
                font-size: 26px;
            }
        }

        /* Below this width, "Customer Sign Up"/"Reseller Sign Up" side by
           side don't have room to stay on one line each (white-space:nowrap
           above needs it) — stack them full-width instead of letting the
           text overflow past the button/card edge. */
        @media (max-width: 420px) {
            .signup-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <!-- Left Panel -->
            <div class="login-left">
                <div class="brand-section">
                    <div class="brand-logo-wrap">
                        <img class="brand-logo-img" src="<?php echo base_url('public/uploads/avatars/c6e87fc1363436e5468a05c9c2a59b26.webp'); ?>" alt="Dhendhen Beauty Products and Boutique">
                    </div>
                    <h1 class="brand-name">DropSell</h1>
                    <p class="brand-title">Dropshipping Management System</p>
                    <div class="brand-divider"></div>
                    <p class="brand-tagline">Manage your business. Grow your sales. Scale effortlessly.</p>
                </div>

                <div class="features">
                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Sales Analytics</h4>
                            <p>Track and analyze your performance</p>
                        </div>
                    </div>

                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Inventory Management</h4>
                            <p>Manage stock and product availability</p>
                        </div>
                    </div>

                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Team Collaboration</h4>
                            <p>Work together and boost productivity</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel -->
            <div class="login-right">
                <div class="login-box">
                    <h2><span class="welcome-dark">Welcome</span> <span class="welcome-pink">Back!</span></h2>
                    <p class="login-subtitle">Sign in to your DropSell account</p>

                    <?php if ($error ?? false): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success ?? false): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo site_url('auth/do_login'); ?>" novalidate>
                        <?php echo csrf_field(); ?>
                        <div class="form-group">
                            <label>Email Address</label>
                            <div class="input-group" style="position: relative; width: 100%;">
                                <i class="fas fa-envelope field-icon" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #ff69b4; font-size: 16px; pointer-events: none; z-index: 2;"></i>
                                <input type="email" name="email" placeholder="your@email.com" required autofocus style="padding-left: 50px; width: 100%;">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Password</label>
                            <div class="input-group has-toggle no-left-icon">
                                <input type="password" name="password" id="login_password" placeholder="••••••••" required>
                                <button type="button" class="toggle-password" onclick="togglePwd('login_password', this)" tabindex="-1">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <label class="checkbox-group">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                        
                        <button type="submit" class="btn-login">
                            Sign In
                        </button>
                    </form>

                    <div class="divider">Don't have an account?</div>

                    <div class="login-links signup-buttons">
                        <a href="<?php echo site_url('auth/register'); ?>" class="btn-outline"><i class="fas fa-user"></i> Customer Sign Up</a>
                        <a href="<?php echo site_url('auth/register-reseller'); ?>" class="btn-outline"><i class="fas fa-store"></i> Reseller Sign Up</a>
                    </div>

                    <div class="forgot-link">
                        <a href="<?php echo site_url('auth/forgot-password'); ?>">Forgot your password?</a>
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
        
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 5000);
            });
        });
    </script>
</body>
</html>
