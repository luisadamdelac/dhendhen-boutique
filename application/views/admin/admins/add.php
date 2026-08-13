<?php
// application/views/admin/admins/add.php
?>
<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1a1a2e;"><i class="fas fa-user-shield"></i> Add New Admin</h4>
            <small class="text-muted">Create a new administrator account.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/admins'); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Admins
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="post">
                <div class="row">
                    <div class="col col-4">
                        <div class="form-group">
                            <label for="first_name">First Name <span style="color:var(--danger);">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?= set_value('first_name'); ?>" required>
                        </div>
                    </div>
                    <div class="col col-4">
                        <div class="form-group">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?= set_value('middle_name'); ?>">
                        </div>
                    </div>
                    <div class="col col-4">
                        <div class="form-group">
                            <label for="last_name">Last Name <span style="color:var(--danger);">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?= set_value('last_name'); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-6">
                        <div class="form-group">
                            <label for="email">Email <span style="color:var(--danger);">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= set_value('email'); ?>" required>
                        </div>
                    </div>
                    <div class="col col-6">
                        <div class="form-group">
                            <label for="contact_number">Contact Number <span style="color:var(--danger);">*</span></label>
                            <input type="text" class="form-control" id="contact_number" name="contact_number" value="<?= set_value('contact_number'); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-4">
                        <div class="form-group">
                            <label for="street">Street</label>
                            <input type="text" class="form-control" id="street" name="street" value="<?= set_value('street'); ?>">
                        </div>
                    </div>
                    <div class="col col-4">
                        <div class="form-group">
                            <label for="barangay">Barangay</label>
                            <input type="text" class="form-control" id="barangay" name="barangay" value="<?= set_value('barangay'); ?>">
                        </div>
                    </div>
                    <div class="col col-4">
                        <div class="form-group">
                            <label for="city">City / Municipality <span style="color:var(--danger);">*</span></label>
                            <input type="text" class="form-control" id="city" name="city" value="<?= set_value('city'); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="branch_id">Assigned Branch</label>
                    <select class="form-control" id="branch_id" name="branch_id">
                        <option value="">Unassigned</option>
                        <?php foreach (($branches ?? []) as $b): ?>
                            <option value="<?= $b['branch_id']; ?>" <?= set_value('branch_id') == $b['branch_id'] ? 'selected' : ''; ?>><?= htmlspecialchars($b['branch_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <span><strong>Note:</strong> A random one-time temporary password will be generated and shown here after you create the account. Share it securely (it can't be retrieved again afterward) and ask the new admin to change it on first login.</span>
                </div>

                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn btn-primary">Create Admin</button>
                    <a href="<?= site_url('admin/admins'); ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
