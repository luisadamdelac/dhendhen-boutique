-- Reverts checkout back to manual GCash only (migration 022 added PayMongo
-- support; that integration has been removed from the app).
-- payment_method's default is reset to 'GCash' so new rows created by the
-- restored manual-GCash checkout flow default correctly again.
-- The paymongo_* columns added in 022 are left in place (nullable, unused,
-- harmless) rather than dropped, to avoid a destructive schema change for
-- what's now dead but inert data.
ALTER TABLE `payment_transaction_tbl`
  MODIFY COLUMN `payment_method` ENUM('GCash','PayMongo') DEFAULT 'GCash';
