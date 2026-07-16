-- Per-variation-value image (e.g. a Toner-specific product photo) — lets a
-- simple (single-axis) variation option swap the displayed product image,
-- the same way generated combinations already can via product_variant_images.
ALTER TABLE `product_variation_tbl`
  ADD COLUMN `image_path` VARCHAR(255) NULL AFTER `sku_suffix`;
