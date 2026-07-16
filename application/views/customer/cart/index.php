<style>
    .cart-container {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .cart-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 15px;
    }
    
    .cart-item {
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .cart-item td {
        padding: 20px;
        vertical-align: middle;
    }
    
    .cart-item-image {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
    }
    
    .cart-item-name {
        font-weight: 600;
        font-size: 16px;
        color: var(--dark);
        margin-bottom: 5px;
    }
    
    .cart-item-price {
        font-size: 18px;
        color: var(--primary);
        font-weight: bold;
    }
    
    .cart-qty-controls {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    /* .cart-summary container now comes from the shared public/css/style.css
       component (white card + gradient accent bar) instead of being
       redeclared here — only the page-specific spacing is kept. */
    .cart-summary {
        margin-top: 30px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        font-size: 16px;
    }
    
    .summary-total {
        font-size: 24px;
        font-weight: bold;
        color: var(--primary);
        border-top: 2px solid #ddd;
        padding-top: 15px;
        margin-top: 10px;
    }
    
    .cart-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
        gap: 15px;
    }
</style>

<h1><i class="fas fa-shopping-cart"></i> Shopping Cart</h1>

<?php if (empty($cartItems)): ?>
    <div class="empty-state">
        <i class="fas fa-shopping-cart"></i>
        <h3>Your cart is empty</h3>
        <p>Start adding products to your cart</p>
        <a href="<?php echo BASE_URL; ?>shop" class="btn" style="margin-top: 20px;">
            <i class="fas fa-shopping-bag"></i> Continue Shopping
        </a>
    </div>
<?php else: ?>
    <div class="cart-container">
        <div class="table-responsive">
        <table class="cart-table">
            <?php foreach ($cartItems as $item): ?>
                <tr class="cart-item">
                    <td class="cart-td-image">
                        <?php $itemImage = $item['product_image'] ?? $item['image'] ?? ''; ?>
                        <?php if (!empty($itemImage)): ?>
                            <img src="<?php echo BASE_URL . $itemImage; ?>"
                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                 class="cart-item-image">
                        <?php else: ?>
                            <div class="cart-item-image" style="display: flex; align-items: center; justify-content: center; background: #f0f0f0;">
                                <i class="fas fa-image" style="color: #ccc;"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="cart-td-info">
                        <div class="cart-item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                        <?php if (!empty($item['variation_label'])): ?>
                            <div style="font-size: 13px; color: var(--gray); margin-bottom: 4px;">
                                <strong><?php echo htmlspecialchars($item['variation_label']); ?></strong>
                            </div>
                        <?php endif; ?>
                        <div class="cart-item-price">₱<?php echo number_format($item['price'], 2); ?></div>
                    </td>
                    <td class="cart-td-qty">
                        <form method="POST" action="<?php echo BASE_URL; ?>cart/update" class="cart-qty-controls">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item['cart_key']); ?>">
                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>"
                                   min="1" max="<?php echo $item['stock_quantity']; ?>"
                                   style="width: 70px; padding: 8px; border: 2px solid #ddd; border-radius: 8px; text-align: center;"
                                   onchange="this.form.submit()">
                            <span style="font-size: 14px; color: var(--gray);">max: <?php echo $item['stock_quantity']; ?></span>
                        </form>
                    </td>
                    <td class="cart-td-total">
                        <div style="font-size: 20px; font-weight: bold; color: var(--dark);">
                            ₱<?php echo number_format($item['item_total'], 2); ?>
                        </div>
                    </td>
                    <td class="cart-td-remove">
                        <a href="<?php echo BASE_URL; ?>cart/remove/<?php echo htmlspecialchars($item['cart_key']); ?>"
                           class="btn btn-secondary"
                           style="padding: 10px; border-radius: 50%; width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;"
                           onclick="event.preventDefault(); openCartConfirmModal('Remove Item?', 'This item will be removed from your cart.', this.href, 'Yes, Remove');">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        </div>

        <div class="cart-summary">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span style="font-weight: 600;">₱<?php echo number_format($subtotal, 2); ?></span>
            </div>
            <div class="summary-row summary-total">
                <span>Total:</span>
                <span>₱<?php echo number_format($subtotal, 2); ?></span>
            </div>
        </div>
        
        <div class="cart-actions">
            <a href="<?php echo BASE_URL; ?>shop" class="btn btn-outline btn-lg">
                <i class="fas fa-arrow-left"></i> Continue Shopping
            </a>

            <div style="display: flex; gap: 15px;">
                <a href="<?php echo BASE_URL; ?>cart/clear" class="btn btn-secondary btn-lg" onclick="event.preventDefault(); openCartConfirmModal('Clear Cart?', 'All items in your cart will be removed.', this.href, 'Yes, Clear Cart');">
                    <i class="fas fa-trash"></i> Clear Cart
                </a>
                <a href="<?php echo BASE_URL; ?>checkout" class="btn btn-primary btn-lg">
                    <i class="fas fa-credit-card"></i> Proceed to Checkout
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Clear Cart / Remove Item Confirm Modal (shared .modal component) -->
<div class="modal" id="cartConfirmModal">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h3 class="modal-title" id="cartConfirmTitle">Remove Item?</h3>
            <button type="button" class="modal-close" onclick="closeModal(document.getElementById('cartConfirmModal'))">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p id="cartConfirmBody" style="margin:0;color:#6b7280;">This item will be removed from your cart.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal(document.getElementById('cartConfirmModal'))">Cancel</button>
            <a href="#" id="cartConfirmLink" class="btn btn-danger btn-sm">Yes, Remove</a>
        </div>
    </div>
</div>

<script>
    function openCartConfirmModal(title, body, url, confirmLabel) {
        document.getElementById('cartConfirmTitle').textContent = title;
        document.getElementById('cartConfirmBody').textContent = body;
        document.getElementById('cartConfirmLink').href = url;
        document.getElementById('cartConfirmLink').textContent = confirmLabel;
        openModal('cartConfirmModal');
    }
</script>
