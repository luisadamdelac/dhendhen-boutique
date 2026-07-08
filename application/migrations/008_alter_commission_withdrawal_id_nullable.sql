-- Migration: Make commission_transaction_tbl.withdrawal_id nullable
-- Purpose: A commission starts 'pending' and is only linked to a withdrawal_id
--          once the reseller actually withdraws it. The column was created
--          NOT NULL with no default, which makes every commission insert fail.

ALTER TABLE `commission_transaction_tbl`
  MODIFY `withdrawal_id` INT(11) NULL DEFAULT NULL;
