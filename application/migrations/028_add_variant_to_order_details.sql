-- Same pattern as migration 019 (variation_id/variation_label): records
-- which product_variants row was purchased, plus a denormalized combined
-- label (e.g. "Shade: Love That Pink, Finish: Cream") so historical orders
-- keep displaying correctly even if the variant/values are later edited or
-- deleted.
ALTER TABLE `order_details`
  ADD COLUMN `variant_id` INT NULL AFTER `variation_label`,
  ADD COLUMN `variant_label` VARCHAR(300) NULL AFTER `variant_id`;
