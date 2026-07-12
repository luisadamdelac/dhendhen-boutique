<style>
    .settings-container { margin-top: 30px; max-width: 600px; }

    .settings-item {
        background: white;
        padding: 20px 25px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 18px;
        text-decoration: none;
        color: inherit;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .settings-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 14px rgba(0,0,0,0.12);
    }

    .settings-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--primary-pink-light);
        color: var(--primary-pink-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .settings-text h3 {
        font-size: 16px;
        font-weight: 600;
        color: var(--dark-gray);
        margin-bottom: 4px;
    }

    .settings-text p {
        font-size: 13px;
        color: var(--gray-600);
        margin: 0;
    }

    .settings-chevron {
        margin-left: auto;
        color: var(--gray-400);
    }

    .settings-item.logout-item {
        color: #e53935;
    }
    .settings-item.logout-item .settings-icon {
        background: #fdecea;
        color: #e53935;
    }
    .settings-item.logout-item .settings-text h3 {
        color: #e53935;
    }
</style>

<h1><i class="fas fa-cog"></i> Settings</h1>

<div class="settings-container">
    <a href="<?php echo BASE_URL; ?>account" class="settings-item">
        <div class="settings-icon">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="settings-text">
            <h3>My Account</h3>
            <p>View and edit your profile details</p>
        </div>
        <i class="fas fa-chevron-right settings-chevron"></i>
    </a>

    <a href="<?php echo BASE_URL; ?>orders" class="settings-item">
        <div class="settings-icon">
            <i class="fas fa-box"></i>
        </div>
        <div class="settings-text">
            <h3>My Orders</h3>
            <p>Track and view your order history</p>
        </div>
        <i class="fas fa-chevron-right settings-chevron"></i>
    </a>

    <a href="<?php echo BASE_URL; ?>addresses" class="settings-item">
        <div class="settings-icon">
            <i class="fas fa-map-marker-alt"></i>
        </div>
        <div class="settings-text">
            <h3>My Addresses</h3>
            <p>Manage your default and saved delivery addresses</p>
        </div>
        <i class="fas fa-chevron-right settings-chevron"></i>
    </a>

    <a href="<?php echo BASE_URL; ?>settings/activity_history" class="settings-item">
        <div class="settings-icon">
            <i class="fas fa-history"></i>
        </div>
        <div class="settings-text">
            <h3>Activity History</h3>
            <p>View your recent orders and account changes</p>
        </div>
        <i class="fas fa-chevron-right settings-chevron"></i>
    </a>

    <a href="<?php echo BASE_URL; ?>auth/logout/customer" class="settings-item logout-item">
        <div class="settings-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        <div class="settings-text">
            <h3>Logout</h3>
            <p>Sign out of your account</p>
        </div>
        <i class="fas fa-chevron-right settings-chevron"></i>
    </a>
</div>
