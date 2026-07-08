<?php
$page_title = 'Shipping Fee Settings';
$current_page = 'settings';
?>
<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Shipping Fee Settings</h4>
            <small class="text-muted">Set the Pasabay delivery fee per municipality. Pick-up is always free.</small>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/settings_tabs.php'; ?>

    <div class="row">
        <div class="col">
            <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
                <div class="card-body">
                    <form method="post">
                        <div class="page-section" style="margin-top:0;">
                            <span class="section-title"><i class="fas fa-truck me-2"></i>Pasabay Fee by Municipality</span>
                            <hr>
                        </div>
                        <div class="row">
                            <?php foreach ($municipalities as $municipality): ?>
                                <div class="col col-6 col-md-4">
                                    <div class="form-group">
                                        <label for="fee_<?= md5($municipality); ?>"><?= htmlspecialchars($municipality); ?></label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" step="0.01" min="0" class="form-control"
                                                   id="fee_<?= md5($municipality); ?>" name="fee_<?= md5($municipality); ?>"
                                                   value="<?= htmlspecialchars($shipping_fees[$municipality] ?? 30); ?>">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="form-group" style="margin-top: 30px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Shipping Fees
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
