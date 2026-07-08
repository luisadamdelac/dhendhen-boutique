<?php
$page_title = 'Edit Reseller';
$current_page = 'reseller';
?>
<div class="container-fluid py-4 fade-in">

    <!-- Page Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1a1a2e;"><i class="fas fa-user-edit"></i> Edit Reseller</h4>
            <small class="text-muted">Update reseller profile and address details.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/reseller/view/' . $reseller['reseller_id']); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card">
        <form method="post">
            <div class="row">
                <div class="col col-6">
                    <div class="form-group">
                        <label for="first_name">First Name <span style="color: var(--danger);">*</span></label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?= set_value('first_name', $reseller['first_name'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="col col-6">
                    <div class="form-group">
                        <label for="last_name">Last Name <span style="color: var(--danger);">*</span></label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?= set_value('last_name', $reseller['last_name'] ?? ''); ?>" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col col-6">
                    <div class="form-group">
                        <label for="contact_number">Contact Number <span style="color: var(--danger);">*</span></label>
                        <input type="text" class="form-control" id="contact_number" name="contact_number" value="<?= set_value('contact_number', $reseller['contact_number'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="col col-6">
                    <div class="form-group">
                        <label for="business_name">Business Name</label>
                        <input type="text" class="form-control" id="business_name" name="business_name" value="<?= set_value('business_name', $reseller['business_name'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col col-4">
                    <div class="form-group">
                        <label for="street">Street</label>
                        <input type="text" class="form-control" id="street" name="street" value="<?= set_value('street', $reseller['street'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col col-4">
                    <div class="form-group">
                        <label for="barangay">Barangay</label>
                        <input type="text" class="form-control" id="barangay" name="barangay" value="<?= set_value('barangay', $reseller['barangay'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col col-4">
                    <div class="form-group">
                        <label for="city">City <span style="color: var(--danger);">*</span></label>
                        <input type="text" class="form-control" id="city" name="city" value="<?= set_value('city', $reseller['city'] ?? ''); ?>" required>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </form>
    </div>
</div>
