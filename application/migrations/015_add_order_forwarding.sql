-- Migration: Order forwarding flag + refund evidence upload column
-- Purpose:
--   1) reseller/Orders.php has no way to hand an order to the supplier/warehouse.
--      Add an additive timestamp flag rather than a new order_status enum value,
--      so the existing status state machine (side effects, valid_statuses lists)
--      stays untouched.
--   2) refund_request_tbl.images exists as an unused BLOB. Retype to a VARCHAR
--      path column so it can store an uploaded evidence file path, matching the
--      convention already used by product_image.
-- Status: ALTER EXISTING TABLES

ALTER TABLE `order_tbl`
  ADD COLUMN `forwarded_at` DATETIME NULL AFTER `order_status`;

ALTER TABLE `refund_request_tbl`
  MODIFY COLUMN `images` VARCHAR(255) NULL;
