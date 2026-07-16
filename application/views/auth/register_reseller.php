<?php
/**
 * Reseller Registration View — 3-step wizard (UI/UX only).
 * Field names, form action, CSRF token, and validation rules are unchanged;
 * this is purely a client-side reorganization of the same single form into
 * three visually-paginated sections.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register as Reseller - DropSell</title>

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
            --ds-text: #24202b;
            --ds-muted: #6b7280;
            --ds-border: #e5e7eb;
            --ds-danger: #e53935;
            --ds-success: #22c55e;
            --ds-radius-lg: 20px;
            --ds-radius-md: 12px;
            --ds-radius-sm: 8px;
            --ds-shadow: 0 24px 60px rgba(133, 49, 122, 0.14);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { min-height: 100%; }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #fce4ec 0%, #f3e5f5 50%, #e1bee7 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--ds-text);
        }

        a { color: inherit; }

        /* ============ Layout shell ============ */
        .register-wrapper { width: 100%; max-width: 1160px; }

        .register-container {
            background: #ffffff;
            border-radius: var(--ds-radius-lg);
            border: 1px solid rgba(255, 255, 255, 0.75);
            box-shadow: var(--ds-shadow);
            overflow: hidden;
            display: grid;
            grid-template-columns: 0.92fr 1.15fr;
            min-height: 720px;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ============ Left branding panel ============ */
        .register-left {
            background: var(--ds-gradient);
            padding: 52px 42px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: sticky;
            top: 0;
            align-self: stretch;
            height: 100%;
        }

        .brand-section { margin-bottom: 36px; }

        .brand-logo {
            font-size: 58px;
            margin-bottom: 18px;
            display: inline-block;
            animation: pulse 2.2s ease-in-out infinite;
        }

        @media (prefers-reduced-motion: reduce) {
            .brand-logo { animation: none; }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50%       { transform: scale(1.08); }
        }

        .brand-name { font-size: 32px; font-weight: 700; margin-bottom: 6px; letter-spacing: -0.02em; }
        .brand-title { font-size: 15px; font-weight: 300; opacity: 0.95; line-height: 1.5; max-width: 320px; }

        .benefits { width: 100%; display: flex; flex-direction: column; gap: 20px; margin-top: 8px; }

        .benefit {
            display: flex;
            align-items: center;
            gap: 16px;
            animation: slideInLeft 0.6s ease backwards;
        }

        .benefit:nth-child(1) { animation-delay: 0.08s; }
        .benefit:nth-child(2) { animation-delay: 0.16s; }
        .benefit:nth-child(3) { animation-delay: 0.24s; }
        .benefit:nth-child(4) { animation-delay: 0.32s; }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-18px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        .benefit-icon {
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255,255,255,0.28);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .benefit-text h4 { font-size: 14.5px; font-weight: 600; margin-bottom: 3px; letter-spacing: 0.1px; }
        .benefit-text p { font-size: 12.5px; opacity: 0.88; line-height: 1.4; }

        .approval-note {
            margin-top: 32px;
            background: rgba(255,255,255,0.16);
            border: 1px solid rgba(255,255,255,0.22);
            border-radius: var(--ds-radius-md);
            padding: 16px 18px;
            font-size: 12.5px;
            line-height: 1.6;
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }
        .approval-note i { margin-top: 2px; flex-shrink: 0; }

        /* ============ Right panel: wizard ============ */
        .register-right {
            padding: 44px 44px 32px;
            display: flex;
            justify-content: center;
            overflow-y: auto;
        }

        .register-box { width: 100%; max-width: 460px; display: flex; flex-direction: column; }

        .register-box h2 { font-size: 26px; font-weight: 700; color: var(--ds-text); margin-bottom: 6px; }
        .register-subtitle { font-size: 13.5px; color: var(--ds-muted); margin-bottom: 22px; font-weight: 400; }

        /* ---- Alerts (always visible above the stepper) ---- */
        .alert {
            padding: 13px 16px;
            border-radius: var(--ds-radius-md);
            margin-bottom: 18px;
            font-size: 13px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .alert-danger { background: #fff2f4; color: #c72a3c; border: 1px solid #ffc8d8; }
        .alert-success { background: #f0fdf4; color: #1f8554; border: 1px solid #bbf7d0; }

        /* ---- Stepper / progress indicator ---- */
        .stepper-wrap { margin-bottom: 26px; }

        .stepper-caption {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--ds-pink-dark);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 12px;
        }

        .stepper {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
        }

        .step-node {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            flex: 1;
            position: relative;
        }

        .step-node .step-circle {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 2px solid var(--ds-border);
            background: #fff;
            color: var(--ds-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            transition: background 0.3s ease, border-color 0.3s ease, color 0.3s ease, transform 0.3s ease;
            z-index: 1;
        }

        .step-node .step-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--ds-muted);
            text-align: center;
            line-height: 1.3;
            max-width: 92px;
            transition: color 0.3s ease;
        }

        .step-node::after {
            content: '';
            position: absolute;
            top: 17px;
            left: calc(50% + 24px);
            right: calc(-50% + 24px);
            height: 2px;
            background: var(--ds-border);
            transition: background 0.4s ease;
            z-index: 0;
        }
        .step-node:last-child::after { display: none; }

        .step-node.active .step-circle {
            background: var(--ds-gradient);
            border-color: transparent;
            color: #fff;
            transform: scale(1.08);
            box-shadow: 0 6px 16px rgba(238, 130, 238, 0.35);
        }
        .step-node.active .step-label { color: var(--ds-text); }

        .step-node.complete .step-circle {
            background: var(--ds-gradient);
            border-color: transparent;
            color: #fff;
        }
        .step-node.complete::after { background: var(--ds-violet); }
        .step-node.complete .step-label { color: var(--ds-text); }

        /* ---- Step content ---- */
        .wizard-step {
            display: none;
        }
        .wizard-step.active {
            display: block;
            animation: stepIn 300ms ease;
        }

        @keyframes stepIn {
            from { opacity: 0; transform: translateX(14px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        @media (prefers-reduced-motion: reduce) {
            .wizard-step.active { animation: none; }
        }

        .step-heading {
            font-size: 16px;
            font-weight: 700;
            color: var(--ds-text);
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .step-heading i { color: var(--ds-pink); font-size: 15px; }
        .step-subtext { font-size: 12.5px; color: var(--ds-muted); margin-bottom: 18px; }

        .form-group { margin-bottom: 16px; }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #333;
            font-size: 12.5px;
        }

        .optional-tag { font-weight: 400; color: #999; }
        .required-tag { color: var(--ds-danger); }

        .input-group { position: relative; }

        .input-group > i.field-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--ds-pink);
            font-size: 15px;
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 11px 40px 11px 40px;
            border: 1.8px solid var(--ds-border);
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
            background: #fff;
            color: var(--ds-text);
        }

        .form-group select { appearance: none; }

        .has-toggle input { padding-right: 42px; }
        .input-group.no-left-icon > i.field-icon { display: none !important; }
        .input-group.no-left-icon input { padding-left: 14px !important; }

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
            width: 0; height: 0;
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
            padding: 4px;
            line-height: 1;
        }
        .toggle-password:hover { color: var(--ds-pink); }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #ff6ea5;
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 105, 180, 0.14);
        }

        /* ---- Inline validation state ---- */
        .field-status-icon {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            display: none;
        }
        .input-group.has-toggle .field-status-icon { right: 42px; }

        .form-group.is-valid input,
        .form-group.is-valid select { border-color: var(--ds-success); }
        .form-group.is-valid .field-status-icon.icon-valid { display: block; color: var(--ds-success); }

        .form-group.is-invalid input,
        .form-group.is-invalid select { border-color: var(--ds-danger); }
        .form-group.is-invalid .field-status-icon.icon-invalid { display: block; color: var(--ds-danger); }

        .field-message {
            font-size: 11.5px;
            margin-top: 5px;
            display: none;
            align-items: center;
            gap: 5px;
        }
        .form-group.is-invalid .field-message.msg-error { display: flex; color: var(--ds-danger); }
        .form-group.is-valid .field-message.msg-ok { display: flex; color: var(--ds-success); }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        /* ---- Password strength meter ---- */
        .strength-meter { margin-top: 10px; }
        .strength-bars { display: flex; gap: 5px; margin-bottom: 8px; }
        .strength-bars span {
            flex: 1;
            height: 5px;
            border-radius: 3px;
            background: var(--ds-border);
            transition: background 0.3s ease;
        }
        .strength-label { font-size: 11.5px; font-weight: 600; color: var(--ds-muted); }

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
        .password-reqs .req i { font-size: 8px; transition: transform 0.2s ease; }
        .password-reqs .req.ok { color: var(--ds-success); }
        .password-reqs .req.ok i::before { content: "\f00c"; font-size: 10px; }

        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 12.5px;
            color: #5f5f5f;
            margin: 18px 0 6px;
            line-height: 1.5;
        }
        .checkbox-group input {
            width: 17px;
            height: 17px;
            cursor: pointer;
            flex-shrink: 0;
            margin-top: 2px;
            accent-color: var(--ds-pink);
        }
        .checkbox-group a { color: var(--ds-pink-dark); text-decoration: none; font-weight: 600; }
        .checkbox-group a:hover { text-decoration: underline; }

        /* ---- Buttons ---- */
        .step-actions {
            display: flex;
            gap: 12px;
            margin-top: 22px;
        }
        .step-actions.single { justify-content: flex-end; }

        .btn {
            border: none;
            padding: 13px 22px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: transform 220ms ease, box-shadow 220ms ease, background 220ms ease, opacity 220ms ease;
        }

        .btn-primary {
            background: var(--ds-gradient);
            color: #fff;
            flex: 1;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(255, 105, 180, 0.3); }
        .btn-primary:active { transform: translateY(0); }
        .btn-primary:disabled { opacity: 0.55; cursor: not-allowed; transform: none; box-shadow: none; }

        .btn-outline {
            background: #fff;
            color: var(--ds-text);
            border: 1.8px solid var(--ds-border);
            flex: 0 0 auto;
            min-width: 110px;
        }
        .btn-outline:hover { border-color: var(--ds-pink); color: var(--ds-pink-dark); background: #fff7fa; }

        .alert { animation: fadeIn 300ms ease; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .divider { text-align: center; font-size: 12px; color: #aaa; margin: 18px 0 10px; position: relative; }
        .divider span { background: #ffffff; padding: 0 12px; position: relative; z-index: 1; }
        .divider::before {
            content: '';
            display: block;
            border-top: 1px solid #ececec;
            margin-top: 10px;
            margin-bottom: -9px;
        }

        .login-link { text-align: center; font-size: 13px; color: #666; }
        .login-link p + p { margin-top: 8px; }
        .login-link a { color: var(--ds-pink-dark); text-decoration: none; font-weight: 600; }
        .login-link a:hover { color: var(--ds-violet); }

        /* Screen-reader only live region for step announcements */
        .sr-only {
            position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
            overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0;
        }

        /* ============ Responsive ============ */
        @media (max-width: 980px) {
            .register-container { grid-template-columns: 1fr; min-height: auto; }
            .register-left { position: static; padding: 34px 28px; }
            .register-right { padding: 34px 28px 24px; }
            .register-box { max-width: 100%; }
            .form-row { grid-template-columns: 1fr; }
        }

        @media (max-width: 600px) {
            body { padding: 0; }
            .register-wrapper { max-width: 100%; }
            .register-container { border-radius: 0; box-shadow: none; }
            .register-left { padding: 26px 20px; }
            .register-right { padding: 22px 18px 90px; }
            .register-box h2 { font-size: 22px; }
            .brand-logo { font-size: 44px; animation: none; margin-bottom: 12px; }
            .brand-title { max-width: none; }
            .benefits { gap: 14px; margin-top: 4px; }
            .benefit-icon { width: 38px; height: 38px; font-size: 15px; border-radius: 11px; }
            .benefit-text h4 { font-size: 13px; }
            .benefit-text p { font-size: 11px; }
            .approval-note { margin-top: 20px; }
            .step-node .step-label { font-size: 10px; max-width: 74px; }

            /* Sticky primary action on mobile */
            .step-actions {
                position: fixed;
                left: 0; right: 0; bottom: 0;
                background: #fff;
                padding: 14px 18px calc(14px + env(safe-area-inset-bottom));
                box-shadow: 0 -8px 24px rgba(0,0,0,0.08);
                margin-top: 0;
                z-index: 20;
            }
            .register-box { padding-bottom: 4px; }
        }
    </style>
</head>
<body>
<div class="register-wrapper">
    <div class="register-container">
        <!-- Left Panel -->
        <div class="register-left">
            <div class="brand-section">
                <div class="brand-logo">🛍️</div>
                <h1 class="brand-name">DropSell</h1>
                <p class="brand-title">Start your own online store — zero inventory, real commissions, full support.</p>
            </div>

            <div class="benefits">
                <div class="benefit">
                    <div class="benefit-icon"><i class="fas fa-coins"></i></div>
                    <div class="benefit-text">
                        <h4>Earn Commissions</h4>
                        <p>Get paid on every sale you make</p>
                    </div>
                </div>
                <div class="benefit">
                    <div class="benefit-icon"><i class="fas fa-tags"></i></div>
                    <div class="benefit-text">
                        <h4>Set Your Own Prices</h4>
                        <p>Control your markup on all products</p>
                    </div>
                </div>
                <div class="benefit">
                    <div class="benefit-icon"><i class="fas fa-boxes"></i></div>
                    <div class="benefit-text">
                        <h4>No Inventory Needed</h4>
                        <p>We handle stock and fulfillment</p>
                    </div>
                </div>
                <div class="benefit">
                    <div class="benefit-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="benefit-text">
                        <h4>Track Your Earnings</h4>
                        <p>Real-time dashboard for your sales</p>
                    </div>
                </div>
            </div>

            <div class="approval-note">
                <i class="fas fa-info-circle"></i>
                <span>Applications are reviewed within 1–2 business days. You'll be able to log in once your account is approved.</span>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="register-right">
            <div class="register-box">
                <h2>Reseller Registration</h2>
                <p class="register-subtitle">Fill in your details to apply as a DropSell reseller</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo htmlspecialchars($success); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Progress indicator -->
                <div class="stepper-wrap">
                    <div class="stepper-caption" id="stepCaption">Step 1 of 3</div>
                    <div class="stepper" role="list" aria-label="Registration progress">
                        <div class="step-node active" data-node="1" role="listitem">
                            <div class="step-circle"><span class="step-num">1</span><i class="fas fa-check" style="display:none;"></i></div>
                            <div class="step-label">Personal Information</div>
                        </div>
                        <div class="step-node" data-node="2" role="listitem">
                            <div class="step-circle"><span class="step-num">2</span><i class="fas fa-check" style="display:none;"></i></div>
                            <div class="step-label">Business Information</div>
                        </div>
                        <div class="step-node" data-node="3" role="listitem">
                            <div class="step-circle"><span class="step-num">3</span><i class="fas fa-check" style="display:none;"></i></div>
                            <div class="step-label">Account Security</div>
                        </div>
                    </div>
                </div>
                <div class="sr-only" aria-live="polite" id="stepAnnouncer"></div>

                <form method="POST" action="<?php echo site_url('auth/do_register_reseller'); ?>" novalidate id="resellerForm">
                    <?php echo csrf_field(); ?>

                    <!-- ============ STEP 1: Personal Information ============ -->
                    <section class="wizard-step active" data-step="1" aria-labelledby="step1Heading">
                        <div class="step-heading" id="step1Heading"><i class="fas fa-user"></i> Personal Information</div>
                        <p class="step-subtext">Tell us who you are.</p>

                        <div class="form-row">
                            <div class="form-group" data-field="first_name">
                                <label for="first_name">First Name <span class="required-tag">*</span></label>
                                <div class="input-group">
                                    <i class="fas fa-user field-icon"></i>
                                    <input type="text" name="first_name" id="first_name" placeholder="Juan" value="<?php echo set_value('first_name'); ?>" required>
                                    <i class="fas fa-check-circle field-status-icon icon-valid"></i>
                                    <i class="fas fa-exclamation-circle field-status-icon icon-invalid"></i>
                                </div>
                                <div class="field-message msg-error"><i class="fas fa-circle-exclamation"></i> First name is required.</div>
                            </div>
                            <div class="form-group" data-field="last_name">
                                <label for="last_name">Last Name <span class="required-tag">*</span></label>
                                <div class="input-group">
                                    <i class="fas fa-user field-icon"></i>
                                    <input type="text" name="last_name" id="last_name" placeholder="Dela Cruz" value="<?php echo set_value('last_name'); ?>" required>
                                    <i class="fas fa-check-circle field-status-icon icon-valid"></i>
                                    <i class="fas fa-exclamation-circle field-status-icon icon-invalid"></i>
                                </div>
                                <div class="field-message msg-error"><i class="fas fa-circle-exclamation"></i> Last name is required.</div>
                            </div>
                        </div>

                        <div class="form-group" data-field="middle_name">
                            <label for="middle_name">Middle Name <span class="optional-tag">(optional)</span></label>
                            <div class="input-group">
                                <i class="fas fa-user field-icon"></i>
                                <input type="text" name="middle_name" id="middle_name" placeholder="Santos" value="<?php echo set_value('middle_name'); ?>">
                            </div>
                        </div>

                        <div class="step-actions single">
                            <button type="button" class="btn btn-primary" data-action="next">
                                Next <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </section>

                    <!-- ============ STEP 2: Business & Contact Information ============ -->
                    <section class="wizard-step" data-step="2" aria-labelledby="step2Heading">
                        <div class="step-heading" id="step2Heading"><i class="fas fa-store"></i> Business &amp; Contact Information</div>
                        <p class="step-subtext">Where and how customers will find and reach you.</p>

                        <div class="form-group" data-field="business_name">
                            <label for="business_name">Business / Store Name <span class="required-tag">*</span></label>
                            <div class="input-group">
                                <i class="fas fa-store field-icon"></i>
                                <input type="text" name="business_name" id="business_name" placeholder="e.g. Juan's Online Shop" maxlength="50" value="<?php echo set_value('business_name'); ?>" required>
                                <i class="fas fa-check-circle field-status-icon icon-valid"></i>
                                <i class="fas fa-exclamation-circle field-status-icon icon-invalid"></i>
                            </div>
                            <div class="field-message msg-error"><i class="fas fa-circle-exclamation"></i> Business / store name is required.</div>
                        </div>

                        <div class="form-group" data-field="email">
                            <label for="email">Email Address <span class="required-tag">*</span></label>
                            <div class="input-group">
                                <i class="fas fa-envelope field-icon"></i>
                                <input type="email" name="email" id="email" placeholder="your@email.com" value="<?php echo set_value('email'); ?>" required>
                                <i class="fas fa-check-circle field-status-icon icon-valid"></i>
                                <i class="fas fa-exclamation-circle field-status-icon icon-invalid"></i>
                            </div>
                            <div class="field-message msg-error"><i class="fas fa-circle-exclamation"></i> Enter a valid email address.</div>
                        </div>

                        <div class="form-group" data-field="contact_number">
                            <label for="contact_number">Contact Number <span class="required-tag">*</span></label>
                            <div class="input-group">
                                <i class="fas fa-phone field-icon"></i>
                                <input type="text" name="contact_number" id="contact_number" placeholder="09XXXXXXXXX" maxlength="11" value="<?php echo set_value('contact_number'); ?>" required>
                                <i class="fas fa-check-circle field-status-icon icon-valid"></i>
                                <i class="fas fa-exclamation-circle field-status-icon icon-invalid"></i>
                            </div>
                            <div class="field-message msg-error"><i class="fas fa-circle-exclamation"></i> Enter an 11-digit mobile number.</div>
                        </div>

                        <div class="form-group">
                            <label for="province_display">Province</label>
                            <div class="input-group">
                                <i class="fas fa-map-marker-alt field-icon"></i>
                                <input type="text" id="province_display" value="Oriental Mindoro" readonly style="background:#f3f4f6;color:#666;">
                                <input type="hidden" name="province" value="Oriental Mindoro">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group" data-field="municipality">
                                <label for="municipality">Municipality / City <span class="required-tag">*</span></label>
                                <div class="input-group">
                                    <i class="fas fa-map-marker-alt field-icon"></i>
                                    <select name="municipality" id="municipality" required>
                                        <option value="">Select Municipality</option>
                                    </select>
                                    <i class="fas fa-check-circle field-status-icon icon-valid"></i>
                                    <i class="fas fa-exclamation-circle field-status-icon icon-invalid"></i>
                                </div>
                                <div class="field-message msg-error"><i class="fas fa-circle-exclamation"></i> Select your municipality/city.</div>
                            </div>
                            <div class="form-group" data-field="barangay">
                                <label for="barangay">Barangay <span class="required-tag">*</span></label>
                                <div class="input-group">
                                    <i class="fas fa-map-marker-alt field-icon"></i>
                                    <select name="barangay" id="barangay" required disabled>
                                        <option value="">Select Municipality first</option>
                                    </select>
                                    <i class="fas fa-check-circle field-status-icon icon-valid"></i>
                                    <i class="fas fa-exclamation-circle field-status-icon icon-invalid"></i>
                                </div>
                                <div class="field-message msg-error"><i class="fas fa-circle-exclamation"></i> Select your barangay.</div>
                            </div>
                        </div>

                        <div class="form-group" data-field="street">
                            <label for="street">Street / House No. <span class="required-tag">*</span></label>
                            <div class="input-group">
                                <i class="fas fa-map-marker-alt field-icon"></i>
                                <input type="text" name="street" id="street" placeholder="House No., Street" maxlength="100" value="<?php echo set_value('street'); ?>" required>
                                <i class="fas fa-check-circle field-status-icon icon-valid"></i>
                                <i class="fas fa-exclamation-circle field-status-icon icon-invalid"></i>
                            </div>
                            <div class="field-message msg-error"><i class="fas fa-circle-exclamation"></i> Street / house number is required.</div>
                        </div>

                        <div class="step-actions">
                            <button type="button" class="btn btn-outline" data-action="back">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                            <button type="button" class="btn btn-primary" data-action="next">
                                Next <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </section>

                    <!-- ============ STEP 3: Account Security ============ -->
                    <section class="wizard-step" data-step="3" aria-labelledby="step3Heading">
                        <div class="step-heading" id="step3Heading"><i class="fas fa-lock"></i> Account Security</div>
                        <p class="step-subtext">Create a strong password to protect your reseller account.</p>

                        <div class="form-group" data-field="password">
                            <label for="res_password">Password <span class="required-tag">*</span></label>
                            <div class="input-group has-toggle no-left-icon">
                                <input type="password" name="password" id="res_password" placeholder="Min. 12 characters" required autocomplete="new-password">
                                <button type="button" class="toggle-password" onclick="togglePwd('res_password', this)" tabindex="-1" aria-label="Show password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>

                            <div class="strength-meter" aria-hidden="true">
                                <div class="strength-bars">
                                    <span id="bar-1"></span><span id="bar-2"></span><span id="bar-3"></span><span id="bar-4"></span><span id="bar-5"></span>
                                </div>
                                <div class="strength-label" id="strengthLabel">Password strength</div>
                            </div>

                            <div class="password-reqs">
                                <div class="req" id="r-len"><i class="fas fa-circle"></i> At least 12 characters</div>
                                <div class="req" id="r-upper"><i class="fas fa-circle"></i> Uppercase letter (A–Z)</div>
                                <div class="req" id="r-lower"><i class="fas fa-circle"></i> Lowercase letter (a–z)</div>
                                <div class="req" id="r-num"><i class="fas fa-circle"></i> Number (0–9)</div>
                                <div class="req" id="r-special"><i class="fas fa-circle"></i> Special character (!@#$%^&amp;*)</div>
                            </div>
                        </div>

                        <div class="form-group" data-field="password_confirm">
                            <label for="res_password_confirm">Confirm Password <span class="required-tag">*</span></label>
                            <div class="input-group has-toggle no-left-icon">
                                <input type="password" name="password_confirm" id="res_password_confirm" placeholder="••••••••" required autocomplete="new-password">
                                <button type="button" class="toggle-password" onclick="togglePwd('res_password_confirm', this)" tabindex="-1" aria-label="Show password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="field-message msg-error"><i class="fas fa-circle-exclamation"></i> Passwords do not match.</div>
                            <div class="field-message msg-ok"><i class="fas fa-check-circle"></i> Passwords match.</div>
                        </div>

                        <label class="checkbox-group">
                            <input type="checkbox" name="terms" id="terms" required>
                            <span>I agree to the <a href="#" data-legal="terms">Terms of Service</a> and <a href="#" data-legal="privacy">Privacy Policy</a>. I understand my application needs admin approval before I can log in.</span>
                        </label>

                        <div class="step-actions">
                            <button type="button" class="btn btn-outline" data-action="back">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-paper-plane"></i> Submit Application
                            </button>
                        </div>
                    </section>
                </form>

                <div class="divider"><span>or</span></div>

                <div class="login-link">
                    <p>Want to shop instead? <a href="<?php echo site_url('auth/register'); ?>">Register as Customer</a></p>
                    <p>Already have an account? <a href="<?php echo site_url('auth/login'); ?>">Sign In</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.alert').forEach(function(el) {
            setTimeout(function() { el.style.display = 'none'; }, 8000);
        });
    });

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

    /* ===================== Password strength + requirements ===================== */
    (function() {
        var pwd = document.getElementById('res_password');
        if (!pwd) return;

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

        function evaluate() {
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

            validateStepField('res_password');
            validateStepField('res_password_confirm');
        }

        pwd.addEventListener('input', evaluate);

        var confirmPwd = document.getElementById('res_password_confirm');
        if (confirmPwd) confirmPwd.addEventListener('input', function() { validateStepField('res_password_confirm'); });
    })();

    /* ===================== Municipality / Barangay data (unchanged) ===================== */
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

    (function() {
        var mun = document.getElementById('municipality');
        var brgy = document.getElementById('barangay');

        Object.keys(ORIENTAL_MINDORO_BARANGAYS).sort().forEach(function(name) {
            var opt = document.createElement('option');
            opt.value = name; opt.textContent = name;
            mun.appendChild(opt);
        });

        var savedMun = '<?php echo set_value('municipality'); ?>';
        var savedBrgy = '<?php echo set_value('barangay'); ?>';
        if (savedMun) {
            mun.value = savedMun;
            buildBarangays(savedMun, savedBrgy);
        }

        mun.addEventListener('change', function() {
            buildBarangays(this.value, '');
            validateStepField('municipality');
        });
        brgy.addEventListener('change', function() { validateStepField('barangay'); });

        function buildBarangays(municipality, selected) {
            var barangays = ORIENTAL_MINDORO_BARANGAYS[municipality] || [];
            brgy.innerHTML = '';
            if (!barangays.length) {
                brgy.appendChild(new Option('Select Municipality first', ''));
                brgy.disabled = true;
                return;
            }
            brgy.disabled = false;
            brgy.appendChild(new Option('Select Barangay', ''));
            barangays.forEach(function(b) {
                var opt = new Option(b, b);
                if (b === selected) opt.selected = true;
                brgy.appendChild(opt);
            });
        }
    })();

    /* ===================== Wizard navigation + inline validation ===================== */
    (function() {
        var form = document.getElementById('resellerForm');
        var steps = Array.prototype.slice.call(document.querySelectorAll('.wizard-step'));
        var nodes = Array.prototype.slice.call(document.querySelectorAll('.step-node'));
        var caption = document.getElementById('stepCaption');
        var announcer = document.getElementById('stepAnnouncer');
        var stepTitles = ['Personal Information', 'Business & Contact Information', 'Account Security'];
        var currentStep = 1;

        var validators = {
            first_name: function() { return requiredText('first_name'); },
            last_name: function() { return requiredText('last_name'); },
            business_name: function() { return requiredText('business_name'); },
            email: function() {
                var v = document.getElementById('email').value.trim();
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
            },
            contact_number: function() {
                var v = document.getElementById('contact_number').value.trim();
                return /^[0-9]{11}$/.test(v);
            },
            municipality: function() { return document.getElementById('municipality').value !== ''; },
            barangay: function() { return document.getElementById('barangay').value !== ''; },
            street: function() { return requiredText('street'); },
            password: function() {
                var v = document.getElementById('res_password').value;
                return v.length >= 12 && /[A-Z]/.test(v) && /[a-z]/.test(v) && /[0-9]/.test(v) && /[!@#$%^&*]/.test(v);
            },
            password_confirm: function() {
                var v = document.getElementById('res_password_confirm').value;
                var p = document.getElementById('res_password').value;
                return v.length > 0 && v === p;
            }
        };

        function requiredText(id) {
            return document.getElementById(id).value.trim().length > 0;
        }

        function setFieldState(group, valid) {
            if (!group) return;
            group.classList.remove('is-valid', 'is-invalid');
            group.classList.add(valid ? 'is-valid' : 'is-invalid');
        }

        window.validateStepField = function(inputId) {
            var input = document.getElementById(inputId);
            if (!input) return true;
            var fieldName = input.name;
            var group = document.querySelector('.form-group[data-field="' + fieldName + '"]');
            if (!group) return true;

            // Only show a state once the user has interacted with the field.
            var validator = validators[fieldName];
            if (!validator) return true;
            var valid = validator();

            if (input.value === '' && !group.dataset.touched) {
                return valid;
            }
            group.dataset.touched = '1';
            setFieldState(group, valid);
            return valid;
        };

        function attachLiveValidation(container) {
            container.querySelectorAll('input, select').forEach(function(el) {
                var evt = (el.tagName === 'SELECT') ? 'change' : 'input';
                el.addEventListener(evt, function() { validateStepField(el.id); });
                el.addEventListener('blur', function() { validateStepField(el.id); });
            });
        }
        steps.forEach(attachLiveValidation);

        function validateStep(stepNum) {
            var container = steps[stepNum - 1];
            var fields = Array.prototype.slice.call(container.querySelectorAll('[data-field]'));
            var allValid = true;
            var firstInvalid = null;

            fields.forEach(function(group) {
                var input = group.querySelector('input, select');
                if (!input) return;
                var fieldName = input.name;
                var validator = validators[fieldName];
                if (!validator) return;
                var valid = validator();
                group.dataset.touched = '1';
                setFieldState(group, valid);
                if (!valid) {
                    allValid = false;
                    if (!firstInvalid) firstInvalid = input;
                }
            });

            if (firstInvalid) {
                firstInvalid.focus({ preventScroll: false });
            }

            return allValid;
        }

        function renderStepper() {
            nodes.forEach(function(node) {
                var n = parseInt(node.getAttribute('data-node'), 10);
                node.classList.remove('active', 'complete');
                var circleNum = node.querySelector('.step-num');
                var circleCheck = node.querySelector('.fa-check');
                if (n < currentStep) {
                    node.classList.add('complete');
                    circleNum.style.display = 'none';
                    circleCheck.style.display = 'inline';
                } else if (n === currentStep) {
                    node.classList.add('active');
                    circleNum.style.display = 'inline';
                    circleCheck.style.display = 'none';
                } else {
                    circleNum.style.display = 'inline';
                    circleCheck.style.display = 'none';
                }
            });
            caption.textContent = 'Step ' + currentStep + ' of 3';
            announcer.textContent = 'Step ' + currentStep + ' of 3: ' + stepTitles[currentStep - 1];
        }

        function goToStep(n) {
            steps.forEach(function(s) {
                s.classList.toggle('active', parseInt(s.getAttribute('data-step'), 10) === n);
            });
            currentStep = n;
            renderStepper();
            var box = document.querySelector('.register-box');
            if (box) box.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        form.addEventListener('click', function(e) {
            var btn = e.target.closest('[data-action]');
            if (!btn) return;

            if (btn.dataset.action === 'next') {
                if (validateStep(currentStep)) {
                    goToStep(Math.min(currentStep + 1, 3));
                }
            } else if (btn.dataset.action === 'back') {
                goToStep(Math.max(currentStep - 1, 1));
            }
        });

        // Prevent Enter key from prematurely submitting the form before the
        // final step (the Submit button lives in the DOM the whole time).
        form.addEventListener('keydown', function(e) {
            if (e.key !== 'Enter') return;
            if (e.target.tagName === 'TEXTAREA') return;
            if (currentStep < 3) {
                e.preventDefault();
                if (validateStep(currentStep)) {
                    goToStep(currentStep + 1);
                }
            }
        });

        form.addEventListener('submit', function(e) {
            if (!validateStep(3)) {
                e.preventDefault();
            }
        });

        // If the server rejected the submission (e.g. a field it validates
        // that JS doesn't, like duplicate email or the Terms checkbox),
        // jump straight to the earliest step containing the failing field
        // instead of leaving the user stranded back on step 1.
        var errorFields = <?php echo json_encode($error_fields ?? []); ?>;
        if (errorFields.length) {
            var jumpToStep = null;
            errorFields.forEach(function(fieldName) {
                var input = document.querySelector('[name="' + fieldName + '"]');
                if (!input) return;
                var stepEl = input.closest('.wizard-step');
                if (stepEl) {
                    var stepNum = parseInt(stepEl.getAttribute('data-step'), 10);
                    if (jumpToStep === null || stepNum < jumpToStep) jumpToStep = stepNum;
                }
                var group = input.closest('.form-group[data-field]');
                if (group) {
                    group.classList.add('is-invalid');
                    group.dataset.touched = '1';
                }
            });
            if (jumpToStep !== null) {
                goToStep(jumpToStep);
            }
        }

        renderStepper();
    })();
</script>

<?php include __DIR__ . '/partials/legal_modals.php'; ?>
</body>
</html>
