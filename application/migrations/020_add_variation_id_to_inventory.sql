-- Adds a nullable variation_id to inventory_batches/inventory_movements so
-- stock can be tracked per (branch, variation) instead of just per branch.
-- NULL variation_id = stock belongs to the base product (no variation).
-- Admin-side only: Cart/Checkout/order fulfillment still deduct against the
-- product's pooled stock and are not variation/branch aware.
ALTER TABLE `inventory_batches`
  ADD COLUMN `variation_id` INT NULL DEFAULT NULL AFTER `product_id`,
  ADD KEY `idx_variation_id` (`variation_id`),
  ADD CONSTRAINT `fk_batch_variation` FOREIGN KEY (`variation_id`) REFERENCES `product_variation_tbl` (`variation_id`) ON DELETE CASCADE;

ALTER TABLE `inventory_movements`
  ADD COLUMN `variation_id` INT NULL DEFAULT NULL AFTER `product_id`,
  ADD KEY `idx_movement_variation_id` (`variation_id`),
  ADD CONSTRAINT `fk_movement_variation` FOREIGN KEY (`variation_id`) REFERENCES `product_variation_tbl` (`variation_id`) ON DELETE SET NULL;
