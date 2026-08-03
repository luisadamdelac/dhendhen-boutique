<?php
$page_title = 'Payment Settings';
$current_page = 'settings';
?>
<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div style="width:46px;height:46px;border-radius:14px;background:var(--primary-pink-dark);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
                <i class="fas fa-cog"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Payment Settings</h4>
                <small class="text-muted">Manage GCash and bank payment details.</small>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/settings_tabs.php'; ?>

    <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                        <div class="page-section" style="margin-top:0;">
                            <span class="section-title"><i class="fas fa-mobile-alt me-2"></i>GCash</span>
                            <hr>
                        </div>
                        <div class="row">
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="payment_method">Payment Method</label>
                                    <input type="text" class="form-control" id="payment_method" name="payment_method" value="<?= set_value('payment_method', $settings['payment_method'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="gcash_name">GCash Account Name</label>
                                    <input type="text" class="form-control" id="gcash_name" name="gcash_name" value="<?= set_value('gcash_name', $settings['gcash_name'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="gcash_number">GCash Number</label>
                                    <input type="text" class="form-control" id="gcash_number" name="gcash_number" value="<?= set_value('gcash_number', $settings['gcash_number'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="gcash_qr_code">GCash QR Code</label>
                                    <input type="file" class="form-control" id="gcash_qr_code" name="gcash_qr_code" accept="image/png,image/jpeg">
                                    <small class="text-muted">Shown to customers at checkout so they can scan and pay. JPG/PNG, max 2MB.</small>
                                    <?php if (!empty($settings['gcash_qr_code'])): ?>
                                        <div style="margin-top:10px; text-align:center;">
                                            <img src="<?= BASE_URL . htmlspecialchars($settings['gcash_qr_code']); ?>" alt="Current GCash QR" style="width:120px;height:120px;object-fit:contain;border:1px solid var(--gray-200);border-radius:8px;padding:6px;">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 30px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Payment Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
</div>
