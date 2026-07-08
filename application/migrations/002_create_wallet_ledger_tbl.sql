-- Migration: Create wallet_ledger_tbl
-- Purpose: Immutable ledger of reseller wallet transactions (source of truth for balance)
-- Status: NEW TABLE

CREATE TABLE IF NOT EXISTS `wallet_ledger_tbl` (
  `ledger_id` INT AUTO_INCREMENT PRIMARY KEY,
  `reseller_id` INT NOT NULL,
  `transaction_type` ENUM('credit','debit','hold','hold_release','reversal') NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `balance_after` DECIMAL(10,2) NOT NULL,
  `reference_type` ENUM('commission','withdrawal','reversal','adjustment') NOT NULL,
  `reference_id` INT NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_reseller (reseller_id),
  INDEX idx_transaction_type (transaction_type),
  INDEX idx_created (created_at),
  INDEX idx_reseller_created (reseller_id, created_at),
  
  FOREIGN KEY (reseller_id) REFERENCES reseller_tbl(reseller_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
