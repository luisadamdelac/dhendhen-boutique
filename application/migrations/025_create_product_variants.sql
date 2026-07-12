-- Product Variants: one row = one purchasable Cartesian combination of up to
-- two variation values (e.g. Shade "Love That Pink" x Finish "Cream"), or a
-- single-axis combination (variation_id_2 NULL) for products with only one
-- variation type. Sits above product_variation_tbl, which continues to only
-- define TYPE + VALUE + default price adjustment + default status per
-- variation_id — product_variants is the actual inventory item row, carrying
-- SKU, barcode, its own price_adjustment/status, and (via
-- inventory_batches.variant_id) real per-branch stock.
CREATE TABLE IF NOT EXISTS `product_variants` (
  `variant_id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `variation_id_1` INT NOT NULL,
  `variation_id_2` INT NULL DEFAULT NULL,
  `sku` VARCHAR(64) NULL,
  `barcode` VARCHAR(64) NULL,
  `price_adjustment` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `stock` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_variant_combo` (`product_id`, `variation_id_1`, `variation_id_2`),
  KEY `idx_variant_product` (`product_id`),
  KEY `idx_variant_value1` (`variation_id_1`),
  KEY `idx_variant_value2` (`variation_id_2`),
  CONSTRAINT `fk_variant_product` FOREIGN KEY (`product_id`) REFERENCES `product_tbl` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_variant_value1` FOREIGN KEY (`variation_id_1`) REFERENCES `product_variation_tbl` (`variation_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_variant_value2` FOREIGN KEY (`variation_id_2`) REFERENCES `product_variation_tbl` (`variation_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
