<div class="row">
    <div class="col col-8 offset-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-plus"></i> Create New Staff Account
                </h3>
            </div>
            <div class="card-body">
                <?php if(!empty($data['error']) && is_string($data['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($data['error']); ?>
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="full_name"><strong>Full Name *</strong></label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="email"><strong>Email Address *</strong></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="password"><strong>Password *</strong></label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Min 8 characters" required>
                            <small class="form-text text-muted">Must be at least 8 characters</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="phone"><strong>Phone Number</strong></label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="staff_id_number"><strong>Staff ID Number *</strong></label>
                            <input type="text" class="form-control" id="staff_id_number" name="staff_id_number" placeholder="e.g., STAFF001" required>
                            <small class="form-text text-muted">Unique identifier for this staff member</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="hire_date"><strong>Hire Date *</strong></label>
                            <input type="date" class="form-control" id="hire_date" name="hire_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <a href="<?php echo BASE_URL; ?>admin/staff" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary float-right">
                            <i class="fas fa-check"></i> Create Staff Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
