-- Admin had no way to know where to actually send the refund money — the
-- customer's own GCash number/name, collected here at request time so it's
-- on hand when the admin reaches the "Mark as Refunded" step. Same fields
-- as reseller_tbl's gcash_number/gcash_name used for withdrawal payouts.

ALTER TABLE `refund_request_tbl`
  ADD COLUMN `gcash_number` VARCHAR(20) NULL AFTER `reason`,
  ADD COLUMN `gcash_name` VARCHAR(100) NULL AFTER `gcash_number`;
