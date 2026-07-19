-- Pick-up orders are paid in cash at the store, not via GCash, but
-- payment_method was still an ENUM('GCash','PayMongo') — adds 'Cash' so
-- checkout can record pick-up payments correctly instead of being forced
-- into an inaccurate 'GCash' row.

ALTER TABLE `payment_transaction_tbl`
  MODIFY COLUMN `payment_method` ENUM('GCash','PayMongo','Cash') DEFAULT 'GCash';
