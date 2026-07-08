-- Migration: Create password_reset_tbl
-- Purpose: Store forgot-password reset tokens with expiry
-- Status: NEW TABLE

CREATE TABLE IF NOT EXISTS `password_reset_tbl` (
  `reset_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_account_id` INT NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL UNIQUE,
  `token_hash` VARCHAR(255) NOT NULL UNIQUE,
  `is_used` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` DATETIME NOT NULL,
  
  INDEX idx_token (token_hash),
  INDEX idx_email_created (email, created_at),
  INDEX idx_user_account (user_account_id),
  
  FOREIGN KEY (user_account_id) REFERENCES user_account_tbl(user_account_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
