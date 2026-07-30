-- The checkout form's "Additional instructions for your order" textarea was
-- submitted but had nowhere to go — Checkout::process() never read it and
-- order_tbl had no column for it, so customer order notes were silently
-- discarded on every single order.

ALTER TABLE `order_tbl`
  ADD COLUMN `notes` TEXT NULL AFTER `delivery_fee`;
