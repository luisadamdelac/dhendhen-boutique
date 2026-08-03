-- Walk-in Sale (038) only anticipated the plain single-axis variation
-- system (product_variation_tbl); it turns out most real products in this
-- catalog actually use generated combinations (product_variants) instead,
-- even for a single axis (e.g. Color: White/Grey) — so walk-in sale detail
-- rows need their own variant_id alongside variation_id to record which
-- system a given sale line actually used.

ALTER TABLE `walkin_sale_details_tbl`
  ADD COLUMN `variant_id` INT(11) NULL AFTER `variation_id`;
