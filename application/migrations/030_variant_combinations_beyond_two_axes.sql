-- Lifts product_variants' 2-axis-only limitation (variation_id_1 +
-- variation_id_2 columns). Combinations with a 3rd, 4th, ... axis store
-- their extra values in product_variant_extra_values_tbl instead; the
-- first two axes keep using the original columns unchanged, so every
-- existing 1- and 2-axis product/variant is unaffected.
--
-- Uniqueness can no longer be expressed as a 2-column key (two 3+-axis
-- combinations can share the same first two values and differ only on a
-- 3rd+ axis), so it moves to a computed `combo_key`: all of a variant's
-- axis variation_ids, sorted ascending and joined with '-'. Order-
-- independent by construction, so it stays stable regardless of the
-- order axes were added/re-posted in.

ALTER TABLE `product_variants`
  ADD COLUMN `combo_key` VARCHAR(191) NOT NULL DEFAULT '' AFTER `variation_id_2`;

-- Backfill existing rows (both are single-axis today, but the CASE also
-- covers any 2-axis row correctly).
UPDATE `product_variants`
SET `combo_key` = CASE
    WHEN `variation_id_2` IS NULL THEN CAST(`variation_id_1` AS CHAR)
    ELSE CONCAT(LEAST(`variation_id_1`, `variation_id_2`), '-', GREATEST(`variation_id_1`, `variation_id_2`))
END;

ALTER TABLE `product_variants`
  DROP INDEX `uq_variant_combo`,
  ADD UNIQUE KEY `uq_variant_combo_key` (`product_id`, `combo_key`);

CREATE TABLE IF NOT EXISTS `product_variant_extra_values_tbl` (
  `variant_id` INT NOT NULL,
  `variation_id` INT NOT NULL,
  `axis_order` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`variant_id`, `variation_id`),
  KEY `idx_pvev_variation` (`variation_id`),
  CONSTRAINT `fk_pvev_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`variant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pvev_variation` FOREIGN KEY (`variation_id`) REFERENCES `product_variation_tbl` (`variation_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
