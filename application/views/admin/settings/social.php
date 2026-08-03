<?php
$page_title = 'Social Media Settings';
$current_page = 'settings';
?>
<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div style="width:46px;height:46px;border-radius:14px;background:var(--primary-pink-dark);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
                <i class="fas fa-cog"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Social Media Settings</h4>
                <small class="text-muted">Links shown under "Follow Us" in the shop's footer. Leave a field blank to hide that icon.</small>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/settings_tabs.php'; ?>

    <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
        <div class="card-body">
            <form method="post">
                        <div class="row">
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="social_facebook"><i class="fab fa-facebook-f me-1"></i> Facebook Page URL</label>
                                    <input type="url" class="form-control" id="social_facebook" name="social_facebook"
                                           placeholder="https://facebook.com/yourpage"
                                           value="<?= set_value('social_facebook', $settings['social_facebook'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="social_instagram"><i class="fab fa-instagram me-1"></i> Instagram URL</label>
                                    <input type="url" class="form-control" id="social_instagram" name="social_instagram"
                                           placeholder="https://instagram.com/yourpage"
                                           value="<?= set_value('social_instagram', $settings['social_instagram'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="social_tiktok"><i class="fab fa-tiktok me-1"></i> TikTok URL</label>
                                    <input type="url" class="form-control" id="social_tiktok" name="social_tiktok"
                                           placeholder="https://tiktok.com/@yourpage"
                                           value="<?= set_value('social_tiktok', $settings['social_tiktok'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 30px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Social Links
                            </button>
                        </div>
                    </form>
                </div>
            </div>
</div>
