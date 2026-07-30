-- "Mark as Refunded" only ever required a GCash reference NUMBER, with
-- nothing to back it up — payment_proof lets the admin attach a screenshot
-- of the actual GCash send confirmation, same evidence pattern already used
-- for the customer's own return-request photo (refund_request_tbl.images).

ALTER TABLE `refund_request_tbl`
  ADD COLUMN `payment_proof` VARCHAR(255) NULL AFTER `payment_reference`;
