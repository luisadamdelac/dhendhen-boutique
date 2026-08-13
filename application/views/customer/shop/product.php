<style>
    /* ============================================
       PRODUCT DETAILS PAGE (Shopee-inspired)
       Scoped to this page only — prefixed pdp- so
       nothing here touches shared site styles.
       ============================================ */
    .pdp-top {
        display: grid;
        grid-template-columns: 440px 1fr;
        gap: 32px;
        background: var(--white);
        padding: 28px;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        margin-bottom: 24px;
    }

    /* --- Gallery --- */
    .pdp-gallery-main {
        width: 100%;
        aspect-ratio: 1 / 1;
        border-radius: var(--radius-md);
        overflow: hidden;
        background: var(--light-gray);
        border: 1px solid var(--gray-100);
    }

    .pdp-gallery-main img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .pdp-gallery-main .pdp-no-image {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pdp-gallery-main .pdp-no-image i {
        font-size: 90px;
        color: var(--gray-300);
    }

    .pdp-thumbs {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
    }

    .pdp-thumb {
        width: 72px;
        height: 72px;
        border-radius: var(--radius-sm);
        object-fit: cover;
        cursor: pointer;
        border: 2px solid var(--gray-200);
        transition: var(--transition-base);
    }

    .pdp-thumb:hover {
        border-color: var(--primary-pink-light);
    }

    .pdp-thumb.active {
        border-color: var(--primary-pink);
        box-shadow: 0 0 0 2px var(--primary-pink-light);
    }

    /* --- Info column --- */
    .pdp-info h1 {
        font-size: 26px;
        font-weight: 700;
        color: var(--dark-gray);
        margin: 0 0 12px;
        line-height: 1.3;
    }

    .pdp-meta-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 14px;
        padding-bottom: 16px;
        margin-bottom: 16px;
        border-bottom: 1px solid var(--gray-100);
        font-size: 13.5px;
        color: var(--gray);
    }

    .pdp-meta-row .pdp-stars {
        color: #FFB800;
    }

    .pdp-meta-divider {
        width: 1px;
        height: 16px;
        background: var(--gray-200);
    }

    .pdp-sold-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        background: #FFF3E0;
        color: #E65100;
        border-radius: var(--radius-full);
        font-size: 12.5px;
        font-weight: 600;
    }

    .pdp-price-box {
        background: var(--primary-pink-soft);
        border-radius: var(--radius-md);
        padding: 20px 22px;
        margin-bottom: 20px;
    }

    .pdp-price-current {
        font-size: 32px;
        font-weight: 700;
        color: var(--primary-pink-dark);
    }

    .pdp-price-original {
        font-size: 17px;
        color: var(--gray-400);
        text-decoration: line-through;
        margin-right: 10px;
    }

    .pdp-discount-badge {
        display: inline-block;
        background: var(--danger);
        color: var(--white);
        font-size: 12.5px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: var(--radius-full);
        margin-left: 10px;
        vertical-align: middle;
    }

    .pdp-stock-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
        font-size: 14px;
    }

    .pdp-variation-block {
        margin-bottom: 18px;
    }

    .pdp-variation-label {
        font-weight: 600;
        display: block;
        margin-bottom: 8px;
        font-size: 13.5px;
        color: var(--dark-gray);
    }

    .pdp-variation-options {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .pdp-variation-btn {
        padding: 8px 16px;
        border-radius: var(--radius-full);
        border: 1.5px solid var(--gray-200);
        background: var(--white);
        cursor: pointer;
        font-size: 13.5px;
        transition: var(--transition-base);
        font-family: var(--font-primary);
    }

    .pdp-variation-btn:hover:not(:disabled) {
        border-color: var(--primary-pink);
    }

    .pdp-variation-btn:disabled,
    .pdp-variation-btn.disabled {
        color: var(--gray-300);
        border-color: var(--gray-100);
        cursor: not-allowed;
    }

    .pdp-inline-error {
        display: none;
        color: var(--danger);
        font-size: 12.5px;
        margin-top: -6px;
        margin-bottom: 12px;
    }

    .pdp-qty-row {
        display: flex;
        align-items: center;
        gap: 16px;
        margin: 22px 0;
    }

    .pdp-qty-label {
        font-weight: 600;
        font-size: 13.5px;
        color: var(--dark-gray);
    }

    .pdp-qty-control {
        display: flex;
        align-items: center;
        border: 1.5px solid var(--gray-200);
        border-radius: var(--radius-md);
        overflow: hidden;
    }

    .pdp-qty-btn {
        width: 38px;
        height: 40px;
        border: none;
        background: var(--light-gray);
        color: var(--dark-gray);
        font-size: 15px;
        cursor: pointer;
        transition: var(--transition-base);
    }

    .pdp-qty-btn:hover {
        background: var(--primary-pink-light);
        color: var(--primary-pink-dark);
    }

    .pdp-qty-input {
        width: 60px;
        text-align: center;
        font-size: 15px;
        border: none;
        border-left: 1.5px solid var(--gray-200);
        border-right: 1.5px solid var(--gray-200);
        height: 40px;
    }

    .pdp-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        margin-top: 22px;
    }

    .pdp-actions .btn {
        flex: 1;
        min-width: 160px;
        padding: 14px 20px;
        font-size: 15px;
    }

    /* --- Generic card section used by shop info / specs / description --- */
    .pdp-section {
        background: var(--white);
        padding: 28px;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        margin-bottom: 24px;
    }

    .pdp-section h2 {
        font-size: 19px;
        font-weight: 700;
        color: var(--dark-gray);
        margin: 0 0 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .pdp-section h2 i {
        color: var(--primary-pink);
    }

    /* --- Shop info --- */
    .pdp-shop-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .pdp-shop-logo {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--gray-100);
        flex-shrink: 0;
    }

    .pdp-shop-details {
        flex: 1;
        min-width: 200px;
    }

    .pdp-shop-name {
        font-size: 17px;
        font-weight: 700;
        color: var(--dark-gray);
        margin-bottom: 6px;
    }

    .pdp-shop-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
        font-size: 13.5px;
        color: var(--gray);
    }

    .pdp-shop-stats span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .pdp-shop-stats i {
        color: var(--primary-pink);
    }

    /* --- Specifications --- */
    .pdp-specs-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0 24px;
    }

    .pdp-spec-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid var(--gray-100);
        font-size: 14px;
    }

    .pdp-spec-label {
        width: 40%;
        flex-shrink: 0;
        color: var(--gray);
        font-weight: 500;
    }

    .pdp-spec-value {
        color: var(--dark-gray);
        font-weight: 600;
    }

    .pdp-description-text {
        line-height: 1.8;
        color: var(--gray-700);
        font-size: 14.5px;
        white-space: pre-line;
    }

    .pdp-related h2 {
        font-size: 22px;
        margin-bottom: 24px;
    }

    .pdp-related h2 i {
        color: var(--danger);
    }

    @media (max-width: 900px) {
        .pdp-top {
            grid-template-columns: 1fr;
        }

        .pdp-specs-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 600px) {
        .pdp-top,
        .pdp-section {
            padding: 16px;
        }

        .pdp-info h1 {
            font-size: 20px;
        }

        .pdp-price-current {
            font-size: 24px;
        }

        .pdp-price-box {
            padding: 14px 16px;
        }

        .pdp-actions {
            flex-direction: column;
        }

        .pdp-actions .btn {
            min-width: 0;
            width: 100%;
        }

        .pdp-shop-row .btn {
            width: 100%;
        }
    }
</style>

<!-- 1 & 2: Product Gallery + Product Information -->
<div class="pdp-top">
    <div class="pdp-gallery">
        <?php
            $galleryList = !empty($galleryImages) ? $galleryImages : [];
            $primaryImage = $product['product_image'] ?? $product['image'] ?? '';
            if (empty($galleryList) && !empty($primaryImage)) {
                $galleryList = [['image_path' => $primaryImage, 'is_primary' => 1]];
            }
            $mainImage = !empty($galleryList) ? $galleryList[0]['image_path'] : '';
        ?>
        <div class="pdp-gallery-main">
            <?php if (!empty($mainImage)): ?>
                <img src="<?php echo BASE_URL . $mainImage; ?>"
                     alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                     id="productMainImage"
                     data-default-src="<?php echo BASE_URL . $mainImage; ?>">
            <?php else: ?>
                <div class="pdp-no-image">
                    <i class="fas fa-image"></i>
                </div>
            <?php endif; ?>
        </div>

        <?php if (count($galleryList) > 1): ?>
            <div class="pdp-thumbs">
                <?php foreach ($galleryList as $idx => $img): ?>
                    <img src="<?php echo BASE_URL . $img['image_path']; ?>"
                         class="pdp-thumb<?php echo $idx === 0 ? ' active' : ''; ?>"
                         alt="<?php echo htmlspecialchars($product['product_name']); ?> thumbnail <?php echo $idx + 1; ?>"
                         onclick="pdpSelectThumb(this)">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="pdp-info">
        <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>

        <div class="pdp-meta-row">
            <?php if (!empty($product['avg_rating'])): ?>
                <span class="pdp-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star<?php echo $i <= round($product['avg_rating']) ? '' : ($i - 0.5 <= $product['avg_rating'] ? '-half-alt' : ''); ?>" style="<?php echo $i > round($product['avg_rating']) ? 'color: #ddd;' : ''; ?>"></i>
                    <?php endfor; ?>
                </span>
                <strong><?php echo number_format($product['avg_rating'], 1); ?></strong>
                <span>(<?php echo (int) $product['review_count']; ?> review<?php echo $product['review_count'] != 1 ? 's' : ''; ?>)</span>
                <span class="pdp-meta-divider"></span>
            <?php endif; ?>

            <span class="pdp-sold-badge"><i class="fas fa-fire"></i> <?php echo (int) ($product['total_sold'] ?? 0); ?> sold</span>
        </div>

        <div class="pdp-price-box">
            <span class="pdp-price-current" id="current-price-display" data-base-price="<?php echo (float) $product['price']; ?>">₱<?php echo number_format($product['price'], 2); ?></span>
        </div>

        <?php $isPreOrder = ($purchasable ?? false) && $product['stock'] <= 0; ?>
        <div class="pdp-stock-row">
            <span class="stock-indicator <?php echo $product['stock'] > 10 ? 'in-stock' : ($product['stock'] > 0 ? 'low-stock' : ($isPreOrder ? 'low-stock' : 'out-of-stock')); ?>">
                <?php echo $product['stock'] > 0 ? $product['stock'] . ' available' : ($isPreOrder ? 'Pre Order' : 'Out of stock'); ?>
            </span>
        </div>

        <?php if (!($purchasable ?? false)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                This product isn't currently listed by any reseller yet.
            </div>
            <button type="button" class="btn btn-outline" id="pdpNotifyBtn"
                data-product-id="<?php echo (int) $product['product_id']; ?>"
                <?php echo !empty($already_preordered) ? 'disabled' : ''; ?>
                onclick="submitPdpPreorder(this);">
                <i class="fas fa-<?php echo !empty($already_preordered) ? 'check' : 'bell'; ?>"></i>
                <?php echo !empty($already_preordered) ? "You'll Be Notified" : 'Notify Me'; ?>
            </button>
        <?php elseif ($product['stock'] > 0): ?>
            <?php if ((($_SESSION['user_type'] ?? '') === 'customer')): ?>
            <form method="POST" action="<?php echo BASE_URL; ?>cart/add" id="buy-form">
                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                <input type="hidden" name="reseller_id" value="<?php echo (int) $selected_reseller_id; ?>">
                <input type="hidden" name="variation_id" id="selected_variation_id" value="">

                <?php if (!empty($combinations)): ?>
                    <!-- Generated variant combinations exist for this product — cascading
                         per-axis selection (e.g. pick Shade, then only valid Finish options
                         show), resolving to one exact product_variants row. -->
                    <?php $variationsByType = []; foreach ($variations as $v) { $variationsByType[$v['variation_type']][] = $v; } ?>
                    <div class="variation-selector pdp-variation-block" data-mode="combinations">
                        <?php $axisIndex = 0; foreach ($variationsByType as $type => $values): $axisIndex++; ?>
                            <div style="margin-bottom: 14px;">
                                <label class="pdp-variation-label"><?php echo htmlspecialchars($type); ?></label>
                                <div class="variation-axis-group pdp-variation-options" data-axis="<?php echo $axisIndex; ?>" data-type="<?php echo htmlspecialchars($type); ?>">
                                    <?php foreach ($values as $v): ?>
                                        <button type="button"
                                                class="variation-axis-btn pdp-variation-btn"
                                                data-value="<?php echo htmlspecialchars($v['variation_value']); ?>"
                                                onclick="selectAxisValue(<?php echo $axisIndex; ?>, this)">
                                            <?php echo htmlspecialchars($v['variation_value']); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div id="variation-required-msg" class="pdp-inline-error">Please select an option for every variation before continuing.</div>
                        <div id="variation-oos-msg" class="pdp-inline-error">This combination is currently out of stock.</div>
                    </div>
                    <input type="hidden" name="variant_id" id="selected_variant_id" value="">
                <?php elseif (!empty($variations)): ?>
                    <?php $variationsByType = []; foreach ($variations as $v) { $variationsByType[$v['variation_type']][] = $v; } ?>
                    <div class="variation-selector pdp-variation-block">
                        <?php foreach ($variationsByType as $type => $values): ?>
                            <div style="margin-bottom: 14px;">
                                <label class="pdp-variation-label"><?php echo htmlspecialchars($type); ?></label>
                                <div class="pdp-variation-options">
                                    <?php foreach ($values as $v): ?>
                                        <?php $outOfStock = (int) $v['stock'] <= 0; ?>
                                        <button type="button"
                                                class="variation-opt-btn pdp-variation-btn<?php echo $outOfStock ? ' disabled' : ''; ?>"
                                                data-variation-id="<?php echo $v['variation_id']; ?>"
                                                data-price-adjustment="<?php echo (float) $v['price_adjustment']; ?>"
                                                data-stock="<?php echo (int) $v['stock']; ?>"
                                                data-image="<?php echo !empty($v['image_url']) ? htmlspecialchars($v['image_url']) : ''; ?>"
                                                onclick="<?php echo $outOfStock ? '' : 'selectVariation(this)'; ?>"
                                                <?php echo $outOfStock ? 'disabled' : ''; ?>>
                                            <?php echo htmlspecialchars($v['variation_value']); ?>
                                            <?php if ($v['price_adjustment'] > 0): ?>
                                                (+₱<?php echo number_format($v['price_adjustment'], 2); ?>)
                                            <?php endif; ?>
                                            <?php if ($outOfStock): ?> (Out of stock)<?php endif; ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div id="variation-required-msg" class="pdp-inline-error">Please select an option before continuing.</div>
                    </div>
                <?php endif; ?>

                <div class="pdp-qty-row">
                    <span class="pdp-qty-label">Quantity:</span>
                    <div class="pdp-qty-control">
                        <button type="button" class="pdp-qty-btn" onclick="decreaseQty()">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="pdp-qty-input">
                        <button type="button" class="pdp-qty-btn" onclick="increaseQty()">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div id="buy-now-alert" style="display:none; padding: 12px 14px; border-radius: 8px; margin-bottom: 15px;"></div>

                <div class="pdp-actions">
                    <button type="submit" class="btn btn-outline">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                    <button type="button" class="btn" onclick="buyNow()">
                        <i class="fas fa-bolt"></i> Buy Now
                    </button>
                </div>
            </form>
            <?php else: ?>
            <div style="margin: 25px 0; padding: 20px; background: var(--light-gray); border-radius: 12px;">
                <h4 style="margin-bottom: 4px;">Ready to buy?</h4>
                <p style="color: var(--gray); font-size: 14px; margin-bottom: 15px;">Create an account or log in to add this to your cart.</p>
                <div class="pdp-actions" style="margin: 0; flex-wrap: wrap;">
                    <a href="<?php echo BASE_URL; ?>auth/login" class="btn" style="text-align: center;">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="<?php echo BASE_URL; ?>auth/register" class="btn btn-outline" style="text-align: center;">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                    <a href="<?php echo BASE_URL; ?>auth/register_reseller" class="btn btn-outline" style="text-align: center;">
                        <i class="fas fa-store-alt"></i> Become a Reseller
                    </a>
                    <a href="<?php echo BASE_URL; ?>shop" class="btn btn-outline" style="text-align: center;">
                        <i class="fas fa-shopping-bag"></i> Continue Shopping
                    </a>
                </div>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-clock"></i>
                Pre Order: this product is temporarily out of stock. Check back soon for restock.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- 3: Shop Information -->
<?php if (!empty($shopInfo)): ?>
<div class="pdp-section">
    <h2><i class="fas fa-store"></i> Shop Information</h2>
    <div class="pdp-shop-row">
        <?php $shopLogo = !empty($shopInfo['logo']) ? BASE_URL . $shopInfo['logo'] : BASE_URL . default_avatar_url(); ?>
        <img src="<?php echo $shopLogo; ?>" alt="<?php echo htmlspecialchars($shopInfo['name']); ?>" class="pdp-shop-logo">

        <div class="pdp-shop-details">
            <div class="pdp-shop-name"><?php echo htmlspecialchars($shopInfo['name']); ?></div>
            <div class="pdp-shop-stats">
                <span>
                    <i class="fas fa-star"></i>
                    <?php echo $shopInfo['avg_rating'] !== NULL ? number_format($shopInfo['avg_rating'], 1) . ' (' . $shopInfo['review_count'] . ' review' . ($shopInfo['review_count'] != 1 ? 's' : '') . ')' : 'New seller'; ?>
                </span>
                <span><i class="fas fa-box-open"></i> <?php echo (int) $shopInfo['total_products']; ?> products</span>
                <span><i class="fas fa-calendar-alt"></i> Joined <?php echo !empty($shopInfo['joined_at']) ? date('M Y', strtotime($shopInfo['joined_at'])) : '-'; ?></span>
            </div>
        </div>

        <a href="<?php echo BASE_URL; ?>shop/reseller/<?php echo $shopInfo['reseller_id']; ?>" class="btn btn-outline">
            <i class="fas fa-store-alt"></i> Visit Shop
        </a>
    </div>
</div>
<?php endif; ?>

<!-- 4: Product Specifications -->
<div class="pdp-section">
    <h2><i class="fas fa-list-ul"></i> Product Specifications</h2>
    <div class="pdp-specs-grid">
        <?php
            $sku = null;
            foreach (['sku','SKU','product_sku','productSku','product_code','code'] as $k) {
                if (array_key_exists($k, $product) && $product[$k] !== null && $product[$k] !== '') { $sku = $product[$k]; break; }
            }

            $specs = [
                'SKU'      => !empty($sku) ? (string) $sku : null,
                'Brand'    => !empty($product['brand']) ? $product['brand'] : null,
                'Category' => $product['category_name'] ?? 'Uncategorized',
                'Tags'     => !empty($product['tags']) ? $product['tags'] : null,
                'Stock'    => $product['stock'] . ' units',
            ];
        ?>
        <?php foreach ($specs as $label => $value): ?>
            <?php if ($value !== null): ?>
                <div class="pdp-spec-row">
                    <span class="pdp-spec-label"><?php echo htmlspecialchars($label); ?></span>
                    <span class="pdp-spec-value"><?php echo htmlspecialchars((string) $value); ?></span>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<!-- 5: Product Description -->
<?php if (!empty($product['description'])): ?>
<div class="pdp-section">
    <h2><i class="fas fa-align-left"></i> Product Description</h2>
    <div class="pdp-description-text"><?php echo nl2br(htmlspecialchars($product['description'])); ?></div>
</div>
<?php endif; ?>

<!-- Customer Reviews -->
<?php if (!empty($reviews)): ?>
<div class="pdp-section">
    <h2><i class="fas fa-star"></i> Customer Reviews (<?php echo count($reviews); ?>)</h2>
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
                <div style="color: #ffc107; font-size: 13px;">
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
<?php else: ?>
<div class="pdp-section" style="text-align: center; color: var(--gray);">
    <i class="fas fa-comment-slash" style="font-size: 40px; margin-bottom: 10px; opacity: 0.3;"></i>
    <p>No reviews yet for this product.</p>
</div>
<?php endif; ?>

<!-- 6: You May Also Like -->
<?php if (!empty($otherShopListings) || !empty($relatedProducts)): ?>
    <div class="pdp-related">
        <h2><i class="fas fa-heart"></i> You May Also Like</h2>
        <div class="products-grid">
            <?php foreach ($otherShopListings as $listing): ?>
                <?php $shopName = !empty($listing['business_name']) ? $listing['business_name'] : trim($listing['first_name'] . ' ' . $listing['last_name']); ?>
                <div class="product-card" onclick="location.href='<?php echo BASE_URL; ?>shop/product/<?php echo $product['product_id']; ?>?reseller=<?php echo (int) $listing['reseller_id']; ?>'">
                    <?php $productImage = $product['product_image'] ?? $product['image'] ?? ''; ?>
                    <?php if (!empty($productImage)): ?>
                        <img src="<?php echo BASE_URL . $productImage; ?>"
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                             class="product-image">
                    <?php else: ?>
                        <div class="product-image" style="display: flex; align-items: center; justify-content: center; background: #f0f0f0;">
                            <i class="fas fa-image" style="font-size: 60px; color: #ccc;"></i>
                        </div>
                    <?php endif; ?>

                    <div class="product-info">
                        <div class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></div>
                        <div style="font-size: 13px; color: var(--gray); margin: 4px 0;">
                            <i class="fas fa-store"></i> <?php echo htmlspecialchars($shopName); ?>
                        </div>
                        <div class="product-price">₱<?php echo number_format((float) $listing['commission_price'], 2); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
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
function submitPdpPreorder(btn) {
    <?php if (($_SESSION['user_type'] ?? '') !== 'customer'): ?>
        showGuestChoice();
        return;
    <?php endif; ?>

    btn.disabled = true;
    const productId = btn.dataset.productId;
    fetch('<?php echo BASE_URL; ?>shop/preorder', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + encodeURIComponent(productId)
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                btn.innerHTML = '<i class="fas fa-check"></i> You\'ll Be Notified';
            } else {
                btn.disabled = false;
                if (data.needs_login) { showGuestChoice(); return; }
                if (typeof customAlert === 'function') customAlert(data.message || 'Something went wrong. Please try again.');
                else alert(data.message || 'Something went wrong. Please try again.');
            }
        })
        .catch(() => {
            btn.disabled = false;
            if (typeof customAlert === 'function') customAlert('Something went wrong. Please try again.');
            else alert('Something went wrong. Please try again.');
        });
}

const hasVariations = document.querySelector('.variation-selector') !== null;
const combinationsMode = document.querySelector('.variation-selector[data-mode="combinations"]') !== null;
const COMBINATIONS = <?php echo !empty($combinations) ? json_encode($combinations, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) : '[]'; ?>;
let selectedAxisValues = {}; // axisIndex -> { type, value }

function pdpSelectThumb(el) {
    document.querySelectorAll('.pdp-thumb').forEach(function (t) { t.classList.remove('active'); });
    el.classList.add('active');
    const main = document.getElementById('productMainImage');
    if (main) main.src = el.src;
}

function updatePriceDisplay(adjustment) {
    const el = document.getElementById('current-price-display');
    if (!el) return;
    const base = parseFloat(el.dataset.basePrice || '0');
    el.textContent = '₱' + (base + (adjustment || 0)).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function clearVariationSelection() {
    document.querySelectorAll('.variation-opt-btn').forEach(b => {
        b.style.borderColor = '';
        b.style.background = '';
        b.style.fontWeight = '';
    });
    document.getElementById('selected_variation_id').value = '';
    updatePriceDisplay(0);

    const img = document.getElementById('productMainImage');
    if (img && img.dataset.defaultSrc) img.src = img.dataset.defaultSrc;

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

    btn.style.borderColor = 'var(--primary-pink)';
    btn.style.background = 'var(--primary-pink-soft)';
    btn.style.fontWeight = '600';

    document.getElementById('selected_variation_id').value = btn.dataset.variationId;
    document.getElementById('variation-required-msg').style.display = 'none';
    updatePriceDisplay(parseFloat(btn.dataset.priceAdjustment || '0'));

    if (btn.dataset.image) {
        const img = document.getElementById('productMainImage');
        if (img) img.src = btn.dataset.image;
    }

    const stock = parseInt(btn.dataset.stock, 10);
    const qtyInput = document.getElementById('quantity');
    if (qtyInput) {
        qtyInput.max = stock;
        if (parseInt(qtyInput.value, 10) > stock) qtyInput.value = stock;
    }
}

/* ── Combination mode: cascading per-axis selection (e.g. Shade → Finish) ── */
function axisGroups() {
    return Array.from(document.querySelectorAll('.variation-axis-group'));
}

function selectAxisValue(axisIndex, btn) {
    if (btn.disabled) return;
    const group = btn.closest('.variation-axis-group');
    const type = group.dataset.type;
    const value = btn.dataset.value;

    // Clicking the already-selected option toggles it off.
    if (selectedAxisValues[axisIndex] && selectedAxisValues[axisIndex].value === value) {
        delete selectedAxisValues[axisIndex];
    } else {
        selectedAxisValues[axisIndex] = { type: type, value: value };
    }

    group.querySelectorAll('.variation-axis-btn').forEach(b => {
        const active = selectedAxisValues[axisIndex] && b.dataset.value === selectedAxisValues[axisIndex].value;
        b.style.borderColor = active ? 'var(--primary-pink)' : '';
        b.style.background = active ? 'var(--primary-pink-soft)' : '';
        b.style.fontWeight = active ? '600' : '';
    });

    document.getElementById('variation-required-msg').style.display = 'none';
    document.getElementById('variation-oos-msg').style.display = 'none';
    filterOtherAxes(axisIndex);
    resolveVariant();
}

// Grey out options on the OTHER axis that can't pair with the currently
// selected axis's value, based on the real generated combinations — this is
// the "select Shade, Finish filters to valid options" behavior.
function filterOtherAxes(changedAxisIndex) {
    const groups = axisGroups();
    if (groups.length < 2) return;

    // A candidate is pairable if some combination simultaneously matches it
    // AND every other axis currently selected (not just the axis that just
    // changed) — required once there can be 3+ axes at once.
    const otherPicks = groups
        .map(g => parseInt(g.dataset.axis, 10))
        .filter(idx => idx !== changedAxisIndex)
        .map(idx => selectedAxisValues[idx])
        .filter(Boolean);

    groups.forEach(group => {
        const axisIndex = parseInt(group.dataset.axis, 10);
        if (axisIndex === changedAxisIndex) return;

        group.querySelectorAll('.variation-axis-btn').forEach(btn => {
            const candidateValue = btn.dataset.value;
            const candidateType = group.dataset.type;
            const required = otherPicks.concat([{ type: candidateType, value: candidateValue }]);
            const pairable = COMBINATIONS.some(c =>
                required.every(req => c.axes.some(a => a.type === req.type && a.value === req.value))
            );
            btn.disabled = !pairable;
            btn.style.opacity = pairable ? '1' : '0.35';
            btn.style.cursor = pairable ? 'pointer' : 'not-allowed';
        });
    });
}

function findMatchingCombination() {
    const groups = axisGroups();
    const picks = groups.map(g => selectedAxisValues[parseInt(g.dataset.axis, 10)]).filter(Boolean);
    if (picks.length !== groups.length) return null;

    return COMBINATIONS.find(c => {
        return picks.every(p => c.axes.some(v => v.type === p.type && v.value === p.value))
            && c.axes.every(v => picks.some(p => p.type === v.type && p.value === v.value));
    }) || null;
}

function resolveVariant() {
    const combo = findMatchingCombination();
    const oosMsg = document.getElementById('variation-oos-msg');
    const qtyInput = document.getElementById('quantity');

    if (!combo) {
        document.getElementById('selected_variant_id').value = '';
        updatePriceDisplay(0);
        if (oosMsg) oosMsg.style.display = 'none';
        return;
    }

    document.getElementById('selected_variant_id').value = combo.variant_id;
    updatePriceDisplay(parseFloat(combo.price_adjustment) || 0);

    if (combo.image_url) {
        const img = document.getElementById('productMainImage');
        if (img) img.src = combo.image_url;
    }

    const stock = parseInt(combo.total_stock, 10) || 0;
    if (qtyInput) {
        qtyInput.max = stock;
        if (parseInt(qtyInput.value, 10) > stock) qtyInput.value = Math.max(stock, 0);
    }
    if (oosMsg) oosMsg.style.display = stock <= 0 ? 'block' : 'none';
}

function validateVariationSelected() {
    if (!hasVariations) return true;

    if (combinationsMode) {
        const groups = axisGroups();
        const allPicked = groups.every(g => selectedAxisValues[parseInt(g.dataset.axis, 10)]);
        const variantId = document.getElementById('selected_variant_id').value;
        if (!allPicked || !variantId) {
            document.getElementById('variation-required-msg').style.display = 'block';
            document.querySelector('.variation-selector').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
        const combo = COMBINATIONS.find(c => String(c.variant_id) === String(variantId));
        if (combo && (parseInt(combo.total_stock, 10) || 0) <= 0) {
            document.getElementById('variation-oos-msg').style.display = 'block';
            return false;
        }
        return true;
    }

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
