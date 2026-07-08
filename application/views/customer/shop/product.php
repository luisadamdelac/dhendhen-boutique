<style>
    .product-detail-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin: 30px 0;
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .product-image-main {
        width: 100%;
        border-radius: 12px;
        object-fit: cover;
    }
    
    .product-detail-info h1 {
        font-size: 32px;
        margin-bottom: 15px;
        color: var(--dark);
    }
    
    .product-category {
        display: inline-block;
        padding: 5px 15px;
        background: var(--light-gray);
        border-radius: 20px;
        font-size: 14px;
        color: var(--gray);
        margin-bottom: 15px;
    }
    
    .product-price-large {
        font-size: 36px;
        font-weight: bold;
        color: var(--primary);
        margin: 20px 0;
    }
    
    .product-description {
        line-height: 1.8;
        color: var(--gray);
        margin: 20px 0;
        padding: 20px;
        background: var(--light-gray);
        border-radius: 8px;
    }
    
    .stock-info {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 20px 0;
        font-size: 16px;
    }
    
    .stock-badge {
        padding: 5px 15px;
        border-radius: 20px;
        font-weight: 600;
    }
    
    .stock-available {
        background: #d4edda;
        color: #155724;
    }
    
    .stock-low {
        background: #fff3cd;
        color: #856404;
    }
    
    .quantity-selector {
        display: flex;
        align-items: center;
        gap: 15px;
        margin: 25px 0;
    }
    
    .qty-btn {
        width: 40px;
        height: 40px;
        border: 2px solid var(--primary);
        background: white;
        color: var(--primary);
        border-radius: 8px;
        font-size: 20px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .qty-btn:hover {
        background: var(--primary);
        color: white;
    }
    
    .qty-input {
        width: 80px;
        text-align: center;
        font-size: 18px;
        padding: 10px;
        border: 2px solid #ddd;
        border-radius: 8px;
    }
    
    .action-buttons {
        display: flex;
        gap: 15px;
        margin: 30px 0;
    }
    
    .related-products {
        margin-top: 60px;
    }
    
    .related-products h2 {
        margin-bottom: 30px;
        font-size: 28px;
    }
    
    @media (max-width: 768px) {
        .product-detail-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="product-detail-container">
    <div>
        <?php $productImage = $product['product_image'] ?? $product['image'] ?? ''; ?>
        <?php if (!empty($productImage)): ?>
            <img src="<?php echo BASE_URL . $productImage; ?>"
                 alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                 class="product-image-main">
        <?php else: ?>
            <div class="product-image-main" style="height: 500px; display: flex; align-items: center; justify-content: center; background: #f0f0f0;">
                <i class="fas fa-image" style="font-size: 100px; color: #ccc;"></i>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="product-detail-info">
        <?php if ($product['category_name']): ?>
            <span class="product-category">
                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['category_name']); ?>
            </span>
        <?php endif; ?>
        
        <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
        
        <?php if (!empty($product['has_discount']) && !empty($product['discounted_price'])): ?>
            <div class="product-price-large">
                <span style="text-decoration: line-through; color: #999; font-size: 22px;">₱<?php echo number_format($product['price'], 2); ?></span>
                <span id="current-price-display" data-base-price="<?php echo (float) $product['discounted_price']; ?>" style="margin-left: 8px;">₱<?php echo number_format($product['discounted_price'], 2); ?></span>
                <span style="background: #e74c3c; color: white; font-size: 14px; padding: 3px 10px; border-radius: 20px; margin-left: 10px;">
                    -<?php echo round((1 - $product['discounted_price'] / $product['price']) * 100); ?>% OFF
                </span>
            </div>
        <?php else: ?>
            <div class="product-price-large" id="current-price-display" data-base-price="<?php echo (float) $product['price']; ?>">₱<?php echo number_format($product['price'], 2); ?></div>
        <?php endif; ?>

        <?php if (!empty($product['avg_rating'])): ?>
        <div style="display: flex; align-items: center; gap: 10px; margin: 10px 0;">
            <div style="color: #f5a623;">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star<?php echo $i <= round($product['avg_rating']) ? '' : ($i - 0.5 <= $product['avg_rating'] ? '-half-alt' : ''); ?>" style="<?php echo $i > round($product['avg_rating']) ? 'color: #ddd;' : ''; ?>"></i>
                <?php endfor; ?>
            </div>
            <span style="font-weight: 600;"><?php echo number_format($product['avg_rating'], 1); ?></span>
            <span style="color: var(--gray);">(<?php echo $product['review_count']; ?> review<?php echo $product['review_count'] != 1 ? 's' : ''; ?>)</span>
        </div>
        <?php endif; ?>

        <?php if (!empty($product['total_sold'])): ?>
        <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: #fff3e0; color: #e65100; border-radius: 20px; font-size: 13px; font-weight: 600; margin-bottom: 10px;">
            <i class="fas fa-fire"></i> <?php echo $product['total_sold']; ?> sold
        </div>
        <?php endif; ?>

        <div class="stock-info">
            <i class="fas fa-box"></i>
            <span class="stock-badge <?php echo $product['stock'] > 10 ? 'stock-available' : 'stock-low'; ?>">
                <?php echo $product['stock']; ?> Available
            </span>
        </div>
        
        <?php if ($product['description']): ?>
            <div class="product-description">
                <h3 style="margin-bottom: 10px;">Description</h3>
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($sellers) && count($sellers) > 1): ?>
        <div style="margin: 20px 0; padding: 15px; background: var(--light-gray); border-radius: 8px;">
            <h4 style="margin-bottom: 10px; font-size: 15px;">Choose a Seller</h4>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <?php foreach ($sellers as $seller): ?>
                    <?php
                        $sellerName = !empty($seller['business_name']) ? $seller['business_name'] : trim($seller['first_name'] . ' ' . $seller['last_name']);
                        $isSelected = (int) $seller['reseller_id'] === (int) $selected_reseller_id;
                    ?>
                    <a href="?reseller=<?php echo $seller['reseller_id']; ?>" style="display:flex; justify-content:space-between; padding:10px 14px; border-radius:8px; text-decoration:none; color:inherit; border:2px solid <?php echo $isSelected ? 'var(--primary)' : '#ddd'; ?>; background:white;">
                        <span><?php echo $isSelected ? '<i class="fas fa-check-circle" style="color:var(--primary);"></i> ' : ''; ?><?php echo htmlspecialchars($sellerName); ?></span>
                        <strong>₱<?php echo number_format($seller['commission_price'], 2); ?></strong>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!($purchasable ?? false)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                This product isn't currently listed by any reseller.
            </div>
        <?php elseif ($product['stock'] > 0): ?>
            <?php if ((($_SESSION['user_type'] ?? '') === 'customer')): ?>
            <form method="POST" action="<?php echo BASE_URL; ?>cart/add" id="buy-form">
                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                <input type="hidden" name="reseller_id" value="<?php echo (int) $selected_reseller_id; ?>">
                <input type="hidden" name="variation_id" id="selected_variation_id" value="">

                <?php if (!empty($variations)): ?>
                    <?php $variationsByType = []; foreach ($variations as $v) { $variationsByType[$v['variation_type']][] = $v; } ?>
                    <div class="variation-selector" style="margin-bottom: 20px;">
                        <?php foreach ($variationsByType as $type => $values): ?>
                            <div style="margin-bottom: 14px;">
                                <label style="font-weight: 600; display: block; margin-bottom: 8px;"><?php echo htmlspecialchars($type); ?></label>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    <?php foreach ($values as $v): ?>
                                        <?php $outOfStock = (int) $v['stock'] <= 0; ?>
                                        <button type="button"
                                                class="variation-opt-btn<?php echo $outOfStock ? ' disabled' : ''; ?>"
                                                data-variation-id="<?php echo $v['variation_id']; ?>"
                                                data-price-adjustment="<?php echo (float) $v['price_adjustment']; ?>"
                                                data-stock="<?php echo (int) $v['stock']; ?>"
                                                onclick="<?php echo $outOfStock ? '' : 'selectVariation(this)'; ?>"
                                                <?php echo $outOfStock ? 'disabled' : ''; ?>
                                                style="padding: 8px 16px; border-radius: 20px; border: 2px solid <?php echo $outOfStock ? '#eee' : '#ddd'; ?>; background: #fff; cursor: <?php echo $outOfStock ? 'not-allowed' : 'pointer'; ?>; font-size: 14px; color: <?php echo $outOfStock ? '#bbb' : 'inherit'; ?>;">
                                            <?php echo htmlspecialchars($v['variation_value']); ?>
                                            <?php if ($v['price_adjustment'] > 0): ?>
                                                (+₱<?php echo number_format($v['price_adjustment'], 2); ?>)
                                            <?php endif; ?>
                                            <?php if ($outOfStock): ?> — Out of stock<?php endif; ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div id="variation-required-msg" style="display:none; color:#c62828; font-size:13px; margin-top:-6px;">Please select an option before continuing.</div>
                    </div>
                <?php endif; ?>

                <div class="quantity-selector">
                    <label style="font-weight: 600;">Quantity:</label>
                    <button type="button" class="qty-btn" onclick="decreaseQty()">
                        <i class="fas fa-minus"></i>
                    </button>
                    <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="qty-input">
                    <button type="button" class="qty-btn" onclick="increaseQty()">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>

                <div id="buy-now-alert" style="display:none; padding: 12px 14px; border-radius: 8px; margin-bottom: 15px;"></div>

                <div class="action-buttons">
                    <button type="button" class="btn" style="flex: 1; padding: 15px; font-size: 16px; background: var(--gradient-hover);" onclick="buyNow()">
                        <i class="fas fa-bolt"></i> Buy Now
                    </button>
                    <button type="submit" class="btn btn-outline" style="flex: 1; padding: 15px; font-size: 16px;">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                    <a href="<?php echo BASE_URL; ?>shop" class="btn btn-outline" style="padding: 15px 30px;">
                        <i class="fas fa-arrow-left"></i> Back to Shop
                    </a>
                </div>
            </form>
            <?php else: ?>
            <div style="margin: 25px 0; padding: 20px; background: var(--light-gray); border-radius: 12px;">
                <h4 style="margin-bottom: 4px;">Ready to buy?</h4>
                <p style="color: var(--gray); font-size: 14px; margin-bottom: 15px;">Create an account or log in to add this to your cart.</p>
                <div class="action-buttons" style="margin: 0; flex-wrap: wrap;">
                    <a href="<?php echo BASE_URL; ?>auth/login" class="btn" style="flex: 1; min-width: 140px; padding: 14px; text-align: center;">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="<?php echo BASE_URL; ?>auth/register" class="btn btn-outline" style="flex: 1; min-width: 140px; padding: 14px; text-align: center;">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                    <a href="<?php echo BASE_URL; ?>auth/register_reseller" class="btn btn-outline" style="flex: 1; min-width: 140px; padding: 14px; text-align: center;">
                        <i class="fas fa-store-alt"></i> Become a Reseller
                    </a>
                    <a href="<?php echo BASE_URL; ?>shop" class="btn btn-outline" style="flex: 1; min-width: 140px; padding: 14px; text-align: center;">
                        <i class="fas fa-shopping-bag"></i> Continue Shopping
                    </a>
                </div>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                This product is currently out of stock
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #f0f0f0;">
            <h4 style="margin-bottom: 15px;">Product Details</h4>
            <ul style="line-height: 2;">
                <?php
                    $sku = null;
                    foreach (['sku','SKU','product_sku','productSku','product_code','code'] as $k) {
                        if (array_key_exists($k, $product) && $product[$k] !== null && $product[$k] !== '') { $sku = $product[$k]; break; }
                    }
                    // Debug fallback: if database column name differs, it will still show real value if present.
                ?>
                <li><strong>SKU:</strong> <?php echo !empty($sku) ? htmlspecialchars((string)$sku) : '—'; ?></li>
                <li><strong>Category:</strong> <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></li>
                <li><strong>Stock:</strong> <?php echo $product['stock']; ?> units</li>
            </ul>
        </div>
    </div>
</div>

<!-- Customer Reviews Section -->
<?php if ((($_SESSION['user_type'] ?? '') === 'customer')): ?>
    <div style="margin-top: 50px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 20px;"><i class="fas fa-pen"></i> Write a Review</h2>
        <div id="review-submit-alert" style="display:none; padding: 12px 14px; border-radius: 8px; margin-bottom: 15px;"></div>
        <form id="review-form">
            <input type="hidden" name="product_id" value="<?php echo (int)$product['product_id']; ?>">

            <div style="margin-bottom: 15px;">
                <label style="font-weight: 600;">Rating</label>
                <div style="display:flex; gap:6px; margin-top: 8px;" id="rating-stars">
                    <?php for ($i=1; $i<=5; $i++): ?>
                        <button type="button" class="btn" style="padding: 6px 10px;" data-rating="<?php echo $i; ?>" onclick="setRating(<?php echo $i; ?>)">★</button>
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="rating" id="rating" value="">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="font-weight: 600;">Review</label>
                <textarea name="review" required maxlength="1000" rows="5" style="width:100%; padding: 12px; border-radius: 10px; border: 1px solid #ddd;" placeholder="Share your experience..."></textarea>
            </div>

            <button type="submit" class="btn" style="padding: 12px 18px;">
                <i class="fas fa-paper-plane"></i> Submit Review
            </button>
        </form>
        <div style="margin-top: 10px; color: var(--gray); font-size: 13px;">
            Note: After submitting, refresh this page to see your review when it becomes visible.
        </div>

    </div>
<?php endif; ?>

<?php if (!empty($reviews)): ?>
<div style="margin-top: 20px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <h2 style="margin-bottom: 25px;"><i class="fas fa-star"></i> Customer Reviews (<?php echo count($reviews); ?>)</h2>
    <?php foreach ($reviews as $review): ?>
    <div style="padding: 20px 0; border-bottom: 1px solid #f0f0f0;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
            <?php if (!empty($review['profile_image'])): ?>
                <img src="<?php echo BASE_URL . $review['profile_image']; ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
            <?php else: ?>
                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                    <?php echo strtoupper(substr($review['customer_name'] ?? 'U', 0, 1)); ?>
                </div>
            <?php endif; ?>
            <div>
                <strong><?php echo htmlspecialchars($review['customer_name'] ?? 'Customer'); ?></strong>
                <div style="color: #f5a623; font-size: 13px;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star" style="<?php echo $i > $review['rating'] ? 'color: #ddd;' : ''; ?>"></i>
                    <?php endfor; ?>
                </div>
            </div>
            <span style="margin-left: auto; font-size: 13px; color: var(--gray);"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
        </div>
        <p style="color: var(--gray); line-height: 1.6; margin: 0;"> <?php echo nl2br(htmlspecialchars($review['review'])); ?></p>
    </div>
    <?php endforeach; ?>
</div>
<?php elseif (empty($reviews)): ?>
<div style="margin-top: 20px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; color: var(--gray);">
    <i class="fas fa-comment-slash" style="font-size: 40px; margin-bottom: 10px; opacity: 0.3;"></i>
    <p>No reviews yet for this product.</p>
</div>
<?php endif; ?>

<?php if (!empty($relatedProducts)): ?>
    <div class="related-products">
        <h2><i class="fas fa-heart"></i> You Might Also Like</h2>
        <div class="products-grid">
            <?php foreach ($relatedProducts as $related): ?>
                <div class="product-card" onclick="location.href='<?php echo BASE_URL; ?>shop/product/<?php echo $related['product_id']; ?>'">
                    <?php $relatedImage = $related['product_image'] ?? $related['image'] ?? ''; ?>
                    <?php if (!empty($relatedImage)): ?>
                        <img src="<?php echo BASE_URL . $relatedImage; ?>"
                             alt="<?php echo htmlspecialchars($related['product_name']); ?>" 
                             class="product-image">
                    <?php else: ?>
                        <div class="product-image" style="display: flex; align-items: center; justify-content: center; background: #f0f0f0;">
                            <i class="fas fa-image" style="font-size: 60px; color: #ccc;"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="product-info">
                        <div class="product-name"><?php echo htmlspecialchars($related['product_name']); ?></div>
                        <div class="product-price">₱<?php echo number_format($related['price'], 2); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<script>
function setRating(val) {
    const ratingInput = document.getElementById('rating');
    if (!ratingInput) return;
    ratingInput.value = val;

    document.querySelectorAll('#rating-stars button').forEach((btn) => {
        const btnRating = parseInt(btn.getAttribute('data-rating'), 10);
        btn.style.opacity = btnRating <= val ? '1' : '0.4';
    });
}

<?php if ((($_SESSION['user_type'] ?? '') === 'customer')): ?>
document.getElementById('review-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const alertBox = document.getElementById('review-submit-alert');
    const rating = document.getElementById('rating')?.value;
    if (!rating) {
        if (alertBox) {
            alertBox.style.display = 'block';
            alertBox.style.background = '#fff3e0';
            alertBox.style.color = '#e65100';
            alertBox.innerHTML = 'Please select a rating.';
        }
        return;
    }

    const formData = new FormData(this);
    const payload = new URLSearchParams(formData).toString();

    try {
        if (alertBox) {
            alertBox.style.display = 'block';
            alertBox.style.background = '#e8f5e9';
            alertBox.style.color = '#2e7d32';
            alertBox.innerHTML = 'Submitting review...';
        }

        const res = await fetch('<?php echo BASE_URL; ?>review/submit', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: payload
        });

        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.success) {
            console.error('Review submit failed:', {status: res.status, data});
            throw new Error(data.message || 'Failed to submit review');
        }

        if (alertBox) {
            alertBox.style.background = '#e8f5e9';
            alertBox.style.color = '#2e7d32';
            alertBox.innerHTML = data.message || 'Review submitted successfully.';
        }

        this.reset();
        const ratingInput = document.getElementById('rating');
        if (ratingInput) ratingInput.value = '';
        document.querySelectorAll('#rating-stars button').forEach((btn) => (btn.style.opacity = '0.4'));

        setTimeout(() => window.location.reload(), 800);
    } catch (err) {
        if (alertBox) {
            alertBox.style.display = 'block';
            alertBox.style.background = '#ffebee';
            alertBox.style.color = '#c62828';
            alertBox.innerHTML = err.message || 'Failed to submit review.';
        }
    }
});
<?php endif; ?>

const hasVariations = document.querySelector('.variation-selector') !== null;

function updatePriceDisplay(adjustment) {
    const el = document.getElementById('current-price-display');
    if (!el) return;
    const base = parseFloat(el.dataset.basePrice || '0');
    el.textContent = '₱' + (base + (adjustment || 0)).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function clearVariationSelection() {
    document.querySelectorAll('.variation-opt-btn').forEach(b => {
        b.style.borderColor = '#ddd';
        b.style.background = '#fff';
        b.style.fontWeight = 'normal';
    });
    document.getElementById('selected_variation_id').value = '';
    updatePriceDisplay(0);

    const qtyInput = document.getElementById('quantity');
    if (qtyInput) qtyInput.max = <?php echo (int) $product['stock']; ?>;
}

function selectVariation(btn) {
    // Clicking the already-selected option toggles it off.
    if (document.getElementById('selected_variation_id').value === btn.dataset.variationId) {
        clearVariationSelection();
        return;
    }

    // Only one variation can be active at a time, across every type group.
    clearVariationSelection();

    btn.style.borderColor = 'var(--primary)';
    btn.style.background = 'var(--light-gray)';
    btn.style.fontWeight = '600';

    document.getElementById('selected_variation_id').value = btn.dataset.variationId;
    document.getElementById('variation-required-msg').style.display = 'none';
    updatePriceDisplay(parseFloat(btn.dataset.priceAdjustment || '0'));

    const stock = parseInt(btn.dataset.stock, 10);
    const qtyInput = document.getElementById('quantity');
    if (qtyInput) {
        qtyInput.max = stock;
        if (parseInt(qtyInput.value, 10) > stock) qtyInput.value = stock;
    }
}

function validateVariationSelected() {
    if (!hasVariations) return true;
    if (!document.getElementById('selected_variation_id').value) {
        document.getElementById('variation-required-msg').style.display = 'block';
        document.querySelector('.variation-selector').scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }
    return true;
}

document.getElementById('buy-form')?.addEventListener('submit', function (e) {
    if (!validateVariationSelected()) e.preventDefault();
});

function buyNow() {
    if (!validateVariationSelected()) return;

    const form = document.getElementById('buy-form');
    const alertBox = document.getElementById('buy-now-alert');
    const formData = new FormData(form);

    fetch('<?php echo BASE_URL; ?>cart/add', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData,
    })
        .then((r) => r.json())
        .then((data) => {
            if (data.success) {
                window.location.href = '<?php echo BASE_URL; ?>checkout';
                return;
            }
            if (alertBox) {
                alertBox.style.display = 'block';
                alertBox.style.background = '#ffebee';
                alertBox.style.color = '#c62828';
                alertBox.innerHTML = data.message || 'Unable to buy this item right now.';
            }
        })
        .catch(() => {
            if (alertBox) {
                alertBox.style.display = 'block';
                alertBox.style.background = '#ffebee';
                alertBox.style.color = '#c62828';
                alertBox.innerHTML = 'Request failed. Please try again.';
            }
        });
}

function increaseQty() {
    const input = document.getElementById('quantity');
    if (!input) return;
    const max = parseInt(input.max, 10);
    const current = parseInt(input.value, 10);
    if (!isNaN(max) && !isNaN(current) && current < max) {
        input.value = current + 1;
    }
}

function decreaseQty() {
    const input = document.getElementById('quantity');
    if (!input) return;
    const current = parseInt(input.value, 10);
    if (!isNaN(current) && current > 1) {
        input.value = current - 1;
    }
}

// Ensure the quantity never exceeds stock.
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('quantity');
    if (!input) return;
    input.addEventListener('change', function () {
        const max = parseInt(input.max, 10);
        let value = parseInt(input.value, 10);
        if (isNaN(value) || value < 1) value = 1;
        if (!isNaN(max) && value > max) value = max;
        input.value = value;
    });
});
</script>
