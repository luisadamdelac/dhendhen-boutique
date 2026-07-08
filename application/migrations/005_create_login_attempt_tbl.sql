-- Migration: Create login_attempt_tbl
-- Purpose: Track failed login attempts for brute force protection
-- Status: NEW TABLE

CREATE TABLE IF NOT EXISTS `login_attempt_tbl` (
  `attempt_id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `success` TINYINT(1) DEFAULT 0,
  `reason` VARCHAR(100) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_email_created (email, created_at),
  INDEX idx_ip_created (ip_address, created_at),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
