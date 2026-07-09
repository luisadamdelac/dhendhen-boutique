<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$page_title = 'Edit Product';
$current_page = 'inventory';
$current_image_path = !empty($primary_image['image_path']) ? $primary_image['image_path'] : '';
?>
<style>
/* ── Image upload zone ─────────────────────────────────────── */
.upload-zone {
    border: 2px dashed #c0c0d0;
    border-radius: 12px;
    background: #F3F5FF;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 220px;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    position: relative;
    overflow: hidden;
}
.upload-zone:hover, .upload-zone.dragover {
    border-color: var(--primary-pink);
    background: #FBCFE822;
}
.upload-zone .zone-icon { font-size: 2.4rem; color: #9ca3c8; margin-bottom: 10px; }
.upload-zone .zone-label { font-size: .85rem; color: var(--gray); text-align: center; }
.upload-zone .zone-label strong { color: var(--primary-pink); }
.upload-zone .zone-hint { font-size: .75rem; color: #aaa; margin-top: 4px; }
#imagePreviewWrap { display: none; }
#imagePreview {
    width: 100%; height: 220px;
    object-fit: cover; border-radius: 10px;
}
.remove-image-btn {
    position: absolute; top: 8px; right: 8px;
    background: rgba(220,53,69,.9);
    border: none; border-radius: 50%; width: 28px; height: 28px;
    color: #fff; font-size: 12px; cursor: pointer; display: flex;
    align-items: center; justify-content: center;
}

/* ── SKU field ─────────────────────────────────────────────── */
.sku-field { font-family: monospace; font-weight: 600; letter-spacing: .05em; }
.char-counter { font-size: .73rem; color: #aaa; float: right; }

/* ── Tags input ────────────────────────────────────────────── */
.tags-container {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 6px 8px;
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    min-height: 42px;
    cursor: text;
    background: #fff;
}
.tags-container:focus-within {
    border-color: var(--primary-pink);
    box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.12);
}
.tag-chip {
    display: inline-flex; align-items: center; gap: 4px;
    background: var(--primary-pink-light); color: var(--primary-pink-dark);
    border-radius: 20px; padding: 2px 10px;
    font-size: .78rem; font-weight: 500; white-space: nowrap;
}
.tag-chip .remove-tag {
    cursor: pointer; font-size: 11px; opacity: .6;
    background: none; border: none; color: var(--primary-pink-dark); padding: 0; line-height: 1;
}
.tag-chip .remove-tag:hover { opacity: 1; }
#tagInput {
    border: none; outline: none; font-size: .875rem;
    flex: 1; min-width: 100px; padding: 2px 4px;
}

/* ── Pricing hints ─────────────────────────────────────────── */
.markup-badge {
    background: rgba(40, 167, 69, 0.15); color: var(--success);
    border-radius: 20px; padding: 2px 10px;
    font-size: .78rem; font-weight: 600;
    display: inline-flex; align-items: center; gap: 4px;
}
.markup-badge.negative { background: rgba(220, 53, 69, 0.15); color: var(--danger); }

/* ── Status selector ───────────────────────────────────────── */
.status-options { display: flex; gap: 8px; flex-wrap: wrap; }
.status-opt input { display: none; }
.status-opt label {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 20px;
    border: 1.5px solid var(--border);
    font-size: .82rem; cursor: pointer;
}
.status-opt input:checked + label {
    border-color: var(--opt-color);
    background: var(--opt-bg);
    color: var(--opt-color);
    font-weight: 600;
}
.status-opt label i { font-size: 11px; }

/* ── Smart select (searchable Brand/Category picker) ─────── */
.smart-select { position: relative; }
.smart-select-input-wrap { position: relative; display: flex; align-items: center; }
.smart-select-input-wrap .smart-select-input { padding-right: 42px; }
.smart-select-add-btn {
    position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
    width: 30px; height: 30px; border-radius: 50%; border: none;
    background: var(--primary-pink-light); color: var(--primary-pink-dark);
    display: flex; align-items: center; justify-content: center; cursor: pointer;
    font-size: .8rem; transition: background .15s ease, color .15s ease;
}
.smart-select-add-btn:hover { background: var(--primary-pink); color: #fff; }
.smart-select-dropdown {
    position: absolute; top: calc(100% + 6px); left: 0; right: 0; z-index: 40;
    background: #fff; border: 1px solid var(--border); border-radius: var(--radius-md);
    box-shadow: var(--shadow-md); max-height: 220px; overflow-y: auto; padding: 6px;
}
.smart-select-option {
    padding: 8px 10px; border-radius: 8px; cursor: pointer; font-size: .88rem; color: var(--text);
}
.smart-select-option:hover, .smart-select-option.active { background: var(--primary-pink-light); color: var(--primary-pink-dark); }
.smart-select-option mark { background: transparent; color: var(--primary-pink-dark); font-weight: 700; }
.smart-select-empty { padding: 10px; font-size: .82rem; color: var(--gray); text-align: center; }
.smart-select-create-hint {
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    padding: 8px 10px; margin-top: 4px; border-top: 1px dashed var(--border);
    font-size: .82rem; color: var(--primary-pink-dark); cursor: pointer; border-radius: 8px;
}
.smart-select-create-hint:hover { background: var(--primary-pink-light); }

/* ── Product Variations ───────────────────────────────────── */
.variation-type-block {
    border: 1px solid var(--border); border-radius: 12px; padding: 16px; margin-bottom: 14px; background: #fafbff;
}
.variation-type-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
.variation-type-header h5 { margin: 0; font-size: .95rem; color: var(--primary-pink-dark); }
.variation-type-header .remove-type-btn { background: none; border: none; color: var(--danger); cursor: pointer; font-size: .85rem; }
.variation-values-table { margin-bottom: 10px; }
.variation-values-table th {
    font-size: .72rem; color: var(--gray); font-weight: 600; text-transform: uppercase; letter-spacing: .03em;
    border-top: none;
}
.variation-values-table th.text-center, .variation-values-table td.text-center { text-align: center; }
.variation-values-table td { vertical-align: middle; }
.variation-value-row input { font-size: .85rem; }
.add-variation-value-btn { font-size: .78rem; padding: 4px 10px; }

/* ── Shared quick-create modal ───────────────────────────── */
.ss-modal-overlay {
    position: fixed; inset: 0; background: rgba(15,23,42,.45); z-index: 1000;
    display: flex; align-items: center; justify-content: center; padding: 20px;
    opacity: 0; pointer-events: none; transition: opacity .2s ease;
}
.ss-modal-overlay.open { opacity: 1; pointer-events: auto; }
.ss-modal {
    background: #fff; width: 100%; max-width: 420px; border-radius: 14px;
    box-shadow: 0 8px 32px rgba(0,0,0,.18); padding: 24px;
    transform: translateY(12px); transition: transform .2s ease;
}
.ss-modal-overlay.open .ss-modal { transform: translateY(0); }
.ss-modal h4 { margin: 0 0 4px; font-size: 1.05rem; color: var(--text); display: flex; align-items: center; gap: 8px; }
.ss-modal h4 i { color: var(--primary-pink); }
.ss-modal p.hint { margin: 0 0 16px; font-size: .8rem; color: var(--gray); }
.ss-modal-error {
    color: var(--danger); font-size: .8rem; margin-top: 8px; display: none; align-items: center; gap: 5px;
}
.ss-modal-error.show { display: flex; }
.ss-modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }

/* ── Toasts ───────────────────────────────────────────────── */
.ss-toast-container {
    position: fixed; bottom: 24px; right: 24px; z-index: 1100;
    display: flex; flex-direction: column; gap: 10px;
}
.ss-toast {
    background: #fff; border-left: 4px solid var(--success); box-shadow: var(--shadow-md);
    border-radius: 10px; padding: 12px 16px; font-size: .85rem; color: var(--text);
    display: flex; align-items: center; gap: 10px; min-width: 240px; max-width: 320px;
    transform: translateX(20px); opacity: 0; transition: transform .25s ease, opacity .25s ease;
}
.ss-toast.show { transform: translateX(0); opacity: 1; }
.ss-toast i { color: var(--success); }
.ss-toast.error { border-left-color: var(--danger); }
.ss-toast.error i { color: var(--danger); }
@media (max-width: 576px) {
    .ss-toast-container { left: 16px; right: 16px; bottom: 16px; }
    .ss-toast { min-width: 0; max-width: none; }
}

/* ── Pricing stat tiles ───────────────────────────────────── */
.pricing-stats { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 10px; }
.pricing-stat {
    flex: 1; min-width: 150px; background: #FDF2F8;
    border: 1px solid var(--primary-pink-light); border-radius: 12px; padding: 14px 16px;
}
.pricing-stat .stat-lbl {
    font-size: .72rem; color: var(--gray); font-weight: 600;
    text-transform: uppercase; letter-spacing: .03em;
}
.pricing-stat .stat-val { font-size: 1.3rem; font-weight: 700; color: var(--text); margin-top: 2px; }
.pricing-stat.negative .stat-val { color: var(--danger); }
.pricing-stat.positive .stat-val { color: var(--success); }

/* ── Inventory summary ────────────────────────────────────── */
.stock-summary { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 6px; }
.stock-summary .stat-tile {
    flex: 1; min-width: 150px; background: #F3F5FF; border: 1px solid #dfe3ff;
    border-radius: 12px; padding: 14px 16px;
}
.stock-summary .stat-tile .stat-lbl {
    font-size: .72rem; color: var(--gray); font-weight: 600;
    text-transform: uppercase; letter-spacing: .03em;
}
.stock-summary .stat-tile .stat-val { font-size: 1.3rem; font-weight: 700; color: var(--text); margin-top: 2px; }

.branch-stock-msg {
    font-size: .75rem; margin-top: 6px; display: none; align-items: center; gap: 6px;
}
.branch-stock-msg.ok   { display: flex; color: #2e7d32; }
.branch-stock-msg.warn { display: flex; color: #8d6e00; }
.branch-stock-msg.out  { display: flex; color: var(--danger); }

/* ── Description guidelines ──────────────────────────────── */
.desc-guidelines {
    background: #F3F5FF; border: 1px solid #dfe3ff; border-radius: 10px;
    padding: 12px 14px; font-size: .8rem; color: var(--text); margin-top: 10px;
}
.desc-guidelines ul { margin: 6px 0 0 18px; padding: 0; }
</style>

<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="page-title mb-1" style="margin:0;">Edit Product</h2>
            <p class="text-muted" style="margin:0;">Update product details, pricing, and category.</p>
        </div>
        <div>
            <a href="<?= site_url('admin/product/view/' . $product['product_id']); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <?php if (!empty($error)): ?>
    <div class="alert alert-danger" role="alert">
        <i class="fas fa-exclamation-circle"></i>
        <div><?= $error; ?></div>
    </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" id="editProductForm" novalidate>
        <div class="row">

            <!-- LEFT COLUMN: Image + Status -->
            <div class="col col-4">
                <div class="card">
                    <div class="card-body">
                        <div class="page-section" style="margin-top:0;">
                            <span class="section-title"><i class="fas fa-image me-2"></i>Product Image</span>
                            <hr>
                        </div>
                        <input type="file" id="product_image" name="product_image" accept=".jpg,.jpeg,.png,.webp" style="display:none;">
                        <div class="upload-zone" id="uploadZone" onclick="document.getElementById('product_image').click()">
                            <div id="zonePlaceholder" style="<?= $current_image_path ? 'display:none;' : ''; ?>">
                                <div class="zone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                <div class="zone-label"><strong>Click to upload</strong> or drag & drop</div>
                                <div class="zone-hint">JPG, PNG, WebP — max 5 MB</div>
                            </div>
                            <div id="imagePreviewWrap" style="<?= $current_image_path ? 'display:block;' : ''; ?>">
                                <img id="imagePreview" src="<?= $current_image_path ? htmlspecialchars(base_url($current_image_path)) : '#'; ?>" alt="Preview">
                                <button type="button" class="remove-image-btn" onclick="removeImage(event)" title="Choose a different image">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div id="imageError" style="color: var(--danger); margin-top: 6px; font-size:.8rem;display:none;"></div>
                        <small style="color: var(--gray); display:block; margin-top:6px;">Leave unchanged to keep the current image.</small>
                    </div>
                </div>

                <div class="card" style="margin-top: 20px;">
                    <div class="card-body">
                        <div class="page-section" style="margin-top:0;">
                            <span class="section-title"><i class="fas fa-toggle-on me-2"></i>Product Status</span>
                            <hr>
                        </div>
                        <?php $currentStatus = set_value('status', $product['status'] ?? 'available'); ?>
                        <div class="status-options">
                            <div class="status-opt" style="--opt-color:#2e7d32;--opt-bg:#e8f5e9;">
                                <input type="radio" name="status" id="st_available" value="available" <?= $currentStatus === 'available' ? 'checked' : ''; ?>>
                                <label for="st_available"><i class="fas fa-circle-check"></i> Available</label>
                            </div>
                            <div class="status-opt" style="--opt-color:#6c757d;--opt-bg:#f8f9fa;">
                                <input type="radio" name="status" id="st_not_available" value="not_available" <?= $currentStatus === 'not_available' ? 'checked' : ''; ?>>
                                <label for="st_not_available"><i class="fas fa-ban"></i> Not Available</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /col-4 -->

            <!-- RIGHT COLUMN -->
            <div class="col col-8">

                <!-- Product Information -->
                <div class="card">
                    <div class="card-body">
                        <div class="page-section" style="margin-top:0;">
                            <span class="section-title"><i class="fas fa-box me-2"></i>Product Information</span>
                            <hr>
                        </div>
                        <div class="row">
                            <div class="col col-8">
                                <div class="form-group">
                                    <label for="product_name">Product Name *</label>
                                    <input type="text" class="form-control" id="product_name" name="product_name"
                                        maxlength="150" placeholder="e.g. Organic Coconut Oil 500ml"
                                        value="<?= htmlspecialchars(set_value('product_name', $product['product_name'] ?? '')); ?>" required>
                                    <div style="text-align:right;">
                                        <span class="char-counter"><span id="nameCount">0</span>/150</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col col-4">
                                <div class="form-group">
                                    <label for="brand">Brand</label>
                                    <div class="smart-select" id="brandSmartSelect">
                                        <div class="smart-select-input-wrap">
                                            <input type="text" class="form-control smart-select-input" id="brand" name="brand"
                                                maxlength="100" placeholder="Select existing or type a brand"
                                                value="<?= htmlspecialchars(set_value('brand', $product['brand'] ?? '')); ?>" autocomplete="off">
                                            <button type="button" class="smart-select-add-btn" title="Add new brand" aria-label="Add new brand">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                        <div class="smart-select-dropdown" hidden></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="sku">SKU</label>
                                    <input type="text" class="form-control sku-field" id="sku"
                                        value="<?= htmlspecialchars($product['sku'] ?? ''); ?>" readonly>
                                    <small style="color: var(--gray);">SKU is fixed after creation</small>
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="category_search">Category</label>
                                    <?php
                                        $selected_category_id = set_value('category_id', $product['category_id'] ?? '');
                                        $selected_category_label = '';
                                        if (!empty($selected_category_id)) {
                                            foreach ($categories as $cat) {
                                                if ((string) $cat['category_id'] === (string) $selected_category_id) {
                                                    $selected_category_label = $cat['category_name'];
                                                    break;
                                                }
                                            }
                                        }
                                    ?>
                                    <div class="smart-select" id="categorySmartSelect">
                                        <div class="smart-select-input-wrap">
                                            <input type="text" class="form-control smart-select-input" id="category_search"
                                                maxlength="100" placeholder="Select existing or type a category"
                                                value="<?= htmlspecialchars($selected_category_label); ?>" autocomplete="off">
                                            <button type="button" class="smart-select-add-btn" title="Add new category" aria-label="Add new category">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                        <div class="smart-select-dropdown" hidden></div>
                                    </div>
                                    <input type="hidden" id="category_id" name="category_id" value="<?= htmlspecialchars($selected_category_id ?: '0'); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                <div class="card" style="margin-top: 20px;">
                    <div class="card-body">
                        <div class="page-section" style="margin-top:0;">
                            <span class="section-title"><i class="fas fa-tag me-2"></i>Pricing</span>
                            <hr>
                        </div>
                        <div class="row">
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="cost_price">Cost Price *</label>
                                    <input type="number" step="0.01" min="0.01" class="form-control" id="cost_price" name="cost_price"
                                        placeholder="0.00" value="<?= set_value('cost_price', $product['cost_price'] ?? ''); ?>" required>
                                    <small style="color: var(--gray);">What you paid per unit</small>
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="price">Selling Price *</label>
                                    <input type="number" step="0.01" min="0.01" class="form-control" id="price" name="price"
                                        placeholder="0.00" value="<?= set_value('price', $product['price'] ?? ''); ?>" required>
                                    <small style="color: var(--gray);">Customer selling price</small>
                                </div>
                            </div>
                            <div class="col col-12">
                                <label style="margin-bottom:6px;">Automatic Markup Calculation</label>
                                <div class="pricing-stats">
                                    <div class="pricing-stat" id="profitTile">
                                        <div class="stat-lbl">Profit (per unit)</div>
                                        <div class="stat-val" id="statProfit">₱0.00</div>
                                    </div>
                                    <div class="pricing-stat" id="markupPctTile">
                                        <div class="stat-lbl">Markup Percentage</div>
                                        <div class="stat-val" id="statMarkupPct">0%</div>
                                    </div>
                                    <div class="pricing-stat" id="diffTile">
                                        <div class="stat-lbl">Price Difference</div>
                                        <div class="stat-val" id="statDiff">₱0.00</div>
                                    </div>
                                </div>
                                <div style="display:flex;align-items:center;gap:8px;margin-top:10px;">
                                    <span style="color: var(--gray); font-size:.82rem;">Summary:</span>
                                    <span id="markupBadge" class="markup-badge"><i class="fas fa-arrow-up"></i> ₱0.00</span>
                                    <span id="marginBadge" style="color: var(--gray); font-size:.78rem;"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inventory -->
                <div class="card" style="margin-top: 20px;">
                    <div class="card-body">
                        <div class="page-section" style="margin-top:0;">
                            <span class="section-title"><i class="fas fa-warehouse me-2"></i>Inventory</span>
                            <hr>
                        </div>
                        <div class="row">
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="min_stock_alert">Minimum Stock Alert *</label>
                                    <input type="number" min="1" class="form-control" id="min_stock_alert" name="min_stock_alert"
                                        placeholder="10" value="<?= set_value('min_stock_alert', $product['min_stock_alert'] ?? '10'); ?>" required>
                                    <small style="color: var(--gray);">Trigger low-stock warning (applies to total across branches)</small>
                                </div>
                            </div>
                            <div class="col col-6">
                                <div class="form-group">
                                    <label for="expiry_date">Expiry Date</label>
                                    <input type="date" class="form-control" id="expiry_date" name="expiry_date"
                                        value="<?= set_value('expiry_date', $product['expiry_date'] ?? ''); ?>">
                                    <small style="color: var(--gray);">Leave blank if not applicable</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Variations -->
                <div class="card" style="margin-top: 20px;">
                    <div class="card-body">
                        <div class="page-section" style="margin-top:0;">
                            <span class="section-title"><i class="fas fa-layer-group me-2"></i>Product Variations &amp; Branch Stock</span>
                            <hr>
                        </div>
                        <p class="text-muted" style="margin-top:-6px;font-size:.85rem;">Optional. Each variation value has its own stock per branch and an optional price adjustment on top of the selling price above.</p>

                        <div id="variationTypesContainer"></div>

                        <div class="smart-select" id="variationTypeSelect" style="max-width:280px;position:relative;">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="addVariationTypeBtn">
                                <i class="fas fa-plus"></i> Add Variation Type
                            </button>
                            <div class="smart-select-dropdown" id="variationTypeDropdown" hidden style="position:absolute;top:calc(100% + 4px);left:0;min-width:200px;"></div>
                        </div>

                        <div style="margin-top:20px;">
                            <label style="margin-bottom:6px;">Automatic Stock Summary</label>
                            <div class="stock-summary">
                                <div class="stat-tile">
                                    <div class="stat-lbl">Total Stock</div>
                                    <div class="stat-val"><span id="totalStockDisplay">0</span> unit(s)</div>
                                </div>
                                <div class="stat-tile">
                                    <div class="stat-lbl">Branches Stocked</div>
                                    <div class="stat-val" id="branchesStockedDisplay">0</div>
                                </div>
                            </div>
                            <small style="color: var(--gray); display:block; margin-top:8px;">Changing a variation's branch quantity creates a stock adjustment batch for that branch (FIFO), it does not overwrite history.</small>
                        </div>

                        <input type="hidden" name="variations_json" id="variationsJson" value="">
                    </div>
                </div>

                <!-- Product Details -->
                <div class="card" style="margin-top: 20px;">
                    <div class="card-body">
                        <div class="page-section" style="margin-top:0;">
                            <span class="section-title"><i class="fas fa-align-left me-2"></i>Product Details</span>
                            <hr>
                        </div>
                        <div class="form-group">
                            <label for="description">Product Description</label>
                            <textarea class="form-control" id="description" name="description"
                                rows="5" maxlength="1000"
                                placeholder="Describe the product — ingredients, usage, benefits…"><?= htmlspecialchars(set_value('description', $product['description'] ?? '')); ?></textarea>
                            <div style="text-align:right;">
                                <span class="char-counter"><span id="descCount">0</span>/1000</span>
                            </div>
                            <div class="desc-guidelines">
                                <strong><i class="fas fa-lightbulb"></i> Description Guidelines</strong>
                                <ul>
                                    <li>Mention key ingredients or materials.</li>
                                    <li>Explain how to use the product.</li>
                                    <li>Highlight the main benefits for the customer.</li>
                                    <li>Keep it clear and concise — up to 1000 characters.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="form-group" style="margin-top:20px;">
                            <label for="tagInput">Tags <span style="color:var(--gray); font-weight:400;">(optional)</span></label>
                            <div class="tags-container" id="tagsContainer" onclick="document.getElementById('tagInput').focus()">
                                <input type="text" id="tagInput" placeholder="Type a tag and press Enter or comma…">
                            </div>
                            <input type="hidden" id="tagsHidden" name="tags" value="<?= htmlspecialchars(set_value('tags', $product['tags'] ?? '')); ?>">
                            <small style="color: var(--gray);">Press Enter or comma to add a tag</small>
                        </div>
                    </div>
                </div>

            </div><!-- /col-8 -->
        </div><!-- /row -->

        <!-- Action bar -->
        <div class="card mt-4">
            <div class="card-body d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
                <div class="text-muted" style="font-size:.9rem;">
                    <i class="fas fa-shield-alt text-success"></i>
                    Changes are saved permanently on submission.
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= site_url('admin/product/view/' . $product['product_id']); ?>" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
/* ── Image upload (optional replace) ─────────────────────── */
const fileInput   = document.getElementById('product_image');
const uploadZone  = document.getElementById('uploadZone');
const placeholder = document.getElementById('zonePlaceholder');
const previewWrap = document.getElementById('imagePreviewWrap');
const previewImg  = document.getElementById('imagePreview');
const imageError  = document.getElementById('imageError');
const MAX_BYTES   = 5 * 1024 * 1024;
const ALLOWED     = ['image/jpeg','image/png','image/webp'];

fileInput.addEventListener('change', function() {
    const file = this.files[0];
    if (file) validateAndPreview(file);
});

['dragover','dragenter'].forEach(ev => uploadZone.addEventListener(ev, e => {
    e.preventDefault(); uploadZone.classList.add('dragover');
}));
['dragleave','drop'].forEach(ev => uploadZone.addEventListener(ev, e => {
    e.preventDefault(); uploadZone.classList.remove('dragover');
}));
uploadZone.addEventListener('drop', e => {
    const file = e.dataTransfer.files[0];
    if (file) { fileInput.files = e.dataTransfer.files; validateAndPreview(file); }
});

function validateAndPreview(file) {
    imageError.style.display = 'none';
    if (!ALLOWED.includes(file.type)) {
        imageError.textContent = 'Only JPG, PNG, or WebP files are allowed.';
        imageError.style.display = 'block';
        fileInput.value = '';
        return;
    }
    if (file.size > MAX_BYTES) {
        imageError.textContent = 'File size exceeds 5 MB limit.';
        imageError.style.display = 'block';
        fileInput.value = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = e => {
        previewImg.src = e.target.result;
        placeholder.style.display = 'none';
        previewWrap.style.display = 'block';
    };
    reader.readAsDataURL(file);
}

function removeImage(e) {
    e.stopPropagation();
    document.getElementById('product_image').click();
}

/* ── Markup calculator ───────────────────────────────────── */
const costInput = document.getElementById('cost_price');
const sellInput = document.getElementById('price');
const markupBadge = document.getElementById('markupBadge');
const marginBadge = document.getElementById('marginBadge');
const statProfit = document.getElementById('statProfit');
const statMarkupPct = document.getElementById('statMarkupPct');
const statDiff = document.getElementById('statDiff');
const profitTile = document.getElementById('profitTile');
const markupPctTile = document.getElementById('markupPctTile');
const diffTile = document.getElementById('diffTile');

function updateMarkup() {
    const cost = parseFloat(costInput.value) || 0;
    const sell = parseFloat(sellInput.value) || 0;
    const markup = sell - cost;
    const pos = markup >= 0;

    markupBadge.className = 'markup-badge' + (pos ? '' : ' negative');
    markupBadge.innerHTML = (pos ? '<i class="fas fa-arrow-up"></i>' : '<i class="fas fa-arrow-down"></i>') +
        ' ₱' + markup.toFixed(2);
    if (cost > 0 && sell > 0) {
        const margin = ((markup / sell) * 100).toFixed(1);
        marginBadge.textContent = '(' + margin + '% margin)';
    } else {
        marginBadge.textContent = '';
    }

    statProfit.textContent = '₱' + markup.toFixed(2);
    statDiff.textContent = '₱' + markup.toFixed(2);
    const markupPct = cost > 0 ? ((markup / cost) * 100).toFixed(1) : '0';
    statMarkupPct.textContent = markupPct + '%';
    [profitTile, markupPctTile, diffTile].forEach(tile => {
        tile.classList.toggle('positive', pos && markup > 0);
        tile.classList.toggle('negative', !pos);
    });
}
costInput.addEventListener('input', updateMarkup);
sellInput.addEventListener('input', updateMarkup);
updateMarkup();

/* ── Stock total, summed across every variation's per-branch inputs ── */
const totalStockDisplay = document.getElementById('totalStockDisplay');
const branchesStockedDisplay = document.getElementById('branchesStockedDisplay');
const editMinStockInput = document.getElementById('min_stock_alert');

function updateTotalStock() {
    // Variation rows are added/removed dynamically, so query fresh each time
    // rather than caching a NodeList at page load.
    const branchTotals = {};
    let total = 0;
    document.querySelectorAll('.variation-branch-stock-input').forEach(inp => {
        const qty = parseInt(inp.value, 10) || 0;
        const branchId = inp.dataset.branchId;
        branchTotals[branchId] = (branchTotals[branchId] || 0) + qty;
        total += qty;
    });
    const stocked = Object.values(branchTotals).filter(qty => qty > 0).length;
    totalStockDisplay.textContent = total;
    branchesStockedDisplay.textContent = stocked;
}
editMinStockInput.addEventListener('input', updateTotalStock);
updateTotalStock();

/* ── Character counters ──────────────────────────────────── */
const nameInput = document.getElementById('product_name');
const descInput = document.getElementById('description');
function updateNameCount() { document.getElementById('nameCount').textContent = nameInput.value.length; }
function updateDescCount() { document.getElementById('descCount').textContent = descInput.value.length; }
nameInput.addEventListener('input', updateNameCount);
descInput.addEventListener('input', updateDescCount);
updateNameCount();
updateDescCount();

/* ── Tags widget ─────────────────────────────────────────── */
let tags = [];

(function initTags() {
    const existing = document.getElementById('tagsHidden').value.trim();
    if (existing) {
        existing.split(',').forEach(t => { t = t.trim(); if (t) addTag(t); });
    }
})();

document.getElementById('tagInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        const val = this.value.replace(/,/g, '').trim();
        if (val) addTag(val);
        this.value = '';
    } else if (e.key === 'Backspace' && this.value === '' && tags.length) {
        removeTag(tags[tags.length - 1]);
    }
});

function addTag(text) {
    text = text.substring(0, 30);
    if (tags.includes(text) || tags.length >= 15) return;
    tags.push(text);
    renderTags();
}

function removeTag(text) {
    tags = tags.filter(t => t !== text);
    renderTags();
}

function renderTags() {
    const container = document.getElementById('tagsContainer');
    const input     = document.getElementById('tagInput');
    container.querySelectorAll('.tag-chip').forEach(c => c.remove());
    tags.forEach(tag => {
        const chip = document.createElement('span');
        chip.className = 'tag-chip';
        chip.innerHTML = `${escHtml(tag)}<button type="button" class="remove-tag" onclick="removeTag('${escHtml(tag)}')">&times;</button>`;
        container.insertBefore(chip, input);
    });
    document.getElementById('tagsHidden').value = tags.join(',');
}

function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

/* ── Smart select: searchable Brand/Category picker ──────── */
const BRAND_LIST = <?= json_encode(
    array_values(array_filter(array_column($brands, 'brand'))),
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
); ?>;
const CATEGORY_LIST = <?= json_encode(
    array_map(fn($c) => ['id' => (int) $c['category_id'], 'label' => $c['category_name']], $categories),
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
); ?>;

function initSmartSelect(opts) {
    const root = opts.root;
    const input = opts.input;
    const hidden = opts.hidden || null;
    const dropdown = root.querySelector('.smart-select-dropdown');
    const addBtn = root.querySelector('.smart-select-add-btn');
    let items = opts.items.slice();
    let activeIndex = -1;

    function labelOf(item) { return typeof item === 'string' ? item : item.label; }

    function highlightMatch(label, query) {
        const q = query.trim();
        if (!q) return escHtml(label);
        const idx = label.toLowerCase().indexOf(q.toLowerCase());
        if (idx === -1) return escHtml(label);
        return escHtml(label.slice(0, idx)) + '<mark>' + escHtml(label.slice(idx, idx + q.length)) + '</mark>' + escHtml(label.slice(idx + q.length));
    }

    function render(query) {
        const q = (query || '').trim().toLowerCase();
        const filtered = q ? items.filter(it => labelOf(it).toLowerCase().includes(q)) : items;
        dropdown.innerHTML = '';
        activeIndex = -1;

        if (filtered.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'smart-select-empty';
            empty.textContent = q ? `No matching ${opts.entity} found` : `No ${opts.entity}s yet`;
            dropdown.appendChild(empty);
        } else {
            filtered.slice(0, 50).forEach(item => {
                const opt = document.createElement('div');
                opt.className = 'smart-select-option';
                opt.setAttribute('role', 'option');
                opt.innerHTML = highlightMatch(labelOf(item), query || '');
                opt.addEventListener('mousedown', (e) => { e.preventDefault(); select(item); });
                dropdown.appendChild(opt);
            });
        }

        if (q) {
            const exists = items.some(it => labelOf(it).toLowerCase() === q);
            if (!exists) {
                const hint = document.createElement('div');
                hint.className = 'smart-select-create-hint';
                hint.innerHTML = '<span><i class="fas fa-plus-circle"></i> Create "' + escHtml(query.trim()) + '"</span> <i class="fas fa-arrow-right"></i>';
                hint.addEventListener('mousedown', (e) => { e.preventDefault(); openCreateModal(query.trim()); });
                dropdown.appendChild(hint);
            }
        }

        dropdown.hidden = false;
    }

    function select(item) {
        input.value = labelOf(item);
        if (hidden) hidden.value = item.id;
        close();
    }

    function close() { dropdown.hidden = true; }

    input.addEventListener('focus', () => render(input.value));
    input.addEventListener('input', () => {
        if (hidden) hidden.value = '0';
        render(input.value);
    });
    input.addEventListener('blur', () => setTimeout(close, 150));
    input.addEventListener('keydown', (e) => {
        const options = dropdown.querySelectorAll('.smart-select-option');
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIndex = Math.min(activeIndex + 1, options.length - 1);
            updateActive(options);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIndex = Math.max(activeIndex - 1, 0);
            updateActive(options);
        } else if (e.key === 'Enter') {
            if (activeIndex >= 0 && options[activeIndex]) {
                e.preventDefault();
                options[activeIndex].dispatchEvent(new Event('mousedown'));
            }
        } else if (e.key === 'Escape') {
            close();
        }
    });
    function updateActive(options) {
        options.forEach(o => o.classList.remove('active'));
        if (options[activeIndex]) {
            options[activeIndex].classList.add('active');
            options[activeIndex].scrollIntoView({ block: 'nearest' });
        }
    }

    addBtn.addEventListener('click', () => openCreateModal(input.value.trim()));

    function openCreateModal(prefill) {
        openSSModal({
            title: opts.modalTitle,
            placeholder: opts.placeholder,
            prefill: prefill || '',
            onSubmit: (name, done) => {
                const alreadyAdded = items.some(it => labelOf(it).toLowerCase() === name.toLowerCase());
                if (alreadyAdded) {
                    done(false, 'This ' + opts.entity + ' already exists.');
                    return;
                }
                fetch(opts.createUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'name=' + encodeURIComponent(name)
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const newItem = opts.entity === 'brand'
                            ? data.brand.brand
                            : { id: data.category.category_id, label: data.category.category_name };
                        items.push(newItem);
                        items.sort((a, b) => labelOf(a).localeCompare(labelOf(b)));
                        select(newItem);
                        done(true, null);
                        showSSToast((opts.entity === 'brand' ? 'Brand' : 'Category') + ' created and selected.', 'success');
                    } else {
                        done(false, data.message || 'Something went wrong.');
                    }
                })
                .catch(() => done(false, 'Network error. Please try again.'));
            }
        });
    }

    return { render, close };
}

/* ── Shared quick-create modal ───────────────────────────── */
let ssModalOverlay, ssModalTitleText, ssModalInput, ssModalError, ssModalSubmitBtn, ssModalCancelBtn, ssModalSubmitHandler;

function ensureSSModal() {
    if (ssModalOverlay) return;
    const div = document.createElement('div');
    div.className = 'ss-modal-overlay';
    div.innerHTML =
        '<div class="ss-modal" role="dialog" aria-modal="true">' +
            '<h4><i class="fas fa-plus-circle"></i> <span class="ss-modal-title-text"></span></h4>' +
            '<p class="hint">This will be available immediately in the dropdown.</p>' +
            '<div class="form-group">' +
                '<input type="text" class="form-control ss-modal-input" maxlength="100">' +
                '<div class="ss-modal-error"><i class="fas fa-exclamation-circle"></i> <span></span></div>' +
            '</div>' +
            '<div class="ss-modal-actions">' +
                '<button type="button" class="btn btn-outline-secondary ss-modal-cancel">Cancel</button>' +
                '<button type="button" class="btn btn-primary ss-modal-submit"><i class="fas fa-check"></i> Create</button>' +
            '</div>' +
        '</div>';
    document.body.appendChild(div);

    ssModalOverlay = div;
    ssModalTitleText = div.querySelector('.ss-modal-title-text');
    ssModalInput = div.querySelector('.ss-modal-input');
    ssModalError = div.querySelector('.ss-modal-error');
    ssModalSubmitBtn = div.querySelector('.ss-modal-submit');
    ssModalCancelBtn = div.querySelector('.ss-modal-cancel');

    ssModalCancelBtn.addEventListener('click', closeSSModal);
    div.addEventListener('mousedown', (e) => { if (e.target === div) closeSSModal(); });
    ssModalInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); ssModalSubmitBtn.click(); } });
    ssModalSubmitBtn.addEventListener('click', () => {
        const name = ssModalInput.value.trim();
        ssModalError.classList.remove('show');
        if (!name) { showSSModalError('Please enter a name.'); return; }
        if (name.length > 100) { showSSModalError('Maximum 100 characters.'); return; }

        ssModalSubmitBtn.disabled = true;
        ssModalCancelBtn.disabled = true;
        const originalHtml = ssModalSubmitBtn.innerHTML;
        ssModalSubmitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating…';

        ssModalSubmitHandler(name, (ok, message) => {
            ssModalSubmitBtn.disabled = false;
            ssModalCancelBtn.disabled = false;
            ssModalSubmitBtn.innerHTML = originalHtml;
            if (ok) { closeSSModal(); } else { showSSModalError(message || 'Something went wrong.'); }
        });
    });
}

function showSSModalError(msg) {
    ssModalError.querySelector('span').textContent = msg;
    ssModalError.classList.add('show');
}

function openSSModal({ title, placeholder, prefill, onSubmit }) {
    ensureSSModal();
    ssModalTitleText.textContent = title;
    ssModalInput.placeholder = placeholder || '';
    ssModalInput.value = prefill || '';
    ssModalError.classList.remove('show');
    ssModalSubmitHandler = onSubmit;
    ssModalOverlay.classList.add('open');
    setTimeout(() => ssModalInput.focus(), 50);
}

function closeSSModal() {
    if (ssModalOverlay) ssModalOverlay.classList.remove('open');
}

/* ── Toasts ───────────────────────────────────────────────── */
let ssToastContainer;
function showSSToast(message, type) {
    if (!ssToastContainer) {
        ssToastContainer = document.createElement('div');
        ssToastContainer.className = 'ss-toast-container';
        document.body.appendChild(ssToastContainer);
    }
    const toast = document.createElement('div');
    toast.className = 'ss-toast' + (type === 'error' ? ' error' : '');
    toast.innerHTML = '<i class="fas fa-' + (type === 'error' ? 'exclamation-circle' : 'check-circle') + '"></i><span>' + escHtml(message) + '</span>';
    ssToastContainer.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('show'));
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

initSmartSelect({
    root: document.getElementById('brandSmartSelect'),
    input: document.getElementById('brand'),
    hidden: null,
    items: BRAND_LIST,
    entity: 'brand',
    createUrl: '<?= site_url('admin/product/quick_create_brand'); ?>',
    modalTitle: 'Add New Brand',
    placeholder: 'e.g. Nivea'
});

initSmartSelect({
    root: document.getElementById('categorySmartSelect'),
    input: document.getElementById('category_search'),
    hidden: document.getElementById('category_id'),
    items: CATEGORY_LIST,
    entity: 'category',
    createUrl: '<?= site_url('admin/product/quick_create_category'); ?>',
    modalTitle: 'Add New Category',
    placeholder: 'e.g. Skincare'
});

/* ── Product Variations ───────────────────────────────────── */
const VARIATION_TYPES = ['Color', 'Size', 'Shade', 'Volume', 'Material', 'Bundle', 'Scent', 'Hair Type', 'Skin Type', 'Pattern', 'Fabric', 'Weight'];
const VARIATION_BRANCHES = <?= json_encode(array_map(fn($b) => [
    'id' => (int) $b['branch_id'],
    'label' => explode(' ', trim($b['branch_name']))[0] . ' Stock',
], $branches)); ?>;
const variationTypesContainer = document.getElementById('variationTypesContainer');
const addVariationTypeBtn = document.getElementById('addVariationTypeBtn');
const variationTypeDropdown = document.getElementById('variationTypeDropdown');

function addedVariationTypes() {
    return Array.from(variationTypesContainer.querySelectorAll('.variation-type-block')).map(b => b.dataset.type);
}

function commitCustomVariationType(input) {
    const added = addedVariationTypes();
    const type = input.value.trim();
    if (!type) return;
    if (added.some(t => t.toLowerCase() === type.toLowerCase())) {
        input.style.borderColor = 'var(--danger, #dc3545)';
        return;
    }
    addVariationTypeBlock(type);
    variationTypeDropdown.hidden = true;
}

addVariationTypeBtn.addEventListener('click', () => {
    const added = addedVariationTypes();
    const available = VARIATION_TYPES.filter(t => !added.includes(t));
    variationTypeDropdown.innerHTML = '';

    const customRow = document.createElement('div');
    customRow.style.cssText = 'display:flex;gap:6px;padding:8px;border-bottom:1px solid var(--border);';
    customRow.innerHTML =
        '<input type="text" class="form-control form-control-sm" placeholder="Type a custom name…" maxlength="50">' +
        '<button type="button" class="btn btn-primary btn-sm" style="flex-shrink:0;">Add</button>';
    const customInput = customRow.querySelector('input');
    const customBtn = customRow.querySelector('button');
    customBtn.addEventListener('mousedown', (e) => { e.preventDefault(); commitCustomVariationType(customInput); });
    customInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') { e.preventDefault(); commitCustomVariationType(customInput); }
    });
    customInput.addEventListener('click', (e) => e.stopPropagation());
    variationTypeDropdown.appendChild(customRow);

    if (!available.length) {
        const empty = document.createElement('div');
        empty.className = 'smart-select-empty';
        empty.textContent = 'All preset types added — type a custom name above.';
        variationTypeDropdown.appendChild(empty);
    } else {
        available.forEach(type => {
            const opt = document.createElement('div');
            opt.className = 'smart-select-option';
            opt.textContent = type;
            opt.addEventListener('mousedown', (e) => { e.preventDefault(); addVariationTypeBlock(type); variationTypeDropdown.hidden = true; });
            variationTypeDropdown.appendChild(opt);
        });
    }
    variationTypeDropdown.hidden = !variationTypeDropdown.hidden;
    if (!variationTypeDropdown.hidden) customInput.focus();
});
document.addEventListener('click', (e) => {
    if (!document.getElementById('variationTypeSelect').contains(e.target)) variationTypeDropdown.hidden = true;
});

function addVariationTypeBlock(type, values) {
    const block = document.createElement('div');
    block.className = 'variation-type-block';
    block.dataset.type = type;

    const branchHeaders = VARIATION_BRANCHES.map(b => '<th>' + escHtml(b.label) + '</th>').join('');

    block.innerHTML =
        '<div class="variation-type-header">' +
            '<h5><i class="fas fa-tag me-1"></i>' + escHtml(type) + '</h5>' +
            '<button type="button" class="remove-type-btn"><i class="fas fa-trash"></i> Remove Type</button>' +
        '</div>' +
        '<div class="table-responsive">' +
            '<table class="table table-sm variation-values-table">' +
                '<thead><tr>' +
                    '<th>Value</th>' + branchHeaders + '<th>Price Adjustment</th><th class="text-center">Remove</th>' +
                '</tr></thead>' +
                '<tbody class="variation-values"></tbody>' +
            '</table>' +
        '</div>' +
        '<button type="button" class="btn btn-outline-secondary add-variation-value-btn"><i class="fas fa-plus"></i> Add ' + escHtml(type) + ' Value</button>';

    block.querySelector('.remove-type-btn').addEventListener('click', () => { block.remove(); syncVariationsJson(); });
    block.querySelector('.add-variation-value-btn').addEventListener('click', () => addVariationValueRow(block));

    variationTypesContainer.appendChild(block);

    if (values && values.length) {
        values.forEach(v => addVariationValueRow(block, v));
    } else {
        addVariationValueRow(block);
    }
}

function addVariationValueRow(block, value) {
    const row = document.createElement('tr');
    row.className = 'variation-value-row';

    // value.branch_stock (when present) is the real per-branch breakdown for
    // this variation, loaded from inventory_batches by the controller.
    const branchCells = VARIATION_BRANCHES.map(b => {
        const seed = (value && value.branch_stock) ? (parseInt(value.branch_stock[b.id], 10) || 0) : 0;
        return '<td><input type="number" min="0" class="form-control form-control-sm variation-branch-stock-input" data-branch-id="' + b.id + '" placeholder="Stock" value="' + seed + '"></td>';
    }).join('');

    row.innerHTML =
        '<td><input type="text" class="form-control form-control-sm variation-value-input" placeholder="Value (e.g. Red)" value="' + escHtml(value ? value.variation_value : '') + '"></td>' +
        branchCells +
        '<td><input type="number" step="0.01" class="form-control form-control-sm variation-price-input" placeholder="+/- Price" value="' + (value ? parseFloat(value.price_adjustment) : 0) + '"></td>' +
        '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-value-btn" title="Remove"><i class="fas fa-trash"></i></button></td>';

    row.querySelectorAll('input').forEach(inp => inp.addEventListener('input', syncVariationsJson));
    row.querySelector('.remove-value-btn').addEventListener('click', () => { row.remove(); syncVariationsJson(); });

    block.querySelector('.variation-values').appendChild(row);
    syncVariationsJson();
}

function syncVariationsJson() {
    const variations = [];
    variationTypesContainer.querySelectorAll('.variation-type-block').forEach(block => {
        const type = block.dataset.type;
        block.querySelectorAll('.variation-value-row').forEach(row => {
            const value = row.querySelector('.variation-value-input').value.trim();
            if (!value) return;
            const branchStock = {};
            let totalStock = 0;
            row.querySelectorAll('.variation-branch-stock-input').forEach(inp => {
                const qty = parseInt(inp.value, 10) || 0;
                branchStock[inp.dataset.branchId] = qty;
                totalStock += qty;
            });
            variations.push({
                type: type,
                value: value,
                stock: totalStock,
                branch_stock: branchStock,
                price_adjustment: parseFloat(row.querySelector('.variation-price-input').value) || 0,
            });
        });
    });
    document.getElementById('variationsJson').value = JSON.stringify(variations);
    updateTotalStock();
}

/* ── Pre-populate existing variations, grouped by type ────── */
(function initExistingVariations() {
    const existing = <?= json_encode($variations ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const byType = {};
    existing.forEach(v => {
        if (!byType[v.variation_type]) byType[v.variation_type] = [];
        byType[v.variation_type].push(v);
    });
    Object.keys(byType).forEach(type => addVariationTypeBlock(type, byType[type]));
})();

/* ── Form submit ─────────────────────────────────────────── */
document.getElementById('editProductForm').addEventListener('submit', function() {
    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
});
</script>
