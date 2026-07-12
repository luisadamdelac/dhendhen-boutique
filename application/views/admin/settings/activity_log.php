<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Activity Log</h4>
            <small class="text-muted">History of actions taken by admins, staff, and resellers across the system.</small>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/settings_tabs.php'; ?>

    <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
        <div class="card-body">
            <form method="get" class="row g-2 align-items-end mb-3">
                <div class="col-md-4">
                    <label class="form-label mb-1" for="search">Search action / details</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search" value="<?= htmlspecialchars($search ?? ''); ?>" placeholder="e.g. approve_reseller_registration">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1" for="user_type">Role</label>
                    <select id="user_type" name="user_type" class="form-select form-select-sm">
                        <option value="">All Roles</option>
                        <option value="admin" <?= ($user_type ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="staff" <?= ($user_type ?? '') === 'staff' ? 'selected' : ''; ?>>Staff</option>
                        <option value="reseller" <?= ($user_type ?? '') === 'reseller' ? 'selected' : ''; ?>>Reseller</option>
                        <option value="customer" <?= ($user_type ?? '') === 'customer' ? 'selected' : ''; ?>>Customer</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search"></i> Filter</button>
                </div>
                <?php if (!empty($search) || !empty($user_type)): ?>
                    <div class="col-md-2">
                        <a href="<?= site_url('admin/settings/activity_log'); ?>" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                <?php endif; ?>
            </form>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Role</th>
                            <th>User ID</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= htmlspecialchars($log['created_at']); ?></td>
                                    <td>
                                        <span class="badge badge-<?= $log['user_type'] === 'admin' ? 'primary' : 'secondary'; ?>">
                                            <?= htmlspecialchars(ucfirst($log['user_type'])); ?>
                                        </span>
                                    </td>
                                    <td><?= (int) $log['user_id']; ?></td>
                                    <td><?= htmlspecialchars($log['action']); ?></td>
                                    <td><?= htmlspecialchars($log['details'] ?? '—'); ?></td>
                                    <td><?= htmlspecialchars($log['ip_address'] ?? '—'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No activity logged yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($pages) && $pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a class="page-link <?= $i == ($page ?? 1) ? 'active' : ''; ?>"
                           href="<?= site_url('admin/settings/activity_log?page=' . $i . '&search=' . urlencode($search ?? '') . '&user_type=' . urlencode($user_type ?? '')); ?>"><?= $i; ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
