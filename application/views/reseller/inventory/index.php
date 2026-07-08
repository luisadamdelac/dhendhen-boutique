<!-- Page Header -->
<div class="page-header-section">
    <div>
        <h1 class="page-title"><i class="fas fa-store"></i> My Mini-Shop</h1>
        <p class="page-subtitle">Stock is centralized — publish products from the catalog below, set your own price, and go.</p>
    </div>
</div>

<?php $shopUrl = BASE_URL . 'shop/reseller/' . $this->session->userdata('user_id'); ?>
<div class="card">
    <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
        <div>
            <p style="font-weight: 600; color: var(--dark); margin-bottom: 4px;"><i class="fas fa-store"></i> Your Mini-Shop</p>
            <p style="margin: 0; color: var(--gray); font-size: 14px;">Share this link so customers buy directly from your storefront — every sale is automatically credited to you.</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <input type="text" readonly value="<?php echo htmlspecialchars($shopUrl); ?>" id="shopUrlInput"
                   style="padding: 8px 12px; border: 1px solid var(--gray-300); border-radius: 8px; min-width: 260px;">
            <button type="button" class="btn btn-primary" onclick="copyShopUrl()">
                <i class="fas fa-copy"></i> Copy
            </button>
            <a href="<?php echo $shopUrl; ?>" target="_blank" class="btn btn-outline">
                <i class="fas fa-external-link-alt"></i> View
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

<!-- My Mini-Shop Section -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3><i class="fas fa-store"></i> Published Listings</h3>
        <span class="badge badge-primary"><?php echo count($myListings); ?> Products</span>
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
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Base Price</th>
                            <th>My Price</th>
                            <th>Central Stock</th>
                            <th>Sold (via me)</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myListings as $listing): ?>
                            <?php $sold = (int) ($soldByProduct[$listing['product_id']] ?? 0); ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($listing['product_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($listing['category_name'] ?? 'Uncategorized'); ?></small>
                                </td>
                                <td>₱<?php echo number_format($listing['base_price'], 2); ?></td>
                                <td>
                                    <form class="price-form d-inline-flex" data-id="<?php echo $listing['reseller_product_id']; ?>" style="gap:6px;">
                                        <input type="number" step="0.01" min="<?php echo $listing['base_price']; ?>" name="commission_price"
                                               value="<?php echo htmlspecialchars($listing['commission_price']); ?>"
                                               style="width:100px;" class="form-control form-control-sm">
                                        <button type="submit" class="btn btn-sm btn-info" title="Save price"><i class="fas fa-save"></i></button>
                                    </form>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $listing['central_stock'] > 10 ? 'success' : ($listing['central_stock'] > 0 ? 'warning' : 'danger'); ?>">
                                        <?php echo (int) $listing['central_stock']; ?> units
                                    </span>
                                </td>
                                <td><span class="badge badge-primary"><?php echo $sold; ?> sold</span></td>
                                <td>
                                    <span class="badge badge-<?php echo $listing['is_published'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $listing['is_published'] ? 'Published' : 'Unpublished'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button onclick="shareProduct(<?php echo $listing['product_id']; ?>)" class="btn btn-sm btn-outline-primary" title="Copy share link">
                                            <i class="fas fa-share-alt"></i>
                                        </button>
                                        <a href="<?php echo BASE_URL; ?>reseller/inventory/history/<?php echo $listing['product_id']; ?>" class="btn btn-sm btn-outline" title="Stock history">
                                            <i class="fas fa-clock"></i>
                                        </a>
                                        <?php if ($listing['is_published']): ?>
                                            <form method="post" action="<?php echo BASE_URL; ?>reseller/inventory/unpublish/<?php echo $listing['reseller_product_id']; ?>" class="d-inline" onsubmit="return confirm('Remove this product from your mini-shop?');">
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

<!-- Browse Central Catalog Section -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-shopping-bag"></i> Browse Central Catalog</h3>
        <span class="badge badge-info"><?php echo count($catalog); ?> Available</span>
    </div>
    <div class="card-body">
        <?php if (empty($catalog)): ?>
            <p style="text-align: center; color: var(--gray-500); padding: 2rem;">You've published everything currently available — check back later for new stock.</p>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($catalog as $product): ?>
                    <?php $suggested = round((float) $product['price'] + (float) ($product['reseller_commission'] ?? 0), 2); ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if (!empty($product['product_image'])): ?>
                                <img src="<?php echo BASE_URL . $product['product_image']; ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                            <?php else: ?>
                                <div class="product-image-placeholder"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                            <div class="product-badge">
                                <span class="badge badge-<?php echo $product['stock'] > 10 ? 'success' : ($product['stock'] > 0 ? 'warning' : 'danger'); ?>">
                                    <?php echo (int) $product['stock']; ?> in stock
                                </span>
                            </div>
                        </div>
                        <div class="product-details">
                            <h4><?php echo htmlspecialchars($product['product_name']); ?></h4>
                            <p class="product-description">
                                <?php echo htmlspecialchars(substr($product['description'] ?? 'No description available', 0, 80)); ?>...
                            </p>
                            <div class="product-pricing">
                                <div>
                                    <small>Base Price</small>
                                    <strong class="price-customer">₱<?php echo number_format($product['price'], 2); ?></strong>
                                </div>
                                <div>
                                    <small>Suggested Price</small>
                                    <strong class="price-reseller">₱<?php echo number_format($suggested, 2); ?></strong>
                                </div>
                            </div>
                            <div class="product-actions">
                                <?php if ((int) $product['stock'] > 0): ?>
                                    <form method="post" action="<?php echo BASE_URL; ?>reseller/inventory/publish/<?php echo $product['product_id']; ?>" class="d-inline">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-plus"></i> Publish to My Shop
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-outline" disabled title="Out of stock centrally">
                                        <i class="fas fa-ban"></i> Out of Stock
                                    </button>
                                <?php endif; ?>
                                <a href="<?php echo BASE_URL; ?>reseller/inventory/history/<?php echo $product['product_id']; ?>" class="btn btn-sm btn-outline" style="display:none;">
                                    <i class="fas fa-clock"></i> History
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
.product-card { border: 1px solid var(--gray-200); border-radius: 12px; overflow: hidden; transition: all 0.3s; background: white; }
.product-card:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12); }
.product-image { position: relative; height: 200px; overflow: hidden; background: var(--gray-100); }
.product-image img { width: 100%; height: 100%; object-fit: cover; }
.product-image-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--gray-400); font-size: 3rem; }
.product-badge { position: absolute; top: 10px; right: 10px; }
.product-details { padding: 1.25rem; }
.product-details h4 { margin: 0 0 0.75rem 0; font-size: 1rem; color: var(--gray-800); line-height: 1.4; }
.product-description { color: var(--gray-600); font-size: 0.85rem; margin: 0 0 1rem 0; line-height: 1.5; }
.product-pricing { display: flex; justify-content: space-between; gap: 1rem; padding-top: 1rem; border-top: 1px solid var(--gray-200); }
.product-pricing > div { flex: 1; display: flex; flex-direction: column; }
.product-pricing small { color: var(--gray-500); font-size: 0.75rem; margin-bottom: 0.25rem; }
.price-customer { color: var(--gray-700); font-size: 1.1rem; }
.price-reseller { color: var(--primary-pink); font-size: 1.1rem; }
.product-actions { display: flex; gap: 0.5rem; margin-top: 1rem; flex-wrap: wrap; }
@media (max-width: 768px) {
    .products-grid { grid-template-columns: 1fr; }
}
</style>

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
</script>
