<style>
    .addr-container { margin-top: 30px; }

    .addr-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .addr-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 16px;
        position: relative;
    }

    .addr-card.is-default {
        border: 2px solid var(--primary-pink);
    }

    .addr-label {
        font-weight: 700;
        font-size: 16px;
        color: var(--dark-gray);
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }

    .default-badge {
        background: var(--primary-pink-light);
        color: var(--primary-pink-dark);
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 999px;
        text-transform: uppercase;
    }

    .addr-text {
        color: var(--gray-600);
        line-height: 1.6;
        margin-bottom: 16px;
    }

    .addr-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .addr-actions form { display: inline; }

    .btn-sm {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: 2px solid var(--primary-pink);
        background: white;
        color: var(--primary-pink);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-sm:hover { background: var(--primary-pink); color: white; }

    .btn-sm-danger { border-color: #dc3545; color: #dc3545; }
    .btn-sm-danger:hover { background: #dc3545; color: white; }

    .btn-add {
        padding: 12px 22px;
        border-radius: 8px;
        font-weight: 600;
        background: var(--primary-pink);
        color: white;
        border: none;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-add:hover { background: var(--primary-pink-dark); }

    .empty-state {
        background: white;
        padding: 50px 30px;
        border-radius: 12px;
        text-align: center;
        color: var(--gray-600);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-success { background: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
    .alert-error { background: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--gray-600);
        margin-bottom: 15px;
        text-decoration: none;
        font-size: 14px;
    }

    .back-link:hover { color: var(--primary-pink); }
</style>

<a href="<?php echo BASE_URL; ?>settings" class="back-link">
    <i class="fas fa-arrow-left"></i> Back to Settings
</a>
<h1><i class="fas fa-map-marker-alt"></i> My Addresses</h1>

<div class="addr-container">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($this->session->flashdata('success')); ?>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
        </div>
    <?php endif; ?>

    <div class="addr-header">
        <p style="margin:0; color: var(--gray-600);">Manage the delivery addresses saved to your account.</p>
        <a href="<?php echo BASE_URL; ?>addresses/add" class="btn-add">
            <i class="fas fa-plus"></i> Add New Address
        </a>
    </div>

    <?php if (empty($addresses)): ?>
        <div class="empty-state">
            <i class="fas fa-map-marker-alt" style="font-size: 40px; color: var(--gray-400); margin-bottom: 15px;"></i>
            <p>You haven't saved any address yet.</p>
            <a href="<?php echo BASE_URL; ?>addresses/add" class="btn-add" style="margin-top: 10px;">
                <i class="fas fa-plus"></i> Add Your First Address
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($addresses as $addr): ?>
            <div class="addr-card <?php echo $addr['is_default'] ? 'is-default' : ''; ?>">
                <div class="addr-label">
                    <i class="fas fa-home"></i> <?php echo htmlspecialchars($addr['label'] ?: 'Address'); ?>
                    <?php if ($addr['is_default']): ?>
                        <span class="default-badge">Default</span>
                    <?php endif; ?>
                </div>
                <div class="addr-text">
                    <?php echo htmlspecialchars($addr['street']); ?>,
                    Brgy. <?php echo htmlspecialchars($addr['barangay']); ?>,
                    <?php echo htmlspecialchars($addr['municipality']); ?>,
                    <?php echo htmlspecialchars($addr['province']); ?>
                </div>
                <div class="addr-actions">
                    <a href="<?php echo BASE_URL; ?>addresses/edit/<?php echo $addr['address_id']; ?>" class="btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <?php if (!$addr['is_default']): ?>
                        <form method="POST" action="<?php echo BASE_URL; ?>addresses/set_default/<?php echo $addr['address_id']; ?>">
                            <button type="submit" class="btn-sm">
                                <i class="fas fa-check"></i> Set as Default
                            </button>
                        </form>
                    <?php endif; ?>
                    <form method="POST" action="<?php echo BASE_URL; ?>addresses/delete/<?php echo $addr['address_id']; ?>" onsubmit="return confirm('Delete this address?');">
                        <button type="submit" class="btn-sm btn-sm-danger">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
