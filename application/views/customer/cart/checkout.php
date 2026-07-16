<style>
    .checkout-container {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 30px;
        margin-top: 30px;
    }
    
    .checkout-section {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .section-title {
        font-size: 20px;
        font-weight: 600;
        color: var(--dark-gray);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--dark-gray);
    }
    
    .form-group input[type="text"],
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
    }
    
    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary-pink);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    .gcash-input-wrap { position: relative; }
    .gcash-status-icon {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 15px;
        display: none;
        pointer-events: none;
    }
    .gcash-field.is-valid .gcash-input-wrap input { border-color: #22c55e !important; }
    .gcash-field.is-valid .gcash-status-icon.icon-valid { display: block; color: #22c55e; }
    .gcash-field.is-invalid .gcash-input-wrap input { border-color: #e53935 !important; }
    .gcash-field.is-invalid .gcash-status-icon.icon-invalid { display: block; color: #e53935; }
    .gcash-field-message {
        font-size: 11.5px;
        margin-top: 5px;
        display: none;
        align-items: center;
        gap: 5px;
        color: #e53935;
    }
    .gcash-field.is-invalid .gcash-field-message { display: flex; }

    .delivery-options {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .delivery-option {
        display: flex;
        align-items: center;
        padding: 15px;
        border: 2px solid #ddd;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .delivery-option:hover {
        border-color: var(--primary-pink);
        background: #fff5f8;
    }
    
    .delivery-option input[type="radio"] {
        margin-right: 15px;
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    
    .delivery-option.selected {
        border-color: var(--primary-pink);
        background: #fff5f8;
    }
    
    .delivery-info {
        flex: 1;
    }
    
    .delivery-name {
        font-weight: 600;
        color: var(--dark-gray);
        margin-bottom: 5px;
    }
    
    .delivery-desc {
        font-size: 13px;
        color: var(--gray);
    }
    
    .delivery-fee {
        font-weight: bold;
        color: var(--primary-pink);
        font-size: 16px;
    }
    
    .payment-options {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .payment-option {
        display: flex;
        align-items: center;
        padding: 15px;
        border: 2px solid #ddd;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .payment-option:hover {
        border-color: var(--primary-pink);
        background: #fff5f8;
    }
    
    .payment-option input[type="radio"] {
        margin-right: 15px;
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    
    .payment-option.selected {
        border-color: var(--primary-pink);
        background: #fff5f8;
    }
    
    .payment-info {
        flex: 1;
    }
    
    .payment-name {
        font-weight: 600;
        color: var(--dark-gray);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cart-summary-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px 0;
        border-bottom: 1px solid #eee;
    }
    
    .cart-summary-item:last-child {
        border-bottom: none;
    }
    
    .cart-summary-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        background: #f0f0f0;
    }
    
    .cart-summary-details {
        flex: 1;
    }
    
    .cart-summary-name {
        font-weight: 500;
        font-size: 14px;
        color: var(--dark-gray);
        margin-bottom: 5px;
    }
    
    .cart-summary-qty {
        font-size: 13px;
        color: var(--gray);
    }
    
    .cart-summary-price {
        font-weight: 600;
        color: var(--primary-pink);
    }
    
    .order-summary {
        background: var(--light-gray);
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        font-size: 15px;
    }
    
    .summary-total {
        font-size: 22px;
        font-weight: bold;
        color: var(--primary-pink);
        border-top: 2px solid #ddd;
        padding-top: 15px;
        margin-top: 10px;
    }
    
    /* .place-order-btn now uses the shared .btn/.btn-primary component
       (see markup below) instead of a bespoke one-off button class. */

    #btnConfirmPurchase:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    @media (max-width: 968px) {
        .checkout-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<h1><i class="fas fa-credit-card"></i> Checkout</h1>

<?php if (empty($cartItems)): ?>
    <div class="empty-state">
        <i class="fas fa-shopping-cart"></i>
        <h3>Your cart is empty</h3>
        <p>Add items to your cart before checking out</p>
        <a href="<?php echo BASE_URL; ?>shop" class="btn" style="margin-top: 20px;">
            <i class="fas fa-shopping-bag"></i> Start Shopping
        </a>
    </div>
<?php else: ?>
    <form method="POST" action="<?php echo BASE_URL; ?>checkout/process" id="checkoutForm" enctype="multipart/form-data">
        <div class="checkout-container">
            <!-- Left Column -->
            <div>
                <!-- Delivery Type Section -->
                <div class="checkout-section">
                    <h2 class="section-title"><i class="fas fa-truck"></i> Preferred Mode of Delivery</h2>
                    <div class="delivery-options" id="deliveryMethodOptions">
                        <label class="delivery-option" onclick="selectDelivery(this, 'pickup', 0)">
                            <input type="radio" name="delivery_type" value="pickup" required>
                            <div class="delivery-info">
                                <div class="delivery-name">Pick-up (Store)</div>
                                <div class="delivery-desc">Pick up from our branch/store (Available immediately)</div>
                            </div>
                            <div class="delivery-fee">FREE</div>
                        </label>

                        <label class="delivery-option" onclick="selectDelivery(this, 'pasabay_jeep')">
                            <input type="radio" name="delivery_type" value="pasabay_jeep" required>
                            <div class="delivery-info">
                                <div class="delivery-name">Pasabay via Jeep (Local)</div>
                                <div class="delivery-desc">Local delivery through jeepney pasabay service within the area &mdash; fee depends on your delivery address</div>
                            </div>
                            <div class="delivery-fee" id="pasabay-fee-label">₱<?php echo number_format($shipping_fees[$addresses[0]['municipality']] ?? 30, 2); ?></div>
                        </label>
                    </div>
                </div>
                
                <!-- Delivery Address Section -->
                <div class="checkout-section" style="margin-top: 20px;">
                    <h2 class="section-title"><i class="fas fa-map-marker-alt"></i> Delivery Address</h2>
                    <div class="delivery-options" id="addressOptions">
                        <?php foreach ($addresses as $addr): ?>
                            <label class="delivery-option <?php echo $addr['is_default'] ? 'selected' : ''; ?>" onclick="selectAddress(this, '<?php echo htmlspecialchars($addr['municipality'], ENT_QUOTES); ?>')">
                                <input type="radio" name="address_id" value="<?php echo $addr['address_id']; ?>" <?php echo $addr['is_default'] ? 'checked' : ''; ?> required>
                                <div class="delivery-info">
                                    <div class="delivery-name">
                                        <?php echo htmlspecialchars($addr['label'] ?: 'Address'); ?>
                                        <?php if ($addr['is_default']): ?><span style="font-size:11px; color:#28a745; font-weight:600;">(Default)</span><?php endif; ?>
                                    </div>
                                    <div class="delivery-desc">
                                        <?php echo htmlspecialchars($addr['street'] . ', ' . $addr['barangay'] . ', ' . $addr['municipality'] . ', ' . $addr['province']); ?>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" onclick="openAddAddressModal()" style="margin-top:12px; font-size:13px; color:var(--primary-pink); background:none; border:none; padding:0; cursor:pointer; font-family:inherit; font-weight:600;">
                        <i class="fas fa-plus"></i> Add another address
                    </button>
                </div>
                
                <!-- Payment Method Section (GCash only) -->
                <div class="checkout-section" style="margin-top: 20px;">
                    <h2 class="section-title"><i class="fas fa-wallet"></i> Payment Method</h2>
                    <div class="payment-options">
                        <label class="payment-option selected">
                            <input type="hidden" name="payment_method" value="gcash">
                            <div class="payment-info">
                                <div class="payment-name">
                                    <i class="fas fa-mobile-alt" style="color: #007DFF;"></i>
                                    GCash
                                </div>
                                <div style="font-size:12px; color:#888; margin-top:4px;">Pay via GCash mobile wallet</div>
                            </div>
                        </label>
                    </div>

                    <!-- GCash Payment Details (shown when GCash is selected) -->
                    <div id="gcashPaymentSection" style="margin-top:20px; background:linear-gradient(135deg, #007DFF10, #007DFF05); border:2px solid #007DFF40; border-radius:12px; padding:20px;">
                        <div style="text-align:center; margin-bottom:15px;">
                            <div style="width:50px; height:50px; background:#007DFF; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-bottom:10px;">
                                <i class="fas fa-mobile-alt" style="font-size:24px; color:white;"></i>
                            </div>
                            <h3 style="color:#007DFF; margin:0; font-size:16px;">Send Payment via GCash</h3>
                        </div>

                        <?php if (!empty($gcash_qr_code)): ?>
                        <div style="background:white; border-radius:10px; padding:20px; margin-bottom:15px; text-align:center;">
                            <img src="<?php echo BASE_URL . htmlspecialchars($gcash_qr_code); ?>" alt="GCash QR Code" style="width:180px; height:180px; object-fit:contain; display:block; margin:0 auto;">
                            <p style="margin:10px 0 0; font-size:12px; color:#666;"><i class="fas fa-qrcode"></i> Scan this QR code using your GCash app to pay</p>
                            <a href="<?php echo BASE_URL . htmlspecialchars($gcash_qr_code); ?>" download="GCash-QR-Code"
                               style="display:inline-flex; align-items:center; gap:6px; margin-top:10px; font-size:12px; color:#007DFF; text-decoration:none; font-weight:600;">
                                <i class="fas fa-download"></i> Download QR Code
                            </a>
                        </div>
                        <?php else: ?>
                        <div style="background:#fff3cd; border-radius:8px; padding:10px 14px; margin-bottom:15px; font-size:12px; color:#856404; text-align:center;">
                            <i class="fas fa-info-circle"></i> No GCash QR code has been set up yet — send payment manually to the number below.
                        </div>
                        <?php endif; ?>

                        <div style="background:white; border-radius:10px; padding:15px; margin-bottom:15px;">
                            <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #eee;">
                                <span style="color:#666; font-size:13px;">Account Name:</span>
                                <span style="font-weight:600; color:#333;"><?php echo htmlspecialchars($gcash_name); ?></span>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid #eee;">
                                <span style="color:#666; font-size:13px;">GCash Number:</span>
                                <span style="display:flex; align-items:center; gap:8px;">
                                    <span style="font-weight:600; color:#007DFF; font-size:16px;" id="gcash-display-number"><?php echo htmlspecialchars($gcash_number); ?></span>
                                    <button type="button" onclick="copyGcashNumber()" id="copyGcashBtn"
                                            style="border:1px solid #007DFF40; background:#007DFF10; color:#007DFF; border-radius:6px; padding:3px 8px; font-size:11px; font-weight:600; cursor:pointer; font-family:inherit;">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                </span>
                            </div>
                            <div style="display:flex; justify-content:space-between; padding:8px 0;">
                                <span style="color:#666; font-size:13px;">Amount to Send:</span>
                                <span style="font-weight:700; color:#007DFF; font-size:18px;" id="gcash-amount">₱<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                        </div>

                        <button type="button" onclick="openGcashApp()"
                                style="display:flex; align-items:center; justify-content:center; gap:8px; width:100%; padding:12px; border:none; background:#007DFF; color:white; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; font-family:inherit; margin-bottom:15px;">
                            <i class="fas fa-mobile-alt"></i> Open GCash
                        </button>

                        <div style="background:#fff3cd; border-radius:8px; padding:10px 14px; margin-bottom:15px; font-size:12px; color:#856404;">
                            <i class="fas fa-info-circle"></i> Send the exact amount to the GCash number above using the app, then come back and confirm below.
                        </div>

                        <!-- Step 1: prompt to confirm payment was sent -->
                        <div id="gcashConfirmPrompt">
                            <button type="button" onclick="showGcashConfirmationForm()"
                                    style="width:100%; padding:14px; border:2px solid #22c55e; background:#f0fdf4; color:#15803d; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit;">
                                <i class="fas fa-check-circle"></i> I've Completed My Payment
                            </button>
                        </div>

                        <!-- Step 2: reference number + receipt, hidden until Step 1 is clicked -->
                        <div id="gcashConfirmationForm" style="display:none;">
                            <div class="form-group gcash-field" style="margin-bottom:12px;">
                                <label style="font-size:13px; font-weight:600; color:#333;">GCash Reference Number *</label>
                                <div class="gcash-input-wrap">
                                    <input type="text" name="gcash_reference" id="gcash_reference" placeholder="13 digits, e.g. 1234567890123"
                                           style="width:100%; padding:10px 40px 10px 14px; border:2px solid #ddd; border-radius:8px; font-size:14px; font-family:inherit; box-sizing:border-box;"
                                           inputmode="numeric" pattern="\d{13}" maxlength="13">
                                    <i class="fas fa-check-circle gcash-status-icon icon-valid"></i>
                                    <i class="fas fa-exclamation-circle gcash-status-icon icon-invalid"></i>
                                </div>
                                <small style="color:#888;">Exactly 13 digits, as shown in your GCash receipt.</small>
                                <div class="gcash-field-message"><i class="fas fa-circle-exclamation"></i> Enter a valid 13-digit reference number.</div>
                            </div>

                            <div class="form-group" style="margin-bottom:0;">
                                <label style="font-size:13px; font-weight:600; color:#333;">GCash Receipt Screenshot *</label>
                                <input type="file" name="gcash_receipt" id="gcash_receipt" accept="image/*"
                                       style="width:100%; padding:10px 14px; border:2px solid #ddd; border-radius:8px; font-size:13px; font-family:inherit; box-sizing:border-box; background:white;">
                                <small style="color:#888;">Upload a screenshot of your GCash payment confirmation (JPG/PNG, max 2MB).</small>
                                <div id="receipt-preview" style="display:none; margin-top:10px; text-align:center;">
                                    <img id="receipt-preview-img" src="" alt="Receipt preview" style="max-width:160px; max-height:160px; border-radius:8px; border:2px solid #007DFF;">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Order Notes Section -->
                <div class="checkout-section" style="margin-top: 20px;">
                    <h2 class="section-title"><i class="fas fa-comment"></i> Order Notes (Optional)</h2>
                    <div class="form-group">
                        <label for="order_notes">Additional instructions for your order</label>
                        <textarea name="order_notes" id="order_notes" placeholder="e.g., Special delivery instructions, gift message, etc."></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Order Summary -->
            <div>
                <div class="checkout-section" style="position: sticky; top: 100px;">
                    <h2 class="section-title"><i class="fas fa-receipt"></i> Order Summary</h2>
                    
                    <!-- Cart Items -->
                    <div style="max-height: 300px; overflow-y: auto; margin-bottom: 20px;">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-summary-item">
                                <?php $itemImage = $item['product_image'] ?? $item['image'] ?? ''; ?>
                                <?php if (!empty($itemImage)): ?>
                                    <img src="<?php echo BASE_URL . $itemImage; ?>"
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                         class="cart-summary-image">
                                <?php else: ?>
                                    <div class="cart-summary-image" style="display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image" style="color: #ccc;"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="cart-summary-details">
                                    <div class="cart-summary-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                    <?php if (!empty($item['variation_label'])): ?>
                                        <div class="cart-summary-qty"><strong><?php echo htmlspecialchars($item['variation_label']); ?></strong></div>
                                    <?php endif; ?>
                                    <div class="cart-summary-qty">Qty: <?php echo $item['quantity']; ?></div>
                                </div>
                                <div class="cart-summary-price">₱<?php echo number_format($item['item_total'], 2); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Order Summary Totals -->
                    <div class="order-summary">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span style="font-weight: 600;" id="subtotal-display">₱<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Delivery Fee:</span>
                            <span style="font-weight: 600;" id="delivery-fee-display">₱0.00</span>
                        </div>
                        <div class="summary-row summary-total">
                            <span>Total:</span>
                            <span id="total-display">₱<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                    </div>
                    
                    <input type="hidden" name="subtotal" value="<?php echo $subtotal; ?>">
                    <input type="hidden" name="delivery_fee" id="delivery_fee" value="0">
                    <input type="hidden" name="total_amount" id="total_amount" value="<?php echo $subtotal; ?>">
                    
                    <button type="button" class="btn btn-primary btn-lg w-100 mt-3" onclick="showConfirmModal()">
                        <i class="fas fa-check-circle"></i> Place Order
                    </button>
                    
                    <a href="<?php echo BASE_URL; ?>cart" style="display: block; text-align: center; margin-top: 15px; color: var(--gray); text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Back to Cart
                    </a>
                </div>
            </div>
        </div>
    </form>

<!-- Validation Notice Modal (shared .modal component) -->
<div class="modal" id="checkoutValidationModal">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h3 class="modal-title">Notice</h3>
            <button type="button" class="modal-close" onclick="closeModal(document.getElementById('checkoutValidationModal'))">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p id="checkoutValidationBody" style="margin:0;"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary btn-sm" id="checkoutValidationOkBtn">OK</button>
        </div>
    </div>
</div>

<!-- Order Confirmation Modal -->
<div id="confirmModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:16px; max-width:500px; width:90%; margin:auto; padding:35px; box-shadow:0 20px 60px rgba(0,0,0,0.3); animation:modalSlideIn 0.3s ease-out; position:relative; top:50%; transform:translateY(-50%); max-height:90vh; overflow-y:auto;">
        <div style="text-align:center; margin-bottom:25px;">
            <div style="width:70px; height:70px; background:linear-gradient(135deg, #EC4899, #DB2777); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 15px;">
                <i class="fas fa-shopping-bag" style="font-size:30px; color:white;"></i>
            </div>
            <h2 style="font-size:22px; color:#333; margin-bottom:5px;">Confirm Your Order</h2>
            <p style="color:#888; font-size:14px;">Please review your order details before placing</p>
        </div>

        <div style="background:#f8f9fa; border-radius:10px; padding:18px; margin-bottom:20px;">
            <div style="display:flex; justify-content:space-between; padding:8px 0; font-size:14px; color:#555;">
                <span>Items:</span>
                <span style="font-weight:600;" id="confirm-items"><?php echo count($cartItems); ?> product(s)</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:8px 0; font-size:14px; color:#555;">
                <span>Delivery:</span>
                <span style="font-weight:600;" id="confirm-delivery">--</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:8px 0; font-size:14px; color:#555;">
                <span>Payment:</span>
                <span style="font-weight:600;" id="confirm-payment">--</span>
            </div>
            <!-- GCash details in confirm modal -->
            <div id="confirm-gcash-details" style="display:none; padding:10px 0 4px; border-top:1px dashed #ddd; margin-top:4px;">
                <div style="display:flex; justify-content:space-between; padding:4px 0; font-size:13px; color:#555;">
                    <span>Ref. Number:</span>
                    <span style="font-weight:600; color:#007DFF;" id="confirm-gcash-ref">--</span>
                </div>
            </div>
            <div style="display:flex; justify-content:space-between; padding:12px 0 0; font-size:18px; font-weight:700; color:var(--primary-pink); border-top:2px solid #ddd; margin-top:8px;">
                <span>Total:</span>
                <span id="confirm-total">₱<?php echo number_format($subtotal, 2); ?></span>
            </div>
        </div>

        <div style="display:flex; gap:12px;">
            <button type="button" onclick="closeConfirmModal()" style="flex:1; padding:14px; border:2px solid #ddd; background:white; border-radius:10px; font-size:15px; font-weight:600; cursor:pointer; font-family:inherit; color:#666; transition:all 0.2s;">
                <i class="fas fa-arrow-left"></i> Go Back
            </button>
            <button type="button" onclick="confirmAndSubmit()" id="btnConfirmPurchase" style="flex:1; padding:14px; border:none; background:linear-gradient(135deg, #EC4899, #DB2777); color:white; border-radius:10px; font-size:15px; font-weight:600; cursor:pointer; font-family:inherit; transition:all 0.2s;">
                <i class="fas fa-check"></i> Confirm Purchase
            </button>
        </div>
    </div>
</div>

<!-- Add Address Modal -->
<div id="addAddressModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:16px; max-width:480px; width:90%; margin:auto; padding:30px; box-shadow:0 20px 60px rgba(0,0,0,0.3); animation:modalSlideIn 0.3s ease-out; position:relative; top:50%; transform:translateY(-50%); max-height:90vh; overflow-y:auto;">
        <h2 style="font-size:20px; color:#333; margin-bottom:20px;"><i class="fas fa-map-marker-alt" style="color:var(--primary-pink);"></i> Add New Address</h2>

        <div id="addAddressError" style="display:none; background:#f8d7da; color:#842029; border:1px solid #f5c2c7; border-radius:8px; padding:10px 14px; margin-bottom:16px; font-size:13px;"></div>

        <div style="margin-bottom:16px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; font-size:13px; color:#555;">Label As</label>
            <div style="display:flex; gap:10px;">
                <button type="button" class="new-addr-label-btn selected" data-label="Home"
                        style="flex:1; padding:11px 14px; border:2px solid var(--primary-pink); border-radius:8px; background:var(--primary-pink); color:white; font-size:14px; font-family:inherit; font-weight:600; cursor:pointer; transition:all 0.2s;">
                    Home
                </button>
                <button type="button" class="new-addr-label-btn" data-label="Work"
                        style="flex:1; padding:11px 14px; border:2px solid #ddd; border-radius:8px; background:white; color:#333; font-size:14px; font-family:inherit; font-weight:600; cursor:pointer; transition:all 0.2s;">
                    Work
                </button>
            </div>
            <input type="hidden" id="newAddrLabel" value="Home">
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
            <div>
                <label style="display:block; margin-bottom:6px; font-weight:500; font-size:13px; color:#555;">Municipality / City</label>
                <select id="newAddrMunicipality" style="width:100%; padding:11px 14px; border:2px solid #ddd; border-radius:8px; font-size:14px; font-family:inherit;">
                    <option value="">Select Municipality</option>
                </select>
            </div>
            <div>
                <label style="display:block; margin-bottom:6px; font-weight:500; font-size:13px; color:#555;">Barangay</label>
                <select id="newAddrBarangay" disabled style="width:100%; padding:11px 14px; border:2px solid #ddd; border-radius:8px; font-size:14px; font-family:inherit;">
                    <option value="">Select Municipality first</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom:24px;">
            <label style="display:block; margin-bottom:6px; font-weight:500; font-size:13px; color:#555;">Street (House No., Street)</label>
            <input type="text" id="newAddrStreet" maxlength="100" style="width:100%; padding:11px 14px; border:2px solid #ddd; border-radius:8px; font-size:14px; font-family:inherit;">
        </div>

        <div style="display:flex; gap:12px;">
            <button type="button" onclick="closeAddAddressModal()" style="flex:1; padding:13px; border:2px solid #ddd; background:white; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; font-family:inherit; color:#666;">
                Cancel
            </button>
            <button type="button" onclick="submitNewAddress()" id="btnSaveNewAddress" style="flex:1; padding:13px; border:none; background:linear-gradient(135deg, #EC4899, #DB2777); color:white; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; font-family:inherit;">
                <i class="fas fa-save"></i> Save Address
            </button>
        </div>
    </div>
</div>

<style>
    @keyframes modalSlideIn {
        from { opacity:0; transform:translateY(-40%); }
        to { opacity:1; transform:translateY(-50%); }
    }

    /* #confirmModal and #addAddressModal size themselves with inline
       styles (fixed padding + percentage width), which beats any normal
       stylesheet rule regardless of specificity — !important is required
       here to tighten them up on small phones without touching the
       desktop inline values at all. */
    @media (max-width: 575.98px) {
        #confirmModal > div,
        #addAddressModal > div {
            width: 95% !important;
            padding: 20px !important;
            max-height: 85vh !important;
        }
    }
</style>

<script>
    const subtotal = <?php echo $subtotal; ?>;
    const shippingFees = <?php echo json_encode($shipping_fees); ?>;
    const barangayData = <?php echo json_encode($barangay_data); ?>;
    let deliveryFee = 0;
    let selectedDeliveryType = '';
    let selectedMunicipality = <?php echo json_encode($addresses[0]['municipality']); ?>;
    let selectedPayment = '';

    // --- Preserve checkout state across the trip to the GCash app and back.
    // Mobile browsers can suspend or even reload a backgrounded tab, so this
    // is a safety net on top of the normal in-memory state — restored on
    // load so the customer never has to redo delivery method, address, or
    // notes. File inputs can't be restored (browser security), so the
    // receipt upload is intentionally left out of this. ---
    const CHECKOUT_STATE_KEY = 'dropsell_checkout_state';

    function saveCheckoutState() {
        const addressInput = document.querySelector('input[name="address_id"]:checked');
        sessionStorage.setItem(CHECKOUT_STATE_KEY, JSON.stringify({
            deliveryType: selectedDeliveryType,
            addressId: addressInput ? addressInput.value : null,
            municipality: selectedMunicipality,
            notes: document.getElementById('order_notes').value,
            gcashConfirmed: document.getElementById('gcashConfirmationForm').style.display !== 'none',
        }));
    }

    function clearCheckoutState() {
        sessionStorage.removeItem(CHECKOUT_STATE_KEY);
    }

    function restoreCheckoutState() {
        const raw = sessionStorage.getItem(CHECKOUT_STATE_KEY);
        if (!raw) return;

        let state;
        try { state = JSON.parse(raw); } catch (e) { return; }

        if (state.deliveryType) {
            const input = document.querySelector('#deliveryMethodOptions input[value="' + state.deliveryType + '"]');
            if (input) {
                input.checked = true;
                selectDelivery(input.closest('.delivery-option'), state.deliveryType);
            }
        }

        if (state.addressId) {
            const input = document.querySelector('#addressOptions input[value="' + state.addressId + '"]');
            if (input) {
                input.checked = true;
                selectAddress(input.closest('.delivery-option'), state.municipality || selectedMunicipality);
            }
        }

        if (state.notes) {
            document.getElementById('order_notes').value = state.notes;
        }

        if (state.gcashConfirmed) {
            showGcashConfirmationForm();
        }
    }

    function showCheckoutNotice(message, focusId) {
        document.getElementById('checkoutValidationBody').textContent = message;
        openModal('checkoutValidationModal');
        document.getElementById('checkoutValidationOkBtn').onclick = function () {
            closeModal(document.getElementById('checkoutValidationModal'));
            if (focusId) {
                const el = document.getElementById(focusId);
                if (el) el.focus();
            }
        };
    }

    function feeForSelectedMunicipality() {
        return shippingFees[selectedMunicipality] ?? 30;
    }

    document.getElementById('gcash_receipt').addEventListener('change', function () {
        const file = this.files[0];
        const preview = document.getElementById('receipt-preview');
        const img = document.getElementById('receipt-preview-img');
        if (!file) { preview.style.display = 'none'; return; }
        if (file.size > 2 * 1024 * 1024) {
            showCheckoutNotice('Receipt image must be 2MB or smaller.');
            this.value = '';
            preview.style.display = 'none';
            return;
        }
        const reader = new FileReader();
        reader.onload = e => { img.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(file);
    });

    function selectDelivery(element, type) {
        document.querySelectorAll('#deliveryMethodOptions .delivery-option').forEach(opt => opt.classList.remove('selected'));
        element.classList.add('selected');
        selectedDeliveryType = type;
        deliveryFee = type === 'pasabay_jeep' ? feeForSelectedMunicipality() : 0;
        updateTotal();
        saveCheckoutState();
    }

    function selectAddress(element, municipality) {
        document.querySelectorAll('#addressOptions .delivery-option').forEach(opt => opt.classList.remove('selected'));
        element.classList.add('selected');
        selectedMunicipality = municipality;
        const fee = feeForSelectedMunicipality();
        document.getElementById('pasabay-fee-label').textContent = '₱' + fee.toFixed(2);
        if (selectedDeliveryType === 'pasabay_jeep') {
            deliveryFee = fee;
            updateTotal();
        }
        saveCheckoutState();
    }

    function selectPayment(element, method) {
        document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('selected'));
        element.classList.add('selected');
        selectedPayment = method;

        // Show/hide GCash payment section
        const gcashSection = document.getElementById('gcashPaymentSection');
        if (method === 'gcash') {
            gcashSection.style.display = 'block';
            updateGcashAmount();
        } else {
            gcashSection.style.display = 'none';
        }
    }

    function updateTotal() {
        const total = subtotal + deliveryFee;
        document.getElementById('delivery-fee-display').textContent = '₱' + deliveryFee.toFixed(2);
        document.getElementById('total-display').textContent = '₱' + total.toFixed(2);
        document.getElementById('delivery_fee').value = deliveryFee;
        document.getElementById('total_amount').value = total;
        updateGcashAmount();
    }

    function updateGcashAmount() {
        const total = subtotal + deliveryFee;
        const gcashAmountEl = document.getElementById('gcash-amount');
        if (gcashAmountEl) {
            gcashAmountEl.textContent = '₱' + total.toFixed(2);
        }
    }

    // Live red/green validation on the GCash fields, same pattern as the
    // registration forms' email/contact fields.
    function setGcashFieldState(input, regex) {
        const group = input.closest('.gcash-field');
        if (!group) return;
        const val = input.value.trim();
        if (val === '') {
            group.classList.remove('is-valid', 'is-invalid');
            return;
        }
        const valid = regex.test(val);
        group.classList.toggle('is-valid', valid);
        group.classList.toggle('is-invalid', !valid);
    }

    (function() {
        const gcashRefInput = document.getElementById('gcash_reference');
        if (gcashRefInput) {
            const check = () => setGcashFieldState(gcashRefInput, /^\d{13}$/);
            gcashRefInput.addEventListener('input', check);
            gcashRefInput.addEventListener('blur', check);
        }
    })();

    // --- Step 2 reveal: reference/receipt fields only appear once the
    // customer says they've actually sent the money. ---
    function showGcashConfirmationForm() {
        document.getElementById('gcashConfirmPrompt').style.display = 'none';
        document.getElementById('gcashConfirmationForm').style.display = 'block';
        document.getElementById('gcashConfirmationForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
        saveCheckoutState();
    }

    function copyGcashNumber() {
        const number = document.getElementById('gcash-display-number').textContent.trim();
        const btn = document.getElementById('copyGcashBtn');
        const restore = () => { btn.innerHTML = '<i class="fas fa-copy"></i> Copy'; };

        const done = () => {
            btn.innerHTML = '<i class="fas fa-check"></i> Copied';
            setTimeout(restore, 1500);
        };

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(number).then(done).catch(() => showCheckoutNotice('Could not copy automatically — please copy the number manually.'));
        } else {
            showCheckoutNotice('Could not copy automatically — please copy the number manually.');
        }
    }

    // Best-effort attempt to hand off to the GCash mobile app. GCash has no
    // publicly documented custom URL scheme, so this tries the common
    // "gcash://" intent — but only on an actual phone. On desktop there's no
    // OS handler for it at all, so navigating there does nothing visible,
    // which read as "the button doesn't work" — show a clear message
    // instead of a silent no-op, and only attempt the deep link where it
    // could plausibly succeed.
    function openGcashApp() {
        saveCheckoutState();
        // GCash has no publicly documented/registered custom URL scheme to
        // launch it programmatically — attempting one (e.g. "gcash://")
        // just throws a "no registered handler" error on devices without a
        // matching app association and does nothing on devices with it.
        // Rather than guess at an unverified scheme, tell the customer
        // exactly what to do instead — the QR code and number above are the
        // reliable way to pay.
        showCheckoutNotice('Open the GCash app on your phone to send payment, or scan the QR code / use the number above.');
    }

    function showConfirmModal() {
        const deliveryTypeInput = document.querySelector('input[name="delivery_type"]:checked');
        const paymentMethod = document.querySelector('input[name="payment_method"]');
        const addressInput = document.querySelector('input[name="address_id"]:checked');

        if (!deliveryTypeInput) { showCheckoutNotice('Please select a delivery method'); return; }
        if (!paymentMethod || paymentMethod.value !== 'gcash') { showCheckoutNotice('Payment method is not properly set to GCash. Please reload the page.'); return; }
        if (!addressInput) { showCheckoutNotice('Please select a delivery address'); return; }

        // GCash validation — real GCash reference numbers are exactly 13
        // digits. Reject anything else before it ever hits the server.
        if (paymentMethod.value === 'gcash') {
            if (document.getElementById('gcashConfirmationForm').style.display === 'none') {
                showGcashConfirmationForm();
                showCheckoutNotice('Please confirm your GCash payment first, then enter your reference number and receipt below.');
                return;
            }

            const gcashRef = document.getElementById('gcash_reference').value.trim();

            if (!/^\d{13}$/.test(gcashRef)) { showCheckoutNotice('GCash Reference Number must be exactly 13 digits.', 'gcash_reference'); return; }
            if (!document.getElementById('gcash_receipt').files.length) { showCheckoutNotice('Please upload a screenshot of your GCash receipt.'); return; }

            // Show GCash details in confirm modal
            document.getElementById('confirm-gcash-details').style.display = 'block';
            document.getElementById('confirm-gcash-ref').textContent = gcashRef;
        } else {
            document.getElementById('confirm-gcash-details').style.display = 'none';
        }

        const deliveryLabels = {
            'pickup': 'Pick-up (Store, FREE)',
            'pasabay_jeep': 'Pasabay via Jeep (₱' + deliveryFee.toFixed(2) + ')'
        };
        const paymentLabels = {
            'gcash': 'GCash'
        };

        document.getElementById('confirm-delivery').textContent = deliveryLabels[deliveryTypeInput.value] || deliveryTypeInput.value;
        document.getElementById('confirm-payment').textContent = paymentLabels[paymentMethod.value] || paymentMethod.value;
        document.getElementById('confirm-total').textContent = '₱' + (subtotal + deliveryFee).toFixed(2);

        document.getElementById('confirmModal').style.display = 'flex';
    }
    
    function closeConfirmModal() {
        document.getElementById('confirmModal').style.display = 'none';
    }
    
    function confirmAndSubmit() {
        const btn = document.getElementById('btnConfirmPurchase');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        document.getElementById('confirmModal').style.display = 'none';

        // checkout/process returns JSON (order_id + redirect_url), so this
        // must be submitted via fetch — a native form .submit() would
        // navigate the whole page to that raw JSON response instead of the
        // order confirmation page.
        const form = document.getElementById('checkoutForm');
        fetch(form.action, { method: 'POST', body: new FormData(form) })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    clearCheckoutState();
                    window.location.href = data.redirect_url;
                } else {
                    showCheckoutNotice(data.message || 'Failed to place order. Please try again.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check"></i> Confirm Purchase';
                }
            })
            .catch(() => {
                showCheckoutNotice('Something went wrong placing your order. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle"></i> Confirm Purchase';
            });
    }
    
    document.getElementById('confirmModal').addEventListener('click', function(e) {
        if (e.target === this) closeConfirmModal();
    });

    // --- Add Address modal (inline, no page navigation) ---
    const newAddrMunicipality = document.getElementById('newAddrMunicipality');
    const newAddrBarangay = document.getElementById('newAddrBarangay');

    Object.keys(barangayData).sort().forEach(function(name) {
        const opt = document.createElement('option');
        opt.value = name;
        opt.textContent = name;
        newAddrMunicipality.appendChild(opt);
    });

    newAddrMunicipality.addEventListener('change', function() {
        const barangays = barangayData[this.value] || [];
        newAddrBarangay.innerHTML = '';
        if (!barangays.length) {
            newAddrBarangay.appendChild(new Option('Select Municipality first', ''));
            newAddrBarangay.disabled = true;
            return;
        }
        newAddrBarangay.disabled = false;
        newAddrBarangay.appendChild(new Option('Select Barangay', ''));
        barangays.forEach(b => newAddrBarangay.appendChild(new Option(b, b)));
    });

    function selectNewAddrLabel(val) {
        document.getElementById('newAddrLabel').value = val;
        document.querySelectorAll('.new-addr-label-btn').forEach(function(btn) {
            const isSelected = btn.dataset.label === val;
            btn.classList.toggle('selected', isSelected);
            btn.style.background = isSelected ? 'var(--primary-pink)' : 'white';
            btn.style.color = isSelected ? 'white' : '#333';
            btn.style.borderColor = isSelected ? 'var(--primary-pink)' : '#ddd';
        });
    }

    document.querySelectorAll('.new-addr-label-btn').forEach(function(btn) {
        btn.addEventListener('click', function() { selectNewAddrLabel(btn.dataset.label); });
    });

    function openAddAddressModal() {
        document.getElementById('addAddressError').style.display = 'none';
        selectNewAddrLabel('Home');
        document.getElementById('newAddrStreet').value = '';
        newAddrMunicipality.value = '';
        newAddrBarangay.innerHTML = '<option value="">Select Municipality first</option>';
        newAddrBarangay.disabled = true;
        document.getElementById('addAddressModal').style.display = 'flex';
    }

    function closeAddAddressModal() {
        document.getElementById('addAddressModal').style.display = 'none';
    }

    document.getElementById('addAddressModal').addEventListener('click', function(e) {
        if (e.target === this) closeAddAddressModal();
    });

    function submitNewAddress() {
        const errorBox = document.getElementById('addAddressError');
        const street = document.getElementById('newAddrStreet').value.trim();
        const municipality = newAddrMunicipality.value;
        const barangay = newAddrBarangay.value;

        if (!municipality || !barangay || !street) {
            errorBox.textContent = 'Please fill in municipality, barangay, and street.';
            errorBox.style.display = 'block';
            return;
        }

        const btn = document.getElementById('btnSaveNewAddress');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        const params = new URLSearchParams({
            label: document.getElementById('newAddrLabel').value.trim(),
            municipality: municipality,
            barangay: barangay,
            street: street
        });

        fetch('<?php echo BASE_URL; ?>addresses/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: params.toString()
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Save Address';

            if (!data.success) {
                errorBox.textContent = data.message || 'Failed to save address.';
                errorBox.style.display = 'block';
                return;
            }

            const addr = data.address;
            const label = document.createElement('label');
            label.className = 'delivery-option';
            label.setAttribute('onclick', "selectAddress(this, " + JSON.stringify(addr.municipality) + ")");
            label.innerHTML = '<input type="radio" name="address_id" value="' + addr.address_id + '" required>'
                + '<div class="delivery-info">'
                + '<div class="delivery-name">' + escapeHtml(addr.label || 'Address') + '</div>'
                + '<div class="delivery-desc">' + escapeHtml(addr.street + ', ' + addr.barangay + ', ' + addr.municipality + ', ' + addr.province) + '</div>'
                + '</div>';

            document.getElementById('addressOptions').appendChild(label);
            document.querySelectorAll('#addressOptions .delivery-option').forEach(opt => opt.classList.remove('selected'));
            label.classList.add('selected');
            label.querySelector('input').checked = true;
            selectAddress(label, addr.municipality);

            closeAddAddressModal();
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Save Address';
            errorBox.textContent = 'Something went wrong. Please try again.';
            errorBox.style.display = 'block';
        });
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    document.getElementById('order_notes').addEventListener('input', saveCheckoutState);

    // Restore whatever the customer had entered before switching to the
    // GCash app (or before a mobile browser suspended/reloaded this tab).
    restoreCheckoutState();
</script>

<?php endif; ?>
