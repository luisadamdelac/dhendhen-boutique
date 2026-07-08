-- Records which product variation (if any) was purchased on each order line,
-- plus a denormalized label (e.g. "Color: Red") so historical orders still
-- display correctly even if the variation is later edited/removed.
ALTER TABLE `order_details`
  ADD COLUMN `variation_id` INT NULL AFTER `product_id`,
  ADD COLUMN `variation_label` VARCHAR(150) NULL AFTER `variation_id`;
