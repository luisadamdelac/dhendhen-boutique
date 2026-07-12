-- Per-variant images (e.g. a swatch photo for "Love That Pink + Cream"),
-- mirroring the existing per-product product_image table's shape.
CREATE TABLE IF NOT EXISTS `product_variant_images` (
  `variant_image_id` INT AUTO_INCREMENT PRIMARY KEY,
  `variant_id` INT NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_variant_image_variant` (`variant_id`),
  CONSTRAINT `fk_variant_image_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`variant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
