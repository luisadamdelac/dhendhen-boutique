<!-- Review Details -->
<div class="container-fluid py-4 fade-in">
    <!-- Page Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1a1a2e;"><i class="fas fa-star"></i> Review Details</h4>
            <small class="text-muted">View review content and moderate its visibility.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?php echo BASE_URL; ?>admin/reviews" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Reviews
            </a>
        </div>
    </div>

    <!-- Review Information -->
    <div class="row">
        <div class="col col-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Review Information</h3>
                    <div class="btn-group">
                        <?php if (!$review['is_visible']): ?>
                        <button class="btn btn-success btn-sm" 
                                onclick="approveReview(<?php echo $review['review_id']; ?>)">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button class="btn btn-danger btn-sm" 
                                onclick="rejectReview(<?php echo $review['review_id']; ?>)">
                            <i class="fas fa-times"></i> Reject
                        </button>
                        <?php endif; ?>
                        <button class="btn btn-danger btn-sm" 
                                onclick="deleteReview(<?php echo $review['review_id']; ?>)">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Review Status -->
                    <div style="margin-bottom: 30px; padding: 20px; background: var(--gradient-card); border-radius: 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h4 style="margin: 0 0 10px 0;">Review Status</h4>
                                <?php if ($review['is_visible'] && $review['is_verified']): ?>
                                    <span class="badge-status badge-approved" style="font-size: 13px;">
                                        <i class="fas fa-check-circle"></i> Approved & Visible
                                    </span>
                                <?php else: ?>
                                    <span class="badge-status badge-pending" style="font-size: 13px;">
                                        <i class="fas fa-clock"></i> Pending Approval
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div style="text-align: right;">
                                <div class="rating-display-large">
                                    <?php 
                                    $rating = intval($review['rating']);
                                    for ($i = 1; $i <= 5; $i++): 
                                    ?>
                                        <i class="fas fa-star <?php echo $i <= $rating ? 'star-filled' : 'star-empty'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <div style="font-size: 32px; font-weight: 700; color: var(--primary-pink); margin-top: 10px;">
                                    <?php echo $rating; ?>/5
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Product Information -->
                    <div class="info-section">
                        <h4><i class="fas fa-box" style="color: var(--primary-pink);"></i> Product Information</h4>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Product Name:</label>
                                <span><?php echo htmlspecialchars($review['product_name']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Customer Information -->
                    <div class="info-section">
                        <h4><i class="fas fa-user" style="color: var(--secondary-violet);"></i> Customer Information</h4>
                        <?php
                            $customerAvatarSrc = !empty($review['customer_profile_image'])
                                ? BASE_URL . $review['customer_profile_image']
                                : BASE_URL . default_avatar_url();
                        ?>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Customer Name:</label>
                                <span style="display:flex;align-items:center;gap:8px;">
                                    <img src="<?php echo $customerAvatarSrc; ?>" alt=""
                                         style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                                    <?php echo htmlspecialchars($review['customer_name']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Review Content -->
                    <div class="info-section">
                        <h4><i class="fas fa-comment" style="color: var(--secondary-purple);"></i> Review Content</h4>
                        <div class="review-content">
                            <p style="line-height: 1.8; color: #333; font-size: 15px;">
                                <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Review Metadata -->
                    <div class="info-section">
                        <h4><i class="fas fa-info-circle" style="color: #17a2b8;"></i> Additional Information</h4>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Review ID:</label>
                                <span>#<?php echo htmlspecialchars($review['review_id']); ?></span>
                            </div>
                            <div class="info-item">
                                <label>Submitted On:</label>
                                <span><?php echo date('F d, Y h:i A', strtotime($review['created_at'])); ?></span>
                            </div>
                            <div class="info-item">
                                <label>Verified Purchase:</label>
                                <span>
                                    <?php if ($review['is_verified']): ?>
                                        <span style="color: #28a745;"><i class="fas fa-check-circle"></i> Yes</span>
                                    <?php else: ?>
                                        <span style="color: #6c757d;"><i class="fas fa-times-circle"></i> Not Verified</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <label>Visible to Public:</label>
                                <span>
                                    <?php if ($review['is_visible']): ?>
                                        <span style="color: #28a745;"><i class="fas fa-eye"></i> Yes</span>
                                    <?php else: ?>
                                        <span style="color: #dc3545;"><i class="fas fa-eye-slash"></i> No</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col col-4">
            <!-- Review Timeline -->
            <div class="card" style="margin-top: 20px;">
                <div class="card-header">
                    <h3 class="card-title">Timeline</h3>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-icon" style="background: var(--primary-pink);">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="timeline-content">
                                <h5>Review Submitted</h5>
                                <p><?php echo date('M d, Y h:i A', strtotime($review['created_at'])); ?></p>
                            </div>
                        </div>
                        
                        <?php if ($review['is_visible'] && $review['is_verified']): ?>
                        <div class="timeline-item">
                            <div class="timeline-icon" style="background: #28a745;">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h5>Review Approved</h5>
                                <p>Now visible to customers</p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rating-display-large {
    display: flex;
    gap: 5px;
}

.rating-display-large .star-filled {
    color: #ffc107;
    font-size: 28px;
}

.rating-display-large .star-empty {
    color: #dee2e6;
    font-size: 28px;
}

.info-section {
    margin-bottom: 30px;
    padding-bottom: 30px;
    border-bottom: 1px solid #f0f0f0;
}

.info-section:last-child {
    border-bottom: none;
}

.info-section h4 {
    margin-bottom: 20px;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

@media (max-width: 600px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.info-item label {
    font-size: 13px;
    color: var(--gray);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-item span {
    font-size: 15px;
    color: #333;
    font-weight: 500;
}

.review-content {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid var(--primary-pink);
}

.timeline {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.timeline-item {
    display: flex;
    gap: 15px;
}

.timeline-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.timeline-content h5 {
    margin: 0 0 5px 0;
    font-size: 14px;
    font-weight: 600;
}

.timeline-content p {
    margin: 0;
    font-size: 12px;
    color: var(--gray);
}

.badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.btn-group {
    display: flex;
    gap: 8px;
}
</style>

<script>
// Approve review
function approveReview(reviewId) {
    customConfirm('Are you sure you want to approve this review? It will become visible to all customers.', function() {
        showLoader();

        fetch('<?php echo BASE_URL; ?>review/approve', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'review_id=' + reviewId
        })
        .then(response => response.json())
        .then(data => {
            hideLoader();
            if (data.success) {
                showNotification('success', data.message || 'Review approved successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('error', data.message || 'Failed to approve review');
            }
        })
        .catch(error => {
            hideLoader();
            showNotification('error', 'An error occurred');
            console.error('Error:', error);
        });
    }, { title: 'Approve Review' });
}

// Reject review
function rejectReview(reviewId) {
    customConfirm('Are you sure you want to reject this review? It will not be visible to customers.', function() {
        showLoader();

        fetch('<?php echo BASE_URL; ?>review/reject', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'review_id=' + reviewId
        })
        .then(response => response.json())
        .then(data => {
            hideLoader();
            if (data.success) {
                showNotification('success', data.message || 'Review rejected successfully');
                setTimeout(() => {
                    window.location.href = '<?php echo BASE_URL; ?>admin/reviews';
                }, 1000);
            } else {
                showNotification('error', data.message || 'Failed to reject review');
            }
        })
        .catch(error => {
            hideLoader();
            showNotification('error', 'An error occurred');
            console.error('Error:', error);
        });
    }, { title: 'Reject Review' });
}

// Delete review
function deleteReview(reviewId) {
    customConfirm('Are you sure you want to permanently delete this review? This action cannot be undone.', function() {
        showLoader();

        fetch('<?php echo BASE_URL; ?>review/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'review_id=' + reviewId
        })
        .then(response => response.json())
        .then(data => {
            hideLoader();
            if (data.success) {
                showNotification('success', data.message || 'Review deleted successfully');
                setTimeout(() => {
                    window.location.href = '<?php echo BASE_URL; ?>admin/reviews';
                }, 1000);
            } else {
                showNotification('error', data.message || 'Failed to delete review');
            }
        })
        .catch(error => {
            hideLoader();
            showNotification('error', 'An error occurred');
            console.error('Error:', error);
        });
    }, { title: 'Delete Review' });
}
</script>
