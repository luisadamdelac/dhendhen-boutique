<?php
$page_title   = 'Pending Reseller Registrations';
$current_page = 'reseller';
?>
<div class="container-fluid py-4 fade-in">

    <!-- Page Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Pending Reseller Registrations</h4>
            <small class="text-muted">Review and approve direct reseller sign-ups.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/reseller'); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Resellers
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <select class="form-select form-select-sm" onchange="location.href='<?= site_url('admin/reseller/pending-registrations'); ?>?status='+this.value">
                    <option value="pending" <?= $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="active" <?= $status === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="rejected" <?= $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-9 d-flex align-items-center justify-content-end">
                <span class="text-muted small"><?= number_format(count($pending)); ?> registration<?= count($pending) != 1 ? 's' : ''; ?></span>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm mb-2" style="border-radius:12px;overflow:hidden;">
        <div class="table-responsive">
            <table class="table inv-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Applicant</th>
                        <th>Business Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Applied</th>
                        <th>Admin Remarks</th>
                        <th class="text-center pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pending)): ?>
                        <?php foreach ($pending as $r): ?>
                            <tr>
                                <td class="ps-3 fw-semibold">
                                    <?= htmlspecialchars(trim($r['first_name'] . ' ' . ($r['middle_name'] ? $r['middle_name'] . ' ' : '') . $r['last_name'])); ?>
                                </td>
                                <td><?= htmlspecialchars($r['business_name'] ?? '—'); ?></td>
                                <td><?= htmlspecialchars($r['email']); ?></td>
                                <td><?= htmlspecialchars($r['contact_number']); ?></td>
                                <td>
                                    <span style="font-size:12px;color:#8a94ad;">
                                        <?= htmlspecialchars(implode(', ', array_filter([$r['street'], $r['barangay'], $r['city']]))); ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="font-size:12px;color:#8a94ad;">
                                        <?= date('M d, Y', strtotime($r['registered_at'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="font-size:12px;color:#8a94ad;"><?= !empty($r['admin_remarks']) ? htmlspecialchars($r['admin_remarks']) : '—'; ?></span>
                                </td>
                                <td class="text-center pe-3">
                                    <?php if ($r['status'] === 'pending'): ?>
                                        <div class="d-flex gap-1 justify-content-center">
                                            <button class="btn btn-sm btn-outline-success btn-approve"
                                                    data-id="<?= $r['reseller_id']; ?>"
                                                    data-name="<?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?>"
                                                    title="Approve" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                                <i class="fas fa-check" style="font-size:11px;"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger btn-reject"
                                                    data-id="<?= $r['reseller_id']; ?>"
                                                    data-name="<?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?>"
                                                    title="Reject" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                                <i class="fas fa-times" style="font-size:11px;"></i>
                                            </button>
                                        </div>
                                    <?php elseif ($r['status'] === 'rejected'): ?>
                                        <div class="d-flex gap-1 justify-content-center">
                                            <button class="btn btn-sm btn-outline-success btn-approve"
                                                    data-id="<?= $r['reseller_id']; ?>"
                                                    data-name="<?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?>"
                                                    data-reapprove="1"
                                                    title="Re-approve" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                                <i class="fas fa-undo" style="font-size:11px;"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-check-circle fa-2x text-muted mb-2 d-block"></i>
                                <span class="text-muted">No registrations found</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal" id="confirmModal">
    <div class="modal-content modal-sm">
        <div class="modal-header" id="modalHeader">
            <h3 class="modal-title" id="modalTitle"></h3>
            <button type="button" class="modal-close" onclick="closeModal(document.getElementById('confirmModal'))">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p id="modalBody"></p>
            <div class="form-group" id="remarksGroup" style="display:none;margin-top:10px;">
                <label for="adminRemarks">Reason for rejection (optional)</label>
                <textarea class="form-control" id="adminRemarks" rows="3" placeholder="Let the applicant know why..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" id="btnCancel" onclick="closeModal(document.getElementById('confirmModal'))">Cancel</button>
            <button type="button" class="btn btn-sm" id="btnConfirm">
                <span id="btnConfirmText">Confirm</span>
                <span id="btnConfirmSpinner" class="spinner-border spinner-border-sm ms-1 d-none"></span>
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    var pendingAction = null;

    function resetConfirmBtn(label) {
        var btn = document.getElementById('btnConfirm');
        btn.disabled = false;
        document.getElementById('btnCancel').disabled = false;
        document.getElementById('btnConfirmText').textContent = label;
        document.getElementById('btnConfirmSpinner').classList.add('d-none');
    }

    document.querySelectorAll('.btn-approve').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id   = this.dataset.id;
            var name = this.dataset.name;
            var isReapprove = this.dataset.reapprove === '1';
            pendingAction = { id: id, type: 'approve' };
            document.getElementById('modalTitle').textContent = isReapprove ? 'Re-approve Registration' : 'Approve Registration';
            document.getElementById('modalBody').textContent  = isReapprove
                ? 'This registration was previously rejected. Re-approve ' + name + ' as a reseller?'
                : 'Approve ' + name + ' as a reseller?';
            document.getElementById('remarksGroup').style.display = 'none';
            document.getElementById('modalHeader').style.background = 'linear-gradient(135deg, #28a745 0%, #218838 100%)';
            document.getElementById('modalHeader').style.color = '#fff';
            // .modal-title has its own color rule that otherwise wins over
            // the header's inherited white.
            document.getElementById('modalTitle').style.color = '#fff';
            document.getElementById('btnConfirm').className   = 'btn btn-sm btn-success';
            resetConfirmBtn('Approve');
            openModal('confirmModal');
        });
    });

    document.querySelectorAll('.btn-reject').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id   = this.dataset.id;
            var name = this.dataset.name;
            pendingAction = { id: id, type: 'reject' };
            document.getElementById('modalTitle').textContent = 'Reject Registration';
            document.getElementById('modalBody').textContent  = 'Reject ' + name + '\'s reseller application?';
            document.getElementById('adminRemarks').value = '';
            document.getElementById('remarksGroup').style.display = 'block';
            document.getElementById('modalHeader').style.background = 'linear-gradient(135deg, #dc3545 0%, #c82333 100%)';
            document.getElementById('modalHeader').style.color = '#fff';
            document.getElementById('modalTitle').style.color = '#fff';
            document.getElementById('btnConfirm').className   = 'btn btn-sm btn-danger';
            resetConfirmBtn('Reject');
            openModal('confirmModal');
        });
    });

    document.getElementById('btnConfirm').addEventListener('click', function () {
        if (!pendingAction) return;

        var btn = this;
        btn.disabled = true;
        document.getElementById('btnCancel').disabled = true;
        document.getElementById('btnConfirmSpinner').classList.remove('d-none');

        var url = pendingAction.type === 'approve'
            ? '<?= site_url('admin/reseller/approve-registration/'); ?>' + pendingAction.id
            : '<?= site_url('admin/reseller/reject-registration/'); ?>' + pendingAction.id;

        var options = { method: 'POST' };
        if (pendingAction.type === 'reject') {
            var remarks = document.getElementById('adminRemarks').value;
            options.headers = { 'Content-Type': 'application/x-www-form-urlencoded' };
            options.body = new URLSearchParams({ admin_remarks: remarks }).toString();
        }

        fetch(url, options)
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                location.reload();
            } else {
                closeModal(document.getElementById('confirmModal'));
                alert(data.message || 'Action failed.');
                resetConfirmBtn(pendingAction.type === 'approve' ? 'Approve' : 'Reject');
                pendingAction = null;
            }
        })
        .catch(function () {
            closeModal(document.getElementById('confirmModal'));
            alert('Request failed. Please try again.');
            resetConfirmBtn(pendingAction.type === 'approve' ? 'Approve' : 'Reject');
            pendingAction = null;
        });
    });
})();
</script>
