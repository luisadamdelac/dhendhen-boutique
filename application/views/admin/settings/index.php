<!-- System Settings -->
<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">System Settings</h4>
            <small class="text-muted">Configure system-wide settings and preferences.</small>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/settings_tabs.php'; ?>

    <div class="row">
        <div class="col">
            <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
                <div class="card-body">
                    <form method="POST" action="<?php echo site_url('admin/settings'); ?>">
                        <div class="page-section" style="margin-top:0;">
                            <span class="section-title"><i class="fas fa-store me-2"></i>Business Information</span>
                            <hr>
                        </div>
                        <div class="row">
                            <div class="col col-6">
                                <div class="form-group">
                                     <label for="company_name">Business Name</label>
                                     <input type="text" class="form-control" id="company_name" name="company_name"
                                         value="<?php echo htmlspecialchars($settings['company_name'] ?? 'DropSell'); ?>" required>
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="company_email">Contact Email</label>
                                    <input type="email" class="form-control" id="company_email" name="company_email"
                                           value="<?php echo htmlspecialchars($settings['company_email'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="company_phone">Contact Phone</label>
                                    <input type="text" class="form-control" id="company_phone" name="company_phone"
                                           value="<?php echo htmlspecialchars($settings['company_phone'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="company_address">Contact Address</label>
                                    <input type="text" class="form-control" id="company_address" name="company_address"
                                           value="<?php echo htmlspecialchars($settings['company_address'] ?? 'Calapan City, Oriental Mindoro'); ?>">
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="currency">Currency</label>
                                    <input type="text" class="form-control" id="currency" name="currency"
                                           value="<?php echo htmlspecialchars($settings['currency'] ?? 'PHP'); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="page-section">
                            <span class="section-title"><i class="fas fa-wallet me-2"></i>Reseller &amp; Commission</span>
                            <hr>
                        </div>
                        <div class="row">
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="minimum_spend">Minimum Spend to Apply as Reseller (₱)</label>
                                    <input type="number" step="0.01" class="form-control" id="minimum_spend" name="minimum_spend"
                                           value="<?php echo htmlspecialchars($settings['minimum_spend'] ?? '3000'); ?>" required>
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="minimum_withdrawal">Minimum Withdrawal Amount (₱)</label>
                                    <input type="number" step="0.01" class="form-control" id="minimum_withdrawal" name="minimum_withdrawal"
                                           value="<?php echo htmlspecialchars($settings['minimum_withdrawal'] ?? '100'); ?>" required>
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="withdrawal_schedule_dates">Withdrawal Processing Days (day-of-month, comma separated)</label>
                                    <input type="text" class="form-control" id="withdrawal_schedule_dates" name="withdrawal_schedule_dates"
                                           value="<?php echo htmlspecialchars($settings['withdrawal_schedule_dates'] ?? '15,30'); ?>" placeholder="e.g. 15,30">
                                    <small class="text-muted">New withdrawal requests are auto-scheduled for the next of these dates.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 30px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
