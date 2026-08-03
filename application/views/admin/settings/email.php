<?php
$page_title = 'Email Settings';
$current_page = 'settings';
?>
<style>
/* Two side-by-side buttons with no wrap/stacking rule squeeze each other's
   text onto multiple lines (or clip it) once the row is narrower than
   both buttons' natural single-line width — stack them instead below
   this width so each gets the full row to itself. */
@media (max-width: 600px) {
    .settings-action-row {
        flex-direction: column;
        align-items: stretch;
    }
    .settings-action-row .btn {
        width: 100%;
    }
}
</style>
<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div style="width:46px;height:46px;border-radius:14px;background:var(--primary-pink-dark);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
                <i class="fas fa-cog"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Email Settings</h4>
                <small class="text-muted">Configure SMTP and outgoing mail options.</small>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/settings_tabs.php'; ?>

    <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
        <div class="card-body">
            <form method="post">
                        <div class="page-section" style="margin-top:0;">
                            <span class="section-title"><i class="fas fa-server me-2"></i>SMTP Configuration</span>
                            <hr>
                        </div>
                        <div class="row">
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="smtp_host">SMTP Host</label>
                                    <input type="text" class="form-control" id="smtp_host" name="smtp_host" placeholder="smtp.gmail.com" value="<?= set_value('smtp_host', $settings['smtp_host'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="smtp_port">SMTP Port</label>
                                    <input type="text" class="form-control" id="smtp_port" name="smtp_port" value="<?= set_value('smtp_port', $settings['smtp_port'] ?? '587'); ?>">
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="smtp_user">SMTP User</label>
                                    <input type="text" class="form-control" id="smtp_user" name="smtp_user" placeholder="you@gmail.com" value="<?= set_value('smtp_user', $settings['smtp_user'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="smtp_password">SMTP Password (Gmail App Password)</label>
                                    <input type="password" class="form-control" id="smtp_password" name="smtp_password"
                                           placeholder="<?= !empty($settings['smtp_password']) ? '●●●●●●●● — leave blank to keep current' : 'Not set yet'; ?>">
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="smtp_encryption">Encryption</label>
                                    <select class="form-control" id="smtp_encryption" name="smtp_encryption">
                                        <?php $enc = $settings['smtp_encryption'] ?? 'tls'; ?>
                                        <option value="tls" <?= $enc === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                        <option value="ssl" <?= $enc === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="smtp_enabled">Status</label>
                                    <div class="form-check form-switch" style="padding-top:8px;">
                                        <input type="checkbox" class="form-check-input" id="smtp_enabled" name="smtp_enabled" value="1" <?= ($settings['smtp_enabled'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="smtp_enabled">Enable SMTP sending</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="page-section">
                            <span class="section-title"><i class="fas fa-paper-plane me-2"></i>Sender Details</span>
                            <hr>
                        </div>
                        <div class="row">
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="from_email">From Email</label>
                                    <input type="email" class="form-control" id="from_email" name="from_email" value="<?= set_value('from_email', $settings['from_email'] ?? 'noreply@dropsell.com'); ?>">
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="from_name">From Name</label>
                                    <input type="text" class="form-control" id="from_name" name="from_name" value="<?= set_value('from_name', $settings['from_name'] ?? 'DropSell'); ?>">
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="reply_to">Reply-To (optional)</label>
                                    <input type="email" class="form-control" id="reply_to" name="reply_to" value="<?= set_value('reply_to', $settings['reply_to'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group d-flex gap-2 settings-action-row" style="margin-top: 30px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Email Settings
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="openModal('testEmailModal')">
                                <i class="fas fa-paper-plane"></i> Send Test Email
                            </button>
                        </div>
                    </form>
                </div>
            </div>
</div>

<!-- Send Test Email Modal -->
<div class="modal" id="testEmailModal">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h3 class="modal-title">Send Test Email</h3>
            <button type="button" class="modal-close" onclick="closeModal(document.getElementById('testEmailModal'))">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="test_email">Recipient Address</label>
                <input type="email" class="form-control" id="test_email" placeholder="you@example.com">
            </div>
            <div id="testEmailResult" style="display:none;margin-top:10px;padding:10px 12px;border-radius:8px;font-size:14px;"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal(document.getElementById('testEmailModal'))">Cancel</button>
            <button type="button" class="btn btn-sm btn-primary" id="btnSendTestEmail">Send</button>
        </div>
    </div>
</div>

<script>
document.getElementById('btnSendTestEmail').addEventListener('click', function () {
    var email = document.getElementById('test_email').value.trim();
    var resultBox = document.getElementById('testEmailResult');
    var btn = this;

    resultBox.style.display = 'none';
    btn.disabled = true;
    btn.textContent = 'Sending…';

    var formData = new FormData();
    formData.append('test_email', email);

    fetch('<?= site_url('admin/settings/send_test_email'); ?>', { method: 'POST', body: formData })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            resultBox.style.display = 'block';
            resultBox.textContent = data.message;
            resultBox.style.background = data.success ? '#e8f5e9' : '#ffebee';
            resultBox.style.color = data.success ? '#2e7d32' : '#c62828';
        })
        .catch(function () {
            resultBox.style.display = 'block';
            resultBox.textContent = 'Request failed. Please try again.';
            resultBox.style.background = '#ffebee';
            resultBox.style.color = '#c62828';
        })
        .finally(function () {
            btn.disabled = false;
            btn.textContent = 'Send';
        });
});
</script>
