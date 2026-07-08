-- Migration: Create stock_movement_tbl
-- Purpose: Track all inventory movements (sales, returns, adjustments, transfers)
-- Status: NEW TABLE

CREATE TABLE IF NOT EXISTS `stock_movement_tbl` (
  `movement_id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `branch_id` INT NULL,
  `movement_type` ENUM('sale','return','adjustment','transfer_in','transfer_out','reservation','reservation_release') NOT NULL,
  `quantity_change` INT NOT NULL,
  `stock_before` INT NOT NULL,
  `stock_after` INT NOT NULL,
  `reference_type` VARCHAR(50) NULL,
  `reference_id` INT NULL,
  `performed_by_type` ENUM('admin','staff','system') NULL,
  `performed_by_id` INT NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_product (product_id),
  INDEX idx_branch (branch_id),
  INDEX idx_movement_type (movement_type),
  INDEX idx_created (created_at),
  INDEX idx_reference (reference_type, reference_id),
  
  FOREIGN KEY (product_id) REFERENCES product_tbl(product_id) ON DELETE RESTRICT,
  FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
