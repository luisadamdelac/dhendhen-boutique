-- Lets admin record why a GCash payment proof was rejected, and lets the
-- customer see that reason and resubmit a corrected reference/receipt for
-- the SAME order (no need to place a new order).
ALTER TABLE `payment_transaction_tbl`
  ADD COLUMN `rejection_reason` VARCHAR(255) DEFAULT NULL AFTER `receipt_image`;
