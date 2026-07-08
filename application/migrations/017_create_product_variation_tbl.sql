-- Product Variations (Color, Size, Shade, etc.) — each value row carries its
-- own stock and an optional price adjustment on top of the base product price.
-- Stock here is tracked centrally (not per-branch) to keep the variant model
-- independent from the existing branch/batch inventory system.
CREATE TABLE IF NOT EXISTS `product_variation_tbl` (
  `variation_id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `variation_type` VARCHAR(50) NOT NULL,
  `variation_value` VARCHAR(100) NOT NULL,
  `price_adjustment` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `stock` INT NOT NULL DEFAULT 0,
  `sku_suffix` VARCHAR(30) NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_product_id` (`product_id`),
  CONSTRAINT `fk_variation_product` FOREIGN KEY (`product_id`) REFERENCES `product_tbl` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
