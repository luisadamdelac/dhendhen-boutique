<?php
$page_title = 'PayMongo Settings';
$current_page = 'settings';
?>
<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">PayMongo Settings</h4>
            <small class="text-muted">API keys for the checkout flow — GCash, GrabPay, Maya, and card payments via PayMongo's hosted checkout.</small>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/settings_tabs.php'; ?>

    <div class="row">
        <div class="col">
            <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
                <div class="card-body">
                    <form method="post">
                        <div class="page-section" style="margin-top:0;">
                            <span class="section-title"><i class="fas fa-shield-halved me-2"></i>PayMongo</span>
                            <hr>
                        </div>

                        <div class="form-group">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="paymongo_enabled" name="paymongo_enabled" value="1" <?= !empty($settings['paymongo_enabled']) && $settings['paymongo_enabled'] === '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="paymongo_enabled">Enable PayMongo checkout</label>
                            </div>
                            <small class="text-muted">While disabled (or if the Secret Key below is empty), customers will see "online payment is currently unavailable" at checkout.</small>
                        </div>

                        <div class="row">
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="paymongo_public_key">Public Key</label>
                                    <input type="text" class="form-control" id="paymongo_public_key" name="paymongo_public_key" placeholder="pk_test_..." value="<?= set_value('paymongo_public_key', $settings['paymongo_public_key'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="paymongo_secret_key">Secret Key</label>
                                    <input type="password" class="form-control" id="paymongo_secret_key" name="paymongo_secret_key" placeholder="<?= !empty($settings['paymongo_secret_key']) ? 'Leave blank to keep current key' : 'sk_test_...'; ?>" autocomplete="new-password">
                                    <small class="text-muted">Never shown once saved — leave blank when re-saving other fields to keep it unchanged.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="paymongo_webhook_secret">Webhook Signing Secret</label>
                                    <input type="password" class="form-control" id="paymongo_webhook_secret" name="paymongo_webhook_secret" placeholder="<?= !empty($settings['paymongo_webhook_secret']) ? 'Leave blank to keep current key' : 'whsk_...'; ?>" autocomplete="new-password">
                                    <small class="text-muted">From the webhook's settings page in the PayMongo dashboard.</small>
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label>Webhook URL</label>
                                    <input type="text" class="form-control" readonly value="<?= BASE_URL . 'webhooks/paymongo'; ?>" onclick="this.select();">
                                    <small class="text-muted">Register this URL as a webhook in the PayMongo dashboard, listening for <code>checkout_session.payment.paid</code> and <code>payment.failed</code>.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 30px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save PayMongo Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
