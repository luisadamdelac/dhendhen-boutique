<!-- Admin Walk-in Sale -->
<style>
.wk-search-wrap { position: relative; }
.wk-search-wrap input { width: 100%; padding: 10px 14px 10px 38px; border: 1px solid var(--border); border-radius: 10px; font-size: 14px; }
.wk-search-wrap i.fa-search { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--gray); }
.wk-results { margin-top: 10px; max-height: 340px; overflow-y: auto; border: 1px solid var(--border); border-radius: 10px; display: none; }
.wk-results.show { display: block; }
.wk-result-row { display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-bottom: 1px solid var(--border); }
.wk-result-row:last-child { border-bottom: none; }
.wk-result-thumb, .wk-result-thumb-placeholder { width: 42px; height: 42px; border-radius: 8px; object-fit: cover; background: var(--page-bg); flex-shrink: 0; }
.wk-result-thumb-placeholder { display: flex; align-items: center; justify-content: center; color: var(--gray); }
.wk-result-info { flex: 1; min-width: 0; }
.wk-result-name { font-weight: 600; font-size: 13.5px; color: var(--text); }
.wk-result-meta { font-size: 11.5px; color: var(--gray); }
.wk-result-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.wk-result-actions select { padding: 5px 8px; border: 1px solid var(--border); border-radius: 6px; font-size: 12px; }
.wk-empty-hint { padding: 16px; text-align: center; color: var(--gray); font-size: 13px; }

.wk-cart-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
.wk-cart-table th { text-align: left; font-size: 11px; text-transform: uppercase; color: var(--gray); padding: 8px; border-bottom: 1px solid var(--border); }
.wk-cart-table td { padding: 8px; border-bottom: 1px solid var(--border); font-size: 13.5px; vertical-align: middle; }
.wk-cart-table input[type="number"] { width: 60px; padding: 5px 6px; border: 1px solid var(--border); border-radius: 6px; text-align: center; }
.wk-cart-empty { padding: 24px; text-align: center; color: var(--gray); font-size: 13.5px; }
.wk-total-row { display: flex; justify-content: space-between; align-items: center; padding: 14px 0 4px; font-size: 16px; font-weight: 700; color: var(--text); }
.wk-remove-btn { border: none; background: none; color: var(--danger, #dc3545); cursor: pointer; font-size: 13px; }

.wk-layout { display: flex; flex-direction: column; gap: 1rem; }
.wk-layout > .wk-main, .wk-layout > .wk-side { min-width: 0; }
@media (min-width: 1200px) {
    .wk-layout { flex-direction: row; }
    .wk-layout > .wk-main { flex: 2; }
    .wk-layout > .wk-side { flex: 1; }
}

/* Squeezing the variation <select> + Add button onto the same line as the
   product name/SKU left .wk-result-info almost no width to work with on a
   phone — narrow enough that its text wrapped one character per line.
   Push the variation picker + Add button onto their own full-width row
   below the thumbnail/name instead of fighting them for horizontal space. */
@media (max-width: 600px) {
    .wk-result-row { flex-wrap: wrap; }
    .wk-result-actions { flex-basis: 100%; margin-top: 8px; flex-wrap: wrap; }
    .wk-result-actions select { flex: 1; min-width: 0; }
}
</style>

<div class="ds-hero-card mb-3">
    <div class="ds-hero-banner">
        <svg class="ds-hero-wave" viewBox="0 0 1440 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,110 C240,170 480,50 720,90 C960,130 1200,50 1440,100 L1440,200 L0,200 Z" fill="rgba(255,105,180,0.16)"></path>
            <path d="M0,140 C280,80 560,180 840,120 C1080,70 1280,140 1440,130 L1440,200 L0,200 Z" fill="rgba(233,30,99,0.22)"></path>
        </svg>
        <div class="ds-hero-banner-content">
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;"><i class="fas fa-cash-register"></i> Walk-in Sale</h4>
            <small class="text-muted">Record an in-store sale — pick a branch, add products, and stock is deducted immediately.</small>
        </div>
    </div>
</div>

<div class="wk-layout">
    <div class="wk-main">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-store-alt"></i> Branch & Product</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Branch *</label>
                    <select id="wkBranchSelect" class="form-select" onchange="wkOnBranchChange()">
                        <option value="">Select branch...</option>
                        <?php foreach ($branches as $b): ?>
                            <option value="<?php echo (int) $b['branch_id']; ?>"><?php echo htmlspecialchars($b['branch_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="wk-search-wrap mt-2">
                    <i class="fas fa-search"></i>
                    <input type="text" id="wkSearchInput" placeholder="Select a branch first, then search by product name or SKU..." autocomplete="off" disabled>
                </div>
                <div class="wk-results" id="wkResults"></div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-shopping-basket"></i> Current Sale</h3>
            </div>
            <div class="card-body">
                <div id="wkCartEmpty" class="wk-cart-empty">No items added yet — select a branch and search for a product above.</div>
                <div class="table-responsive" id="wkCartTableWrap" style="display:none;">
                    <table class="wk-cart-table">
                        <thead>
                            <tr><th>Product</th><th>Qty</th><th class="text-end">Unit Price</th><th class="text-end">Total</th><th></th></tr>
                        </thead>
                        <tbody id="wkCartBody"></tbody>
                    </table>
                </div>

                <div class="wk-total-row">
                    <span>Total</span>
                    <span id="wkTotalDisplay">₱0.00</span>
                </div>

                <div class="row g-2 mt-2">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label>Payment Method</label>
                            <select id="wkPaymentMethod" class="form-select">
                                <option value="cash">Cash</option>
                                <option value="gcash">GCash</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label>Notes (optional)</label>
                            <input type="text" id="wkNotes" class="form-control" placeholder="e.g. buyer's name">
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-primary w-100 mt-2" id="wkSubmitBtn" onclick="submitWalkinSale()">
                    <i class="fas fa-check-circle"></i> Complete Sale
                </button>
            </div>
        </div>
    </div>

    <div class="wk-side">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-receipt"></i> Recent Walk-in Sales</h3>
            </div>
            <div class="card-body" style="max-height:640px; overflow-y:auto;">
                <?php if (empty($recent_sales)): ?>
                    <p class="text-center text-muted" style="font-size:13px;">No walk-in sales recorded yet.</p>
                <?php else: ?>
                    <?php foreach ($recent_sales as $sale): ?>
                        <div style="padding:10px 0; border-bottom:1px solid var(--border);">
                            <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:600;">
                                <span>#<?php echo htmlspecialchars($sale['sale_number']); ?></span>
                                <span style="color:var(--primary-pink-dark);">₱<?php echo number_format($sale['total_amount'], 2); ?></span>
                            </div>
                            <div style="font-size:11.5px; color:var(--gray);">
                                <?php echo htmlspecialchars($sale['branch_name'] ?? '—'); ?> &middot;
                                <?php echo date('M j, Y g:i A', strtotime($sale['created_at'])); ?><br>
                                <?php echo ucfirst($sale['payment_method']); ?> &middot;
                                <?php echo count($sale['items']); ?> item<?php echo count($sale['items']) === 1 ? '' : 's'; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const WK_SEARCH_URL = '<?php echo site_url('admin/walkin/search_products'); ?>';
const WK_CREATE_URL = '<?php echo site_url('admin/walkin/create'); ?>';

let wkCart = [];
let wkSearchTimer = null;
let wkLastProducts = {};

function wkOnBranchChange() {
    const branchId = document.getElementById('wkBranchSelect').value;

    const proceed = () => {
        const input = document.getElementById('wkSearchInput');
        input.disabled = !branchId;
        input.placeholder = branchId ? 'Search by product name or SKU...' : 'Select a branch first, then search by product name or SKU...';
        input.value = '';
        document.getElementById('wkResults').classList.remove('show');
        wkLastBranchId = branchId;
        wkCart = [];
        wkRenderCart();
    };

    if (wkCart.length) {
        customConfirm('Changing branch will clear the current sale. Continue?', proceed, {
            title: 'Change Branch?',
            okText: 'Yes, Change Branch',
            onCancel: () => { document.getElementById('wkBranchSelect').value = wkLastBranchId || ''; }
        });
    } else {
        proceed();
    }
}
let wkLastBranchId = '';

document.getElementById('wkSearchInput')?.addEventListener('input', function () {
    clearTimeout(wkSearchTimer);
    const q = this.value.trim();
    wkSearchTimer = setTimeout(() => wkRunSearch(q), 300);
});

function wkRunSearch(q) {
    const branchId = document.getElementById('wkBranchSelect').value;
    if (!branchId) return;

    const resultsBox = document.getElementById('wkResults');
    fetch(WK_SEARCH_URL + '?branch_id=' + encodeURIComponent(branchId) + '&q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                resultsBox.innerHTML = '<div class="wk-empty-hint">' + (data.message || 'Search failed') + '</div>';
                resultsBox.classList.add('show');
                return;
            }
            if (!data.products.length) {
                resultsBox.innerHTML = '<div class="wk-empty-hint">No products found.</div>';
                resultsBox.classList.add('show');
                return;
            }
            wkLastProducts = {};
            data.products.forEach(p => { wkLastProducts[p.product_id] = p; });
            resultsBox.innerHTML = data.products.map(wkRenderResultRow).join('');
            resultsBox.classList.add('show');
        })
        .catch(() => {
            resultsBox.innerHTML = '<div class="wk-empty-hint">Search failed. Please try again.</div>';
            resultsBox.classList.add('show');
        });
}

function wkRenderResultRow(p) {
    const thumb = p.image
        ? '<img src="' + p.image + '" class="wk-result-thumb" alt="">'
        : '<div class="wk-result-thumb-placeholder"><i class="fas fa-image"></i></div>';

    // A product with options (either generated combinations or plain
    // single-axis values) has no standalone "base" price/stock of its own —
    // same rule the customer-facing product page enforces: a real option
    // must be picked before it's purchasable at all.
    let optionPicker = '';
    if (p.options && p.options.length) {
        optionPicker = '<select id="wk-opt-' + p.product_id + '" onchange="wkOnOptionChange(' + p.product_id + ')">' +
            '<option value="">Select an option...</option>' +
            p.options.map(o =>
                '<option value="' + o.id + '" data-price="' + (p.price + o.price_adjustment).toFixed(2) + '" data-stock="' + o.stock + '">' +
                wkEscape(o.label) + ' (₱' + (p.price + o.price_adjustment).toFixed(2) + ', ' + o.stock + ' left)' +
                '</option>'
            ).join('') +
            '</select>';
    }

    const disabled = p.stock <= 0;

    return '<div class="wk-result-row">' + thumb +
        '<div class="wk-result-info">' +
            '<div class="wk-result-name">' + wkEscape(p.product_name) + '</div>' +
            '<div class="wk-result-meta">SKU: ' + wkEscape(p.sku) + ' &middot; ₱' + p.price.toFixed(2) + ' &middot; ' + p.stock + ' in stock</div>' +
        '</div>' +
        '<div class="wk-result-actions">' + optionPicker +
            '<button type="button" class="btn btn-sm btn-primary" ' + (disabled ? 'disabled' : '') +
                ' data-product-id="' + p.product_id + '" onclick="wkAddToCart(this)">Add</button>' +
        '</div>' +
    '</div>';
}

function wkEscape(s) {
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

// Once an option is picked, disable it in the dropdown itself so a second
// Add click can't silently reuse a stale (already-consumed-in-cart) stock
// number — wkAddToCart() re-reads the select fresh each time anyway, this
// is just to stop the button from being clicked before anything is chosen.
function wkOnOptionChange(productId) {
    const btn = document.querySelector('button[data-product-id="' + productId + '"]');
    const select = document.getElementById('wk-opt-' + productId);
    if (btn && select) {
        btn.disabled = !select.value;
    }
}

function wkAddToCart(btn) {
    const productId = parseInt(btn.dataset.productId, 10);
    const product = wkLastProducts[productId];
    if (!product) return;

    const hasOptions = product.options && product.options.length;
    const optSelect = document.getElementById('wk-opt-' + productId);

    let optionType = null, optionId = null, optionLabel = null;
    let price = product.price, stock = product.stock;

    if (hasOptions) {
        if (!optSelect || !optSelect.value) {
            showAlert('warning', 'Please select an option first.');
            return;
        }
        const opt = optSelect.options[optSelect.selectedIndex];
        optionType = product.mode; // 'variant' or 'variation'
        optionId = parseInt(optSelect.value, 10);
        optionLabel = opt.textContent.split(' (₱')[0];
        price = parseFloat(opt.dataset.price);
        stock = parseInt(opt.dataset.stock, 10);
    }

    if (stock <= 0) {
        showAlert('warning', 'This item is out of stock at this branch.');
        return;
    }

    const existing = wkCart.find(i => i.product_id === productId && i.option_id === optionId);
    if (existing) {
        if (existing.quantity >= stock) {
            showAlert('warning', 'Only ' + stock + ' unit(s) available.');
            return;
        }
        existing.quantity += 1;
    } else {
        wkCart.push({
            product_id: productId,
            product_name: product.product_name,
            option_type: optionType,
            option_id: optionId,
            option_label: optionLabel,
            unit_price: price,
            quantity: 1,
            max_stock: stock,
        });
    }

    wkRenderCart();
}

function wkRenderCart() {
    const body = document.getElementById('wkCartBody');
    const emptyEl = document.getElementById('wkCartEmpty');
    const tableWrap = document.getElementById('wkCartTableWrap');

    if (!wkCart.length) {
        emptyEl.style.display = 'block';
        tableWrap.style.display = 'none';
        document.getElementById('wkTotalDisplay').textContent = '₱0.00';
        return;
    }

    emptyEl.style.display = 'none';
    tableWrap.style.display = 'block';

    body.innerHTML = wkCart.map((item, idx) => {
        const lineTotal = item.unit_price * item.quantity;
        return '<tr>' +
            '<td>' + wkEscape(item.product_name) + (item.option_label ? '<br><small class="text-muted">' + wkEscape(item.option_label) + '</small>' : '') + '</td>' +
            '<td><input type="number" min="1" max="' + item.max_stock + '" value="' + item.quantity + '" onchange="wkUpdateQty(' + idx + ', this.value)"></td>' +
            '<td class="text-end">₱' + item.unit_price.toFixed(2) + '</td>' +
            '<td class="text-end">₱' + lineTotal.toFixed(2) + '</td>' +
            '<td><button type="button" class="wk-remove-btn" onclick="wkRemoveItem(' + idx + ')"><i class="fas fa-trash"></i></button></td>' +
        '</tr>';
    }).join('');

    const total = wkCart.reduce((sum, i) => sum + i.unit_price * i.quantity, 0);
    document.getElementById('wkTotalDisplay').textContent = '₱' + total.toFixed(2);
}

function wkUpdateQty(idx, value) {
    const item = wkCart[idx];
    let qty = parseInt(value, 10);
    if (isNaN(qty) || qty < 1) qty = 1;
    if (qty > item.max_stock) {
        qty = item.max_stock;
        showAlert('warning', 'Only ' + item.max_stock + ' unit(s) available.');
    }
    item.quantity = qty;
    wkRenderCart();
}

function wkRemoveItem(idx) {
    wkCart.splice(idx, 1);
    wkRenderCart();
}

function submitWalkinSale() {
    const branchId = document.getElementById('wkBranchSelect').value;
    if (!branchId) {
        showAlert('warning', 'Please select a branch.');
        return;
    }
    if (!wkCart.length) {
        showAlert('warning', 'Please add at least one product to the sale.');
        return;
    }

    const btn = document.getElementById('wkSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    const formData = new FormData();
    formData.append('branch_id', branchId);
    formData.append('items', JSON.stringify(wkCart.map(i => ({
        product_id: i.product_id,
        option_type: i.option_type,
        option_id: i.option_id,
        option_label: i.option_label,
        unit_price: i.unit_price,
        quantity: i.quantity,
    }))));
    formData.append('payment_method', document.getElementById('wkPaymentMethod').value);
    formData.append('notes', document.getElementById('wkNotes').value);

    fetch(WK_CREATE_URL, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => window.location.reload(), 1200);
            } else {
                showAlert('danger', data.message || 'Failed to record sale.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle"></i> Complete Sale';
            }
        })
        .catch(() => {
            showAlert('danger', 'Request failed. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Complete Sale';
        });
}

document.addEventListener('click', function (e) {
    const wrap = document.querySelector('.wk-search-wrap');
    const results = document.getElementById('wkResults');
    if (wrap && results && !wrap.contains(e.target) && !results.contains(e.target)) {
        results.classList.remove('show');
    }
});
</script>
