<style>
    /* .products-grid/.product-card/.product-image/.product-info/.product-name/
       .product-price/.empty-state now come from the shared public/css/style.css
       component library instead of being redeclared per page. */
    .product-stock {
        font-size: 13px;
        color: var(--gray);
        margin-bottom: 15px;
    }
    
    .filter-section {
        background: white;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .category-filter {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .category-btn {
        padding: 8px 20px;
        background: white;
        border: 2px solid #ddd;
        border-radius: 25px;
        color: var(--dark);
        text-decoration: none;
        transition: all 0.3s;
        font-size: 14px;
    }
    
    .category-btn:hover, .category-btn.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 40px;
    }
    
    .page-btn {
        padding: 8px 15px;
        background: white;
        border: 2px solid #ddd;
        border-radius: 8px;
        color: var(--dark);
        text-decoration: none;
        transition: all 0.3s;
    }
    
    .page-btn:hover, .page-btn.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }
    
    .page-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* ── Add to Cart modal (structural .modal/.modal-content/etc come from
       the shared component in customer/layouts/footer.php) ──────────── */
    .atc-modal-header {
        display: flex;
        gap: 14px;
    }
    .atc-modal-header img {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 8px;
        flex-shrink: 0;
        background: #f0f0f0;
    }
    .atc-modal-name { font-weight: 600; font-size: 15px; margin-bottom: 4px; color: var(--dark); }
    .atc-modal-price { color: var(--primary-pink); font-weight: 700; font-size: 18px; }
    .atc-variation-label { font-size: 13px; font-weight: 600; color: #666; margin-bottom: 8px; }
    .atc-variation-group { margin-bottom: 12px; }
    .atc-variation-opt {
        display: inline-block;
        padding: 7px 14px;
        margin: 0 6px 6px 0;
        border: 2px solid #ddd;
        border-radius: 8px;
        background: white;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.15s;
    }
    .atc-variation-opt:hover { border-color: var(--primary-pink); }
    .atc-variation-opt.selected { border-color: var(--primary-pink); background: #fff5f8; font-weight: 600; }
    .atc-variation-opt.disabled { opacity: 0.4; cursor: not-allowed; text-decoration: line-through; }
    .atc-variation-error { display: none; color: #e53935; font-size: 12px; margin-top: 4px; }
    .atc-qty-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 18px 0 22px;
    }
    .atc-qty-controls { display: flex; align-items: center; gap: 12px; }
    .atc-qty-btn {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 2px solid #ddd;
        background: white;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .atc-qty-btn:hover { border-color: var(--primary-pink); color: var(--primary-pink); }
    .atc-qty-value { min-width: 24px; text-align: center; font-weight: 600; }
    .atc-modal-actions { display: flex; gap: 10px; }
    .atc-modal-actions .btn { flex: 1; }
</style>

<?php if (!empty($isResellerShop) && !empty($reseller)): ?>
    <div style="background: linear-gradient(135deg, #ff69b4 0%, #9370db 100%); color: white; padding: 20px 25px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 15px;">
        <i class="fas fa-store" style="font-size: 32px;"></i>
        <div>
            <h2 style="margin: 0; font-size: 20px;">
                <?php echo htmlspecialchars(!empty($reseller['business_name']) ? $reseller['business_name'] : trim($reseller['first_name'] . ' ' . $reseller['last_name'])); ?>
            </h2>
            <p style="margin: 0; opacity: 0.9; font-size: 13px;">Shopping from this reseller's mini-shop &mdash; your purchase supports them directly.</p>
        </div>
    </div>
<?php endif; ?>

<div class="filter-section">
    <h3 style="margin-bottom: 15px;">
        <i class="fas fa-filter"></i> Filter by Category
    </h3>
    <div class="category-filter">
        <a href="<?php echo BASE_URL; ?>shop" class="category-btn <?php echo !isset($_GET['category']) ? 'active' : ''; ?>">
            All Products
        </a>
        <?php foreach ($categories as $category): ?>
            <a href="<?php echo BASE_URL; ?>shop?category=<?php echo $category['category_id']; ?>" 
               class="category-btn <?php echo (isset($_GET['category']) && $_GET['category'] == $category['category_id']) ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($category['category_name']); ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php if ($searchQuery): ?>
<div style="background: #e3f2fd; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px;">
    <i class="fas fa-search"></i> Search results for: <strong><?php echo htmlspecialchars($searchQuery); ?></strong>
    <a href="<?php echo BASE_URL; ?>shop" style="margin-left: 15px; color: var(--primary);">Clear search</a>
</div>
<?php endif; ?>

<div class="shop-header">
    <div class="shop-back">
        <a class="landing-back-link" href="<?php echo BASE_URL; ?>landing">
            <i class="fas fa-arrow-left"></i> Back to Landing
        </a>
    </div>

    <h2 class="shop-title">
        <i class="fas fa-shopping-bag"></i>
        <?php echo $searchQuery ? 'Search Results' : ($currentCategory ? 'Products' : 'Featured Products'); ?>
    </h2>
</div>


<?php if (empty($products)): ?>
    <div class="empty-state">
        <i class="fas fa-box-open"></i>
        <h3>No products found</h3>
        <p>Try adjusting your search or filter to find what you're looking for</p>
        <a href="<?php echo BASE_URL; ?>shop" class="btn" style="margin-top: 20px;">Browse All Products</a>
    </div>
<?php else: ?>
    <div class="products-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card" onclick="location.href='<?php echo BASE_URL; ?>shop/product/<?php echo $product['product_id']; ?>'">
                <?php $productImage = $product['product_image'] ?? $product['image'] ?? ''; ?>
                <?php if (!empty($productImage)): ?>
                    <img src="<?php echo BASE_URL . $productImage; ?>"
                         alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                         class="product-image"
                         onerror="this.src='<?php echo BASE_URL; ?>public/images/no-image.jpg'">
                <?php else: ?>
                    <div class="product-image" style="display: flex; align-items: center; justify-content: center; background: #f0f0f0;">
                        <i class="fas fa-image" style="font-size: 60px; color: #ccc;"></i>
                    </div>
                <?php endif; ?>
                
                <div class="product-info">
                    <div class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></div>
                    <?php if (!empty($product['has_discount']) && !empty($product['discounted_price'])): ?>
                    <div class="product-price">
                        <span class="original-price">₱<?php echo number_format($product['price'], 2); ?></span>
                        ₱<?php echo number_format($product['discounted_price'], 2); ?>
                    </div>
                    <?php else: ?>
                    <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($product['total_sold'])): ?>
                    <div style="font-size: 12px; color: #e65100; margin-bottom: 5px;"><i class="fas fa-fire"></i> <?php echo $product['total_sold']; ?> sold</div>
                    <?php endif; ?>
                    <div class="product-stock">
                        <i class="fas fa-box"></i>
                        <?php echo $product['stock'] > 0 ? $product['stock'] . ' in stock' : 'Out of stock'; ?>
                    </div>
                    <?php $canBuy = ($product['purchasable'] ?? true) && $product['stock'] > 0; ?>
                    <?php
                        $atcPayload = htmlspecialchars(json_encode([
                            'id' => (int) $product['product_id'],
                            'name' => $product['product_name'],
                            'image' => !empty($productImage) ? BASE_URL . $productImage : (BASE_URL . 'public/images/no-image.jpg'),
                            'price' => (float) $product['price'],
                            'stock' => (int) $product['stock'],
                            'hasVariations' => !empty($product['has_variations']),
                        ]), ENT_QUOTES);
                    ?>
                    <div class="product-actions-row" style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;">
                        <button class="btn btn-sm w-100" style="background: var(--gradient-hover, var(--primary-pink)); white-space: nowrap;" <?php echo $canBuy ? '' : 'disabled'; ?>
                            onclick="event.stopPropagation(); <?php echo $canBuy ? 'openAddToCartModal(' . $atcPayload . ", 'buy')" : ''; ?>">
                            <i class="fas fa-bolt"></i> Buy Now
                        </button>
                        <button class="btn btn-outline btn-sm w-100" style="white-space: nowrap;" <?php echo $canBuy ? '' : 'disabled title="Not currently available from a reseller"'; ?>
                            onclick="event.stopPropagation(); <?php echo $canBuy ? 'openAddToCartModal(' . $atcPayload . ", 'cart')" : ''; ?>">
                            <i class="fas fa-cart-plus"></i> <?php echo $canBuy ? 'Add to Cart' : 'Unavailable'; ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="?page=<?php echo $currentPage - 1; ?><?php echo $searchQuery ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo $currentCategory ? '&category=' . $currentCategory : ''; ?>" class="page-btn">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i === $currentPage): ?>
                    <span class="page-btn active"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?><?php echo $searchQuery ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo $currentCategory ? '&category=' . $currentCategory : ''; ?>" class="page-btn">
                        <?php echo $i; ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?php echo $currentPage + 1; ?><?php echo $searchQuery ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo $currentCategory ? '&category=' . $currentCategory : ''; ?>" class="page-btn">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Add to Cart / Buy Now modal (shared .modal component) -->
<div class="modal" id="addToCartModal">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h3 class="modal-title">Choose Options</h3>
            <button type="button" class="modal-close" onclick="closeAddToCartModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="atc-modal-header">
                <img id="atcImage" src="" alt="">
                <div>
                    <div class="atc-modal-name" id="atcName"></div>
                    <div class="atc-modal-price" id="atcPrice"></div>
                </div>
            </div>

            <div id="atcVariationSection" style="display:none;margin-top:16px;">
                <div id="atcVariationGroups"></div>
                <div class="atc-variation-error" id="atcVariationError">
                    <i class="fas fa-circle-exclamation"></i> Please select an option.
                </div>
            </div>

            <div class="atc-qty-row">
                <span style="font-size:13px;font-weight:600;color:#666;">Quantity:</span>
                <div class="atc-qty-controls">
                    <button type="button" class="atc-qty-btn" onclick="atcChangeQty(-1)">-</button>
                    <span class="atc-qty-value" id="atcQtyValue">1</span>
                    <button type="button" class="atc-qty-btn" onclick="atcChangeQty(1)">+</button>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline btn-sm" onclick="closeAddToCartModal()">Cancel</button>
            <button type="button" class="btn btn-sm" style="background:var(--gradient-hover, var(--primary-pink));color:#fff;" id="atcConfirmBtn" onclick="confirmAddToCart()">Add to Cart</button>
        </div>
    </div>
</div>

<script>
let atcState = { id: null, mode: 'cart', hasVariations: false, basePrice: 0, variations: [], selectedVariationId: null, qty: 1, stock: 0 };

function openAddToCartModal(product, mode) {
    <?php if (($_SESSION['user_type'] ?? '') !== 'customer'): ?>
        showGuestChoice();
        return;
    <?php endif; ?>

    atcState = {
        id: product.id,
        mode: mode,
        hasVariations: !!product.hasVariations,
        basePrice: product.price,
        variations: [],
        selectedVariationId: null,
        qty: 1,
        stock: product.stock
    };

    document.getElementById('atcImage').src = product.image;
    document.getElementById('atcName').textContent = product.name;
    document.getElementById('atcPrice').textContent = '₱' + product.price.toFixed(2);
    document.getElementById('atcQtyValue').textContent = '1';
    document.getElementById('atcConfirmBtn').textContent = mode === 'buy' ? 'Buy Now' : 'Add to Cart';
    document.getElementById('atcVariationError').style.display = 'none';

    const variationSection = document.getElementById('atcVariationSection');
    if (atcState.hasVariations) {
        variationSection.style.display = 'block';
        document.getElementById('atcVariationGroups').innerHTML = '<div style="font-size:13px;color:#888;">Loading options…</div>';
        fetch('<?php echo BASE_URL; ?>shop/variations/' + product.id)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    atcState.variations = data.variations;
                    renderAtcVariationGroups();
                }
            });
    } else {
        variationSection.style.display = 'none';
    }

    openModal('addToCartModal');
}

function closeAddToCartModal() {
    closeModal(document.getElementById('addToCartModal'));
}

function renderAtcVariationGroups() {
    const groups = {};
    atcState.variations.forEach(v => {
        if (!groups[v.variation_type]) groups[v.variation_type] = [];
        groups[v.variation_type].push(v);
    });

    let html = '';
    Object.keys(groups).forEach(type => {
        html += '<div class="atc-variation-group"><div class="atc-variation-label">' + escapeHtml(type) + '</div>';
        groups[type].forEach(v => {
            const outOfStock = v.stock <= 0;
            html += '<span class="atc-variation-opt' + (outOfStock ? ' disabled' : '') + '" data-variation-id="' + v.variation_id + '"' +
                (outOfStock ? '' : ' onclick="atcSelectVariation(' + v.variation_id + ', ' + v.stock + ', ' + v.price_adjustment + ')"') + '">' +
                escapeHtml(v.variation_value) + (outOfStock ? ' (out of stock)' : '') + '</span>';
        });
        html += '</div>';
    });
    document.getElementById('atcVariationGroups').innerHTML = html;
}

function atcSelectVariation(variationId, stock, priceAdjustment) {
    atcState.selectedVariationId = variationId;
    atcState.stock = stock;
    document.getElementById('atcVariationError').style.display = 'none';

    document.querySelectorAll('.atc-variation-opt').forEach(el => {
        el.classList.toggle('selected', el.dataset.variationId == variationId);
    });

    document.getElementById('atcPrice').textContent = '₱' + (atcState.basePrice + priceAdjustment).toFixed(2);

    if (atcState.qty > stock) {
        atcState.qty = Math.max(1, stock);
        document.getElementById('atcQtyValue').textContent = atcState.qty;
    }
}

function atcChangeQty(delta) {
    const max = atcState.stock > 0 ? atcState.stock : 1;
    let next = atcState.qty + delta;
    if (next < 1) next = 1;
    if (next > max) next = max;
    atcState.qty = next;
    document.getElementById('atcQtyValue').textContent = next;
}

function confirmAddToCart() {
    if (atcState.hasVariations && !atcState.selectedVariationId) {
        document.getElementById('atcVariationError').style.display = 'block';
        return;
    }

    const resellerId = <?php echo (int) ($shopResellerId ?? 0); ?>;
    const btn = document.getElementById('atcConfirmBtn');
    const originalLabel = btn.textContent;
    btn.disabled = true;

    fetch('<?php echo BASE_URL; ?>cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'product_id=' + atcState.id + '&quantity=' + atcState.qty +
            (atcState.selectedVariationId ? '&variation_id=' + atcState.selectedVariationId : '') +
            (resellerId ? '&reseller_id=' + resellerId : '')
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeAddToCartModal();
            if (atcState.mode === 'buy') {
                window.location.href = '<?php echo BASE_URL; ?>checkout';
            } else {
                showToast('Product added to cart!', 'success');
                updateCartCount();
            }
        } else {
            showToast(data.message || 'Failed to add product', 'error');
        }
    })
    .catch(() => showToast('Something went wrong', 'error'))
    .finally(() => {
        btn.disabled = false;
        btn.textContent = originalLabel;
    });
}

function escapeHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function showToast(message, type) {
    const existing = document.querySelector('.cart-toast');
    if (existing) existing.remove();
    
    const toast = document.createElement('div');
    toast.className = 'cart-toast cart-toast-' + type;
    toast.innerHTML = '<i class="fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + '"></i> ' + message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>

<style>
.cart-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 25px;
    border-radius: 10px;
    color: white;
    font-weight: 500;
    font-size: 14px;
    z-index: 9999;
    transform: translateX(120%);
    transition: transform 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    gap: 10px;
}
.cart-toast.show { transform: translateX(0); }
.cart-toast-success { background: #28a745; }
.cart-toast-error { background: #dc3545; }
</style>
