-- Adds a nullable variant_id to inventory_batches/inventory_movements,
-- parallel to (not replacing) the existing variation_id column added in
-- migration 020. A batch/movement has EITHER a variation_id (single-axis
-- legacy value, or a product not using combinations) OR a variant_id (a
-- two-axis combination row in product_variants) OR neither (base product
-- stock) — never both. Kept as a separate column rather than repurposing
-- variation_id so existing single-axis products/orders/StockService calls
-- that pass $variationId are completely unaffected.
ALTER TABLE `inventory_batches`
  ADD COLUMN `variant_id` INT NULL DEFAULT NULL AFTER `variation_id`,
  ADD KEY `idx_batch_variant_id` (`variant_id`),
  ADD CONSTRAINT `fk_batch_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`variant_id`) ON DELETE CASCADE;

ALTER TABLE `inventory_movements`
  ADD COLUMN `variant_id` INT NULL DEFAULT NULL AFTER `variation_id`,
  ADD KEY `idx_movement_variant_id` (`variant_id`),
  ADD CONSTRAINT `fk_movement_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`variant_id`) ON DELETE SET NULL;
