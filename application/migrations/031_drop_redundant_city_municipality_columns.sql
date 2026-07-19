-- admin_tbl, staff_tbl, and reseller_tbl each carried both a `city` and a
-- `municipality` column for the same real-world value, but only `city` was
-- ever actually read/written by any form or controller for those three
-- roles — `municipality` was always NULL (confirmed empty before this
-- migration ran). customer_tbl has the same redundancy in reverse: only
-- `municipality` is used there, `city` was always NULL.
--
-- admin_tbl's Add/Edit Admin forms additionally had TWO separate inputs
-- ("City" and "Municipality") asking for the same thing — that redundant
-- second field is removed from the views alongside this migration.

ALTER TABLE `admin_tbl` DROP COLUMN `municipality`;
ALTER TABLE `staff_tbl` DROP COLUMN `municipality`;
ALTER TABLE `reseller_tbl` DROP COLUMN `municipality`;
ALTER TABLE `customer_tbl` DROP COLUMN `city`;
