-- Migration: Create stock_reservation_tbl
-- Purpose: Hold inventory during checkout (30-min TTL), converts to deduction at order placement
-- Status: NEW TABLE

CREATE TABLE IF NOT EXISTS `stock_reservation_tbl` (
  `reservation_id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `customer_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `reserved_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` DATETIME NOT NULL,
  `status` ENUM('active','released','converted','expired') DEFAULT 'active',
  `order_id` INT NULL,
  
  INDEX idx_product (product_id),
  INDEX idx_customer (customer_id),
  INDEX idx_status (status),
  INDEX idx_expires (expires_at),
  
  FOREIGN KEY (product_id) REFERENCES product_tbl(product_id) ON DELETE RESTRICT,
  FOREIGN KEY (customer_id) REFERENCES customer_tbl(customer_id) ON DELETE RESTRICT,
  FOREIGN KEY (order_id) REFERENCES order_tbl(order_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
