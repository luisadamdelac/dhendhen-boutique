-- Migration: Fix schema drift + wire up true multi-branch inventory
-- Purpose:
--   1) reseller_tbl was missing `status`, breaking reseller registration/approval end-to-end.
--   2) product_tbl was missing several columns admin/Product.php already writes on every insert,
--      breaking "Add Product" end-to-end.
--   3) inventory_batches/inventory_movements/inventory_reservations already existed (half-built,
--      never wired in) with the right shape for real per-branch-per-product stock. This migration
--      makes them the source of truth and retires the single-branch product_tbl.branch_id column
--      plus the older, unused stock_movement_tbl/stock_reservation_tbl pair.
-- Status: ALTER EXISTING TABLES

-- --- 1. reseller_tbl.status ---
ALTER TABLE `reseller_tbl`
  ADD COLUMN `status` ENUM('pending','active','rejected') NOT NULL DEFAULT 'active' AFTER `user_account_id`;

-- --- 2. product_tbl missing columns ---
ALTER TABLE `product_tbl`
  ADD COLUMN `brand` VARCHAR(100) NULL AFTER `product_name`,
  ADD COLUMN `cost_price` DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER `price`,
  ADD COLUMN `reseller_commission` DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER `cost_price`,
  ADD COLUMN `min_stock_alert` INT NOT NULL DEFAULT 10 AFTER `stock`,
  ADD COLUMN `tags` VARCHAR(255) NULL AFTER `description`,
  ADD COLUMN `created_by_role` ENUM('admin','staff') NULL AFTER `added_by`;

-- --- 3. Backfill inventory_batches from the old single-branch/stock fields (no-op if empty) ---
INSERT INTO `inventory_batches` (`branch_id`, `product_id`, `batch_quantity`, `remaining_quantity`, `unit_cost`, `received_date`, `status`, `created_at`, `updated_at`)
SELECT `branch_id`, `product_id`, `stock`, `stock`, `cost_price`, DATE(`date_added`), 'active', NOW(), NOW()
FROM `product_tbl`
WHERE `branch_id` IS NOT NULL AND `stock` > 0;

-- --- 4. Retire the single-branch column now that stock lives in inventory_batches ---
ALTER TABLE `product_tbl` DROP COLUMN `branch_id`;

-- --- 5. Retire the older, never-wired-in stock ledger (superseded by inventory_movements/inventory_reservations) ---
DROP TABLE IF EXISTS `stock_movement_tbl`;
DROP TABLE IF EXISTS `stock_reservation_tbl`;
