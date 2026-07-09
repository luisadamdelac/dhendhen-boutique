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
    <form method="POST" action="<?php echo BASE_URL; ?>checkout/process" id="checkoutForm">
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
                
                <!-- Payment Method Section (PayMongo) -->
                <div class="checkout-section" style="margin-top: 20px;">
                    <h2 class="section-title"><i class="fas fa-wallet"></i> Payment</h2>
                    <input type="hidden" name="payment_method" value="paymongo">
                    <div style="background:linear-gradient(135deg, #007DFF10, #007DFF05); border:2px solid #007DFF40; border-radius:12px; padding:20px; display:flex; align-items:center; gap:14px;">
                        <div style="width:44px; height:44px; background:#007DFF; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <i class="fas fa-shield-halved" style="font-size:18px; color:white;"></i>
                        </div>
                        <div>
                            <div style="font-weight:600; color:#333;">Pay securely with PayMongo</div>
                            <div style="font-size:13px; color:#666; margin-top:2px;">You'll be redirected to PayMongo's checkout page to pay via GCash, GrabPay, Maya, or card.</div>
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
                                    <?php if (!empty($item['variation_type'])): ?>
                                        <div class="cart-summary-qty"><?php echo htmlspecialchars($item['variation_type']); ?>: <strong><?php echo htmlspecialchars($item['variation_value']); ?></strong></div>
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
            <div style="width:70px; height:70px; background:linear-gradient(135deg, #ff69b4, #ee82ee); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 15px;">
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
            <div style="display:flex; justify-content:space-between; padding:12px 0 0; font-size:18px; font-weight:700; color:var(--primary-pink); border-top:2px solid #ddd; margin-top:8px;">
                <span>Total:</span>
                <span id="confirm-total">₱<?php echo number_format($subtotal, 2); ?></span>
            </div>
        </div>

        <div style="display:flex; gap:12px;">
            <button type="button" onclick="closeConfirmModal()" style="flex:1; padding:14px; border:2px solid #ddd; background:white; border-radius:10px; font-size:15px; font-weight:600; cursor:pointer; font-family:inherit; color:#666; transition:all 0.2s;">
                <i class="fas fa-arrow-left"></i> Go Back
            </button>
            <button type="button" onclick="confirmAndSubmit()" id="btnConfirmPurchase" style="flex:1; padding:14px; border:none; background:linear-gradient(135deg, #ff69b4, #ee82ee); color:white; border-radius:10px; font-size:15px; font-weight:600; cursor:pointer; font-family:inherit; transition:all 0.2s;">
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
            <label style="display:block; margin-bottom:6px; font-weight:500; font-size:13px; color:#555;">Label</label>
            <input type="text" id="newAddrLabel" maxlength="50" placeholder="Home, Work, etc." style="width:100%; padding:11px 14px; border:2px solid #ddd; border-radius:8px; font-size:14px; font-family:inherit;">
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
            <button type="button" onclick="submitNewAddress()" id="btnSaveNewAddress" style="flex:1; padding:13px; border:none; background:linear-gradient(135deg, #ff69b4, #ee82ee); color:white; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; font-family:inherit;">
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
</style>

<script>
    const subtotal = <?php echo $subtotal; ?>;
    const shippingFees = <?php echo json_encode($shipping_fees); ?>;
    const barangayData = <?php echo json_encode($barangay_data); ?>;
    let deliveryFee = 0;
    let selectedDeliveryType = '';
    let selectedMunicipality = <?php echo json_encode($addresses[0]['municipality']); ?>;
    let selectedPayment = '';

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

    function selectDelivery(element, type) {
        document.querySelectorAll('#deliveryMethodOptions .delivery-option').forEach(opt => opt.classList.remove('selected'));
        element.classList.add('selected');
        selectedDeliveryType = type;
        deliveryFee = type === 'pasabay_jeep' ? feeForSelectedMunicipality() : 0;
        updateTotal();
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
    }

    function updateTotal() {
        const total = subtotal + deliveryFee;
        document.getElementById('delivery-fee-display').textContent = '₱' + deliveryFee.toFixed(2);
        document.getElementById('total-display').textContent = '₱' + total.toFixed(2);
        document.getElementById('delivery_fee').value = deliveryFee;
        document.getElementById('total_amount').value = total;
    }

    function showConfirmModal() {
        const deliveryTypeInput = document.querySelector('input[name="delivery_type"]:checked');
        const paymentMethod = document.querySelector('input[name="payment_method"]');
        const addressInput = document.querySelector('input[name="address_id"]:checked');

        if (!deliveryTypeInput) { showCheckoutNotice('Please select a delivery method'); return; }
        if (!paymentMethod || paymentMethod.value !== 'paymongo') { showCheckoutNotice('Payment method is not properly set. Please reload the page.'); return; }
        if (!addressInput) { showCheckoutNotice('Please select a delivery address'); return; }

        const deliveryLabels = {
            'pickup': 'Pick-up (Store, FREE)',
            'pasabay_jeep': 'Pasabay via Jeep (₱' + deliveryFee.toFixed(2) + ')'
        };
        const paymentLabels = {
            'paymongo': 'PayMongo (GCash / GrabPay / Maya / Card)'
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

        // checkout/process returns JSON (checkout_url), so this must be
        // submitted via fetch — a native form .submit() would navigate the
        // whole page to that raw JSON response instead of redirecting to
        // PayMongo's hosted checkout page.
        const form = document.getElementById('checkoutForm');
        fetch(form.action, { method: 'POST', body: new FormData(form) })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.checkout_url;
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

    function openAddAddressModal() {
        document.getElementById('addAddressError').style.display = 'none';
        document.getElementById('newAddrLabel').value = '';
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
</script>

<?php endif; ?>
