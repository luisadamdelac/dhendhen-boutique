<?php
$statusFilter = $statusFilter ?? 'all';
$statusCounts = $statusCounts ?? [];
$totalOrdersCount = $totalOrdersCount ?? 0;
$totalFiltered = $totalFiltered ?? count($orders);
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$perPage = $perPage ?? 10;
$reviewedProductIds = $reviewedProductIds ?? [];

$tabs = [
    ['key' => 'all',        'label' => 'All Orders', 'count' => $totalOrdersCount],
    ['key' => 'pending',    'label' => 'Pending',    'count' => $statusCounts['pending'] ?? 0],
    ['key' => 'processing', 'label' => 'Processing', 'count' => $statusCounts['processing'] ?? 0],
    ['key' => 'shipped',    'label' => 'Shipped',    'count' => $statusCounts['shipped'] ?? 0],
    ['key' => 'delivered',  'label' => 'Delivered',  'count' => $statusCounts['delivered'] ?? 0],
    ['key' => 'cancelled',  'label' => 'Cancelled',  'count' => $statusCounts['cancelled'] ?? 0],
];

$statusSubtext = [
    'pending'    => 'Waiting for confirmation',
    'processing' => 'Seller is preparing your order',
];

$showingFrom = $totalFiltered > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
$showingTo = min($totalFiltered, $currentPage * $perPage);
?>
<style>
    .orders-page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        margin-top: 10px;
    }

    .orders-page-header h1 {
        margin: 0;
    }

    .orders-page-subtitle {
        color: var(--gray-500);
        font-size: 14px;
        margin-top: 4px;
    }

    .orders-page-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .orders-time-filter,
    .orders-filter-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        background: var(--white);
        color: var(--gray-700);
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
    }

    .orders-container {
        background: var(--white);
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
        margin-top: 20px;
        overflow: hidden;
    }

    .orders-tabs {
        display: flex;
        gap: 26px;
        border-bottom: 1px solid var(--gray-200);
        padding: 0 24px;
        overflow-x: auto;
    }

    .orders-tab {
        display: flex;
        align-items: center;
        gap: 7px;
        padding: 16px 2px;
        border: none;
        background: none;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        color: var(--gray-500);
        white-space: nowrap;
        border-bottom: 2.5px solid transparent;
        margin-bottom: -1px;
    }

    .orders-tab .tab-count {
        background: var(--gray-100);
        color: var(--gray-600);
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 20px;
    }

    .orders-tab.active {
        color: var(--primary-pink-dark);
        border-bottom-color: var(--primary-pink);
    }

    .orders-tab.active .tab-count {
        background: var(--primary-pink);
        color: var(--white);
    }

    .orders-table {
        width: 100%;
        border-collapse: collapse;
    }

    .orders-table thead {
        background: var(--light-gray);
    }

    .orders-table th {
        padding: 14px 16px;
        text-align: left;
        font-weight: 700;
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: var(--gray-600) !important;
        border-bottom: 1px solid var(--gray-200);
        white-space: nowrap;
    }

    .orders-table td {
        padding: 16px;
        color: var(--gray-900);
        border-bottom: 1px solid var(--gray-100);
        vertical-align: middle;
    }

    .orders-table tbody tr:hover {
        background: var(--gray-50);
    }

    .order-details-cell {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 190px;
    }

    .order-thumb,
    .order-thumb-placeholder {
        width: 52px;
        height: 52px;
        border-radius: 10px;
        object-fit: cover;
        background: var(--gray-100);
        flex-shrink: 0;
    }

    .order-thumb-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--gray-400);
    }

    .order-number {
        font-weight: 700;
        color: var(--gray-900);
        font-size: 14px;
    }

    .order-items-sub {
        font-size: 12px;
        color: var(--gray-500);
        margin: 2px 0 3px;
    }

    .order-view-link {
        font-size: 12.5px;
        font-weight: 600;
        color: var(--primary-pink-dark);
    }

    .order-date {
        font-size: 14px;
        color: var(--gray-900);
    }

    .order-time {
        font-size: 12px;
        color: var(--gray-500);
    }

    .order-amount {
        font-size: 15.5px;
        font-weight: 700;
        color: var(--gray-900);
    }

    .status-badge {
        display: inline-block;
        padding: 5px 13px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .status-pending { background: #fff3cd; color: #856404; }
    .status-processing { background: #cfe2ff; color: #084298; }
    .status-shipped { background: #e7d6ff; color: #6f42c1; }
    .status-delivered { background: #d1e7dd; color: #0f5132; }
    .status-cancelled { background: #f8d7da; color: #842029; }

    .status-sub {
        font-size: 11.5px;
        color: var(--gray-500);
        margin-top: 5px;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 18px;
        background: transparent;
        border: 1.5px solid var(--primary-pink);
        color: var(--primary-pink-dark);
        text-decoration: none;
        border-radius: 6px;
        font-size: 13.5px;
        font-weight: 600;
        white-space: nowrap;
        transition: background 0.2s, color 0.2s;
    }

    .action-btn:hover {
        background: var(--primary-pink);
        color: var(--white);
    }

    .rate-cell {
        min-width: 150px;
    }

    .rate-cell-label {
        font-size: 11.5px;
        color: var(--gray-500);
        margin-bottom: 4px;
    }

    .rate-cell-stars {
        color: #ffc107;
        font-size: 14px;
        letter-spacing: 2px;
        margin-bottom: 4px;
    }

    .rate-cell-stars .far {
        color: #ddd;
    }

    .rate-cell-date {
        font-size: 11.5px;
        color: var(--gray-500);
    }

    .rate-now-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 13px;
        border: 1.5px solid var(--primary-pink);
        background: var(--white);
        color: var(--primary-pink-dark);
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    .rate-now-btn:hover {
        background: var(--primary-pink);
        color: var(--white);
    }

    .orders-pagination-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        padding: 16px 20px;
        border-top: 1px solid var(--gray-100);
        font-size: 13px;
        color: var(--gray-500);
    }

    .orders-pagination {
        display: flex;
        gap: 6px;
    }

    .orders-pagination .page-btn {
        min-width: 32px;
        height: 32px;
        padding: 0 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        color: var(--gray-700);
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
    }

    .orders-pagination .page-btn.active {
        background: var(--primary-pink);
        border-color: var(--primary-pink);
        color: var(--white);
    }

    .orders-pagination .page-btn.disabled {
        opacity: 0.4;
        pointer-events: none;
    }

    /* Rate modal item rows (shared markup, reused per delivered order) */
    .rate-item {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 16px 0;
        border-bottom: 1px solid var(--gray-200);
    }

    .rate-item:last-child {
        border-bottom: none;
    }

    .rate-item-header {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .rate-item-header img,
    .rate-item-header .rate-item-noimg {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        object-fit: cover;
        background: var(--gray-100);
        flex-shrink: 0;
    }

    .rate-item-header .rate-item-noimg {
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--gray-400);
    }

    .rate-item-name {
        font-weight: 600;
        color: var(--gray-900);
        font-size: 14px;
    }

    .rate-stars {
        display: flex;
        gap: 5px;
        font-size: 20px;
    }

    .rate-stars i {
        color: #ddd;
        cursor: pointer;
    }

    .rate-comment {
        width: 100%;
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid var(--gray-300);
        font-family: inherit;
        font-size: 13.5px;
        resize: vertical;
    }

    .rate-already {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #0f5132;
        background: #d1e7dd;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        width: fit-content;
    }

    .rate-alert {
        display: none;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 13px;
    }

    /* .empty-state now comes from the shared public/css/style.css component. */

    @media (max-width: 900px) {
        .orders-table {
            font-size: 13px;
        }
    }
</style>

<div class="orders-page-header">
    <div>
        <h1><i class="fas fa-receipt"></i> All Orders</h1>
        <p class="orders-page-subtitle">View and track all your orders in one place.</p>
    </div>
    <div class="orders-page-actions">
        <div class="orders-time-filter"><i class="far fa-calendar"></i> All Time <i class="fas fa-chevron-down" style="font-size:10px;"></i></div>
        <button type="button" class="orders-filter-btn"><i class="fas fa-filter"></i> Filter</button>
    </div>
</div>

<div class="orders-container">
    <div class="orders-tabs">
        <?php foreach ($tabs as $tab): ?>
            <button type="button" class="orders-tab <?php echo $statusFilter === $tab['key'] ? 'active' : ''; ?>" onclick="filterOrders('<?php echo $tab['key']; ?>')">
                <?php echo $tab['label']; ?>
                <span class="tab-count"><?php echo (int) $tab['count']; ?></span>
            </button>
        <?php endforeach; ?>
        <a href="<?php echo BASE_URL; ?>refund" class="orders-tab" style="text-decoration:none;">
            <i class="fas fa-undo-alt"></i> Refund Requests
        </a>
    </div>

    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <i class="fas fa-clipboard-list"></i>
            <h3>No orders found</h3>
            <p>You haven't placed any orders yet</p>
            <a href="<?php echo BASE_URL; ?>shop" class="btn" style="margin-top: 20px;">
                <i class="fas fa-shopping-bag"></i> Start Shopping
            </a>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="orders-table table-stack">
                <thead>
                    <tr>
                        <th>Order Details</th>
                        <th>Date Placed</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                        <th>Rate This Order</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <?php $thumb = $order['items'][0]['product_image'] ?? NULL; ?>
                        <tr>
                            <td>
                                <div class="order-details-cell">
                                    <?php if (!empty($thumb)): ?>
                                        <img src="<?php echo BASE_URL . $thumb; ?>" class="order-thumb" alt="">
                                    <?php else: ?>
                                        <div class="order-thumb-placeholder"><i class="fas fa-image"></i></div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="order-number">Order #<?php echo $order['order_number']; ?></div>
                                        <div class="order-items-sub"><?php echo (int) $order['items_count']; ?> item<?php echo $order['items_count'] == 1 ? '' : 's'; ?></div>
                                        <a href="<?php echo BASE_URL; ?>customer/orders/view/<?php echo $order['order_id']; ?>" class="order-view-link">View Details</a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="order-date"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                                <div class="order-time"><?php echo date('h:i A', strtotime($order['created_at'])); ?></div>
                            </td>
                            <td><?php echo (int) $order['items_count']; ?> item<?php echo $order['items_count'] == 1 ? '' : 's'; ?></td>
                            <td>
                                <div class="order-amount">₱<?php echo number_format($order['total_amount'], 2); ?></div>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                                <div class="status-sub">
                                    <?php if (isset($statusSubtext[$order['order_status']])): ?>
                                        <?php echo $statusSubtext[$order['order_status']]; ?>
                                    <?php else: ?>
                                        <?php echo ucfirst($order['order_status']); ?> on <?php echo date('M d, Y', strtotime($order['updated_at'] ?? $order['created_at'])); ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>customer/orders/view/<?php echo $order['order_id']; ?>" class="action-btn">
                                    <i class="fas fa-eye"></i> View Order
                                </a>
                            </td>
                            <td class="rate-cell">
                                <?php if ($order['order_status'] === 'delivered' && !empty($order['items'])): ?>
                                    <div class="rate-cell-label">Rate this order</div>
                                    <div class="rate-cell-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="<?php echo $i <= round($order['avg_rating_given']) ? 'fas' : 'far'; ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <?php if ($order['fully_rated']): ?>
                                        <div class="rate-cell-date">Rated on <?php echo date('M d, Y', strtotime($order['rated_on'])); ?></div>
                                    <?php else: ?>
                                        <button type="button" class="rate-now-btn" onclick="openModal('rateModal<?php echo $order['order_id']; ?>')">
                                            <i class="fas fa-star"></i> Rate Now
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="orders-pagination-bar">
            <span>Showing <?php echo $showingFrom; ?> to <?php echo $showingTo; ?> of <?php echo (int) $totalFiltered; ?> orders</span>
            <?php if ($totalPages > 1): ?>
                <div class="orders-pagination">
                    <?php
                        $baseQs = $statusFilter !== 'all' ? ['status' => $statusFilter] : [];
                        $pageUrl = fn($p) => BASE_URL . 'orders?' . http_build_query(array_merge($baseQs, ['page' => $p]));
                    ?>
                    <a class="page-btn <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>" href="<?php echo $pageUrl(max(1, $currentPage - 1)); ?>"><i class="fas fa-chevron-left"></i></a>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a class="page-btn <?php echo $i === $currentPage ? 'active' : ''; ?>" href="<?php echo $pageUrl($i); ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <a class="page-btn <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>" href="<?php echo $pageUrl(min($totalPages, $currentPage + 1)); ?>"><i class="fas fa-chevron-right"></i></a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Rate Products modals — one per delivered order, each listing that order's items -->
<?php foreach ($orders as $order): ?>
    <?php if ($order['order_status'] === 'delivered' && !empty($order['items'])): ?>
        <div class="modal" id="rateModal<?php echo $order['order_id']; ?>">
            <div class="modal-content modal-sm">
                <div class="modal-header">
                    <h3 class="modal-title"><i class="fas fa-star"></i> Rate Order #<?php echo htmlspecialchars($order['order_number']); ?></h3>
                    <button type="button" class="modal-close" onclick="closeModal(document.getElementById('rateModal<?php echo $order['order_id']; ?>'))">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <?php foreach ($order['items'] as $item): ?>
                        <?php $rateKey = $order['order_id'] . '_' . $item['product_id']; ?>
                        <div class="rate-item">
                            <div class="rate-item-header">
                                <?php if (!empty($item['product_image'])): ?>
                                    <img src="<?php echo BASE_URL . $item['product_image']; ?>" alt="">
                                <?php else: ?>
                                    <div class="rate-item-noimg"><i class="fas fa-image"></i></div>
                                <?php endif; ?>
                                <div class="rate-item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                            </div>

                            <?php if (in_array((int) $item['product_id'], $reviewedProductIds, true)): ?>
                                <div class="rate-already"><i class="fas fa-check-circle"></i> Already Rated</div>
                            <?php else: ?>
                                <div id="rate-controls-<?php echo $rateKey; ?>">
                                    <div class="rate-alert" id="rate-alert-<?php echo $rateKey; ?>"></div>
                                    <div class="rate-stars" id="rate-stars-<?php echo $rateKey; ?>">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star" data-rating="<?php echo $i; ?>" onclick="setOrderRating('<?php echo $rateKey; ?>', <?php echo $i; ?>)"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <textarea class="rate-comment" id="rate-comment-<?php echo $rateKey; ?>" rows="2" maxlength="1000" placeholder="Share your experience with this product..." style="margin-top:8px;"></textarea>
                                    <button type="button" class="btn btn-sm" style="margin-top:8px;" onclick="submitOrderRating('<?php echo $rateKey; ?>', <?php echo (int) $item['product_id']; ?>)">
                                        <i class="fas fa-paper-plane"></i> Submit Rating
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<script>
    function filterOrders(status) {
        if (status === 'all') {
            window.location.href = '<?php echo BASE_URL; ?>orders';
        } else {
            window.location.href = '<?php echo BASE_URL; ?>orders?status=' + status;
        }
    }

    const orderRatings = {};

    function setOrderRating(key, val) {
        orderRatings[key] = val;
        document.querySelectorAll('#rate-stars-' + key + ' i').forEach((star) => {
            const starRating = parseInt(star.getAttribute('data-rating'), 10);
            star.style.color = starRating <= val ? '#ffc107' : '#ddd';
        });
    }

    function showRateAlert(key, message, type) {
        const alertBox = document.getElementById('rate-alert-' + key);
        if (!alertBox) return;
        alertBox.style.display = 'block';
        alertBox.style.background = type === 'error' ? '#ffebee' : (type === 'info' ? '#fff3e0' : '#e8f5e9');
        alertBox.style.color = type === 'error' ? '#c62828' : (type === 'info' ? '#e65100' : '#2e7d32');
        alertBox.textContent = message;
    }

    function submitOrderRating(key, productId) {
        const rating = orderRatings[key];
        const commentEl = document.getElementById('rate-comment-' + key);
        const comment = commentEl ? commentEl.value.trim() : '';

        if (!rating) {
            showRateAlert(key, 'Please select a rating.', 'info');
            return;
        }
        if (!comment) {
            showRateAlert(key, 'Please write a short review.', 'info');
            return;
        }

        showRateAlert(key, 'Submitting…', 'success');

        fetch('<?php echo BASE_URL; ?>review/submit', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ product_id: productId, rating: rating, review: comment }).toString()
        })
        .then((res) => res.json().catch(() => ({})))
        .then((data) => {
            if (!data.success) {
                showRateAlert(key, data.message || 'Failed to submit rating.', 'error');
                return;
            }
            const controls = document.getElementById('rate-controls-' + key);
            if (controls) {
                controls.innerHTML = '<div class="rate-already"><i class="fas fa-check-circle"></i> Already Rated</div>';
            }
        })
        .catch(() => showRateAlert(key, 'Something went wrong. Please try again.', 'error'));
    }
</script>
