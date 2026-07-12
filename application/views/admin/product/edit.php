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
    position: absolute; top: calc(100% + 6px); left: 0; right: 0; z-index: 400;
    background: #fff; opacity: 1; border: 1px solid var(--border); border-radius: var(--radius-md);
    box-shadow: 0 8px 24px rgba(15, 23, 42, .18); max-height: 220px; overflow-y: auto; overflow-x: hidden; padding: 6px;
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
.variation-type-group-row td {
    background: #fafbff; border-top: 2px solid var(--border); padding: 10px 12px; vertical-align: middle;
}
.variation-type-group-row .variation-type-label { font-weight: 600; font-size: .88rem; color: var(--primary-pink-dark); margin-right: 12px; }
.variation-type-group-row .add-variation-value-btn { font-size: .78rem; padding: 4px 10px; }
.variation-type-group-row .remove-type-btn { background: none; border: none; color: var(--danger); cursor: pointer; font-size: .82rem; float: right; }
.variation-values-table { margin-bottom: 10px; }
.variation-values-table th {
    font-size: .72rem; color: var(--gray); font-weight: 600; text-transform: uppercase; letter-spacing: .03em;
    border-top: none;
}
.variation-values-table th.text-center, .variation-values-table td.text-center { text-align: center; }
.variation-values-table td { vertical-align: middle; }
.variation-value-row input { font-size: .85rem; }
.variant-combinations-table input, .variant-combinations-table select { font-size: .8rem; min-width: 90px; }
.variant-combinations-table td { vertical-align: middle; }

@media (max-width: 576px) {
    /* Generated Variant Combinations: rows become stacked cards instead of
       a horizontally-scrolled table. */
    .variant-combinations-table thead { display: none; }
    .variant-combinations-table, .variant-combinations-table tbody, .variant-combinations-table tr, .variant-combinations-table td {
        display: block; width: 100%;
    }
    .variant-combinations-table tr {
        border: 1px solid var(--border); border-radius: 10px; margin-bottom: 12px; padding: 10px;
    }
    .variant-combinations-table td {
        display: flex; align-items: center; justify-content: space-between; gap: 10px;
        border: none; padding: 6px 0;
    }
    .variant-combinations-table td[data-label]::before {
        content: attr(data-label); font-weight: 600; font-size: .72rem; color: var(--gray); text-transform: uppercase; flex-shrink: 0;
    }
    .variant-combinations-table td input, .variant-combinations-table td select { max-width: 60%; }
}

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
                        <div style="background:#f0f4ff;border:1px solid #c7d2fe;border-radius:10px;padding:8px 14px;margin-bottom:16px;font-size:.82rem;color:var(--text);display:flex;gap:8px;align-items:center;">
                            <i class="fas fa-circle-info" style="color:var(--primary-pink);"></i>
                            <div>Numbers below are the <strong>current stock</strong> remaining per branch. Editing only adjusts the difference — existing stock history is preserved.</div>
                        </div>

                        <p class="text-muted" style="margin-top:0;font-size:.85rem;">Add at least one Variation Type (e.g. Shade, Finish — up to 2), give it a Value, then generate the combination(s) below to enter stock per branch.</p>

                        <div class="table-responsive">
                            <table class="table table-sm variation-values-table" id="variationTypesContainer">
                                <thead>
                                    <tr>
                                        <th>Value</th><th>Default Price Adj.</th><th>Default Status</th><th class="text-center">Smart Apply</th><th class="text-center">Remove</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                        <div class="smart-select" id="variationTypeSelect" style="max-width:280px;position:relative;">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="addVariationTypeBtn">
                                <i class="fas fa-plus"></i> Add Variation Type
                            </button>
                            <div class="smart-select-dropdown" id="variationTypeDropdown" hidden style="position:absolute;top:calc(100% + 4px);left:0;min-width:240px;"></div>
                        </div>

                        <div class="field-feedback invalid" id="variationTypeCapError" style="display:none;margin-top:10px;">
                            <i class="fas fa-exclamation-circle"></i> Only 2 variation types are supported per product (e.g. Shade × Finish).
                        </div>

                        <div style="margin-top:16px;" id="generateCombinationsWrap" hidden>
                            <button type="button" class="btn btn-primary btn-sm" id="generateCombinationsBtn"><i class="fas fa-cogs"></i> Generate Variant Combinations</button>
                            <span class="text-muted" style="font-size:.8rem;margin-left:8px;">Regenerates automatically whenever you add, remove, or edit a value.</span>
                        </div>

                        <!-- Generated Variant Combinations -->
                        <div class="card" id="combinationsCard" style="margin-top:20px;box-shadow:none;border:1px solid var(--border);" hidden>
                            <div class="card-body">
                                <div class="page-section" style="margin-top:0;">
                                    <span class="section-title"><i class="fas fa-th-list me-2"></i>Generated Variant Combinations</span>
                                    <hr>
                                </div>

                                <div class="d-flex flex-wrap align-items-center gap-2" style="margin-bottom:14px;">
                                    <div class="form-check" style="margin-right:8px;">
                                        <input type="checkbox" class="form-check-input" id="selectAllVariants">
                                        <label class="form-check-label" for="selectAllVariants">Select All</label>
                                    </div>
                                    <select class="form-control form-control-sm" id="bulkActionSelect" style="max-width:200px;">
                                        <option value="">Bulk Action…</option>
                                        <option value="stock">Apply Stock</option>
                                        <option value="price">Apply Price Adjustment</option>
                                        <option value="status">Apply Status</option>
                                        <option value="image">Apply Image</option>
                                        <option value="delete">Delete Selected</option>
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="bulkApplyBtn">Apply</button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm variant-combinations-table" id="combinationsTable">
                                        <thead><tr id="combinationsHeaderRow"></tr></thead>
                                        <tbody id="combinationsBody"></tbody>
                                    </table>
                                </div>
                            </div>
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
                            <small style="color: var(--gray); display:block; margin-top:8px;">Changing a branch quantity creates a stock adjustment batch for that branch (FIFO) — only the difference is applied, existing history is preserved.</small>
                        </div>

                        <input type="hidden" name="variations_json" id="variationsJson" value="">
                        <input type="hidden" name="combinations_json" id="combinationsJson" value="">
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
    // Combination rows are added/removed dynamically, so query fresh each
    // time rather than caching a NodeList at page load.
    const branchTotals = {};
    let total = 0;
    document.querySelectorAll('.combo-branch-stock-input').forEach(inp => {
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

/* ── Product Variations & Branch Stock ──────────────────────── */
const VARIATION_TYPES = ['Color', 'Size', 'Shade', 'Volume', 'Material', 'Bundle', 'Scent', 'Hair Type', 'Skin Type', 'Pattern', 'Fabric', 'Weight'];
const VARIATION_BRANCHES = <?= json_encode(array_map(fn($b) => [
    'id' => (int) $b['branch_id'],
    'label' => explode(' ', trim($b['branch_name']))[0] . ' Stock',
], $branches)); ?>;
const MAX_VARIATION_TYPES = 2;

const variationTypesContainer = document.getElementById('variationTypesContainer');
const addVariationTypeBtn = document.getElementById('addVariationTypeBtn');
const variationTypeDropdown = document.getElementById('variationTypeDropdown');
const generateCombinationsWrap = document.getElementById('generateCombinationsWrap');
const combinationsCard = document.getElementById('combinationsCard');
const combinationsBody = document.getElementById('combinationsBody');
const combinationsHeaderRow = document.getElementById('combinationsHeaderRow');
const variationTypeCapError = document.getElementById('variationTypeCapError');

let combinationRowSeq = 0;
// key ("Type:value|Type:value") -> row data, preserved across regenerations
// so entered SKU/barcode/price/status/stock survive edits to other values.
let combinationRows = {};
// Suppressed while pre-populating from server data so intermediate
// single-axis states (built one type block at a time) don't wipe the
// already-seeded two-axis combination rows before the second type exists.
let suppressCombinationRegen = false;

/* ── Section 1 + 2: Variation Types & Values (+ optional defaults) ── */
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
    if (!tryAddVariationTypeBlock(type)) return;
    variationTypeDropdown.hidden = true;
}

addVariationTypeBtn.addEventListener('click', () => {
    const added = addedVariationTypes();
    const available = VARIATION_TYPES.filter(t => !added.includes(t));
    variationTypeDropdown.innerHTML = '';

    const customRow = document.createElement('div');
    customRow.style.cssText = 'display:flex;gap:6px;padding:8px;border-bottom:1px solid var(--border);';
    customRow.innerHTML =
        '<input type="text" class="form-control form-control-sm" placeholder="Custom name…" maxlength="50" style="flex:1 1 auto;min-width:0;width:auto;">' +
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
            opt.addEventListener('mousedown', (e) => { e.preventDefault(); tryAddVariationTypeBlock(type); variationTypeDropdown.hidden = true; });
            variationTypeDropdown.appendChild(opt);
        });
    }
    variationTypeDropdown.hidden = !variationTypeDropdown.hidden;
    if (!variationTypeDropdown.hidden) customInput.focus();
});
document.addEventListener('click', (e) => {
    if (!document.getElementById('variationTypeSelect').contains(e.target)) variationTypeDropdown.hidden = true;
});

function tryAddVariationTypeBlock(type, values) {
    if (addedVariationTypes().length >= MAX_VARIATION_TYPES) {
        variationTypeCapError.style.display = 'block';
        return false;
    }
    variationTypeCapError.style.display = 'none';
    addVariationTypeBlock(type, values);
    return true;
}

function addVariationTypeBlock(type, values) {
    const block = document.createElement('tbody');
    block.className = 'variation-type-block';
    block.dataset.type = type;

    block.innerHTML =
        '<tr class="variation-type-group-row">' +
            '<td colspan="5">' +
                '<span class="variation-type-label"><i class="fas fa-tag me-1"></i>' + escHtml(type) + '</span>' +
                '<button type="button" class="btn btn-sm btn-outline-secondary add-variation-value-btn"><i class="fas fa-plus"></i> Add ' + escHtml(type) + ' Value</button>' +
                '<button type="button" class="remove-type-btn"><i class="fas fa-trash"></i> Remove Type</button>' +
            '</td>' +
        '</tr>';

    block.querySelector('.remove-type-btn').addEventListener('click', () => { block.remove(); onVariationStructureChanged(); });
    block.querySelector('.add-variation-value-btn').addEventListener('click', () => addVariationValueRow(block));

    variationTypesContainer.appendChild(block);
    updateGenerateButtonVisibility();

    if (values && values.length) {
        values.forEach(v => addVariationValueRow(block, v));
    } else {
        addVariationValueRow(block);
    }
}

function addVariationValueRow(block, value) {
    const row = document.createElement('tr');
    row.className = 'variation-value-row';

    row.innerHTML =
        '<td><input type="text" class="form-control form-control-sm variation-value-input" placeholder="Value (e.g. Red)" value="' + escHtml(value ? value.variation_value : '') + '"></td>' +
        '<td><input type="number" step="0.01" class="form-control form-control-sm variation-default-price-input" placeholder="+/- Price" value="' + (value ? parseFloat(value.price_adjustment) : 0) + '"></td>' +
        '<td><select class="form-control form-control-sm variation-default-status-select">' +
            '<option value="active"' + (!value || value.status !== 'inactive' ? ' selected' : '') + '>Active</option>' +
            '<option value="inactive"' + (value && value.status === 'inactive' ? ' selected' : '') + '>Inactive</option>' +
        '</select></td>' +
        '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-primary smart-apply-btn" title="Apply Stock/Price/Status to every combination with this value"><i class="fas fa-bolt"></i></button></td>' +
        '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-value-btn" title="Remove"><i class="fas fa-trash"></i></button></td>';

    row.querySelectorAll('input, select').forEach(inp => inp.addEventListener('input', onVariationStructureChanged));
    row.querySelector('.remove-value-btn').addEventListener('click', () => {
        const valueName = row.querySelector('.variation-value-input').value.trim();
        if (valueName && valueHasStock(block.dataset.type, valueName)) {
            if (!confirm('This value has stock in one or more generated combinations. Removing it will remove those combinations too. Continue?')) return;
        }
        row.remove();
        onVariationStructureChanged();
    });
    row.querySelector('.smart-apply-btn').addEventListener('click', () => {
        const valueName = row.querySelector('.variation-value-input').value.trim();
        if (!valueName) { alert('Enter a value name first.'); return; }
        openApplyModal({ scope: 'value', type: block.dataset.type, value: valueName });
    });

    block.appendChild(row);
    onVariationStructureChanged();
}

function valueHasStock(type, value) {
    return Object.values(combinationRows).some(c => {
        const matches = (c.type_1 === type && c.value_1 === value) || (c.type_2 === type && c.value_2 === value);
        if (!matches) return false;
        return Object.values(c.branch_stock || {}).some(q => (parseInt(q, 10) || 0) > 0);
    });
}

function onVariationStructureChanged() {
    updateGenerateButtonVisibility();
    syncVariationsJson();
    if (!suppressCombinationRegen) generateCombinations();
}

function updateGenerateButtonVisibility() {
    generateCombinationsWrap.hidden = addedVariationTypes().length === 0;
}

function syncVariationsJson() {
    const variations = [];
    variationTypesContainer.querySelectorAll('.variation-type-block').forEach(block => {
        const type = block.dataset.type;
        block.querySelectorAll('.variation-value-row').forEach(row => {
            const value = row.querySelector('.variation-value-input').value.trim();
            if (!value) return;
            variations.push({
                type: type,
                value: value,
                default_price_adjustment: parseFloat(row.querySelector('.variation-default-price-input').value) || 0,
                default_status: row.querySelector('.variation-default-status-select').value,
            });
        });
    });
    document.getElementById('variationsJson').value = JSON.stringify(variations);
}

/* ── Section 3: Generate Variant Combinations (Cartesian product) ── */
document.getElementById('generateCombinationsBtn').addEventListener('click', generateCombinations);

function currentTypeValueLists() {
    const blocks = Array.from(variationTypesContainer.querySelectorAll('.variation-type-block'));
    return blocks.map(block => {
        const type = block.dataset.type;
        const values = Array.from(block.querySelectorAll('.variation-value-row')).map(row => ({
            value: row.querySelector('.variation-value-input').value.trim(),
            price_adjustment: parseFloat(row.querySelector('.variation-default-price-input').value) || 0,
            status: row.querySelector('.variation-default-status-select').value,
        })).filter(v => v.value);
        return { type: type, values: values };
    }).filter(t => t.values.length);
}

// Merges a freshly-computed default combination into any existing row at
// that key. sku/barcode/branch_stock always carry over from the existing
// row (per-combination data, never derived from a default). price_adjustment
// and status only carry over from the existing row if the admin has
// explicitly edited that specific field in the Generated Combinations table
// (or via bulk/Smart Apply) — otherwise they refresh to the current default
// sum, so typing into a value's Default Price Adj. actually flows through
// until the admin manually overrides a specific combination's price/status.
function mergeCombination(key, base) {
    const existing = combinationRows[key];
    const merged = Object.assign({}, base, existing || {});
    if (!existing || !existing.price_manually_set) merged.price_adjustment = base.price_adjustment;
    if (!existing || !existing.status_manually_set) merged.status = base.status;
    merged.price_manually_set = existing ? !!existing.price_manually_set : false;
    merged.status_manually_set = existing ? !!existing.status_manually_set : false;
    merged.row_key = existing ? existing.row_key : 'new_' + (++combinationRowSeq);
    combinationRows[key] = merged;
}

function generateCombinations() {
    const axes = currentTypeValueLists();
    const newKeys = new Set();

    if (axes.length === 1) {
        axes[0].values.forEach(v1 => {
            const key = axes[0].type + ':' + v1.value + '|';
            newKeys.add(key);
            mergeCombination(key, {
                type_1: axes[0].type, value_1: v1.value, type_2: '', value_2: '',
                sku: '', barcode: '', price_adjustment: v1.price_adjustment, status: v1.status,
                branch_stock: {},
            });
        });
    } else if (axes.length >= 2) {
        const [a1, a2] = axes;
        a1.values.forEach(v1 => {
            a2.values.forEach(v2 => {
                const key = a1.type + ':' + v1.value + '|' + a2.type + ':' + v2.value;
                newKeys.add(key);
                mergeCombination(key, {
                    type_1: a1.type, value_1: v1.value, type_2: a2.type, value_2: v2.value,
                    sku: '', barcode: '', price_adjustment: v1.price_adjustment + v2.price_adjustment, status: 'active',
                    branch_stock: {},
                });
            });
        });
    }

    // Drop combinations whose value pair no longer exists.
    Object.keys(combinationRows).forEach(key => {
        if (!newKeys.has(key)) delete combinationRows[key];
    });

    renderCombinationsTable();
}

/* ── Section 4: Generated Variant Combinations table ────────── */
function renderCombinationsTable() {
    const keys = Object.keys(combinationRows);
    combinationsCard.hidden = keys.length === 0;
    if (!keys.length) {
        combinationsBody.innerHTML = '';
        updateTotalStock();
        return;
    }

    combinationsHeaderRow.innerHTML =
        '<th></th><th>Variant Combination</th><th>Image</th>' +
        VARIATION_BRANCHES.map(b => '<th>' + escHtml(b.label) + '</th>').join('') +
        '<th>Price Adj.</th><th>Status</th><th class="text-center">Actions</th>';

    combinationsBody.innerHTML = '';
    keys.forEach(key => {
        const c = combinationRows[key];
        const label = c.type_2 ? (c.value_1 + ' / ' + c.value_2) : c.value_1;
        const branchCells = VARIATION_BRANCHES.map(b =>
            '<td data-label="' + escHtml(b.label) + '"><input type="number" min="0" class="form-control form-control-sm combo-branch-stock-input" data-branch-id="' + b.id + '" value="' + (parseInt(c.branch_stock[b.id], 10) || 0) + '"></td>'
        ).join('');

        const tr = document.createElement('tr');
        tr.className = 'combination-row';
        tr.dataset.key = key;
        tr.innerHTML =
            '<td data-label="Select"><input type="checkbox" class="combo-select-checkbox"></td>' +
            '<td data-label="Variant Combination"><strong>' + escHtml(label) + '</strong></td>' +
            '<td data-label="Image">' +
                (c.image_url ? '<img src="' + escHtml(c.image_url) + '" style="width:36px;height:36px;object-fit:cover;border-radius:6px;display:block;margin-bottom:4px;">' : '') +
                '<input type="file" accept="image/*" class="form-control form-control-sm combo-image-input" name="variant_image[' + escHtml(c.row_key) + ']">' +
            '</td>' +
            branchCells +
            '<td data-label="Price Adj."><input type="number" step="0.01" class="form-control form-control-sm combo-price-input" value="' + (parseFloat(c.price_adjustment) || 0) + '"></td>' +
            '<td data-label="Status"><select class="form-control form-control-sm combo-status-select">' +
                '<option value="active"' + (c.status !== 'inactive' ? ' selected' : '') + '>Active</option>' +
                '<option value="inactive"' + (c.status === 'inactive' ? ' selected' : '') + '>Inactive</option>' +
            '</select></td>' +
            '<td data-label="Actions" class="text-center"><button type="button" class="btn btn-sm btn-outline-danger combo-delete-btn" title="Delete"><i class="fas fa-trash"></i></button></td>';

        tr.querySelectorAll('input, select').forEach(inp => {
            if (inp.type === 'file') return;
            const evtName = inp.tagName === 'SELECT' ? 'change' : 'input';
            inp.addEventListener(evtName, (e) => {
                if (e.target.classList.contains('combo-price-input')) combinationRows[key].price_manually_set = true;
                if (e.target.classList.contains('combo-status-select')) combinationRows[key].status_manually_set = true;
                syncCombinationRowFromDom(tr, key);
            });
        });
        tr.querySelector('.combo-delete-btn').addEventListener('click', () => {
            const stock = Object.values(c.branch_stock || {}).reduce((s, q) => s + (parseInt(q, 10) || 0), 0);
            if (stock > 0 && !confirm('This combination still has ' + stock + ' unit(s) of stock. Delete it anyway?')) return;
            delete combinationRows[key];
            tr.remove();
            syncCombinationsJson();
        });

        combinationsBody.appendChild(tr);
    });

    syncCombinationsJson();
}

function syncCombinationRowFromDom(tr, key) {
    const c = combinationRows[key];
    if (!c) return;
    c.price_adjustment = parseFloat(tr.querySelector('.combo-price-input').value) || 0;
    c.status = tr.querySelector('.combo-status-select').value;
    const branchStock = {};
    tr.querySelectorAll('.combo-branch-stock-input').forEach(inp => {
        branchStock[inp.dataset.branchId] = parseInt(inp.value, 10) || 0;
    });
    c.branch_stock = branchStock;
    syncCombinationsJson();
}

function syncCombinationsJson() {
    const combos = Object.values(combinationRows).map(c => ({
        type_1: c.type_1, value_1: c.value_1, type_2: c.type_2, value_2: c.value_2,
        sku: c.sku, barcode: c.barcode, price_adjustment: c.price_adjustment, status: c.status,
        branch_stock: c.branch_stock, row_key: c.row_key,
    }));
    document.getElementById('combinationsJson').value = JSON.stringify(combos);
    updateTotalStock();
}

/* ── Section 5 + 6: Bulk actions & per-value Smart Apply ────── */
document.getElementById('selectAllVariants').addEventListener('change', function() {
    combinationsBody.querySelectorAll('.combo-select-checkbox').forEach(cb => cb.checked = this.checked);
});

document.getElementById('bulkApplyBtn').addEventListener('click', function() {
    const action = document.getElementById('bulkActionSelect').value;
    if (!action) { alert('Choose a bulk action first.'); return; }
    const selectedRows = Array.from(combinationsBody.querySelectorAll('.combination-row'))
        .filter(tr => tr.querySelector('.combo-select-checkbox').checked);
    if (!selectedRows.length) { alert('Select at least one combination first.'); return; }
    openApplyModal({ scope: 'bulk', action: action, rows: selectedRows });
});

function openApplyModal(opts) {
    const isSmart = opts.scope === 'value';
    const action = isSmart ? null : opts.action;
    const branchStockFields = () => VARIATION_BRANCHES.map(b =>
        '<div class="d-flex align-items-center gap-2" style="margin-bottom:6px;"><span style="min-width:110px;">' + escHtml(b.label) + '</span><input type="number" min="0" class="form-control form-control-sm apply-modal-branch-stock" data-branch-id="' + b.id + '"></div>'
    ).join('');
    let title, bodyHtml;

    if (isSmart) {
        title = 'Smart Apply — ' + opts.value;
        bodyHtml =
            '<p class="text-muted" style="font-size:.85rem;">Applies to every combination containing "' + escHtml(opts.value) + '" without affecting other values.</p>' +
            '<div class="form-group"><label>Price Adjustment (leave blank for no change)</label><input type="number" step="0.01" class="form-control form-control-sm" id="applyModalPrice"></div>' +
            '<div class="form-group"><label>Status</label><select class="form-control form-control-sm" id="applyModalStatus"><option value="">(no change)</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>' +
            '<div class="form-group"><label>Branch Stock (leave blank for no change)</label>' + branchStockFields() + '</div>';
    } else if (action === 'delete') {
        title = 'Delete Selected Combinations';
        bodyHtml = '<p>Delete ' + opts.rows.length + ' selected combination(s)? This cannot be undone once saved.</p>';
    } else if (action === 'stock') {
        title = 'Apply Stock';
        bodyHtml = '<div class="form-group"><label>Branch Stock</label>' + branchStockFields() + '</div>';
    } else if (action === 'price') {
        title = 'Apply Price Adjustment';
        bodyHtml = '<div class="form-group"><label>Price Adjustment</label><input type="number" step="0.01" class="form-control form-control-sm" id="applyModalPrice"></div>';
    } else if (action === 'status') {
        title = 'Apply Status';
        bodyHtml = '<div class="form-group"><label>Status</label><select class="form-control form-control-sm" id="applyModalStatus"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>';
    } else if (action === 'image') {
        title = 'Apply Image';
        bodyHtml = '<p class="text-muted" style="font-size:.85rem;">Browsers don\'t allow reusing one selected file across multiple file inputs — select the image individually in each row\'s Image column instead.</p>';
    }

    showModal(title, bodyHtml, () => {
        if (isSmart) {
            applyToRows(rowsMatchingValue(opts.type, opts.value), {
                price: document.getElementById('applyModalPrice').value,
                status: document.getElementById('applyModalStatus').value,
                branchStock: readModalBranchStock(),
            });
        } else if (action === 'delete') {
            opts.rows.forEach(tr => {
                const key = tr.dataset.key;
                const stock = Object.values((combinationRows[key] || {}).branch_stock || {}).reduce((s, q) => s + (parseInt(q, 10) || 0), 0);
                if (stock > 0 && !confirm('One of the selected combinations still has stock. Delete it anyway?')) return;
                delete combinationRows[key];
                tr.remove();
            });
            syncCombinationsJson();
        } else if (action === 'stock') {
            applyToRows(opts.rows, { branchStock: readModalBranchStock() });
        } else if (action === 'price') {
            applyToRows(opts.rows, { price: document.getElementById('applyModalPrice').value });
        } else if (action === 'status') {
            applyToRows(opts.rows, { status: document.getElementById('applyModalStatus').value });
        }
    });
}

function rowsMatchingValue(type, value) {
    return Array.from(combinationsBody.querySelectorAll('.combination-row')).filter(tr => {
        const c = combinationRows[tr.dataset.key];
        return c && ((c.type_1 === type && c.value_1 === value) || (c.type_2 === type && c.value_2 === value));
    });
}

function readModalBranchStock() {
    const out = {};
    document.querySelectorAll('.apply-modal-branch-stock').forEach(inp => {
        if (inp.value !== '') out[inp.dataset.branchId] = parseInt(inp.value, 10) || 0;
    });
    return out;
}

function applyToRows(rows, changes) {
    rows.forEach(tr => {
        const key = tr.dataset.key;
        const c = combinationRows[key];
        if (!c) return;
        if (changes.price !== undefined && changes.price !== '') {
            c.price_adjustment = parseFloat(changes.price) || 0;
            c.price_manually_set = true;
            const inp = tr.querySelector('.combo-price-input');
            if (inp) inp.value = c.price_adjustment;
        }
        if (changes.status) {
            c.status = changes.status;
            c.status_manually_set = true;
            const sel = tr.querySelector('.combo-status-select');
            if (sel) sel.value = c.status;
        }
        if (changes.branchStock) {
            Object.keys(changes.branchStock).forEach(bId => {
                c.branch_stock[bId] = changes.branchStock[bId];
                const inp = tr.querySelector('.combo-branch-stock-input[data-branch-id="' + bId + '"]');
                if (inp) inp.value = c.branch_stock[bId];
            });
        }
    });
    syncCombinationsJson();
}

/* Small reusable modal, matching the wizard's existing modal look (no new design system). */
function showModal(title, bodyHtml, onConfirm) {
    let modal = document.getElementById('variantApplyModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'variantApplyModal';
        modal.style.cssText = 'display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;';
        modal.innerHTML =
            '<div style="background:#fff;border-radius:12px;max-width:480px;width:92%;padding:24px;box-shadow:0 10px 40px rgba(0,0,0,.2);max-height:85vh;overflow-y:auto;">' +
                '<h5 style="margin-bottom:14px;" id="variantApplyModalTitle"></h5>' +
                '<div id="variantApplyModalBody"></div>' +
                '<div style="display:flex;gap:10px;justify-content:flex-end;margin-top:18px;">' +
                    '<button type="button" class="btn btn-outline-secondary btn-sm" id="variantApplyModalCancel">Cancel</button>' +
                    '<button type="button" class="btn btn-primary btn-sm" id="variantApplyModalConfirm">Apply</button>' +
                '</div>' +
            '</div>';
        document.body.appendChild(modal);
    }
    modal.querySelector('#variantApplyModalTitle').textContent = title;
    modal.querySelector('#variantApplyModalBody').innerHTML = bodyHtml;
    modal.style.display = 'flex';

    const close = () => { modal.style.display = 'none'; };
    modal.querySelector('#variantApplyModalCancel').onclick = close;
    modal.querySelector('#variantApplyModalConfirm').onclick = () => { onConfirm(); close(); };
    modal.onclick = (e) => { if (e.target === modal) close(); };
}

/* ── Pre-populate existing variation types/values + generated
     combinations (with their real per-branch stock) from the server ── */
(function initExistingVariations() {
    const existingCombos = <?= json_encode($combinations ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    existingCombos.forEach(c => {
        const key = c.type_1 + ':' + c.value_1 + '|' + (c.type_2 ? c.type_2 + ':' + c.value_2 : '');
        combinationRows[key] = {
            type_1: c.type_1, value_1: c.value_1, type_2: c.type_2 || '', value_2: c.value_2 || '',
            sku: c.sku || '', barcode: c.barcode || '', price_adjustment: parseFloat(c.price_adjustment) || 0,
            status: c.status || 'active', branch_stock: c.branch_stock || {},
            row_key: 'existing_' + c.variant_id,
            // Already-saved real values, not freshly-generated defaults — so
            // editing a value's default price/status later won't silently
            // overwrite this combination's own price/status.
            price_manually_set: true, status_manually_set: true,
        };
    });

    const existingValues = <?= json_encode($variations ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const byType = {};
    existingValues.forEach(v => {
        if (!byType[v.variation_type]) byType[v.variation_type] = [];
        byType[v.variation_type].push(v);
    });

    suppressCombinationRegen = true;
    Object.keys(byType).forEach(type => addVariationTypeBlock(type, byType[type]));
    suppressCombinationRegen = false;

    generateCombinations();
})();

/* ── Form submit ─────────────────────────────────────────── */
document.getElementById('editProductForm').addEventListener('submit', function() {
    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
});
</script>
