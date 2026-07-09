-- Adds PayMongo support: widens payment_method beyond the old
-- enum('GCash') and tracks PayMongo's own IDs so the webhook handler can
-- look up and idempotently finalize the right order/payment rows.
ALTER TABLE `payment_transaction_tbl`
  MODIFY COLUMN `payment_method` ENUM('GCash','PayMongo') DEFAULT 'PayMongo';

ALTER TABLE `payment_transaction_tbl`
  ADD COLUMN `paymongo_checkout_session_id` VARCHAR(100) DEFAULT NULL AFTER `payment_reference`,
  ADD COLUMN `paymongo_payment_intent_id` VARCHAR(100) DEFAULT NULL AFTER `paymongo_checkout_session_id`,
  ADD COLUMN `paymongo_payment_id` VARCHAR(100) DEFAULT NULL AFTER `paymongo_payment_intent_id`,
  ADD COLUMN `paymongo_channel` VARCHAR(30) DEFAULT NULL AFTER `paymongo_payment_id`;

-- A multi-reseller cart creates MULTIPLE order rows sharing ONE PayMongo
-- checkout session. The webhook payload only contains the checkout_session
-- id, so this is the join key used to find every order that must be
-- finalized together.
ALTER TABLE `order_tbl`
  ADD COLUMN `paymongo_checkout_session_id` VARCHAR(100) DEFAULT NULL AFTER `order_status`,
  ADD INDEX `idx_paymongo_checkout_session_id` (`paymongo_checkout_session_id`);
