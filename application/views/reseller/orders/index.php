<?php
$search = $search ?? '';
$statusFilter = $statusFilter ?? '';
$paymentFilter = $paymentFilter ?? '';
$startDate = $startDate ?? '';
$endDate = $endDate ?? '';
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$totalFiltered = $totalFiltered ?? count($orders);
$perPage = $perPage ?? 10;
$commissionRate = $commissionRate ?? 0;

$statusOptions = [
    'pending' => 'Pending', 'paid' => 'Paid', 'processing' => 'Processing',
    'to_ship' => 'To Ship', 'shipped' => 'Shipped', 'delivered' => 'Delivered',
    'cancelled' => 'Cancelled', 'return_refund' => 'Return/Refund',
];
$paymentOptions = ['pending' => 'Pending', 'completed' => 'Paid', 'failed' => 'Failed', 'refunded' => 'Refunded'];
$paymentBadgeClass = ['pending' => 'pending', 'completed' => 'completed', 'failed' => 'failed', 'refunded' => 'cancelled'];

$avatarPalette = ['#7C3AED', '#2563EB', '#D97706', '#DB2777', '#0891B2', '#16A34A'];

$showingFrom = $totalFiltered > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
$showingTo = min($totalFiltered, $currentPage * $perPage);
?>
<style>
    .avatar-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700;
        font-size: 12.5px;
        flex-shrink: 0;
    }

    .customer-cell { display: flex; align-items: center; gap: 10px; }
    .customer-name { font-weight: 600; font-size: 13.5px; color: #1a1a2e; }
    .customer-phone { font-size: 11.5px; color: #8a94ad; }

    .product-stack { display: flex; align-items: center; }
    .product-stack img,
    .product-stack .stack-more,
    .product-stack .stack-ph {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
        margin-left: -10px;
        background: #f4f5f9;
        flex-shrink: 0;
    }
    .product-stack > *:first-child { margin-left: 0; }
    .stack-ph { display: flex; align-items: center; justify-content: center; color: #c3c9d6; font-size: 12px; }
    .stack-more { display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: #6b7280; }
    .products-cell { display: flex; align-items: center; gap: 10px; }
    .products-count { font-size: 11.5px; color: #8a94ad; white-space: nowrap; }


    .order-date-cell .order-date { font-size: 13px; color: #1a1a2e; }
    .order-date-cell .order-time { font-size: 11.5px; color: #8a94ad; }

    .orders-actions { display: flex; gap: 6px; align-items: center; justify-content: center; }

    .orders-pagination-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        padding: 16px 4px 4px;
        font-size: 13px;
        color: #6b7280;
    }
    .orders-pagination { display: flex; gap: 6px; }
    .orders-pagination .page-btn {
        min-width: 32px; height: 32px; padding: 0 8px; display: inline-flex; align-items: center; justify-content: center;
        border: 1px solid var(--border); border-radius: 8px; color: #374151; text-decoration: none; font-size: 13px; font-weight: 600;
    }
    .orders-pagination .page-btn.active { background: var(--primary-pink); border-color: var(--primary-pink); color: #fff; }
    .orders-pagination .page-btn.disabled { opacity: .4; pointer-events: none; }
    .orders-pagination .page-ellipsis { display: inline-flex; align-items: center; justify-content: center; min-width: 24px; color: #9ca3af; }
</style>

<div class="filter-bar">
    <form method="get" class="row g-2 align-items-center">
        <div class="col-lg-3 col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search by Order ID or Customer…" value="<?php echo htmlspecialchars($search); ?>">
            </div>
        </div>
        <div class="col-lg-2 col-md-3 col-6">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                <?php foreach ($statusOptions as $val => $label): ?>
                    <option value="<?php echo $val; ?>" <?php echo $statusFilter === $val ? 'selected' : ''; ?>><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-lg-2 col-md-3 col-6">
            <select name="payment" class="form-select" onchange="this.form.submit()">
                <option value="">All Payment</option>
                <?php foreach ($paymentOptions as $val => $label): ?>
                    <option value="<?php echo $val; ?>" <?php echo $paymentFilter === $val ? 'selected' : ''; ?>><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>" title="Start Date">
                <span class="input-group-text">–</span>
                <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>" title="End Date">
            </div>
        </div>
        <div class="col-lg-2 col-md-6 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill"><i class="fas fa-filter me-1"></i> Filter</button>
            <?php if ($search || $statusFilter || $paymentFilter || $startDate || $endDate): ?>
                <a href="<?php echo site_url('reseller/orders'); ?>" class="btn btn-outline-secondary" title="Reset filters">
                    <i class="fas fa-rotate-left"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($orders)): ?>
            <p style="text-align: center; color: var(--gray-500); padding: 2rem;">No orders found</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table inv-table mb-0 table-stack">
                    <thead>
                        <tr>
                            <th class="ps-3">Order ID</th>
                            <th>Customer</th>
                            <th>Products</th>
                            <th class="text-end">Total Amount</th>
                            <th>Commission</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-center pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $i => $order): ?>
                            <?php
                                $initials = strtoupper(substr($order['first_name'] ?? 'C', 0, 1) . substr($order['last_name'] ?? '', 0, 1));
                                $avatarColor = $avatarPalette[$i % count($avatarPalette)];
                                $shownItems = array_slice($order['items'], 0, 3);
                                $remainingItems = max(0, $order['items_count'] - count($shownItems));
                                $paymentKey = $order['payment_status'] ?? '';
                            ?>
                            <tr>
                                <td class="ps-3"><strong>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                <td>
                                    <div class="customer-cell">
                                        <div class="avatar-circle" style="background:<?php echo $avatarColor; ?>;"><?php echo htmlspecialchars($initials); ?></div>
                                        <div>
                                            <div class="customer-name"><?php echo htmlspecialchars($order['customer_name'] ?: 'Customer'); ?></div>
                                            <div class="customer-phone"><?php echo htmlspecialchars($order['contact_number'] ?? '—'); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="products-cell">
                                        <div class="product-stack">
                                            <?php foreach ($shownItems as $item): ?>
                                                <?php if (!empty($item['product_image'])): ?>
                                                    <img src="<?php echo base_url($item['product_image']); ?>" alt="" title="<?php echo htmlspecialchars($item['product_name'] ?? ''); ?>">
                                                <?php else: ?>
                                                    <span class="stack-ph"><i class="fas fa-image"></i></span>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                            <?php if ($remainingItems > 0): ?>
                                                <span class="stack-more">+<?php echo $remainingItems; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="products-count"><?php echo (int) $order['items_count']; ?> item<?php echo $order['items_count'] == 1 ? '' : 's'; ?></span>
                                    </div>
                                </td>
                                <td class="text-end fw-semibold">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <?php if ($order['commission_amount'] !== NULL): ?>
                                        ₱<?php echo number_format($order['commission_amount'], 2); ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($paymentKey !== '' && $paymentKey !== NULL): ?>
                                        <span class="badge-status badge-<?php echo $paymentBadgeClass[$paymentKey] ?? 'pending'; ?>">
                                            <?php echo $paymentOptions[$paymentKey] ?? ucfirst($paymentKey); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge-status badge-<?php echo str_replace('return_refund', 'refund_return', $order['order_status']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                                    </span>
                                </td>
                                <td class="order-date-cell">
                                    <div class="order-date"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                                    <div class="order-time"><?php echo date('h:i A', strtotime($order['created_at'])); ?></div>
                                </td>
                                <td class="pe-3">
                                    <div class="orders-actions">
                                        <a href="<?php echo site_url('reseller/orders/view/' . $order['order_id']); ?>"
                                           class="btn btn-sm btn-outline-info" title="View Details"
                                           style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-eye" style="font-size:11px;"></i>
                                        </a>
                                    </div>
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
                            $baseQs = array_filter([
                                'search' => $search ?: NULL,
                                'status' => $statusFilter ?: NULL,
                                'payment' => $paymentFilter ?: NULL,
                                'start_date' => $startDate ?: NULL,
                                'end_date' => $endDate ?: NULL,
                            ]);
                            $pageUrl = fn($p) => site_url('reseller/orders') . '?' . http_build_query(array_merge($baseQs, ['page' => $p]));
                        ?>
                        <a class="page-btn <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>" href="<?php echo $pageUrl(max(1, $currentPage - 1)); ?>"><i class="fas fa-chevron-left"></i></a>
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <?php if ($p === 1 || $p === $totalPages || abs($p - $currentPage) <= 1): ?>
                                <a class="page-btn <?php echo $p === $currentPage ? 'active' : ''; ?>" href="<?php echo $pageUrl($p); ?>"><?php echo $p; ?></a>
                            <?php elseif (abs($p - $currentPage) === 2): ?>
                                <span class="page-ellipsis">…</span>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <a class="page-btn <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>" href="<?php echo $pageUrl(min($totalPages, $currentPage + 1)); ?>"><i class="fas fa-chevron-right"></i></a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
