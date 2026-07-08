-- Migration: Create order_status_history
-- Purpose: Immutable order status lifecycle trail with timestamps and actor info
-- Status: NEW TABLE

CREATE TABLE IF NOT EXISTS `order_status_history` (
  `history_id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `status_from` VARCHAR(50) NULL,
  `status_to` VARCHAR(50) NOT NULL,
  `changed_by_type` ENUM('admin','staff','system','customer') NOT NULL,
  `changed_by_id` INT NULL,
  `reason` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_order (order_id),
  INDEX idx_status_to (status_to),
  INDEX idx_created (created_at),
  
  FOREIGN KEY (order_id) REFERENCES order_tbl(order_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
