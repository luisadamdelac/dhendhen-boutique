-- Migration: Create customer_address_tbl
-- Purpose: Customer "My Addresses" book under Settings — multiple saved
-- addresses per customer, one marked as default, scoped to Oriental Mindoro.
-- Status: NEW TABLE

CREATE TABLE IF NOT EXISTS `customer_address_tbl` (
  `address_id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT NOT NULL,
  `label` VARCHAR(50) NULL,
  `street` VARCHAR(100) NOT NULL,
  `barangay` VARCHAR(50) NOT NULL,
  `municipality` VARCHAR(50) NOT NULL,
  `province` VARCHAR(50) NOT NULL DEFAULT 'Oriental Mindoro',
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_customer_id` (`customer_id`),
  CONSTRAINT `fk_customer_address_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer_tbl` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
