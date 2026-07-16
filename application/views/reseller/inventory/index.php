<?php
$stats = $stats ?? [];
$trend = $trend ?? ($stats['trend'] ?? []);

$statCards = [
    ['icon' => 'fa-shopping-bag',  'bg' => '#fce4ec', 'fg' => '#d6336c', 'label' => 'Total Products',       'value' => number_format($stats['total_products'] ?? 0), 'suffix' => '',      'tag' => 'Published', 'trendKey' => 'products'],
    ['icon' => 'fa-box',           'bg' => '#e7f1ff', 'fg' => '#2563eb', 'label' => 'Total Stock',          'value' => number_format($stats['total_stock'] ?? 0),    'suffix' => 'Units', 'tag' => null,         'trendKey' => 'stock'],
    ['icon' => 'fa-cart-shopping', 'bg' => '#e6f9ee', 'fg' => '#16a34a', 'label' => 'Total Sold (via me)',  'value' => number_format($stats['sold_this_month'] ?? 0),'suffix' => 'This Month', 'tag' => null,    'trendKey' => 'sold'],
    ['icon' => 'fa-peso-sign',     'bg' => '#f3e8ff', 'fg' => '#7c3aed', 'label' => 'Total Sales',          'value' => '₱' . number_format($stats['sales_this_month'] ?? 0, 2), 'suffix' => 'This Month', 'tag' => null, 'trendKey' => 'sales'],
];

$inStockUnits = (int) ($stats['in_stock_units'] ?? 0);
$lowStockUnits = (int) ($stats['low_stock_units'] ?? 0);
$outOfStockCount = (int) ($stats['out_of_stock_count'] ?? 0);
$soldAllTime = (int) ($stats['sold_all_time'] ?? 0);
$totalUnitsForDonut = max(1, $inStockUnits + $lowStockUnits + $outOfStockCount + $soldAllTime);
$lowStockItems = $stats['low_stock_items'] ?? [];

$donutBreakdown = [
    ['label' => 'In Stock',     'color' => '#22C55E', 'count' => $inStockUnits],
    ['label' => 'Low Stock',    'color' => '#F59E0B', 'count' => $lowStockUnits],
    ['label' => 'Out of Stock', 'color' => '#EF4444', 'count' => $outOfStockCount],
    ['label' => 'Sold Items',   'color' => '#3B82F6', 'count' => $soldAllTime],
];
?>
<style>
.stat-card-mini { flex-direction: column; align-items: stretch; gap: .7rem; min-height: auto; padding: 1.1rem 1.2rem .95rem; }
.stat-card-mini-top { display: flex; align-items: flex-start; gap: .85rem; }
.stat-card-mini .stat-icon { width: 46px; height: 46px; border-radius: 13px; font-size: 1.05rem; }
.stat-card-mini .stat-value { font-size: 1.45rem; display: flex; align-items: baseline; gap: 5px; }
.stat-card-mini .stat-value small { font-size: .68rem; font-weight: 600; color: var(--gray); text-transform: none; letter-spacing: 0; }
.stat-tag { display: inline-block; margin-top: 4px; background: var(--primary-pink-light); color: var(--primary-pink-dark); font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; padding: 2px 8px; border-radius: var(--radius-full); }
.stat-trend-line { font-size: 11.5px; font-weight: 700; display: flex; align-items: center; gap: 4px; border-top: 1px solid var(--border); padding-top: .55rem; }
.stat-trend-line.up { color: #16A34A; }
.stat-trend-line.down { color: #DC2626; }
.stat-trend-line.flat { color: var(--gray); }
.stat-trend-line span.muted { color: var(--gray); font-weight: 500; }

.minishop-card { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; padding: 1.1rem 1.3rem; }
.minishop-icon { width: 54px; height: 54px; border-radius: 15px; background: var(--primary-pink-light); color: var(--primary-pink-dark); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
.minishop-info { flex: 1; min-width: 220px; }
.minishop-info h4 { margin: 0 0 2px; font-size: .98rem; font-weight: 700; color: var(--text); }
.minishop-info p { margin: 0; font-size: .82rem; color: var(--gray); }
.minishop-link-group { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.minishop-link-group input { padding: 9px 14px; border: 1px solid var(--border); border-radius: var(--radius-md); min-width: 260px; font-size: .82rem; color: var(--gray); background: var(--page-bg); }

.section-toolbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.section-toolbar .search-box { position: relative; }
.section-toolbar .search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--gray); font-size: .8rem; }
.section-toolbar .search-box input { padding: 8px 14px 8px 32px; border: 1px solid var(--border); border-radius: var(--radius-md); font-size: .82rem; min-width: 200px; }

.donut-legend-row { display: flex; align-items: center; justify-content: space-between; padding: 7px 0; font-size: var(--font-size-sm); }
.donut-legend-row .dot { width: 9px; height: 9px; border-radius: 50%; display: inline-block; margin-right: 8px; }

/* Without a floor on the Product column's width, a long product name has
   nowhere else to go but to get squeezed down to one-word-per-line by the
   other 7 columns' content (price, badges, action buttons) — even on a
   wide/maximized browser — instead of the table just overflowing into its
   own horizontal scrollbar like it's supposed to. */
#listingsTable { table-layout: auto; }
#listingsTable th:first-child, #listingsTable td:first-child { min-width: 240px; }
#listingsTable th:last-child, #listingsTable td:last-child { min-width: 150px; }

/* Listings table: stacked cards instead of a horizontally-scrolled table
   on narrow screens — a 7-column table has no room to breathe there and
   ends up with columns overlapping/cramped no matter how it's scrolled. */
@media (max-width: 768px) {
    #listingsTable thead { display: none; }
    #listingsTable, #listingsTable tbody, #listingsTable tr, #listingsTable td {
        display: block; width: 100%;
    }
    #listingsTable tr {
        border: 1px solid var(--border); border-radius: 10px; margin-bottom: 12px; padding: 10px;
    }
    #listingsTable td {
        display: flex; align-items: center; justify-content: space-between; gap: 10px;
        border: none; padding: 8px 4px;
    }
    #listingsTable td[data-label]::before {
        content: attr(data-label); font-weight: 600; font-size: .72rem; color: var(--gray);
        text-transform: uppercase; flex-shrink: 0;
    }
}
.donut-legend-row .pct { color: var(--gray); font-size: .75rem; font-weight: 600; }

.low-stock-item { display: flex; align-items: center; gap: 10px; padding: 10px 4px; border-bottom: 1px solid var(--border); font-size: .85rem; }
.low-stock-item:last-child { border-bottom: none; }
.low-stock-item .ls-icon { width: 34px; height: 34px; border-radius: 10px; background: #fff7ed; color: #d97706; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.low-stock-item .ls-info { flex: 1; min-width: 0; }
.low-stock-item .ls-info strong { display: block; font-size: .84rem; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.low-stock-item .ls-info span { font-size: .74rem; color: var(--gray); }

.tips-card { background: linear-gradient(135deg, var(--primary-pink-light), #fff); }
.tips-card ul { list-style: none; margin: 0; padding: 0; }
.tips-card ul li { display: flex; align-items: flex-start; gap: 8px; font-size: .82rem; color: var(--text); padding: 6px 0; }
.tips-card ul li i { color: #16A34A; margin-top: 2px; }

.catalog-scroll-wrap { position: relative; }
.catalog-scroll { display: flex; gap: 1rem; overflow-x: auto; scroll-behavior: smooth; padding-bottom: 6px; scrollbar-width: thin; }
.catalog-scroll::-webkit-scrollbar { height: 6px; }
.catalog-scroll::-webkit-scrollbar-thumb { background: var(--primary-pink-light); border-radius: var(--radius-full); }
.catalog-mini-card { flex: 0 0 170px; border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; background: var(--card-bg); transition: box-shadow .2s, transform .2s; }
.catalog-mini-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
.catalog-mini-card .cm-image { height: 110px; background: var(--page-bg); display: flex; align-items: center; justify-content: center; overflow: hidden; }
.catalog-mini-card .cm-image img { width: 100%; height: 100%; object-fit: cover; }
.catalog-mini-card .cm-image .placeholder { color: var(--gray-400, #cbd5e1); font-size: 2rem; }
.catalog-mini-card .cm-body { padding: .7rem .8rem .85rem; }
.catalog-mini-card .cm-body h5 { margin: 0 0 4px; font-size: .8rem; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.catalog-mini-card .cm-body .cm-price { font-size: .88rem; font-weight: 800; color: var(--primary-pink-dark); margin-bottom: 8px; }
.catalog-mini-card .cm-body .btn { width: 100%; font-size: .74rem; padding: 6px 0; }
.catalog-nav-btn { width: 34px; height: 34px; border-radius: 50%; border: 1px solid var(--border); background: var(--card-bg); color: var(--text); display: flex; align-items: center; justify-content: center; flex-shrink: 0; cursor: pointer; }
.catalog-nav-btn:hover { border-color: var(--primary-pink); color: var(--primary-pink); }

@media (max-width: 991px) { .minishop-link-group input { min-width: 160px; } }
</style>

<!-- Page Header -->
<div class="page-header-section">
    <div>
        <h1 class="page-title"><i class="fas fa-store"></i> My Mini-Shop</h1>
        <p class="page-subtitle">Manage your inventory and publish products to your storefront.</p>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mt-2">
    <?php foreach ($statCards as $card): ?>
        <?php
            $pct = $trend[$card['trendKey']] ?? 0;
            $trendClass = $pct > 0.05 ? 'up' : ($pct < -0.05 ? 'down' : 'flat');
            $trendIcon = $trendClass === 'up' ? 'fa-arrow-up' : ($trendClass === 'down' ? 'fa-arrow-down' : 'fa-minus');
        ?>
        <div class="col-6 col-xl-3">
            <div class="stat-card stat-card-mini">
                <div class="stat-card-mini-top">
                    <div class="stat-icon" style="background:<?php echo $card['bg']; ?>;color:<?php echo $card['fg']; ?>;"><i class="fas <?php echo $card['icon']; ?>"></i></div>
                    <div>
                        <div class="stat-label"><?php echo $card['label']; ?></div>
                        <div class="stat-value"><?php echo $card['value']; ?><?php if ($card['suffix']): ?><small><?php echo $card['suffix']; ?></small><?php endif; ?></div>
                        <?php if ($card['tag']): ?><span class="stat-tag"><?php echo $card['tag']; ?></span><?php endif; ?>
                    </div>
                </div>
                <div class="stat-trend-line <?php echo $trendClass; ?>">
                    <i class="fas <?php echo $trendIcon; ?>"></i> <?php echo number_format(abs($pct), 1); ?>% <span class="muted">vs last month</span>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Mini-Shop Link -->
<?php $shopUrl = BASE_URL . 'shop/reseller/' . $this->session->userdata('user_id'); ?>
<div class="card mt-3">
    <div class="minishop-card">
        <div class="minishop-icon"><i class="fas fa-store"></i></div>
        <div class="minishop-info">
            <h4>Your Mini-Shop Link</h4>
            <p>Share your link to start selling and earn commissions!</p>
        </div>
        <div class="minishop-link-group">
            <input type="text" readonly value="<?php echo htmlspecialchars($shopUrl); ?>" id="shopUrlInput">
            <button type="button" class="btn btn-primary" onclick="copyShopUrl()">
                <i class="fas fa-copy"></i> Copy Link
            </button>
            <a href="<?php echo $shopUrl; ?>" target="_blank" class="btn btn-outline">
                <i class="fas fa-external-link-alt"></i> View Store
            </a>
        </div>
    </div>
</div>

<script>
function copyShopUrl() {
    const input = document.getElementById('shopUrlInput');
    input.select();
    navigator.clipboard.writeText(input.value).then(() => alert('Mini-shop link copied!'));
}
</script>

<!-- Published Listings + Sidebar -->
<div class="row g-3 mt-1">
    <div class="col-12 col-xl-8">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-store"></i> Published Listings <span class="badge badge-primary" style="margin-left:6px;"><?php echo count($myListings); ?> Products</span></h3>
                <div class="section-toolbar">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="listingSearch" placeholder="Search product...">
                    </div>
                    <select id="listingFilter" class="form-select form-select-sm" style="width:auto;">
                        <option value="">All Status</option>
                        <option value="published">Published</option>
                        <option value="unpublished">Unpublished</option>
                    </select>
                    <a href="#catalogSection" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Publish Product</a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($myListings)): ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <h3>Nothing Published Yet</h3>
                        <p>Browse the central catalog below and publish a product to start selling.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped" id="listingsTable">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Base Price</th>
                                    <th>My Price</th>
                                    <th>Stock</th>
                                    <th>Expiry</th>
                                    <th>Sold (via me)</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myListings as $listing): ?>
                                    <?php $sold = (int) ($soldByProduct[$listing['product_id']] ?? 0); ?>
                                    <tr data-name="<?php echo htmlspecialchars(strtolower($listing['product_name'])); ?>" data-status="<?php echo $listing['is_published'] ? 'published' : 'unpublished'; ?>">
                                        <td data-label="Product">
                                            <div style="display:flex;align-items:center;gap:10px;">
                                                <?php if (!empty($listing['product_image'])): ?>
                                                    <img src="<?php echo BASE_URL . $listing['product_image']; ?>" alt="" style="width:40px;height:40px;border-radius:var(--radius-md);object-fit:cover;border:1px solid var(--border);flex-shrink:0;">
                                                <?php else: ?>
                                                    <span style="width:40px;height:40px;border-radius:var(--radius-md);background:var(--page-bg);display:flex;align-items:center;justify-content:center;color:var(--gray);flex-shrink:0;"><i class="fas fa-image"></i></span>
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($listing['product_name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($listing['category_name'] ?? 'Uncategorized'); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Base Price">₱<?php echo number_format($listing['base_price'], 2); ?></td>
                                        <td data-label="My Price">
                                            <form class="price-form d-inline-flex" data-id="<?php echo $listing['reseller_product_id']; ?>" style="gap:6px;">
                                                <input type="number" step="0.01" min="<?php echo $listing['base_price']; ?>" name="commission_price"
                                                       value="<?php echo htmlspecialchars($listing['commission_price']); ?>"
                                                       style="width:100px;" class="form-control form-control-sm">
                                                <button type="submit" class="btn btn-sm btn-info" title="Save price"><i class="fas fa-save"></i></button>
                                            </form>
                                        </td>
                                        <td data-label="Stock">
                                            <span class="badge badge-<?php echo $listing['central_stock'] > 10 ? 'success' : ($listing['central_stock'] > 0 ? 'warning' : 'danger'); ?>">
                                                <?php echo (int) $listing['central_stock']; ?> units
                                            </span>
                                        </td>
                                        <td data-label="Expiry">
                                            <?php if (!empty($listing['expiry_date'])): ?>
                                                <?php $daysLeft = (int) ceil((strtotime($listing['expiry_date']) - strtotime(date('Y-m-d'))) / 86400); ?>
                                                <span class="small" style="color:#1a1a2e;"><?php echo date('M j, Y', strtotime($listing['expiry_date'])); ?></span><br>
                                                <span style="font-size:10.5px;font-weight:600;color:<?php echo $daysLeft <= 30 ? '#dc3545' : '#8a94ad'; ?>;">
                                                    <?php echo $daysLeft < 0 ? 'Expired' : $daysLeft . ' days left'; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Sold (via me)"><span class="badge badge-primary"><?php echo $sold; ?> sold</span></td>
                                        <td data-label="Status">
                                            <span class="badge badge-<?php echo $listing['is_published'] ? 'success' : 'secondary'; ?>">
                                                <?php echo $listing['is_published'] ? 'Published' : 'Unpublished'; ?>
                                            </span>
                                        </td>
                                        <td data-label="Actions">
                                            <div class="action-buttons">
                                                <button onclick="shareProduct(<?php echo $listing['product_id']; ?>)" class="btn btn-sm btn-outline-primary" title="Copy share link">
                                                    <i class="fas fa-share-alt"></i>
                                                </button>
                                                <a href="<?php echo BASE_URL; ?>reseller/inventory/history/<?php echo $listing['product_id']; ?>" class="btn btn-sm btn-outline" title="Stock history">
                                                    <i class="fas fa-clock"></i>
                                                </a>
                                                <?php if ($listing['is_published']): ?>
                                                    <form method="post" action="<?php echo BASE_URL; ?>reseller/inventory/unpublish/<?php echo $listing['reseller_product_id']; ?>" class="d-inline" onsubmit="return confirmSubmit(event, this, 'Remove this product from your mini-shop?');">
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Unpublish"><i class="fas fa-eye-slash"></i></button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="post" action="<?php echo BASE_URL; ?>reseller/inventory/publish/<?php echo $listing['product_id']; ?>" class="d-inline">
                                                        <button type="submit" class="btn btn-sm btn-success" title="Re-publish"><i class="fas fa-eye"></i></button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie"></i> Inventory Overview</h3>
            </div>
            <div class="card-body">
                <p class="text-muted" style="margin-top:-8px;font-size:.8rem;">Quick summary of your inventory</p>
                <div class="chart-container" style="height:190px;position:relative;">
                    <canvas id="inventoryDonutChart"></canvas>
                    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none;">
                        <div style="font-size:20px;font-weight:800;color:var(--text);"><?php echo number_format($totalUnitsForDonut); ?></div>
                        <div style="font-size:10px;color:var(--gray);text-transform:uppercase;letter-spacing:.04em;">Total Units</div>
                    </div>
                </div>
                <div style="margin-top:10px;">
                    <?php foreach ($donutBreakdown as $d): ?>
                        <?php $pct = round(($d['count'] / $totalUnitsForDonut) * 100, 1); ?>
                        <div class="donut-legend-row">
                            <span><span class="dot" style="background:<?php echo $d['color']; ?>;"></span><?php echo $d['label']; ?></span>
                            <strong><?php echo (int) $d['count']; ?> <span class="pct">(<?php echo $pct; ?>%)</span></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bell"></i> Low Stock Alerts</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($lowStockItems)): ?>
                    <?php foreach ($lowStockItems as $item): ?>
                        <div class="low-stock-item">
                            <div class="ls-icon"><i class="fas fa-triangle-exclamation"></i></div>
                            <div class="ls-info">
                                <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                <span><?php echo (int) $item['stock']; ?> units left</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted" style="padding:14px 0;font-size:.85rem;">No low stock items right now.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card tips-card">
            <div class="card-header" style="border-bottom-color:rgba(255,105,180,.2);">
                <h3 class="card-title"><i class="fas fa-lightbulb"></i> Tips to Boost Sales</h3>
            </div>
            <div class="card-body">
                <ul>
                    <li><i class="fas fa-check-circle"></i> Keep your prices competitive</li>
                    <li><i class="fas fa-check-circle"></i> Always update your inventory</li>
                    <li><i class="fas fa-check-circle"></i> Share your store link daily</li>
                    <li><i class="fas fa-check-circle"></i> Use quality product images</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Browse Central Catalog Section -->
<div class="card mt-3" id="catalogSection">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-shopping-bag"></i> Browse Central Catalog</h3>
        <span class="badge badge-info"><?php echo count($catalog); ?> Available</span>
    </div>
    <div class="card-body">
        <?php if (empty($catalog)): ?>
            <p style="text-align: center; color: var(--gray-500); padding: 2rem;">You've published everything currently available — check back later for new stock.</p>
        <?php else: ?>
            <div class="catalog-scroll-wrap" style="display:flex;align-items:center;gap:10px;">
                <button type="button" class="catalog-nav-btn" onclick="document.getElementById('catalogScroll').scrollBy({left:-200,behavior:'smooth'})"><i class="fas fa-chevron-left"></i></button>
                <div class="catalog-scroll" id="catalogScroll">
                    <?php foreach ($catalog as $product): ?>
                        <?php $suggested = round((float) $product['price'] + (float) ($product['reseller_commission'] ?? 0), 2); ?>
                        <div class="catalog-mini-card">
                            <div class="cm-image">
                                <?php if (!empty($product['product_image'])): ?>
                                    <img src="<?php echo BASE_URL . $product['product_image']; ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                                         onerror="this.replaceWith(Object.assign(document.createElement('div'), {className:'placeholder', innerHTML:'<i class=\'fas fa-image\'></i>'}));">
                                <?php else: ?>
                                    <div class="placeholder"><i class="fas fa-image"></i></div>
                                <?php endif; ?>
                            </div>
                            <div class="cm-body">
                                <h5 title="<?php echo htmlspecialchars($product['product_name']); ?>"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                                <div class="cm-price">₱<?php echo number_format($product['price'], 2); ?></div>
                                <?php if ((int) $product['stock'] > 0): ?>
                                    <form method="post" action="<?php echo BASE_URL; ?>reseller/inventory/publish/<?php echo $product['product_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-primary">Add to Shop</button>
                                    </form>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-outline" disabled title="Out of stock centrally">Out of Stock</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="catalog-nav-btn" onclick="document.getElementById('catalogScroll').scrollBy({left:200,behavior:'smooth'})"><i class="fas fa-chevron-right"></i></button>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function shareProduct(productId) {
    const resellerId = <?php echo (int) $this->session->userdata('user_id'); ?>;
    const customerShopUrl = '<?php echo str_replace("/reseller/", "/customer/", BASE_URL); ?>shop/product/' + productId + '?ref=' + resellerId;

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(customerShopUrl).then(() => alert('Share link copied to clipboard!'));
    }
}

document.querySelectorAll('.price-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = this.dataset.id;
        const price = this.querySelector('input[name="commission_price"]').value;
        fetch('<?php echo BASE_URL; ?>reseller/inventory/update_price', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'reseller_product_id=' + encodeURIComponent(id) + '&commission_price=' + encodeURIComponent(price)
        })
        .then(r => r.json())
        .then(data => { if (!data.success) alert(data.message || 'Failed to update price'); else location.reload(); })
        .catch(() => alert('Request failed'));
    });
});

(function() {
    const searchInput = document.getElementById('listingSearch');
    const filterSelect = document.getElementById('listingFilter');
    const rows = document.querySelectorAll('#listingsTable tbody tr');

    function applyFilters() {
        const q = (searchInput?.value || '').toLowerCase().trim();
        const status = filterSelect?.value || '';
        rows.forEach(row => {
            const matchesSearch = !q || row.dataset.name.includes(q);
            const matchesStatus = !status || row.dataset.status === status;
            row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
        });
    }

    searchInput?.addEventListener('input', applyFilters);
    filterSelect?.addEventListener('change', applyFilters);

    const donutData = <?php echo json_encode(array_map(fn($d) => (int) $d['count'], $donutBreakdown)); ?>;
    const donutColors = <?php echo json_encode(array_column($donutBreakdown, 'color')); ?>;
    const donutHasData = donutData.some(v => v > 0);

    new Chart(document.getElementById('inventoryDonutChart').getContext('2d'), {
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
