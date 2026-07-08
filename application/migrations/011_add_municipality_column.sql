-- Migration: Add municipality column
-- Purpose: Split address into Street / Barangay / City / Municipality across
-- all 4 role tables, alongside the existing City column.
-- Status: ALTER EXISTING TABLES

ALTER TABLE `admin_tbl` ADD COLUMN `municipality` VARCHAR(50) NULL AFTER `city`;
ALTER TABLE `staff_tbl` ADD COLUMN `municipality` VARCHAR(50) NULL AFTER `city`;
ALTER TABLE `customer_tbl` ADD COLUMN `municipality` VARCHAR(50) NULL AFTER `city`;
ALTER TABLE `reseller_tbl` ADD COLUMN `municipality` VARCHAR(50) NULL AFTER `city`;
