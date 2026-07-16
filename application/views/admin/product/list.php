    <?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.css">
<style>
/* DataTables controls restyled to match the existing filter-bar/pill look instead of its own defaults */
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid var(--border); border-radius: var(--radius-md);
    padding: .4rem .75rem; font-size: .85rem; margin-left: .5rem;
}
.dataTables_wrapper .dataTables_filter input:focus { outline: none; border-color: var(--primary-pink); }
table.dataTable thead th { position: relative; }
table.dataTable thead th.sorting:hover { color: var(--primary-pink); cursor: pointer; }

/* Header alert pills */
.hdr-pill { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border-radius:20px; font-size:12.5px; font-weight:600; background:#fff; border:1px solid #fbc02d; color:#a66a00; text-decoration:none; transition:box-shadow .15s; }
.hdr-pill:hover { color:#a66a00; box-shadow:0 2px 8px rgba(0,0,0,.08); }
.hdr-pill .cnt { background:#dc3545; color:#fff; font-size:10.5px; font-weight:700; border-radius:999px; padding:1px 7px; margin-left:2px; }

/* Stat cards v2 (icon on the right, subtitle below) */
.stat-card-v2 { display:flex; align-items:center; justify-content:space-between; gap:.75rem; padding:1.1rem 1.25rem; border-radius:14px; }
.stat-card-v2 .scv2-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:#8a94ad; margin-bottom:6px; }
.stat-card-v2 .scv2-value { font-size:1.7rem; font-weight:800; color:#1a1a2e; line-height:1; margin-bottom:6px; }
.stat-card-v2 .scv2-sub { font-size:11.5px; color:#8a94ad; }
.stat-card-v2 .scv2-icon { width:42px; height:42px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }

/* Analytics / alerts / branch-stock widgets */
.iv-widget-title { font-size:14px; font-weight:700; color:#1a1a2e; margin-bottom:2px; }
.iv-widget-sub { font-size:11.5px; color:#8a94ad; margin-bottom:14px; }
.iv-legend-row { display:flex; align-items:center; justify-content:space-between; padding:6px 0; font-size:12.5px; }
.iv-legend-row .dot { width:9px; height:9px; border-radius:50%; display:inline-block; margin-right:8px; }
.iv-legend-row .pct { color:#8a94ad; font-size:11px; }

.alert-item { display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid var(--border); }
.alert-item:last-child { border-bottom:none; }
.alert-item .ai-thumb { width:38px; height:38px; border-radius:9px; object-fit:cover; border:1px solid var(--border); flex-shrink:0; }
.alert-item .ai-thumb-ph { width:38px; height:38px; border-radius:9px; background:#f4f5f9; display:flex; align-items:center; justify-content:center; color:#c3c9d6; flex-shrink:0; }
.alert-item .ai-info { flex:1; min-width:0; }
.alert-item .ai-info strong { display:block; font-size:12.5px; color:#1a1a2e; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.alert-item .ai-info span { font-size:11px; color:#8a94ad; }
.alert-item .ai-right { text-align:right; flex-shrink:0; }
.alert-item .ai-right .ai-count { display:block; font-size:11.5px; font-weight:600; color:#1a1a2e; }
.alert-item .ai-right .ai-hint { font-size:10.5px; color:#8a94ad; }
.alert-pill { font-size:10px; font-weight:700; padding:2px 9px; border-radius:20px; display:inline-block; }
.alert-pill.low { background:#fff3e0; color:#e65100; }
.alert-pill.exp { background:#fce4ec; color:#c2185b; }

.branch-bar-row { padding:12px 0; border-bottom:1px solid var(--border); }
.branch-bar-row:last-child { border-bottom:none; }
.branch-bar-row .bbr-top { display:flex; align-items:center; justify-content:space-between; font-size:12.5px; margin-bottom:6px; }
.branch-bar-row .bbr-top strong { color:#1a1a2e; font-weight:700; }
.branch-bar-row .bbr-top span.items { color:#8a94ad; }
.branch-bar-track { height:7px; border-radius:999px; background:#f0f1f6; overflow:hidden; }
.branch-bar-fill { height:100%; border-radius:999px; background:linear-gradient(90deg,#8b5cf6,#a78bfa); }
.branch-bar-pct { text-align:right; font-size:11px; color:#8a94ad; margin-top:4px; }
</style>

<div class="container-fluid py-4">

    <!-- Page Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Inventory Management</h4>
            <small class="text-muted">Products, stock levels, and physical branches</small>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <a href="<?= site_url('admin/product/expiring'); ?>" class="hdr-pill">
                <i class="fas fa-calendar-times"></i> Expiring Soon
                <?php if ($stat_expiring > 0): ?><span class="cnt"><?= $stat_expiring; ?></span><?php endif; ?>
            </a>
            <a href="<?= site_url('admin/product/low_stock'); ?>" class="hdr-pill">
                <i class="fas fa-exclamation-triangle"></i> Low Stock
                <?php if ($stat_low_stock > 0): ?><span class="cnt"><?= $stat_low_stock; ?></span><?php endif; ?>
            </a>
            <a href="<?= site_url('admin/product/add'); ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Add Product
            </a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row row-cols-2 row-cols-md-5 g-3 mb-3">
        <div class="col">
            <div class="stat-card stat-card-v2">
                <div>
                    <div class="scv2-label">Total Products</div>
                    <div class="scv2-value"><?= number_format($stat_total); ?></div>
                    <div class="scv2-sub">All registered products</div>
                </div>
                <div class="scv2-icon" style="background:#eef0ff;color:#4361ee;"><i class="fas fa-boxes"></i></div>
            </div>
        </div>
        <div class="col">
            <div class="stat-card stat-card-v2">
                <div>
                    <div class="scv2-label">Available Stock</div>
                    <div class="scv2-value"><?= number_format($stat_available); ?></div>
                    <div class="scv2-sub">Products in stock</div>
                </div>
                <div class="scv2-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-check"></i></div>
            </div>
        </div>
        <div class="col">
            <div class="stat-card stat-card-v2">
                <div>
                    <div class="scv2-label">Unavailable Stock</div>
                    <div class="scv2-value"><?= number_format($stat_not_available); ?></div>
                    <div class="scv2-sub">Out of stock products</div>
                </div>
                <div class="scv2-icon" style="background:#ffebee;color:#c62828;"><i class="fas fa-xmark"></i></div>
            </div>
        </div>
        <div class="col">
            <div class="stat-card stat-card-v2">
                <div>
                    <div class="scv2-label">Low Stock</div>
                    <div class="scv2-value"><?= number_format($stat_low_stock); ?></div>
                    <div class="scv2-sub">Need restocking</div>
                </div>
                <div class="scv2-icon" style="background:#fff8e1;color:#f57f17;"><i class="fas fa-triangle-exclamation"></i></div>
            </div>
        </div>
        <div class="col">
            <div class="stat-card stat-card-v2">
                <div>
                    <div class="scv2-label">Expiring Soon</div>
                    <div class="scv2-value"><?= number_format($stat_expiring); ?></div>
                    <div class="scv2-sub">Within 30 days</div>
                </div>
                <div class="scv2-icon" style="background:#fff3e0;color:#e65100;"><i class="fas fa-box-archive"></i></div>
            </div>
        </div>
    </div>

    <!-- ===================== PRODUCTS SECTION ===================== -->
    <div class="page-section">
        <span class="section-title"><i class="fas fa-boxes me-2 text-primary"></i><?= $view === 'archived' ? 'Archived Products' : 'Products'; ?></span>
        <hr>
        <span class="text-muted small" style="white-space:nowrap;"><?= number_format($total); ?> result<?= $total != 1 ? 's' : ''; ?></span>
        <div class="section-actions">
            <?php if ($view === 'archived'): ?>
                <a href="<?= site_url('admin/inventory'); ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Active Products
                </a>
            <?php else: ?>
                <a href="<?= site_url('admin/inventory?view=archived'); ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-box-archive me-1"></i> View Archived
                    <?php if (!empty($archived_count)): ?>
                        <span class="badge bg-secondary ms-1"><?= $archived_count; ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="get" id="filterForm" class="row g-2 align-items-end">
            <input type="hidden" name="view" value="<?= htmlspecialchars($view); ?>">
            <div class="col-md-4 col-sm-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0"
                           placeholder="Search name, SKU…"
                           value="<?= htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['category_id']; ?>" <?= $category == $cat['category_id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($cat['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <select name="branch" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Branches</option>
                    <?php foreach ($branches as $b): ?>
                        <option value="<?= $b['branch_id']; ?>" <?= $branch == $b['branch_id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($b['branch_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="available"     <?= $status === 'available'     ? 'selected' : ''; ?>>Available</option>
                    <option value="not_available" <?= $status === 'not_available' ? 'selected' : ''; ?>>Not Available</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">Search</button>
                <?php if ($search || $category || $branch || $status): ?>
                    <a href="<?= site_url('admin/inventory'); ?>" class="btn btn-sm btn-outline-secondary" title="Clear filters">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="card border-0 shadow-sm mb-2" style="border-radius:12px;overflow:hidden;">
        <div class="table-responsive">
            <table class="table inv-table mb-0 table-stack" id="productsTable">
                <thead>
                    <tr>
                        <th class="ps-3" style="width:34px;"><input type="checkbox" id="selectAllProducts" class="form-check-input"></th>
                        <th style="width:30%;">Product</th>
                        <th>Branch</th>
                        <th>Category</th>
                        <th class="text-end">Price</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Expiration</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $p): ?>
                    <?php
                        $st = $p['stock'] ?? 0;
                        $min = $p['min_stock_alert'] ?? 10;
                        $stock_cls = $st == 0 ? 'stock-zero' : ($st <= $min ? 'stock-warn' : 'stock-ok');
                        $status_key = $p['status'] ?? 'available';
                        $status_labels = [
                            'available'    => 'Available',
                            'out_of_stock' => 'Out of Stock',
                            'not_available'=> 'Not Available',
                            'inactive'     => 'Inactive',
                            'archived'     => 'Archived',
                        ];
                        $status_label = $status_labels[$status_key] ?? ucfirst($status_key);
                    ?>
                    <tr>
                        <td class="ps-3"><input type="checkbox" class="form-check-input product-row-check"></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?php if (!empty($p['primary_image'])): ?>
                                    <img src="<?= base_url($p['primary_image']); ?>" class="row-thumb" alt=""
                                         onerror="this.replaceWith(Object.assign(document.createElement('div'), {className:'row-thumb-placeholder', innerHTML:'<i class=\'fas fa-box\'></i>'}));">
                                <?php else: ?>
                                    <div class="row-thumb-placeholder"><i class="fas fa-box"></i></div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-semibold" style="font-size:13px;color:#1a1a2e;"><?= htmlspecialchars($p['product_name']); ?></div>
                                    <div style="font-size:11px;color:#8a94ad;">
                                        <?php if (!empty($p['sku'])): ?>SKU: <span class="fw-medium"><?= htmlspecialchars($p['sku']); ?></span><?php endif; ?>
                                        <?php if (!empty($p['brand'])): ?> &nbsp;·&nbsp; <?= htmlspecialchars($p['brand']); ?><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php $bstock = $p['branch_stock'] ?? []; ?>
                            <?php if (!empty($bstock)): ?>
                                <?php foreach ($branches as $b): ?>
                                    <?php if (!empty($bstock[$b['branch_id']])): ?>
                                    <span style="font-size:11px;color:#4361ee;background:#eef0ff;padding:2px 8px;border-radius:20px;font-weight:500;display:inline-block;margin:1px 0;">
                                        <i class="fas fa-store" style="font-size:10px;"></i> <?= htmlspecialchars($b['branch_name']); ?>: <?= (int) $bstock[$b['branch_id']]; ?>
                                    </span><br>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                        </td>
                        <td><span class="text-muted small"><?= htmlspecialchars($p['category_name'] ?? '—'); ?></span></td>
                        <td class="text-end fw-semibold" style="font-size:13px;">₱<?= number_format($p['price'] ?? 0, 2); ?></td>
                        <td class="text-center">
                            <span class="stock-badge <?= $stock_cls; ?>"><?= number_format((int)$st); ?></span>
                        </td>
                        <td class="text-center">
                            <?php if (!empty($p['expiry_date'])): ?>
                                <?php $daysLeft = (int) ceil((strtotime($p['expiry_date']) - strtotime(date('Y-m-d'))) / 86400); ?>
                                <span class="small" style="color:#1a1a2e;"><?= date('M j, Y', strtotime($p['expiry_date'])); ?></span><br>
                                <span style="font-size:10.5px;font-weight:600;color:<?= $daysLeft <= 30 ? '#dc3545' : '#8a94ad'; ?>;">
                                    <?= $daysLeft < 0 ? 'Expired' : $daysLeft . ' days left'; ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge-status badge-<?= $status_key; ?>"><?= $status_label; ?></span>
                        </td>
                        <td class="text-center pe-3">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="<?= site_url('admin/product/view/' . $p['product_id']); ?>"
                                   class="btn btn-sm btn-outline-info" title="View" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-eye" style="font-size:11px;"></i>
                                </a>
                                <a href="<?= site_url('admin/product/edit/' . $p['product_id']); ?>"
                                   class="btn btn-sm btn-outline-warning" title="Edit" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-edit" style="font-size:11px;"></i>
                                </a>
                                <?php if (!empty($p['is_archived'])): ?>
                                <button type="button" class="btn btn-sm btn-outline-success" title="Restore"
                                        onclick="restoreProduct(<?= $p['product_id']; ?>, '<?= addslashes(htmlspecialchars($p['product_name'])); ?>')"
                                        style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-rotate-left" style="font-size:11px;"></i>
                                </button>
                                <?php else: ?>
                                <button type="button" class="btn btn-sm btn-outline-secondary" title="Archive"
                                        onclick="archiveProduct(<?= $p['product_id']; ?>, '<?= addslashes(htmlspecialchars($p['product_name'])); ?>')"
                                        style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-box-archive" style="font-size:11px;"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php
        // Everything below is derived from data already loaded above ($products, $branches_list)
        // — purely presentational, no new queries.
        $donutTotal = max(1, (int) $stat_total);
        $donutBreakdown = [
            ['label' => 'Available Stock',   'color' => '#22C55E', 'count' => (int) $stat_available],
            ['label' => 'Low Stock',         'color' => '#F59E0B', 'count' => (int) $stat_low_stock],
            ['label' => 'Out of Stock',      'color' => '#EF4444', 'count' => (int) $stat_not_available],
            ['label' => 'Expiring Soon',     'color' => '#8B5CF6', 'count' => (int) $stat_expiring],
        ];

        $lowStockAlerts = array_filter($products, function ($p) {
            $st = (int) ($p['stock'] ?? 0);
            $min = (int) ($p['min_stock_alert'] ?? 10);
            return $st > 0 && $st <= $min;
        });
        usort($lowStockAlerts, fn($a, $b) => (int) $a['stock'] <=> (int) $b['stock']);

        $expiringAlerts = array_filter($products, function ($p) {
            return !empty($p['expiry_date']) && strtotime($p['expiry_date']) <= strtotime('+30 days');
        });
        usort($expiringAlerts, fn($a, $b) => strtotime($a['expiry_date']) <=> strtotime($b['expiry_date']));

        $stockAlerts = array_slice(array_merge(array_slice($lowStockAlerts, 0, 3), array_slice($expiringAlerts, 0, 2)), 0, 5);

        $branchGrandTotal = max(1, array_sum(array_column($branches_list, 'total_stock')));
        $topBranches = $branches_list;
        usort($topBranches, fn($a, $b) => (int) $b['total_stock'] <=> (int) $a['total_stock']);
        $topBranches = array_slice($topBranches, 0, 5);
    ?>

    <!-- Analytics / Alerts / Branch Stock -->
    <div class="row g-3 mb-3">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;padding:1.25rem;">
                <div class="iv-widget-title"><i class="fas fa-chart-pie me-1 text-primary"></i> Inventory Analytics <span class="text-muted fw-normal" style="font-size:11.5px;">(This Month)</span></div>
                <div class="chart-container" style="height:170px;position:relative;margin-top:8px;">
                    <canvas id="inventoryDonutChart"></canvas>
                    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none;">
                        <div style="font-size:20px;font-weight:800;color:#1a1a2e;"><?= number_format($stat_total); ?></div>
                        <div style="font-size:10px;color:#8a94ad;text-transform:uppercase;letter-spacing:.04em;">Total Products</div>
                    </div>
                </div>
                <div style="margin-top:10px;">
                    <?php foreach ($donutBreakdown as $d): ?>
                        <?php $pct = round(($d['count'] / $donutTotal) * 100, 1); ?>
                        <div class="iv-legend-row">
                            <span><span class="dot" style="background:<?= $d['color']; ?>;"></span><?= $d['label']; ?></span>
                            <strong><?= (int) $d['count']; ?> <span class="pct">(<?= $pct; ?>%)</span></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;padding:1.25rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="iv-widget-title mb-0"><i class="fas fa-bell me-1 text-warning"></i> Stock Alerts</div>
                    <a href="<?= site_url('admin/product/low_stock'); ?>" style="font-size:11.5px;font-weight:600;">View All</a>
                </div>
                <div style="margin-top:10px;">
                    <?php if (!empty($stockAlerts)): ?>
                        <?php foreach ($stockAlerts as $p): ?>
                            <?php $isExpiring = !empty($p['expiry_date']) && strtotime($p['expiry_date']) <= strtotime('+30 days') && (int) ($p['stock'] ?? 0) > (int) ($p['min_stock_alert'] ?? 10); ?>
                            <div class="alert-item">
                                <?php if (!empty($p['primary_image'])): ?>
                                    <img src="<?= base_url($p['primary_image']); ?>" class="ai-thumb" alt=""
                                         onerror="this.replaceWith(Object.assign(document.createElement('div'), {className:'ai-thumb-ph', innerHTML:'<i class=\'fas fa-box\'></i>'}));">
                                <?php else: ?>
                                    <div class="ai-thumb-ph"><i class="fas fa-box"></i></div>
                                <?php endif; ?>
                                <div class="ai-info">
                                    <strong><?= htmlspecialchars($p['product_name']); ?></strong>
                                    <span>SKU: <?= htmlspecialchars($p['sku'] ?? '—'); ?></span>
                                </div>
                                <div class="ai-right">
                                    <?php if ($isExpiring): ?>
                                        <span class="alert-pill exp">Expiring Soon</span><br>
                                        <span class="ai-count">Expires in <?= max(0, (int) ceil((strtotime($p['expiry_date']) - time()) / 86400)); ?> days</span><br>
                                        <span class="ai-hint">Check inventory</span>
                                    <?php else: ?>
                                        <span class="alert-pill low">Low Stock</span><br>
                                        <span class="ai-count">Only <?= (int) $p['stock']; ?> left</span><br>
                                        <span class="ai-hint">Reorder suggested</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-muted" style="padding:24px 0;font-size:.85rem;">No stock alerts right now.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;padding:1.25rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="iv-widget-title mb-0"><i class="fas fa-store me-1 text-success"></i> Top Branch Stock</div>
                    <span class="text-muted" style="font-size:11.5px;">This Month <i class="fas fa-chevron-down" style="font-size:9px;"></i></span>
                </div>
                <div style="margin-top:6px;">
                    <?php if (!empty($topBranches)): ?>
                        <?php foreach ($topBranches as $b): ?>
                            <?php $pct = round(((int) $b['total_stock'] / $branchGrandTotal) * 100); ?>
                            <div class="branch-bar-row">
                                <div class="bbr-top">
                                    <strong><?= htmlspecialchars($b['branch_name']); ?></strong>
                                    <span class="items"><?= number_format((int) $b['total_stock']); ?> items</span>
                                </div>
                                <div class="branch-bar-track"><div class="branch-bar-fill" style="width:<?= $pct; ?>%;"></div></div>
                                <div class="branch-bar-pct"><?= $pct; ?>%</div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-muted" style="padding:24px 0;font-size:.85rem;">No branches yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== BRANCHES SECTION ===================== -->
    <div class="page-section">
        <span class="section-title"><i class="fas fa-store me-2 text-success"></i>Branches</span>
        <hr>
        <div class="section-actions">
            <button type="button" class="btn btn-primary btn-sm flex-shrink-0" onclick="openBranchModal()">
                <i class="fas fa-plus me-1"></i> Add Branch
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
        <div class="table-responsive">
            <table class="table inv-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Branch Name</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th class="text-center">Products</th>
                        <th class="text-center">Total Stock</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($branches_list)): ?>
                    <?php foreach ($branches_list as $b): ?>
                    <tr class="branch-row">
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:36px;height:36px;border-radius:9px;background:#e8f5e9;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fas fa-store text-success" style="font-size:14px;"></i>
                                </div>
                                <strong style="font-size:13px;"><?= htmlspecialchars($b['branch_name']); ?></strong>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted small">
                                <?= htmlspecialchars(trim(implode(', ', array_filter([$b['street'] ?? '', $b['barangay'] ?? '', $b['city'] ?? ''])))); ?>
                            </span>
                        </td>
                        <td><span class="small"><?= htmlspecialchars($b['phone_number'] ?? '—'); ?></span></td>
                        <td class="text-center">
                            <span style="font-size:12px;font-weight:600;background:#eef0ff;color:#4361ee;padding:2px 10px;border-radius:20px;">
                                <?= number_format((int)$b['total_products']); ?>
                            </span>
                        </td>
                        <td class="text-center fw-semibold" style="font-size:13px;"><?= number_format((int)$b['total_stock']); ?></td>
                        <td class="text-center">
                            <span class="badge-status <?= $b['status'] === 'active' ? 'badge-available' : 'badge-inactive'; ?>">
                                <?= ucfirst($b['status'] ?? 'active'); ?>
                            </span>
                        </td>
                        <td class="text-center pe-3">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="<?= site_url('admin/inventory?branch=' . $b['branch_id']); ?>"
                                   class="btn btn-sm btn-outline-info" title="View products in this branch"
                                   style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-eye" style="font-size:11px;"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-warning" title="Edit"
                                        onclick='openBranchModal(<?= json_encode($b); ?>)'
                                        style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-edit" style="font-size:11px;"></i>
                                </button>
                                <?php if (($b['status'] ?? 'active') === 'active'): ?>
                                <button type="button" class="btn btn-sm btn-outline-secondary" title="Deactivate"
                                        onclick="toggleBranchStatus(<?= $b['branch_id']; ?>, '<?= addslashes(htmlspecialchars($b['branch_name'])); ?>', 'active')"
                                        style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-ban" style="font-size:11px;"></i>
                                </button>
                                <?php else: ?>
                                <button type="button" class="btn btn-sm btn-outline-success" title="Reactivate"
                                        onclick="toggleBranchStatus(<?= $b['branch_id']; ?>, '<?= addslashes(htmlspecialchars($b['branch_name'])); ?>', 'inactive')"
                                        style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-rotate-left" style="font-size:11px;"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-store fa-2x text-muted mb-2 d-block"></i>
                            <span class="text-muted">No branches yet.</span>
                            <button type="button" class="btn btn-sm btn-outline-success ms-2" onclick="openBranchModal()">
                                Add First Branch
                            </button>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /container-fluid -->

<!-- ===================== MODALS ===================== -->

<!-- Branch Modal -->
<div class="modal fade" id="branchModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 8px 32px rgba(0,0,0,.15);">
            <form method="post" action="<?= site_url('admin/branches/save'); ?>" id="branchForm">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="branchModalTitle">Add Branch</h5>
                        <small class="text-muted">Physical store or warehouse location</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <input type="hidden" name="branch_id" id="branch_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Branch Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="branch_name" id="branch_name"
                               required placeholder="e.g. Calapan City Branch">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Province</label>
                        <input type="text" class="form-control" value="Oriental Mindoro" readonly style="background:#f3f4f6;color:#666;">
                        <input type="hidden" name="province" value="Oriental Mindoro">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold small">City / Municipality <span class="text-danger">*</span></label>
                            <select class="form-select" name="city" id="branch_city" required>
                                <option value="">Select City / Municipality</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold small">Barangay <span class="text-danger">*</span></label>
                            <select class="form-select" name="barangay" id="branch_barangay" required disabled>
                                <option value="">Select City first</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Street / House No. <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="street" id="branch_street" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="phone_number" id="branch_phone" required placeholder="09XXXXXXXXX">
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-semibold small">Status</label>
                        <select class="form-select" name="status" id="branch_status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="branchSaveBtn">
                        <i class="fas fa-save me-1"></i> Save Branch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Product Modal -->
<div class="modal fade" id="deleteProductModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 8px 32px rgba(0,0,0,.15);">
            <div class="modal-body text-center py-4 px-4">
                <div style="width:56px;height:56px;border-radius:50%;background:#ffebee;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                    <i class="fas fa-trash text-danger fa-lg"></i>
                </div>
                <h6 class="fw-bold mb-1">Delete Product?</h6>
                <p class="text-muted small mb-3"><strong id="deleteProductName"></strong> will be permanently removed.</p>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm flex-fill" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger btn-sm flex-fill" id="confirmDeleteProduct">
                        <span id="delBtnText">Delete</span>
                        <span id="delBtnSpinner" class="spinner-border spinner-border-sm ms-1 d-none"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generic Action Confirm Modal (branch/product status actions) -->
<div class="modal fade" id="actionConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 8px 32px rgba(0,0,0,.15);">
            <div class="modal-body text-center py-4 px-4">
                <div id="actionModalIconWrap" style="width:56px;height:56px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                    <i id="actionModalIcon" class="fas fa-lg"></i>
                </div>
                <h6 class="fw-bold mb-1" id="actionModalTitle"></h6>
                <p class="text-muted small mb-3" id="actionModalBody"></p>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm flex-fill" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-sm flex-fill" id="actionModalConfirmBtn">
                        <span id="actionBtnText">Confirm</span>
                        <span id="actionBtnSpinner" class="spinner-border spinner-border-sm ms-1 d-none"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/* ── Oriental Mindoro municipality → barangay data (mirrors application/helpers/address_helper.php) ── */
var ORIENTAL_MINDORO_BARANGAYS = {
    "Calapan City": ["Balingayan","Balite","Baruyan","Batino","Bayanan I","Bayanan II","Biga","Bondoc","Bucayao","Buhuan","Bulusan","Calero","Camansihan","Camilmil","Canubing I","Canubing II","Comunal","Guinobatan","Gulod","Gutad","Ibaba East","Ibaba West","Ilaya","Lalud","Lazareto","Libis","Lumangbayan","Mahal na Pangalan","Maidlang","Malad","Malamig","Managpi","Masipit","Nag-iba I","Nag-iba II","Navotas","Pachoca","Palhi","Panggalaan","Parang","Patas","Personas","Putingtubig","Salong","San Antonio","San Vicente Central","San Vicente East","San Vicente North","San Vicente South","San Vicente West","Santa Cruz","Santa Isabel","Santa Maria Village","Santa Rita","Santo Niño","Sapul","Silonay","Suqui","Tawagan","Tawiran","Tibag","Wawa"],
    "Baco": ["Alag","Bangkatan","Baras","Bayanan","Burbuli","Catwiran I","Catwiran II","Dulangan I","Dulangan II","Lantuyang","Lumangbayan","Malapad","Mangangan I","Mangangan II","Mayabig","Pambisan","Poblacion","Pulang-Tubig","Putican-Cabulo","San Andres","San Ignacio","Santa Cruz","Santa Rosa I","Santa Rosa II","Tabon-tabon","Tagumpay","Water"],
    "Bansud": ["Alcadesma","Bato","Conrazon","Malo","Manihala","Pag-asa","Poblacion","Proper Bansud","Proper Tiguisan","Rosacara","Salcedo","Sumagui","Villa Pag-asa"],
    "Bongabong": ["Anilao","Aplaya","Bagumbayan I","Bagumbayan II","Batangan","Bukal","Camantigue","Carmundo","Cawayan","Dayhagan","Formon","Hagan","Hagupit","Ipil","Kaligtasan","Labasan","Labonan","Libertad","Lisap","Luna","Malitbog","Mapang","Masaguisi","Mina de Oro","Morente","Ogbot","Orconuma","Poblacion","Polusahi","Sagana","San Isidro","San Jose","San Juan","Santa Cruz","Sigange","Tawas"],
    "Bulalacao": ["Bagong Sikat","Balatasan","Benli","Cabugao","Cambunang","Campaasan","Maasin","Maujao","Milagrosa","Nasukob","Poblacion","San Francisco","San Isidro","San Juan","San Roque"],
    "Gloria": ["Agos","Agsalin","Alma Villa","Andres Bonifacio","Balete","Banus","Banutan","Bulaklakan","Buong Lupa","Gaudencio Antonino","Guimbonan","Kawit","Lucio Laurel","Macario Adriatico","Malamig","Malayong","Maligaya","Malubay","Manguyang","Maragooc","Mirayan","Narra","Papandungin","San Antonio","Santa Maria","Santa Theresa","Tambong"],
    "Mansalay": ["B. del Mundo","Balugo","Bonbon","Budburan","Cabalwa","Don Pedro","Maliwanag","Manaul","Panaytayan","Poblacion","Roma","Santa Brigida","Santa Maria","Santa Teresita","Villa Celestial","Wasig","Waygan"],
    "Naujan": ["Adrialuna","Andres Ilagan","Antipolo","Apitong","Arangin","Aurora","Bacungan","Bagong Buhay","Balite","Bancuro","Banuton","Barcenaga","Bayani","Buhangin","Caburo","Concepcion","Curva","Dao","Del Pilar","Estrella","Evangelista","Gamao","General Esco","Herrera","Inarawan","Kalinisan","Laguna","Mabini","Magtibay","Mahabang Parang","Malaya","Malinao","Malvar","Masagana","Masaguing","Melgar A","Melgar B","Metolza","Montelago","Montemayor","Motoderazo","Mulawin","Nag-iba I","Nag-iba II","Pagkakaisa","Paitan","Paniquian","Pinagsabangan I","Pinagsabangan II","Piñahan","Poblacion I","Poblacion II","Poblacion III","Sampaguita","San Agustin I","San Agustin II","San Andres","San Antonio","San Carlos","San Isidro","San Jose","San Luis","San Nicolas","San Pedro","Santa Cruz","Santa Isabel","Santa Maria","Santiago","Santo Niño","Tagumpay","Tigkan"],
    "Pinamalayan": ["Anoling","Bacungan","Bangbang","Banilad","Buli","Cacawan","Calingag","Del Razon","Guinhawa","Inclanay","Lumangbayan","Malaya","Maliangcog","Maningcol","Marayos","Marfrancisco","Nabuslot","Pagalagala","Palayan","Pambisan Malaki","Pambisan Munti","Panggulayan","Papandayan","Pili","Quinabigan","Ranzo","Rosario","Sabang","Santa Isabel","Santa Maria","Santa Rita","Santo Niño","Wawa","Zone I","Zone II","Zone III","Zone IV"],
    "Pola": ["Bacawan","Bacungan","Batuhan","Bayanan","Biga","Buhay na Tubig","Calima","Calubasanhon","Campamento","Casiligan","Malibago","Maluanluan","Matulatula","Misong","Pahilahan","Panikihan","Pula","Puting Cacao","Tagbakin","Tagumpay","Tiguihan","Zone I","Zone II"],
    "Puerto Galera": ["Aninuan","Baclayan","Balatero","Dulangan","Palangan","Poblacion","Sabang","San Antonio","San Isidro","Santo Niño","Sinandigan","Tabinay","Villaflor"],
    "Roxas": ["Bagumbayan","Cantil","Dangay","Happy Valley","Libertad","Libtong","Little Tanauan","Mabuhay","Maraska","Odiong","Paclasan","San Aquilino","San Isidro","San Jose","San Mariano","San Miguel","San Rafael","San Vicente","Uyao","Victoria"],
    "San Teodoro": ["Bigaan","Caagutayan","Calangatan","Calsapa","Ilag","Lumangbayan","Poblacion","Tacligan"],
    "Socorro": ["Bagsok","Batong Dalig","Bayuin","Bugtong na Tuog","Calocmoy","Calubayan","Catiningan","Fortuna","Happy Valley","Leuteboro I","Leuteboro II","Ma. Concepcion","Mabuhay I","Mabuhay II","Malugay","Matungao","Monteverde","Pasi I","Pasi II","Santo Domingo","Subaan","Villareal","Zone I","Zone II","Zone III","Zone IV"],
    "Victoria": ["Alcate","Antonino","Babangonan","Bagong Buhay","Bagong Silang","Bambanin","Bethel","Canaan","Concepcion","Duongan","Jose Leido Jr.","Loyal","Mabini","Macatoc","Malabo","Merit","Ordovilla","Pakyas","Poblacion I","Poblacion II","Poblacion III","Poblacion IV","Sampaguita","San Antonio","San Cristobal","San Gabriel","San Gelacio","San Isidro","San Juan","San Narciso","Urdaneta","Villa Cerveza"]
};

(function() {
    var citySel = document.getElementById('branch_city');
    Object.keys(ORIENTAL_MINDORO_BARANGAYS).sort().forEach(function(name) {
        citySel.appendChild(new Option(name, name));
    });

    citySel.addEventListener('change', function() {
        populateBranchBarangay(this.value, '');
    });
})();

function populateBranchBarangay(city, selectedBarangay) {
    var brgySel = document.getElementById('branch_barangay');
    var barangays = ORIENTAL_MINDORO_BARANGAYS[city] || [];
    brgySel.innerHTML = '';

    if (!barangays.length) {
        brgySel.appendChild(new Option('Select City first', ''));
        brgySel.disabled = true;
    } else {
        brgySel.disabled = false;
        brgySel.appendChild(new Option('Select Barangay', ''));
        barangays.forEach(function(b) { brgySel.appendChild(new Option(b, b, false, b === selectedBarangay)); });
    }
}

/* ── Branch modal ──────────────────────────────────────── */
function openBranchModal(branch) {
    const isEdit = !!branch;
    document.getElementById('branchModalTitle').textContent = isEdit ? 'Edit Branch' : 'Add Branch';
    document.getElementById('branchSaveBtn').innerHTML      = isEdit
        ? '<i class="fas fa-save me-1"></i> Update Branch'
        : '<i class="fas fa-save me-1"></i> Save Branch';

    document.getElementById('branch_id').value       = isEdit ? branch.branch_id    : '';
    document.getElementById('branch_name').value     = isEdit ? branch.branch_name  : '';
    document.getElementById('branch_street').value   = isEdit ? (branch.street       || '') : '';
    document.getElementById('branch_city').value     = isEdit ? (branch.city         || '') : '';
    document.getElementById('branch_phone').value    = isEdit ? (branch.phone_number || '') : '';
    document.getElementById('branch_status').value   = isEdit ? branch.status : 'active';
    populateBranchBarangay(isEdit ? (branch.city || '') : '', isEdit ? (branch.barangay || '') : '');

    new bootstrap.Modal(document.getElementById('branchModal')).show();
}

/* ── Branch form submit ────────────────────────────────── */
document.getElementById('branchForm').addEventListener('submit', function() {
    var btn = document.getElementById('branchSaveBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';
});

/* ── Generic action confirm modal (branch/product status actions) ── */
var _pendingActionUrl = null;

function openActionConfirm(opts) {
    document.getElementById('actionModalIcon').className = 'fas ' + opts.icon + ' fa-lg';
    document.getElementById('actionModalIcon').style.color = opts.iconColor;
    document.getElementById('actionModalIconWrap').style.background = opts.iconBg;
    document.getElementById('actionModalTitle').textContent = opts.title;
    document.getElementById('actionModalBody').innerHTML = opts.body;

    var btn = document.getElementById('actionModalConfirmBtn');
    btn.className = 'btn btn-sm flex-fill ' + opts.confirmClass;
    btn.disabled = false;
    document.getElementById('actionBtnText').textContent = opts.confirmLabel;
    document.getElementById('actionBtnSpinner').classList.add('d-none');

    _pendingActionUrl = opts.url;
    new bootstrap.Modal(document.getElementById('actionConfirmModal')).show();
}

document.getElementById('actionModalConfirmBtn').addEventListener('click', function() {
    if (!_pendingActionUrl) return;
    var btn = this;
    btn.disabled = true;
    document.getElementById('actionBtnSpinner').classList.remove('d-none');

    fetch(_pendingActionUrl, { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                bootstrap.Modal.getInstance(document.getElementById('actionConfirmModal')).hide();
                alert(data.message || 'Action failed.');
                btn.disabled = false;
                document.getElementById('actionBtnSpinner').classList.add('d-none');
            }
        })
        .catch(() => {
            bootstrap.Modal.getInstance(document.getElementById('actionConfirmModal')).hide();
            alert('Request failed. Please try again.');
            btn.disabled = false;
            document.getElementById('actionBtnSpinner').classList.add('d-none');
        });
});

/* ── Delete branch ─────────────────────────────────────── */
function deleteBranch(branchId, name) {
    openActionConfirm({
        icon: 'fa-trash', iconColor: '#dc3545', iconBg: '#ffebee',
        title: 'Delete Branch?',
        body: '<strong>' + name + '</strong> will be permanently removed. This cannot be undone.',
        confirmLabel: 'Delete', confirmClass: 'btn-danger',
        url: '<?= site_url('admin/branches/delete/'); ?>' + branchId
    });
}

/* ── Deactivate / reactivate branch ───────────────────────── */
function toggleBranchStatus(branchId, name, currentStatus) {
    const deactivating = currentStatus === 'active';
    openActionConfirm({
        icon: deactivating ? 'fa-ban' : 'fa-rotate-left',
        iconColor: deactivating ? '#dc3545' : '#28a745',
        iconBg: deactivating ? '#ffebee' : '#e8f5e9',
        title: (deactivating ? 'Deactivate' : 'Reactivate') + ' Branch?',
        body: 'Are you sure you want to ' + (deactivating ? 'deactivate' : 'reactivate') + ' <strong>' + name + '</strong>?',
        confirmLabel: deactivating ? 'Deactivate' : 'Reactivate',
        confirmClass: deactivating ? 'btn-danger' : 'btn-success',
        url: '<?= site_url('admin/branches/toggle_status/'); ?>' + branchId
    });
}

/* ── Delete product ────────────────────────────────────── */
var _delProductId = null;

function deleteProduct(id, name) {
    _delProductId = id;
    document.getElementById('deleteProductName').textContent = name;
    document.getElementById('delBtnText').textContent    = 'Delete';
    document.getElementById('delBtnSpinner').classList.add('d-none');
    document.getElementById('confirmDeleteProduct').disabled = false;
    new bootstrap.Modal(document.getElementById('deleteProductModal')).show();
}

document.getElementById('confirmDeleteProduct').addEventListener('click', function() {
    if (!_delProductId) return;
    this.disabled = true;
    document.getElementById('delBtnText').textContent = 'Deleting…';
    document.getElementById('delBtnSpinner').classList.remove('d-none');

    fetch('<?= site_url('admin/product/delete/'); ?>' + _delProductId, { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            bootstrap.Modal.getInstance(document.getElementById('deleteProductModal')).hide();
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Delete failed.');
                document.getElementById('confirmDeleteProduct').disabled = false;
                document.getElementById('delBtnText').textContent = 'Delete';
                document.getElementById('delBtnSpinner').classList.add('d-none');
            }
        })
        .catch(() => {
            alert('Request failed. Please try again.');
            this.disabled = false;
        });
});

function archiveProduct(id, name) {
    openActionConfirm({
        icon: 'fa-box-archive', iconColor: '#dc3545', iconBg: '#ffebee',
        title: 'Archive Product?',
        body: '<strong>' + name + '</strong> will be hidden from inventory, catalog, and shop listings until restored.',
        confirmLabel: 'Archive', confirmClass: 'btn-danger',
        url: '<?= site_url('admin/product/archive/'); ?>' + id
    });
}

function restoreProduct(id, name) {
    openActionConfirm({
        icon: 'fa-rotate-left', iconColor: '#28a745', iconBg: '#e8f5e9',
        title: 'Restore Product?',
        body: 'Restore <strong>' + name + '</strong> back to active listings?',
        confirmLabel: 'Restore', confirmClass: 'btn-success',
        url: '<?= site_url('admin/product/restore/'); ?>' + id
    });
}
</script>

<script src="<?php echo BASE_URL; ?>public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo BASE_URL; ?>public/vendor/datatables/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
    // Excel/PDF export lives in Reports, not on this operational list page.
    $('#productsTable').DataTable({
        columnDefs: [
            { orderable: false, targets: [0, 2, 6, 8] } // Checkbox, Branch (badges), Expiration (text) and Actions columns aren't meaningfully sortable
        ],
        language: {
            search: '_INPUT_',
            searchPlaceholder: 'Quick search…',
            emptyTable: <?= json_encode('No products found' . (($search || $category || $branch || $status) ? ' — <a href="' . site_url('admin/inventory') . '">clear filters</a>' : '')); ?>
        },
        // Re-apply the mobile stacked-card data-labels after every redraw
        // (pagination/sort/search), since DataTables only touches <tbody>
        // rows and our labels live on those same <td> elements.
        drawCallback: function() { if (typeof initResponsiveTableStacking === 'function') initResponsiveTableStacking(); }
    });

    document.getElementById('selectAllProducts')?.addEventListener('change', function() {
        document.querySelectorAll('.product-row-check').forEach(cb => cb.checked = this.checked);
    });
});

</script>

<script>
(function() {
    const donutCanvas = document.getElementById('inventoryDonutChart');
    if (!donutCanvas) return;
    const donutData = <?php echo json_encode(array_map(fn($d) => (int) $d['count'], $donutBreakdown)); ?>;
    const donutColors = <?php echo json_encode(array_column($donutBreakdown, 'color')); ?>;
    const donutHasData = donutData.some(v => v > 0);

    new Chart(donutCanvas.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($donutBreakdown, 'label')); ?>,
            datasets: [{
                data: donutHasData ? donutData : [1],
                backgroundColor: donutHasData ? donutColors : ['#eee'],
                borderWidth: 3,
                borderColor: '#fff',
                hoverOffset: donutHasData ? 8 : 0,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: donutHasData,
                    backgroundColor: '#fff', titleColor: '#1a1a2e', bodyColor: '#e0559c', bodyFont: { weight: '700' },
                    borderColor: '#f0d9e8', borderWidth: 1, padding: 10, cornerRadius: 10,
                },
            },
        },
    });
})();
</script>
