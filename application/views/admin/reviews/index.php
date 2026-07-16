<?php
    $totalReviews  = $stats['total_reviews'] ?? 0;
    $pendingCount  = $stats['pending_reviews'] ?? 0;
    $approvedCount = $stats['approved_reviews'] ?? 0;
    $avgRating     = $stats['average_rating'] ?? 0;
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.css">
<style>
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid var(--border); border-radius: var(--radius-md);
    padding: .4rem .75rem; font-size: .85rem; margin-left: .5rem;
}
.dataTables_wrapper .dataTables_filter input:focus { outline: none; border-color: var(--primary-pink); }
table.dataTable thead th { position: relative; }
table.dataTable thead th.sorting:hover { color: var(--primary-pink); cursor: pointer; }
</style>
<div class="container-fluid py-4 fade-in">

    <!-- Page Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Reviews</h4>
            <small class="text-muted">Manage customer reviews and approval status.</small>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/order_tabs.php'; ?>

    <!-- Stat Cards -->
    <div class="row g-3 mb-2">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#eef0ff;color:#4361ee;"><i class="fas fa-comments"></i></div>
                <div>
                    <div class="stat-label">Total Reviews</div>
                    <div class="stat-value"><?php echo number_format($totalReviews); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff8e1;color:#f57f17;"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="stat-label">Pending Reviews</div>
                    <div class="stat-value"><?php echo number_format($pendingCount); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-label">Approved Reviews</div>
                    <div class="stat-value"><?php echo number_format($approvedCount); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ede7f6;color:#5e35b1;"><i class="fas fa-star"></i></div>
                <div>
                    <div class="stat-label">Average Rating</div>
                    <div class="stat-value"><?php echo number_format($avgRating, 1); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== REVIEWS SECTION ===================== -->
    <div class="page-section">
        <span class="section-title"><i class="fas fa-comments me-2 text-primary"></i>All Reviews</span>
        <hr>
        <span class="text-muted small" style="white-space:nowrap;"><?php echo number_format($totalReviews); ?> result<?php echo $totalReviews != 1 ? 's' : ''; ?></span>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="row g-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label class="form-label small text-muted mb-1" for="filterReviewStatus">Filter by status</label>
                <select id="filterReviewStatus" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                </select>
            </div>
            <div class="col-md-2 d-flex">
                <button type="button" id="clearReviewFiltersBtn" class="btn btn-sm btn-outline-secondary flex-fill">
                    <i class="fas fa-times"></i> Clear Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Reviews Table -->
    <div class="card border-0 shadow-sm mb-2" style="border-radius:12px;overflow:hidden;">
        <div class="table-responsive">
            <table class="table inv-table mb-0 table-stack" id="reviewsDataTable">
                <thead>
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th class="text-center">Status</th>
                        <th>Date</th>
                        <th class="text-center pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                        <?php foreach ($reviews as $review):
                            $isApproved = ($review['is_visible'] && $review['is_verified']);
                            $reviewStatus = $isApproved ? 'approved' : 'pending';
                        ?>
                        <tr data-status="<?php echo $reviewStatus; ?>">
                            <td class="ps-3">
                                <strong style="color:#1a1a2e;">#<?php echo htmlspecialchars($review['review_id']); ?></strong>
                            </td>
                            <td>
                                <span class="fw-semibold" style="font-size:13px;color:#1a1a2e;"><?php echo htmlspecialchars($review['product_name']); ?></span>
                            </td>
                            <td>
                                <div class="fw-semibold" style="font-size:13px;color:#1a1a2e;"><?php echo htmlspecialchars($review['customer_name']); ?></div>
                                <div style="font-size:11px;color:#8a94ad;"><?php echo htmlspecialchars($review['customer_email']); ?></div>
                            </td>
                            <td>
                                <div class="rating-display">
                                    <?php
                                    $rating = intval($review['rating']);
                                    for ($i = 1; $i <= 5; $i++):
                                    ?>
                                        <i class="fas fa-star <?php echo $i <= $rating ? 'star-filled' : 'star-empty'; ?>"></i>
                                    <?php endfor; ?>
                                    <span class="fw-semibold ms-1"><?php echo $rating; ?>/5</span>
                                </div>
                            </td>
                            <td style="max-width:250px;">
                                <div class="text-truncate small text-muted"><?php echo htmlspecialchars($review['review_text']); ?></div>
                            </td>
                            <td class="text-center">
                                <?php if ($isApproved): ?>
                                    <span class="badge-status badge-approved">Approved</span>
                                <?php else: ?>
                                    <span class="badge-status badge-pending">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="text-muted small"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span></td>
                            <td class="text-center pe-3">
                                <div class="d-flex gap-1 justify-content-center">
                                    <?php if (!$isApproved): ?>
                                    <button class="btn btn-sm btn-outline-success"
                                            onclick="approveReview(<?php echo $review['review_id']; ?>)"
                                            title="Approve" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-check" style="font-size:11px;"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="rejectReview(<?php echo $review['review_id']; ?>)"
                                            title="Reject" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-times" style="font-size:11px;"></i>
                                    </button>
                                    <?php endif; ?>
                                    <a href="<?php echo BASE_URL; ?>admin/reviews/view/<?php echo $review['review_id']; ?>"
                                       class="btn btn-sm btn-outline-info"
                                       title="View Details" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-eye" style="font-size:11px;"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="deleteReview(<?php echo $review['review_id']; ?>)"
                                            title="Delete" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-trash" style="font-size:11px;"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="<?php echo BASE_URL; ?>public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
    // Excel/PDF export lives in Reports, not on this operational list page.
    const table = $('#reviewsDataTable').DataTable({
        order: [[6, 'desc']],
        columnDefs: [{ orderable: false, targets: [4, 7] }],
        language: { search: '_INPUT_', searchPlaceholder: 'Search product, customer, or review…', emptyTable: 'No reviews found' },
        drawCallback: function() { if (typeof initResponsiveTableStacking === 'function') initResponsiveTableStacking(); }
    });

    $.fn.dataTable.ext.search.push(function (settings, searchData, index) {
        if (settings.nTable.id !== 'reviewsDataTable') return true;
        const statusFilter = $('#filterReviewStatus').val();
        if (!statusFilter) return true;
        const $row = $(settings.aoData[index].nTr);
        return $row.data('status') === statusFilter;
    });

    $('#filterReviewStatus').on('change', function () { table.draw(); });
    $('#clearReviewFiltersBtn').on('click', function () {
        $('#filterReviewStatus').val('');
        table.search('').draw();
    });
});

// Approve review
function approveReview(reviewId) {
    customConfirm('Are you sure you want to approve this review?', function() {
        fetch('<?php echo BASE_URL; ?>review/approve', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'review_id=' + reviewId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Review approved successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message || 'Failed to approve review', 'danger');
            }
        })
        .catch(error => {
            showToast('An error occurred', 'danger');
            console.error('Error:', error);
        });
    }, { title: 'Approve Review' });
}

// Reject review
function rejectReview(reviewId) {
    customConfirm('Are you sure you want to reject this review?', function() {
        fetch('<?php echo BASE_URL; ?>review/reject', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'review_id=' + reviewId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Review rejected successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message || 'Failed to reject review', 'danger');
            }
        })
        .catch(error => {
            showToast('An error occurred', 'danger');
            console.error('Error:', error);
        });
    }, { title: 'Reject Review' });
}

// Delete review
function deleteReview(reviewId) {
    customConfirm('Are you sure you want to permanently delete this review? This action cannot be undone.', function() {
        fetch('<?php echo BASE_URL; ?>review/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'review_id=' + reviewId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Review deleted successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message || 'Failed to delete review', 'danger');
            }
        })
        .catch(error => {
            showToast('An error occurred', 'danger');
            console.error('Error:', error);
        });
    }, { title: 'Delete Review' });
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification toast-' + type;
    toast.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i> ' + message;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3000);
}
</script>
