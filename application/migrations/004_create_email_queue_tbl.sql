-- Migration: Create email_queue_tbl
-- Purpose: Queue transactional emails for async sending via cron
-- Status: NEW TABLE

CREATE TABLE IF NOT EXISTS `email_queue_tbl` (
  `queue_id` INT AUTO_INCREMENT PRIMARY KEY,
  `recipient_email` VARCHAR(255) NOT NULL,
  `recipient_name` VARCHAR(100) NULL,
  `subject` VARCHAR(255) NOT NULL,
  `body_html` LONGTEXT NOT NULL,
  `status` ENUM('pending','sent','failed') DEFAULT 'pending',
  `attempts` TINYINT DEFAULT 0,
  `last_attempt` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_status (status),
  INDEX idx_created (created_at),
  INDEX idx_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
