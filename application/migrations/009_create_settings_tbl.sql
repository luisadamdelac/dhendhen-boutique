-- Migration: Create settings_tbl
-- Purpose: Key/value system settings (SMTP config, commission rate, company info)
-- Status: NEW TABLE

CREATE TABLE IF NOT EXISTS `settings_tbl` (
  `setting_id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
