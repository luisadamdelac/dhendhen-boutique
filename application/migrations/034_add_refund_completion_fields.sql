-- Approving a refund only did inventory/commission bookkeeping — there was
-- no way to record that the item was actually received back in good
-- condition, or that the refund money was actually sent to the customer.
-- These back the new "Mark as Refunded" step (mirrors the withdrawal payout
-- flow: GCash reference required, moves status to 'completed').

ALTER TABLE `refund_request_tbl`
  ADD COLUMN `item_received` TINYINT(1) NOT NULL DEFAULT 0 AFTER `images`,
  ADD COLUMN `payment_reference` VARCHAR(30) NULL AFTER `admin_remarks`,
  ADD COLUMN `completed_at` TIMESTAMP NULL AFTER `reviewed_at`;
