<?php
// application/views/admin/admins/edit.php
?>
<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1a1a2e;"><i class="fas fa-user-shield"></i> Edit Admin — <?php echo htmlspecialchars(trim($admin['first_name'] . ' ' . $admin['last_name'])); ?></h4>
            <small class="text-muted">Update administrator account details.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/admins'); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Admins
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col col-4">
                        <div class="form-group">
                            <label>First Name <span style="color:var(--danger);">*</span></label>
                            <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($admin['first_name'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="col col-4">
                        <div class="form-group">
                            <label>Middle Name</label>
                            <input type="text" class="form-control" name="middle_name" value="<?php echo htmlspecialchars($admin['middle_name'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col col-4">
                        <div class="form-group">
                            <label>Last Name <span style="color:var(--danger);">*</span></label>
                            <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($admin['last_name'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-6">
                        <div class="form-group">
                            <label>Email (Read-Only)</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" disabled>
                        </div>
                    </div>
                    <div class="col col-6">
                        <div class="form-group">
                            <label>Contact Number <span style="color:var(--danger);">*</span></label>
                            <input type="text" class="form-control" name="contact_number" value="<?php echo htmlspecialchars($admin['contact_number'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col col-3">
                        <div class="form-group">
                            <label>Street</label>
                            <input type="text" class="form-control" name="street" value="<?php echo htmlspecialchars($admin['street'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col col-3">
                        <div class="form-group">
                            <label>Barangay</label>
                            <input type="text" class="form-control" name="barangay" value="<?php echo htmlspecialchars($admin['barangay'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col col-3">
                        <div class="form-group">
                            <label>City <span style="color:var(--danger);">*</span></label>
                            <input type="text" class="form-control" name="city" value="<?php echo htmlspecialchars($admin['city'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="col col-3">
                        <div class="form-group">
                            <label>Municipality</label>
                            <input type="text" class="form-control" name="municipality" value="<?php echo htmlspecialchars($admin['municipality'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Assigned Branch</label>
                    <select class="form-control" name="branch_id">
                        <option value="">Unassigned</option>
                        <?php foreach (($branches ?? []) as $b): ?>
                            <option value="<?= $b['branch_id']; ?>" <?= ($admin['branch_id'] ?? '') == $b['branch_id'] ? 'selected' : ''; ?>><?= htmlspecialchars($b['branch_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Update Admin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
