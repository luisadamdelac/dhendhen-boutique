<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
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

/* ── Wizard shell & header ────────────────────────────────── */
.wizard-header {
    display: flex; flex-wrap: wrap; align-items: center;
    justify-content: space-between; gap: 12px;
}
.wizard-step-label { color: var(--gray); font-size: .9rem; margin: 2px 0 0; }
.wizard-step-label strong { color: var(--primary-pink-dark); }

/* ── Progress indicator ──────────────────────────────────── */
.wizard-progress {
    display: flex; align-items: flex-start; justify-content: space-between;
    margin: 22px 0 26px; gap: 4px;
}
.wizard-progress-step {
    display: flex; flex-direction: column; align-items: center; gap: 8px;
    flex: 0 0 auto; position: relative; z-index: 2; width: 90px;
}
.wizard-progress-step .step-circle {
    width: 38px; height: 38px; border-radius: 50%;
    background: #fff; border: 2px solid var(--border); color: var(--gray);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .9rem; transition: all .25s ease;
}
.wizard-progress-step.active .step-circle {
    background: var(--gradient-primary); border-color: transparent; color: #fff;
    box-shadow: 0 4px 14px rgba(238,130,238,.4);
}
.wizard-progress-step.completed .step-circle {
    background: var(--primary-pink); border-color: transparent; color: #fff;
}
.wizard-progress-step .step-label {
    font-size: .72rem; color: var(--gray); font-weight: 600;
    text-align: center; white-space: nowrap;
}
.wizard-progress-step.active .step-label,
.wizard-progress-step.completed .step-label { color: var(--primary-pink-dark); }
.wizard-progress-line {
    flex: 1; height: 3px; background: var(--border);
    margin-top: 19px; border-radius: 2px; transition: background .25s ease;
}
.wizard-progress-line.completed { background: var(--gradient-primary); }

/* ── Step transition animation ───────────────────────────── */
@keyframes wizardFadeSlide {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}
.wizard-step { display: block; margin-bottom: 20px; }
.wizard-step.wizard-fade-in { animation: wizardFadeSlide .25s ease; }

/* ── Step action bar ──────────────────────────────────────── */
.wizard-actions {
    display: flex; align-items: center; justify-content: space-between;
    margin-top: 20px; gap: 10px; flex-wrap: wrap;
}

/* ── Inline field validation feedback ────────────────────── */
.field-feedback {
    font-size: .78rem; margin-top: 5px; align-items: center; gap: 5px; display: none;
}
.field-feedback.valid   { display: flex; color: var(--success); }
.field-feedback.invalid { display: flex; color: var(--danger); }
.form-control.is-valid   { border-color: var(--success) !important; }
.form-control.is-invalid { border-color: var(--danger) !important; }

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

/* ── Inventory summary / notice ──────────────────────────── */
.stock-summary {
    display: flex; flex-wrap: wrap; gap: 12px; margin-top: 6px;
}
.stock-summary .stat-tile {
    flex: 1; min-width: 150px; background: #F3F5FF; border: 1px solid #dfe3ff;
    border-radius: 12px; padding: 14px 16px;
}
.stock-summary .stat-tile .stat-lbl {
    font-size: .72rem; color: var(--gray); font-weight: 600;
    text-transform: uppercase; letter-spacing: .03em;
}
.stock-summary .stat-tile .stat-val { font-size: 1.3rem; font-weight: 700; color: var(--text); margin-top: 2px; }
.inventory-notice {
    border-radius: 10px; padding: 12px 14px; font-size: .82rem;
    display: flex; gap: 8px; align-items: flex-start; margin-top: 14px;
}
.inventory-notice.ok   { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
.inventory-notice.warn { background: #fff8e1; color: #8d6e00; border: 1px solid #ffe082; }

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

/* ── Shared quick-create modal ───────────────────────────── */
.ss-modal-overlay {
    position: fixed; inset: 0; background: rgba(15,23,42,.45); z-index: 10000;
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
.ss-modal.ss-modal-wide { max-width: 1100px; max-height: 85vh; overflow-y: auto; }
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
    position: fixed; bottom: 24px; right: 24px; z-index: 10100;
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

/* ── Review step ──────────────────────────────────────────── */
.review-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 24px; }
.review-item { border-bottom: 1px dashed var(--border); padding-bottom: 10px; }
.review-item .rev-lbl {
    font-size: .72rem; color: var(--gray); font-weight: 600;
    text-transform: uppercase; letter-spacing: .03em;
}
.review-item .rev-val { font-size: .95rem; color: var(--text); font-weight: 600; margin-top: 2px; word-break: break-word; }
.review-image-wrap { width: 100%; max-width: 220px; }
.review-image-wrap img {
    width: 100%; aspect-ratio: 1 / 1; object-fit: cover; border-radius: 12px; border: 1px solid var(--border);
}
.review-branch-row {
    display: flex; justify-content: space-between; padding: 6px 0;
    border-bottom: 1px dashed var(--border); font-size: .85rem;
}
@media (max-width: 768px) {
    .review-grid { grid-template-columns: 1fr; }
}

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
.smart-select-dropdown .smart-select-option { padding: 8px 10px; }
.variant-combinations-table input, .variant-combinations-table select { font-size: .8rem; min-width: 90px; }
.variant-combinations-table td { vertical-align: middle; }

/* ── Mobile refinements ───────────────────────────────────── */
@media (max-width: 576px) {
    .wizard-progress-step .step-label { display: none; }
    .wizard-progress-step.active .step-label { display: block; }
    .wizard-header { flex-direction: column; align-items: flex-start; }
    .pricing-stat, .stock-summary .stat-tile { min-width: 100%; }

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
</style>
<div class="container-fluid py-4 fade-in">
    <div class="wizard-header mb-4">
        <div>
            <h2 class="page-title mb-1" style="margin:0;">Create Product</h2>
            <p class="wizard-step-label">Fill in the product details below, then click Create Product.</p>
            <!-- Kept for the submit-time validation JS, which scrolls to and
                 labels the first invalid section — not shown to the admin. -->
            <span style="display:none;">Step <strong id="wizardStepNum">1</strong> of 5 — <span id="wizardStepName">Product Information</span></span>
        </div>
        <div>
            <a href="<?= site_url('admin/inventory'); ?>" class="btn btn-outline-secondary btn-sm">
                Back to Inventory
            </a>
        </div>
    </div>

<form method="post" enctype="multipart/form-data" id="addProductForm" novalidate>

    <!-- ============================================================ -->
    <!-- STEP 1 — Product Information -->
    <!-- ============================================================ -->
    <div class="wizard-step active" data-step="1">
        <div class="row">

            <!-- LEFT COLUMN: Image + Status -->
            <div class="col col-4">

                <!-- Product Image -->
                <div class="card">
                    <div class="card-body">
                        <div class="page-section" style="margin-top:0;">
                            <span class="section-title"><i class="fas fa-image me-2"></i>Product Image *</span>
                            <hr>
                        </div>
                        <input type="file" id="product_image" name="product_image" accept=".jpg,.jpeg,.png,.webp" style="display:none;">
                        <div class="upload-zone" id="uploadZone" onclick="document.getElementById('product_image').click()">
                            <div id="zonePlaceholder">
                                <div class="zone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                <div class="zone-label"><strong>Click to upload</strong> or drag & drop</div>
                                <div class="zone-hint">JPG, PNG, WebP — max 5 MB</div>
                            </div>
                            <div id="imagePreviewWrap">
                                <img id="imagePreview" src="#" alt="Preview">
                                <button type="button" class="remove-image-btn" onclick="removeImage(event)" title="Remove image">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div id="imageError" style="color: var(--danger); margin-top: 6px; font-size:.8rem;display:none;"></div>
                    </div>
                </div>

                <!-- Status -->
                <div class="card" style="margin-top: 20px;">
                    <div class="card-body">
                        <div class="page-section" style="margin-top:0;">
                            <span class="section-title"><i class="fas fa-toggle-on me-2"></i>Product Status</span>
                            <hr>
                        </div>
                        <div class="status-options">
                            <div class="status-opt" style="--opt-color:#2e7d32;--opt-bg:#e8f5e9;">
                                <input type="radio" name="status" id="st_available" value="available" <?= set_radio('status', 'available', TRUE); ?>>
                                <label for="st_available"><i class="fas fa-circle-check"></i> Available</label>
                            </div>
                            <div class="status-opt" style="--opt-color:#6c757d;--opt-bg:#f8f9fa;">
                                <input type="radio" name="status" id="st_not_available" value="not_available" <?= set_radio('status', 'not_available'); ?>>
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
                                        value="<?= htmlspecialchars(set_value('product_name')); ?>" required>
                                    <div style="text-align:right;">
                                        <span class="char-counter"><span id="nameCount">0</span>/150</span>
                                    </div>
                                    <div class="field-feedback" id="fb_product_name"></div>
                                </div>
                            </div>
                            <div class="col col-4">
                                <div class="form-group">
                                    <label for="brand">Brand</label>
                                    <div class="smart-select" id="brandSmartSelect">
                                        <div class="smart-select-input-wrap">
                                            <input type="text" class="form-control smart-select-input" id="brand" name="brand"
                                                maxlength="100" placeholder="Select existing or type a brand"
                                                value="<?= htmlspecialchars(set_value('brand')); ?>" autocomplete="off">
                                            <button type="button" class="smart-select-add-btn" title="Add new brand" aria-label="Add new brand">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                        <div class="smart-select-dropdown" hidden></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col col-4">
                                <div class="form-group">
                                    <label for="sku">SKU *</label>
                                    <input type="text" class="form-control sku-field" id="sku" name="sku"
                                        maxlength="30" placeholder="DHB000001"
                                        value="<?= htmlspecialchars($suggested_sku); ?>" readonly>
                                    <small style="color: var(--gray);">Auto-generated SKU, not editable</small>
                                </div>
                            </div>
                            <div class="col col-4">
                                <div class="form-group">
                                    <label for="category_search">Category</label>
                                    <?php
                                        $selected_category_id = set_value('category_id');
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
                                    <small style="color: var(--gray);">Top-level section (e.g. Beauty, Furniture, Food)</small>
                                </div>
                            </div>
                            <div class="col col-4">
                                <div class="form-group">
                                    <label for="subcategory_search">Subcategory</label>
                                    <?php
                                        $selected_subcategory_id = set_value('subcategory_id');
                                        $selected_subcategory_label = '';
                                        if (!empty($selected_subcategory_id)) {
                                            foreach ($categories as $cat) {
                                                if ((string) $cat['category_id'] === (string) $selected_subcategory_id) {
                                                    $selected_subcategory_label = $cat['category_name'];
                                                    break;
                                                }
                                            }
                                        }
                                    ?>
                                    <div class="smart-select" id="subcategorySmartSelect">
                                        <div class="smart-select-input-wrap">
                                            <input type="text" class="form-control smart-select-input" id="subcategory_search"
                                                maxlength="100" placeholder="<?= empty($selected_category_id) ? 'Select a category first' : 'Select existing or type a subcategory'; ?>"
                                                value="<?= htmlspecialchars($selected_subcategory_label); ?>" autocomplete="off" <?= empty($selected_category_id) ? 'disabled' : ''; ?>>
                                            <button type="button" class="smart-select-add-btn" title="Add new subcategory" aria-label="Add new subcategory">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                        <div class="smart-select-dropdown" hidden></div>
                                    </div>
                                    <input type="hidden" id="subcategory_id" name="subcategory_id" value="<?= htmlspecialchars($selected_subcategory_id ?: '0'); ?>">
                                    <small style="color: var(--gray);">Optional, narrows down the section (e.g. Skin Care)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /col-8 -->
        </div><!-- /row -->

    </div><!-- /step 1 -->

    <!-- ============================================================ -->
    <!-- STEP 2 — Pricing -->
    <!-- ============================================================ -->
    <div class="wizard-step" data-step="2">
        <div class="card">
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
                                placeholder="0.00" value="<?= set_value('cost_price'); ?>" required>
                            <small style="color: var(--gray);">What you paid per unit</small>
                            <div class="field-feedback" id="fb_cost_price"></div>
                        </div>
                    </div>
                    <div class="col col-6">
                        <div class="form-group">
                            <label for="price">Selling Price *</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="price" name="price"
                                placeholder="0.00" value="<?= set_value('price'); ?>" required>
                            <small style="color: var(--gray);">Customer selling price</small>
                            <div class="field-feedback" id="fb_price"></div>
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
                        <!-- legacy badges kept for compatibility with existing markup calculation -->
                        <div style="display:flex;align-items:center;gap:8px;margin-top:10px;">
                            <span style="color: var(--gray); font-size:.82rem;">Summary:</span>
                            <span id="markupBadge" class="markup-badge"><i class="fas fa-arrow-up"></i> ₱0.00</span>
                            <span id="marginBadge" style="color: var(--gray); font-size:.78rem;"></span>
                        </div>
                        <div class="field-feedback" id="fb_pricing_rule"></div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /step 2 -->

    <!-- ============================================================ -->
    <!-- STEP 3 — Inventory -->
    <!-- ============================================================ -->
    <div class="wizard-step" data-step="3">
        <div class="card">
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
                                placeholder="10" value="<?= set_value('min_stock_alert', '10'); ?>" required>
                            <small style="color: var(--gray);">Trigger low-stock warning (applies to total across branches)</small>
                            <div class="field-feedback" id="fb_min_stock_alert"></div>
                        </div>
                    </div>
                    <div class="col col-6">
                        <div class="form-group">
                            <label for="expiry_date">Expiry Date</label>
                            <input type="date" class="form-control" id="expiry_date" name="expiry_date"
                                min="<?= date('Y-m-d'); ?>"
                                value="<?= set_value('expiry_date'); ?>">
                            <small style="color: var(--gray);">Leave blank if not applicable</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Variations -->
        <div class="card" style="margin-top:20px;">
            <div class="card-body">
                <div class="page-section" style="margin-top:0;">
                    <span class="section-title"><i class="fas fa-layer-group me-2"></i>Product Variations &amp; Branch Stock</span>
                    <hr>
                </div>

                <p class="text-muted" style="margin-top:-6px;font-size:.85rem;">Add at least one Variation Type (e.g. Shade, Finish, Size — up to 5), give it a Value, then generate the combination(s) below to enter stock per branch.</p>

                <div class="table-responsive">
                    <table class="table table-sm variation-values-table table-stack" id="variationTypesContainer">
                        <thead>
                            <tr>
                                <th>Value</th><th>Pcs / Unit</th><th>Default Status</th><th class="text-center">Smart Apply</th><th class="text-center">Remove</th>
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
                    <i class="fas fa-exclamation-circle"></i> Up to 5 variation types are supported per product (e.g. Shade × Finish × Size).
                </div>

                <div style="margin-top:16px;" id="generateCombinationsWrap" hidden>
                    <button type="button" class="btn btn-primary btn-sm" id="generateCombinationsBtn"><i class="fas fa-cogs"></i> Generate Variant Combinations</button>
                    <span class="text-muted" style="font-size:.8rem;margin-left:8px;" id="combinationsCountLabel"></span>
                </div>

                <div class="field-feedback invalid" id="variationStepError" style="display:none;margin-top:10px;">
                    <i class="fas fa-exclamation-circle"></i> Product stock is required.
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
                    <div class="inventory-notice ok" id="inventoryNotice">
                        <i class="fas fa-check-circle" style="margin-top:2px;"></i>
                        <div>Enter branch quantities to see a live inventory notice.</div>
                    </div>
                    <div style="background:#f0f4ff;border:1px solid #c7d2fe;border-radius:10px;padding:12px;margin-top:14px;">
                        <div style="display:flex;align-items:flex-start;gap:8px;">
                            <i class="fas fa-layer-group" style="color: var(--primary-pink); margin-top:2px;"></i>
                            <div style="font-size:.8rem;color:var(--text);">
                                <strong>Per-Branch Batches Created Automatically</strong><br>
                                Saving this product creates one stock batch per branch for the base product (or each combination) with a quantity above zero.
                                Editing later only adds or removes the difference, so existing stock history is preserved.
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="variations_json" id="variationsJson" value="">
                <input type="hidden" name="combinations_json" id="combinationsJson" value="">
            </div>
        </div>

    </div><!-- /step 3 -->

    <!-- ============================================================ -->
    <!-- STEP 4 — Product Details -->
    <!-- ============================================================ -->
    <div class="wizard-step" data-step="4">
        <div class="card">
            <div class="card-body">
                <div class="page-section" style="margin-top:0;">
                    <span class="section-title"><i class="fas fa-align-left me-2"></i>Product Details</span>
                    <hr>
                </div>
                <div class="form-group">
                    <label for="description">Product Description</label>
                    <textarea class="form-control" id="description" name="description"
                        rows="5" maxlength="1000"
                        placeholder="Describe the product — ingredients, usage, benefits…"><?= htmlspecialchars(set_value('description')); ?></textarea>
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
                        <!-- chips inserted by JS -->
                        <input type="text" id="tagInput" placeholder="Type a tag and press Enter or comma…">
                    </div>
                    <input type="hidden" id="tagsHidden" name="tags" value="<?= htmlspecialchars(set_value('tags')); ?>">
                    <small style="color: var(--gray);">Press Enter or comma to add a tag</small>
                </div>
            </div>
        </div>

    </div><!-- /step 4 -->

    <!-- ============================================================ -->
    <!-- STEP 5 — Review Product -->
    <!-- ============================================================ -->
    <div class="wizard-step" data-step="5">
        <div class="card">
            <div class="card-body">
                <div class="page-section" style="margin-top:0;">
                    <span class="section-title"><i class="fas fa-clipboard-check me-2"></i>Product Summary</span>
                    <hr>
                </div>
                <p class="text-muted" style="margin-top:-6px;">Live summary of the details you've entered above — check everything before creating the product.</p>
                <div class="row">
                    <div class="col col-4">
                        <div class="review-image-wrap">
                            <img id="rev_image" src="#" alt="Product preview">
                        </div>
                        <div class="review-item" style="margin-top:14px;border-bottom:none;">
                            <div class="rev-lbl">Status</div>
                            <div class="rev-val" id="rev_status">—</div>
                        </div>
                    </div>
                    <div class="col col-8">
                        <div class="review-grid">
                            <div class="review-item"><div class="rev-lbl">Product Name</div><div class="rev-val" id="rev_name">—</div></div>
                            <div class="review-item"><div class="rev-lbl">SKU</div><div class="rev-val" id="rev_sku">—</div></div>
                            <div class="review-item"><div class="rev-lbl">Brand</div><div class="rev-val" id="rev_brand">—</div></div>
                            <div class="review-item"><div class="rev-lbl">Category</div><div class="rev-val" id="rev_category">—</div></div>
                            <div class="review-item"><div class="rev-lbl">Subcategory</div><div class="rev-val" id="rev_subcategory">—</div></div>
                            <div class="review-item"><div class="rev-lbl">Cost Price</div><div class="rev-val" id="rev_cost">—</div></div>
                            <div class="review-item"><div class="rev-lbl">Selling Price</div><div class="rev-val" id="rev_price">—</div></div>
                            <div class="review-item"><div class="rev-lbl">Markup</div><div class="rev-val" id="rev_markup">—</div></div>
                            <div class="review-item"><div class="rev-lbl">Total Stock</div><div class="rev-val" id="rev_total_stock">—</div></div>
                            <div class="review-item"><div class="rev-lbl">Minimum Stock Alert</div><div class="rev-val" id="rev_min_stock">—</div></div>
                            <div class="review-item"><div class="rev-lbl">Expiry Date</div><div class="rev-val" id="rev_expiry">—</div></div>
                        </div>
                    </div>
                    <div class="col col-12">
                        <div class="review-item" style="border-bottom:none;">
                            <div class="rev-lbl">Stock per Branch</div>
                            <div id="rev_branches" style="margin-top:6px;"></div>
                        </div>
                    </div>
                    <div class="col col-12">
                        <div class="review-item" style="border-bottom:none;">
                            <div class="rev-lbl">Description</div>
                            <div class="rev-val" id="rev_description" style="font-weight:400;">—</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="wizard-actions" style="justify-content:flex-end;">
            <button type="submit" class="btn btn-primary" id="saveBtn">
                <i class="fas fa-save"></i> Create Product
            </button>
        </div>
    </div><!-- /step 5 -->

</form>

<!-- Generated Variant Combinations — modal (button-triggered) -->
<div class="ss-modal-overlay" id="combinationsModalOverlay">
    <div class="ss-modal ss-modal-wide" role="dialog" aria-modal="true">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
            <h4><i class="fas fa-th-list"></i> Generated Variant Combinations</h4>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="combinationsModalCloseX" aria-label="Close"><i class="fas fa-times"></i></button>
        </div>
        <p class="hint">Regenerates automatically whenever you add, remove, or edit a value.</p>

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

        <div class="ss-modal-actions">
            <button type="button" class="btn btn-primary" id="combinationsModalCloseBtn">Done</button>
        </div>
    </div>
</div>
</div>

<script>
/* ── Image upload ────────────────────────────────────────── */
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
        previewImg.src     = e.target.result;
        placeholder.style.display  = 'none';
        previewWrap.style.display  = 'block';
    };
    reader.readAsDataURL(file);
}

function removeImage(e) {
    e.stopPropagation();
    fileInput.value          = '';
    previewImg.src           = '#';
    previewWrap.style.display = 'none';
    placeholder.style.display = 'block';
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

/* ── Stock total, summed across every variation's per-branch inputs ── */
const totalStockDisplay = document.getElementById('totalStockDisplay');
const branchesStockedDisplay = document.getElementById('branchesStockedDisplay');
const inventoryNotice = document.getElementById('inventoryNotice');
const minStockInput = document.getElementById('min_stock_alert');

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
    updateInventoryNotice(total);
}

function updateInventoryNotice(total) {
    const minAlert = parseInt(minStockInput.value, 10) || 0;
    if (total > 0 && minAlert > total) {
        inventoryNotice.className = 'inventory-notice warn';
        inventoryNotice.innerHTML = '<i class="fas fa-triangle-exclamation" style="margin-top:2px;"></i>' +
            '<div>Total stock (' + total + ') is below your minimum stock alert (' + minAlert + '). A low-stock notification will be generated after saving.</div>';
    } else if (total === 0) {
        inventoryNotice.className = 'inventory-notice warn';
        inventoryNotice.innerHTML = '<i class="fas fa-circle-info" style="margin-top:2px;"></i>' +
            '<div>No stock entered yet. Add a Variation Type, generate combinations, then enter a quantity for at least one branch.</div>';
    } else {
        inventoryNotice.className = 'inventory-notice ok';
        inventoryNotice.innerHTML = '<i class="fas fa-check-circle" style="margin-top:2px;"></i>' +
            '<div>Stock levels look good across your branches.</div>';
    }
}
minStockInput.addEventListener('input', updateTotalStock);
updateTotalStock();

/* ── Character counters ──────────────────────────────────── */
const nameInput = document.getElementById('product_name');
const descInput = document.getElementById('description');
nameInput.addEventListener('input', () => {
    document.getElementById('nameCount').textContent = nameInput.value.length;
});
descInput.addEventListener('input', () => {
    document.getElementById('descCount').textContent = descInput.value.length;
});
// Sync once on load too — a restored value (validation-failure bounce-back)
// is set via the value/textContent attribute, which doesn't fire 'input'.
document.getElementById('nameCount').textContent = nameInput.value.length;
document.getElementById('descCount').textContent = descInput.value.length;

/* ── Tags widget ─────────────────────────────────────────── */
let tags = [];

// Restore tags from hidden field on page reload (validation bounce-back)
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

/* ── Inline field validation helpers ─────────────────────── */
function showFieldOk(id, msg) {
    const fb = document.getElementById('fb_' + id);
    const input = document.getElementById(id);
    if (fb) { fb.className = 'field-feedback valid'; fb.innerHTML = '<i class="fas fa-check-circle"></i> ' + (msg || 'Looks good'); }
    if (input) { input.classList.add('is-valid'); input.classList.remove('is-invalid'); }
}
function showFieldErr(id, msg) {
    const fb = document.getElementById('fb_' + id);
    const input = document.getElementById(id);
    if (fb) { fb.className = 'field-feedback invalid'; fb.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + msg; }
    if (input) { input.classList.add('is-invalid'); input.classList.remove('is-valid'); }
}
function clearField(id) {
    const fb = document.getElementById('fb_' + id);
    const input = document.getElementById(id);
    if (fb) { fb.className = 'field-feedback'; fb.innerHTML = ''; }
    if (input) { input.classList.remove('is-valid', 'is-invalid'); }
}

/* ── Step validators ──────────────────────────────────────── */
function validateStep1(showErrors) {
    let ok = true;

    if (!fileInput.files.length) {
        ok = false;
        if (showErrors) {
            imageError.textContent = 'Please select a product image before continuing.';
            imageError.style.display = 'block';
        }
    } else if (showErrors) {
        imageError.style.display = 'none';
    }

    const nameVal = nameInput.value.trim();
    if (!nameVal) {
        ok = false;
        if (showErrors) showFieldErr('product_name', 'Product name is required.');
    } else if (showErrors) {
        showFieldOk('product_name');
    }

    return ok;
}

function validateStep2(showErrors) {
    let ok = true;
    const cost = parseFloat(costInput.value);
    const sell = parseFloat(sellInput.value);

    if (isNaN(cost) || cost <= 0) {
        ok = false;
        if (showErrors) showFieldErr('cost_price', 'Cost price is required and must be greater than 0.');
    } else if (showErrors) {
        showFieldOk('cost_price');
    }

    if (isNaN(sell) || sell <= 0) {
        ok = false;
        if (showErrors) showFieldErr('price', 'Selling price is required and must be greater than 0.');
    } else if (!isNaN(cost) && cost > 0 && sell <= cost) {
        ok = false;
        if (showErrors) showFieldErr('price', 'Selling Price must be greater than Cost Price.');
    } else if (showErrors) {
        if (!isNaN(cost) && cost > 0) {
            const markupPct = (((sell - cost) / cost) * 100).toFixed(1);
            showFieldOk('price', 'Looks good — ' + markupPct + '% markup over cost.');
        } else {
            showFieldOk('price');
        }
    }

    const fbRule = document.getElementById('fb_pricing_rule');
    if (!isNaN(cost) && !isNaN(sell) && cost > 0 && sell > 0 && sell <= cost) {
        ok = false;
        if (showErrors) {
            fbRule.className = 'field-feedback invalid';
            fbRule.innerHTML = '<i class="fas fa-exclamation-circle"></i> Selling Price must be greater than Cost Price.';
        }
    } else if (showErrors) {
        fbRule.className = 'field-feedback';
        fbRule.innerHTML = '';
    }

    return ok;
}

function validateStep3(showErrors) {
    let ok = true;
    const minAlert = parseInt(minStockInput.value, 10);
    if (isNaN(minAlert) || minAlert < 1) {
        ok = false;
        if (showErrors) showFieldErr('min_stock_alert', 'Minimum stock alert is required and must be at least 1.');
    } else if (showErrors) {
        showFieldOk('min_stock_alert');
    }

    // Stock now lives entirely in the Generated Variant Combinations table —
    // at least one branch quantity above zero is required.
    const variationError = document.getElementById('variationStepError');
    const hasStock = Array.from(document.querySelectorAll('.combo-branch-stock-input'))
        .some(inp => (parseInt(inp.value, 10) || 0) > 0);

    if (!hasStock) {
        ok = false;
        if (showErrors && variationError) {
            variationError.innerHTML = '<i class="fas fa-exclamation-circle"></i> Stock is required — click "Enter Branch Stock" above and enter a quantity for at least one branch.';
            variationError.style.display = 'block';
        }
    } else if (showErrors && variationError) {
        variationError.style.display = 'none';
    }

    return ok;
}

function runValidation(step, showErrors) {
    switch (step) {
        case 1: return validateStep1(showErrors);
        case 2: return validateStep2(showErrors);
        case 3: return validateStep3(showErrors);
        default: return true;
    }
}

/* ── Live validation while typing ────────────────────────── */
nameInput.addEventListener('input', () => {
    if (nameInput.value.trim()) showFieldOk('product_name'); else clearField('product_name');
});
costInput.addEventListener('input', () => validateStep2(true));
sellInput.addEventListener('input', () => validateStep2(true));
minStockInput.addEventListener('input', () => validateStep3(true));

/* ── Wizard navigation — one step visible at a time ──────────────── */
const STEP_NAMES = { 1: 'Product Information', 2: 'Pricing', 3: 'Inventory', 4: 'Product Details', 5: 'Review Product' };
let currentStep = 1;

function showStep(n) {
    document.querySelectorAll('.wizard-step').forEach(el => el.classList.remove('active', 'wizard-fade-in'));
    const target = document.querySelector('.wizard-step[data-step="' + n + '"]');
    if (target) target.classList.add('active', 'wizard-fade-in');

    document.querySelectorAll('.wizard-progress-step').forEach(el => {
        const s = parseInt(el.dataset.progressStep, 10);
        el.classList.toggle('active', s === n);
        el.classList.toggle('completed', s < n);
    });
    document.querySelectorAll('.wizard-progress-line').forEach((el, i) => {
        el.classList.toggle('completed', (i + 1) < n);
    });

    document.getElementById('wizardStepNum').textContent = n;
    document.getElementById('wizardStepName').textContent = STEP_NAMES[n] || '';

    currentStep = n;
    if (n === 5) populateReview();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function nextStep() {
    if (!runValidation(currentStep, true)) return;
    if (currentStep < 5) showStep(currentStep + 1);
}

function prevStep() {
    if (currentStep > 1) showStep(currentStep - 1);
}

/* ── Review step population ───────────────────────────────── */
function populateReview() {
    document.getElementById('rev_image').src = (previewImg.src && !previewImg.src.endsWith('#')) ? previewImg.src : '';

    document.getElementById('rev_name').textContent = nameInput.value.trim() || '—';

    const statusChecked = document.querySelector('input[name="status"]:checked');
    document.getElementById('rev_status').textContent = statusChecked
        ? (statusChecked.value === 'available' ? 'Available' : 'Not Available')
        : '—';

    document.getElementById('rev_sku').textContent = document.getElementById('sku').value;

    const brand = document.getElementById('brand').value.trim();
    document.getElementById('rev_brand').textContent = brand || '— None —';

    const categoryIdField = document.getElementById('category_id');
    const categoryLabel = document.getElementById('category_search').value.trim();
    document.getElementById('rev_category').textContent = (categoryIdField.value && categoryIdField.value !== '0' && categoryLabel) ? categoryLabel : '— None —';

    const subcategoryIdFieldRev = document.getElementById('subcategory_id');
    const subcategoryLabel = document.getElementById('subcategory_search').value.trim();
    document.getElementById('rev_subcategory').textContent = (subcategoryIdFieldRev.value && subcategoryIdFieldRev.value !== '0' && subcategoryLabel) ? subcategoryLabel : '— None —';

    const cost = parseFloat(costInput.value) || 0;
    const sell = parseFloat(sellInput.value) || 0;
    document.getElementById('rev_cost').textContent = '₱' + cost.toFixed(2);
    document.getElementById('rev_price').textContent = '₱' + sell.toFixed(2);
    const markup = sell - cost;
    const marginTxt = (cost > 0 && sell > 0) ? (((markup / sell) * 100).toFixed(1) + '% margin') : '—';
    document.getElementById('rev_markup').textContent = '₱' + markup.toFixed(2) + ' (' + marginTxt + ')';

    const revBranches = document.getElementById('rev_branches');
    revBranches.innerHTML = '';
    const branchTotals = {};
    document.querySelectorAll('.combo-branch-stock-input').forEach(inp => {
        const qty = parseInt(inp.value, 10) || 0;
        branchTotals[inp.dataset.branchId] = (branchTotals[inp.dataset.branchId] || 0) + qty;
    });
    VARIATION_BRANCHES.forEach(b => {
        const row = document.createElement('div');
        row.className = 'review-branch-row';
        row.innerHTML = '<span>' + escHtml(b.label) + '</span><strong>' + (branchTotals[b.id] || 0) + '</strong>';
        revBranches.appendChild(row);
    });

    document.getElementById('rev_total_stock').textContent = totalStockDisplay.textContent + ' unit(s)';
    document.getElementById('rev_min_stock').textContent = minStockInput.value || '—';
    document.getElementById('rev_expiry').textContent = document.getElementById('expiry_date').value || 'No expiry';
    document.getElementById('rev_description').textContent = descInput.value.trim() || 'No description provided.';
}

// The Summary section is always visible (single-page form, no wizard steps
// anymore), so keep it in sync as the admin fills out the rest of the form
// — including its initial state, since there's no "arrival at step 5" event
// to populate it on anymore.
document.getElementById('addProductForm').addEventListener('input', populateReview);
document.getElementById('addProductForm').addEventListener('change', populateReview);
// NOTE: the initial populateReview() call itself lives at the very end of
// this script, not here — it reads VARIATION_BRANCHES and other consts that
// are only declared further down, and calling it this early threw an
// uncaught "Cannot access before initialization" error that silently
// stopped every script statement after it from ever running (including the
// Add Variation Type button's click handler and the form's submit handler).

/* ── Smart select: searchable Brand/Category picker ──────── */
const BRAND_LIST = <?= json_encode(
    array_values(array_filter(array_column($brands, 'brand'))),
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
); ?>;
const ALL_CATEGORIES = <?= json_encode(
    array_map(fn($c) => [
        'id' => (int) $c['category_id'],
        'label' => $c['category_name'],
        'parent_id' => $c['parent_id'] !== NULL ? (int) $c['parent_id'] : NULL,
    ], $categories),
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
); ?>;
const CATEGORY_LIST = ALL_CATEGORIES.filter(c => !c.parent_id).map(c => ({ id: c.id, label: c.label }));

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
        if (opts.onSelect) opts.onSelect(item);
    }

    function setItems(newItems) {
        items = newItems.slice();
    }

    function close() { dropdown.hidden = true; }

    // Shared by the explicit "Create" modal and the auto-create-on-blur
    // fallback below, so typing a new value and simply moving on (without
    // remembering to click Create) still results in it actually existing
    // and being selected — not just cosmetically sitting in the text box.
    function createAndSelect(name, onDone) {
        const alreadyAdded = items.some(it => labelOf(it).toLowerCase() === name.toLowerCase());
        if (alreadyAdded) {
            const match = items.find(it => labelOf(it).toLowerCase() === name.toLowerCase());
            select(match);
            onDone(true, null);
            return;
        }
        fetch(opts.createUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'name=' + encodeURIComponent(name) + (opts.extraBody ? '&' + opts.extraBody() : '')
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const newItem = opts.parseCreated(data);
                items.push(newItem);
                items.sort((a, b) => labelOf(a).localeCompare(labelOf(b)));
                select(newItem);
                onDone(true, null);
            } else {
                onDone(false, data.message || 'Something went wrong.');
            }
        })
        .catch(() => onDone(false, 'Network error. Please try again.'));
    }

    input.addEventListener('focus', () => render(input.value));
    input.addEventListener('input', () => {
        if (hidden) hidden.value = '0';
        render(input.value);
    });
    input.addEventListener('blur', () => {
        setTimeout(close, 150);
        // Only meaningful for hidden-id-backed fields (Category) — Brand
        // has no id to resolve, its typed text is the value as-is.
        if (!hidden) return;
        const typed = input.value.trim();
        if (!typed || hidden.value !== '0') return;
        const matched = items.find(it => labelOf(it).toLowerCase() === typed.toLowerCase());
        if (matched) { select(matched); return; }
        createAndSelect(typed, (ok, message) => {
            if (ok) {
                showSSToast(opts.entityLabel + ' "' + typed + '" created and selected.', 'success');
            } else {
                showSSToast(message || ('Could not auto-create that ' + opts.entity + ' — please pick it from the list.'), 'error');
            }
        });
    });
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

    addBtn.addEventListener('click', () => {
        if (opts.canCreate && !opts.canCreate()) return;
        openCreateModal(input.value.trim());
    });

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
                createAndSelect(name, (ok, message) => {
                    done(ok, message);
                    if (ok) showSSToast(opts.entityLabel + ' created and selected.', 'success');
                });
            }
        });
    }

    return { render, close, setItems };
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
    entityLabel: 'Brand',
    parseCreated: (data) => data.brand.brand,
    createUrl: '<?= site_url('admin/product/quick_create_brand'); ?>',
    modalTitle: 'Add New Brand',
    placeholder: 'e.g. Nivea'
});

const subcategorySearch = document.getElementById('subcategory_search');
const subcategoryIdField = document.getElementById('subcategory_id');

function refreshSubcategoryOptions(categoryId, resetSelection) {
    const subs = ALL_CATEGORIES.filter(c => c.parent_id === categoryId).map(c => ({ id: c.id, label: c.label }));
    subcategoryApi.setItems(subs);
    if (resetSelection) {
        subcategorySearch.value = '';
        subcategoryIdField.value = '0';
    }
    const hasCategory = !!categoryId;
    subcategorySearch.disabled = !hasCategory;
    subcategorySearch.placeholder = hasCategory ? 'Select existing or type a subcategory' : 'Select a category first';
}

initSmartSelect({
    root: document.getElementById('categorySmartSelect'),
    input: document.getElementById('category_search'),
    hidden: document.getElementById('category_id'),
    items: CATEGORY_LIST,
    entity: 'category',
    entityLabel: 'Category',
    parseCreated: (data) => ({ id: data.category.category_id, label: data.category.category_name }),
    createUrl: '<?= site_url('admin/product/quick_create_category'); ?>',
    modalTitle: 'Add New Category',
    placeholder: 'e.g. Skincare',
    onSelect: (item) => refreshSubcategoryOptions(item.id, true)
});

const subcategoryApi = initSmartSelect({
    root: document.getElementById('subcategorySmartSelect'),
    input: subcategorySearch,
    hidden: subcategoryIdField,
    items: [],
    entity: 'subcategory',
    entityLabel: 'Subcategory',
    parseCreated: (data) => ({ id: data.subcategory.category_id, label: data.subcategory.category_name }),
    createUrl: '<?= site_url('admin/product/quick_create_subcategory'); ?>',
    modalTitle: 'Add New Subcategory',
    placeholder: 'e.g. Skin Care',
    extraBody: () => 'parent_id=' + encodeURIComponent(document.getElementById('category_id').value || '0'),
    canCreate: () => {
        const catId = document.getElementById('category_id').value;
        if (!catId || catId === '0') {
            showSSToast('Please select a category first.', 'error');
            return false;
        }
        return true;
    }
});

// Start disabled — no category chosen yet on a fresh Add Product load.
refreshSubcategoryOptions(parseInt(document.getElementById('category_id').value, 10) || null, false);

/* ── Product Variations & Branch Stock ──────────────────────── */
const VARIATION_TYPES = ['Color', 'Size', 'Shade', 'Volume', 'Material', 'Bundle', 'Scent', 'Hair Type', 'Skin Type', 'Pattern', 'Fabric', 'Weight'];
const VARIATION_BRANCHES = <?= json_encode(array_map(fn($b) => [
    'id' => (int) $b['branch_id'],
    'label' => explode(' ', trim($b['branch_name']))[0] . ' Stock',
], $branches)); ?>;
// Soft ceiling only — guards against runaway combination counts (an N-axis
// cartesian product grows multiplicatively), not an artificial "2 types"
// design limit. Raise it further if a real catalog ever needs more.
const MAX_VARIATION_TYPES = 5;

const variationTypesContainer = document.getElementById('variationTypesContainer');
const addVariationTypeBtn = document.getElementById('addVariationTypeBtn');
const variationTypeDropdown = document.getElementById('variationTypeDropdown');
const generateCombinationsWrap = document.getElementById('generateCombinationsWrap');
const combinationsCountLabel = document.getElementById('combinationsCountLabel');
const combinationsModalOverlay = document.getElementById('combinationsModalOverlay');
const combinationsBody = document.getElementById('combinationsBody');
const combinationsHeaderRow = document.getElementById('combinationsHeaderRow');
const variationTypeCapError = document.getElementById('variationTypeCapError');
const APP_BASE_URL = '<?php echo base_url(); ?>';
let variationRowIdSeq = 0;

let combinationRowSeq = 0;
// key ("Type:value|Type:value") -> row data, preserved across regenerations
// so entered SKU/barcode/price/status/stock survive edits to other values.
let combinationRows = {};

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
            '<td colspan="6">' +
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
    row.dataset.rowId = String(variationRowIdSeq++);

    row.innerHTML =
        '<td><input type="text" class="form-control form-control-sm variation-value-input" placeholder="Value (e.g. Red)" value="' + escHtml(value ? value.variation_value : '') + '"></td>' +
        '<td><input type="number" step="1" min="1" class="form-control form-control-sm variation-pieces-per-unit-input" title="How many individual pieces one unit of this value represents (e.g. 10 for &quot;1 Set (10 pcs)&quot;) — selling 1 unit deducts this many pieces from its stock. Leave at 1 if this value isn\'t a multi-piece bundle." value="' + (value && value.pieces_per_unit ? parseInt(value.pieces_per_unit, 10) : 1) + '"></td>' +
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
            customConfirm('This value has stock in one or more generated combinations. Removing it will remove those combinations too. Continue?', function() {
                row.remove();
                onVariationStructureChanged();
            }, { title: 'Remove Value' });
            return;
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
        const matches = c.axes.some(a => a.type === type && a.value === value);
        if (!matches) return false;
        return Object.values(c.branch_stock || {}).some(q => (parseInt(q, 10) || 0) > 0);
    });
}

function onVariationStructureChanged() {
    updateGenerateButtonVisibility();
    syncVariationsJson();
    generateCombinations();
    if (typeof initResponsiveTableStacking === 'function') initResponsiveTableStacking();
}

function updateGenerateButtonVisibility() {
    // Always visible — even a product with zero Variation Types needs this
    // button, since it's the only way to open the branch-stock table and
    // enter stock for a simple (non-variant) product.
    generateCombinationsWrap.hidden = false;
    const btn = document.getElementById('generateCombinationsBtn');
    if (addedVariationTypes().length === 0) {
        btn.innerHTML = '<i class="fas fa-boxes-stacked"></i> Enter Branch Stock';
    } else {
        btn.innerHTML = '<i class="fas fa-cogs"></i> Generate Variant Combinations';
    }
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
                // Values no longer carry their own default price adjustment
                // (removed — redundant with the per-combination Price Adj.
                // in the Generated Combinations table below); combos are
                // simply generated starting at 0 and priced individually.
                default_price_adjustment: 0,
                pieces_per_unit: Math.max(1, parseInt(row.querySelector('.variation-pieces-per-unit-input').value, 10) || 1),
                default_status: row.querySelector('.variation-default-status-select').value,
                client_row_id: row.dataset.rowId,
            });
        });
    });
    document.getElementById('variationsJson').value = JSON.stringify(variations);
}

/* ── Section 3: Generate Variant Combinations (Cartesian product) ── */
document.getElementById('generateCombinationsBtn').addEventListener('click', () => {
    generateCombinations();
    openCombinationsModal();
});

function openCombinationsModal() { combinationsModalOverlay.classList.add('open'); }
function closeCombinationsModal() { combinationsModalOverlay.classList.remove('open'); }
document.getElementById('combinationsModalCloseX').addEventListener('click', closeCombinationsModal);
document.getElementById('combinationsModalCloseBtn').addEventListener('click', closeCombinationsModal);
combinationsModalOverlay.addEventListener('mousedown', (e) => { if (e.target === combinationsModalOverlay) closeCombinationsModal(); });

function currentTypeValueLists() {
    const blocks = Array.from(variationTypesContainer.querySelectorAll('.variation-type-block'));
    return blocks.map(block => {
        const type = block.dataset.type;
        const values = Array.from(block.querySelectorAll('.variation-value-row')).map(row => ({
            value: row.querySelector('.variation-value-input').value.trim(),
            // No per-value default price anymore — combos start at 0 and
            // get priced individually in the Generated Combinations table.
            price_adjustment: 0,
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
// (or via bulk/Smart Apply) — otherwise every combo starts at 0 until the
// admin sets its price/status directly there.
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

// Cartesian product across however many axes are defined (1..MAX_VARIATION_TYPES).
function cartesianAxes(axes) {
    let combos = [[]];
    axes.forEach(axis => {
        const next = [];
        combos.forEach(prefix => {
            axis.values.forEach(v => { next.push(prefix.concat([{ type: axis.type, value: v }])); });
        });
        combos = next;
    });
    return combos;
}

function generateCombinations() {
    const axes = currentTypeValueLists();
    const newKeys = new Set();

    cartesianAxes(axes).forEach(comboAxes => {
        const key = comboAxes.map(a => a.type + ':' + a.value.value).join('|');
        newKeys.add(key);
        const totalPriceAdj = comboAxes.reduce((sum, a) => sum + a.value.price_adjustment, 0);
        mergeCombination(key, {
            axes: comboAxes.map(a => ({ type: a.type, value: a.value.value })),
            sku: '', barcode: '',
            price_adjustment: totalPriceAdj,
            status: comboAxes.length === 1 ? comboAxes[0].value.status : 'active',
            branch_stock: {},
        });
    });

    // Drop combinations whose value set no longer exists.
    Object.keys(combinationRows).forEach(key => {
        if (!newKeys.has(key)) delete combinationRows[key];
    });

    renderCombinationsTable();
}

/* ── Section 4: Generated Variant Combinations table ────────── */
function renderCombinationsTable() {
    const keys = Object.keys(combinationRows);
    combinationsCountLabel.textContent = keys.length
        ? keys.length + ' combination(s) generated — click to view/edit'
        : '';
    if (!keys.length) {
        combinationsBody.innerHTML = '';
        updateTotalStock();
        return;
    }

    combinationsHeaderRow.innerHTML =
        '<th></th><th>Variant Combination</th><th class="text-center">Image</th>' +
        VARIATION_BRANCHES.map(b => '<th>' + escHtml(b.label) + '</th>').join('') +
        '<th>Price Adj.</th><th>Status</th><th class="text-center">Actions</th>';

    combinationsBody.innerHTML = '';
    keys.forEach(key => {
        const c = combinationRows[key];
        const label = c.axes.length ? c.axes.map(a => a.value).join(' / ') : 'Base Product (No Variations)';
        const branchCells = VARIATION_BRANCHES.map(b =>
            '<td data-label="' + escHtml(b.label) + '"><input type="number" min="0" class="form-control form-control-sm combo-branch-stock-input" data-branch-id="' + b.id + '" value="' + (parseInt(c.branch_stock[b.id], 10) || 0) + '"></td>'
        ).join('');
        const hasSavedImage = !c.pending_image_file && !!c.image_path;
        const comboThumbSrc = c.pending_image_file ? '' : (c.image_path ? APP_BASE_URL + c.image_path : '');

        const tr = document.createElement('tr');
        tr.className = 'combination-row';
        tr.dataset.key = key;
        tr.innerHTML =
            '<td data-label="Select"><input type="checkbox" class="combo-select-checkbox"></td>' +
            '<td data-label="Variant Combination"><strong>' + escHtml(label) + '</strong></td>' +
            '<td data-label="Image" class="text-center">' +
                '<img class="combo-image-thumb" src="' + escHtml(comboThumbSrc) + '" style="width:34px;height:34px;object-fit:cover;border-radius:6px;border:1px solid #ddd;cursor:pointer;' + (hasSavedImage ? '' : 'display:none;') + '" title="View / change image">' +
                '<button type="button" class="btn btn-sm btn-outline-secondary combo-image-btn" title="Upload image for this combination"' + (hasSavedImage ? ' style="display:none;"' : '') + '><i class="fas fa-camera"></i></button>' +
                '<input type="file" accept="image/*" class="combo-image-input" name="variant_image[' + escHtml(c.row_key) + ']" style="display:none">' +
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
            const doDelete = () => { delete combinationRows[key]; tr.remove(); syncCombinationsJson(); };
            if (stock > 0) {
                customConfirm('This combination still has ' + stock + ' unit(s) of stock. Delete it anyway?', doDelete, { title: 'Delete Combination' });
            } else {
                doDelete();
            }
        });

        // A picked-but-unsaved file survives a table rebuild (e.g. typing in
        // the Values section regenerates combinations) only in JS state —
        // the DOM input itself is destroyed/recreated, so restore its
        // FileList via DataTransfer or the pick would silently vanish.
        const comboImageInput = tr.querySelector('.combo-image-input');
        const comboImageThumb = tr.querySelector('.combo-image-thumb');
        const comboImageBtn = tr.querySelector('.combo-image-btn');
        if (c.pending_image_file) {
            const dt = new DataTransfer();
            dt.items.add(c.pending_image_file);
            comboImageInput.files = dt.files;
            comboImageThumb.src = URL.createObjectURL(c.pending_image_file);
            comboImageThumb.style.display = '';
            comboImageBtn.style.display = 'none';
        }
        comboImageThumb.addEventListener('click', () => openCombinationImageModal(tr, key, label));
        comboImageBtn.addEventListener('click', () => openCombinationImageModal(tr, key, label));
        comboImageInput.addEventListener('change', () => {
            if (!comboImageInput.files || !comboImageInput.files[0]) return;
            combinationRows[key].pending_image_file = comboImageInput.files[0];
            comboImageThumb.src = URL.createObjectURL(comboImageInput.files[0]);
            comboImageThumb.style.display = '';
            comboImageBtn.style.display = 'none';
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
        axes: c.axes,
        sku: c.sku, barcode: c.barcode, price_adjustment: c.price_adjustment, status: c.status,
        branch_stock: c.branch_stock, row_key: c.row_key,
    }));
    document.getElementById('combinationsJson').value = JSON.stringify(combos);
    updateTotalStock();
    validateStep3(true);
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
    const targetRows = isSmart ? rowsMatchingValue(opts.type, opts.value) : opts.rows;
    // Pre-fill each branch with its current stock so editing starts from
    // what's already there instead of a blank box — but only when every
    // targeted row agrees on that branch's value; otherwise leave it blank
    // (still means "no change" on Apply) with a hint showing the range.
    const branchStockFields = () => VARIATION_BRANCHES.map(b => {
        const values = (targetRows || [])
            .map(tr => combinationRows[tr.dataset.key])
            .filter(Boolean)
            .map(c => parseInt(c.branch_stock[b.id], 10) || 0);
        const allSame = values.length > 0 && values.every(v => v === values[0]);
        const valueAttr = allSame ? ' value="' + values[0] + '"' : '';
        const placeholder = !allSame && values.length
            ? ' placeholder="Mixed (' + Math.min(...values) + '–' + Math.max(...values) + ')"'
            : '';
        return '<div class="d-flex align-items-center gap-2" style="margin-bottom:6px;"><span style="min-width:110px;">' + escHtml(b.label) + '</span><input type="number" min="0" class="form-control form-control-sm apply-modal-branch-stock" data-branch-id="' + b.id + '"' + valueAttr + placeholder + '></div>';
    }).join('');
    let title, bodyHtml;

    if (isSmart) {
        title = 'Smart Apply — ' + opts.value;
        bodyHtml =
            '<p class="text-muted" style="font-size:.85rem;">Applies to every combination containing "' + escHtml(opts.value) + '" without affecting other values.</p>' +
            '<div class="form-group"><label>Price Adjustment (leave blank for no change)</label><input type="number" step="0.01" class="form-control form-control-sm" id="applyModalPrice"></div>' +
            '<div class="form-group"><label>Status</label><select class="form-control form-control-sm" id="applyModalStatus"><option value="">(no change)</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>' +
            '<div class="form-group"><label>Branch Stock (leave blank for no change)</label>' + branchStockFields() + '</div>';
    } else if (action === 'delete') {
        const hasStock = opts.rows.some(tr => {
            const key = tr.dataset.key;
            return Object.values((combinationRows[key] || {}).branch_stock || {}).reduce((s, q) => s + (parseInt(q, 10) || 0), 0) > 0;
        });
        title = 'Delete Selected Combinations';
        bodyHtml = '<p>Delete ' + opts.rows.length + ' selected combination(s)? This cannot be undone once saved.' +
            (hasStock ? ' <strong>Some of these still have stock.</strong>' : '') + '</p>';
    } else if (action === 'stock') {
        title = 'Apply Stock';
        bodyHtml = '<div class="form-group"><label>Branch Stock</label>' + branchStockFields() + '</div>';
    } else if (action === 'price') {
        title = 'Apply Price Adjustment';
        bodyHtml = '<div class="form-group"><label>Price Adjustment</label><input type="number" step="0.01" class="form-control form-control-sm" id="applyModalPrice"></div>';
    } else if (action === 'status') {
        title = 'Apply Status';
        bodyHtml = '<div class="form-group"><label>Status</label><select class="form-control form-control-sm" id="applyModalStatus"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>';
    }

    showModal(title, bodyHtml, () => {
        if (isSmart) {
            applyToRows(rowsMatchingValue(opts.type, opts.value), {
                price: document.getElementById('applyModalPrice').value,
                status: document.getElementById('applyModalStatus').value,
                branchStock: readModalBranchStock(),
            });
        } else if (action === 'delete') {
            // The confirm above already warns if any selected row still has
            // stock — no need to ask again per row here.
            opts.rows.forEach(tr => {
                delete combinationRows[tr.dataset.key];
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
        return c && c.axes.some(a => a.type === type && a.value === value);
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
function showModal(title, bodyHtml, onConfirm, confirmLabel, hideCancel) {
    let modal = document.getElementById('variantApplyModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'variantApplyModal';
        modal.style.cssText = 'display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:10050;align-items:center;justify-content:center;';
        modal.innerHTML =
            '<div style="background:#fff;border-radius:12px;max-width:480px;width:92%;padding:24px;box-shadow:0 10px 40px rgba(0,0,0,.2);max-height:85vh;overflow-y:auto;position:relative;">' +
                '<button type="button" class="btn btn-outline-secondary btn-sm" id="variantApplyModalClose" style="position:absolute;top:14px;right:14px;width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;"><i class="fas fa-times"></i></button>' +
                '<h5 style="margin-bottom:14px;padding-right:34px;" id="variantApplyModalTitle"></h5>' +
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
    modal.querySelector('#variantApplyModalConfirm').textContent = confirmLabel || 'Apply';
    modal.querySelector('#variantApplyModalCancel').style.display = hideCancel ? 'none' : '';
    modal.style.display = 'flex';

    const close = () => { modal.style.display = 'none'; };
    modal.querySelector('#variantApplyModalClose').onclick = close;
    modal.querySelector('#variantApplyModalCancel').onclick = close;
    modal.querySelector('#variantApplyModalConfirm').onclick = () => { onConfirm(); close(); };
    modal.onclick = (e) => { if (e.target === modal) close(); };
}

/* Per-combination image (product_variant_images), submitted as
   variant_image[<row_key>] and handled server-side by
   Product::_maybe_upload_variant_image(). This is the only place in the
   wizard that manages images now — there's no separate per-value image. */
function openCombinationImageModal(tr, key, label) {
    const imageInput = tr.querySelector('.combo-image-input');
    const imageThumb = tr.querySelector('.combo-image-thumb');
    const hasImage = imageThumb.style.display !== 'none';

    const bodyHtml =
        '<div style="text-align:center;">' +
            (hasImage
                ? '<img src="' + escHtml(imageThumb.src) + '" style="max-width:100%;max-height:260px;border-radius:8px;border:1px solid #ddd;object-fit:contain;background:#f8f9fa;" alt="">'
                : '<div style="color:var(--gray);padding:40px 0;"><i class="fas fa-image" style="font-size:28px;display:block;margin-bottom:8px;"></i>No image set yet.</div>') +
            '<div style="margin-top:14px;">' +
                '<button type="button" class="btn btn-outline-secondary btn-sm" id="variationImageModalChooseBtn"><i class="fas fa-folder-open"></i> Choose File</button>' +
            '</div>' +
            '<small style="color:var(--gray);display:block;margin-top:10px;">Only used for the &ldquo;' + escHtml(label) + '&rdquo; combination.</small>' +
        '</div>';

    showModal('Combination Image — ' + label, bodyHtml, () => {}, 'Done', true);

    document.getElementById('variationImageModalChooseBtn').addEventListener('click', () => imageInput.click());

    if (imageInput._modalCloseHandler) imageInput.removeEventListener('change', imageInput._modalCloseHandler);
    imageInput._modalCloseHandler = () => { document.getElementById('variantApplyModal').style.display = 'none'; };
    imageInput.addEventListener('change', imageInput._modalCloseHandler, { once: true });
}

/* ── Restore Variations & Branch Stock after a validation-failure
   bounce-back, so the admin doesn't have to re-enter everything just
   because e.g. the image upload failed. Mirrors the old_input flashdata
   set server-side in Product::_handle_add_product(). ── */
const OLD_VARIATIONS_JSON = <?= json_encode(set_value('variations_json', '')); ?>;
const OLD_COMBINATIONS_JSON = <?= json_encode(set_value('combinations_json', '')); ?>;

function restoreVariationsAndCombinations() {
    let oldVariations = [];
    let oldCombinations = [];
    try { oldVariations = OLD_VARIATIONS_JSON ? JSON.parse(OLD_VARIATIONS_JSON) : []; } catch (e) { oldVariations = []; }
    try { oldCombinations = OLD_COMBINATIONS_JSON ? JSON.parse(OLD_COMBINATIONS_JSON) : []; } catch (e) { oldCombinations = []; }

    if (!oldVariations.length && !oldCombinations.length) return false;

    // Seed combinationRows with the admin's actual saved data (sku/barcode/
    // branch stock/price/status) first — generateCombinations() below will
    // merge these back in via mergeCombination() instead of starting every
    // combination from scratch.
    oldCombinations.forEach(c => {
        const key = (c.axes || []).map(a => a.type + ':' + a.value).join('|');
        combinationRows[key] = {
            axes: c.axes || [],
            sku: c.sku || '',
            barcode: c.barcode || '',
            price_adjustment: parseFloat(c.price_adjustment) || 0,
            status: c.status || 'active',
            branch_stock: c.branch_stock || {},
            row_key: c.row_key || ('restored_' + (++combinationRowSeq)),
            price_manually_set: true,
            status_manually_set: true,
        };
    });

    // Rebuild each Variation Type block with its previously entered values.
    const byType = {};
    oldVariations.forEach(v => {
        if (!v.type || !v.value) return;
        (byType[v.type] = byType[v.type] || []).push({
            variation_value: v.value,
            price_adjustment: v.default_price_adjustment,
            pieces_per_unit: v.pieces_per_unit,
            status: v.default_status,
        });
    });
    Object.keys(byType).forEach(type => tryAddVariationTypeBlock(type, byType[type]));

    // Recompute combinations from the restored values/axes.
    generateCombinations();
    updateGenerateButtonVisibility();
    updateTotalStock();
    return true;
}

if (!restoreVariationsAndCombinations()) {
    /* ── Seed a base (no-variation) row on load so a simple product can have
       branch stock entered even before any Variation Type is added — without
       this, Step 3 could never be satisfied for a product with no variations,
       since stock is only ever entered through this combinations table. ── */
    generateCombinations();
    updateGenerateButtonVisibility();
}

/* ── Initial Summary population — must run down here, after VARIATION_BRANCHES
   and every other const/function it reads have actually been declared
   (calling it any earlier throws a temporal-dead-zone ReferenceError that
   silently aborts every script statement after it, including the Add
   Variation Type button's click handler and the form's submit handler). ── */
populateReview();

/* ── Warn before losing unsaved changes (accidental back/refresh/close) ── */
let addProductFormDirty = false;
const addProductFormEl = document.getElementById('addProductForm');
addProductFormEl.addEventListener('input', function() { addProductFormDirty = true; });
addProductFormEl.addEventListener('change', function() { addProductFormDirty = true; });

window.addEventListener('beforeunload', function(e) {
    if (!addProductFormDirty) return;
    e.preventDefault();
    e.returnValue = '';
});

/* ── Form submit — a safety net in case step 5 is ever reached with a
   stale/invalid earlier step (e.g. browser back/forward), since Next
   already gates on validation for normal forward navigation. ── */
document.getElementById('addProductForm').addEventListener('submit', function(e) {
    for (let s = 1; s <= 3; s++) {
        if (!runValidation(s, true)) {
            e.preventDefault();
            showStep(s);
            return;
        }
    }
    addProductFormDirty = false; // actual save in progress — no need to warn anymore
    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
});

/* ── Resume at the first invalid step after a server-side validation bounce-back ── */
<?php if (!empty($error)): ?>
(function resumeAfterServerError() {
    for (let s = 1; s <= 4; s++) {
        if (!runValidation(s, false)) { showStep(s); return; }
    }
    showStep(5);
})();
<?php endif; ?>
</script>
