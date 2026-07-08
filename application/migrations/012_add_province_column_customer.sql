-- Migration: Add province column to customer_tbl
-- Purpose: Customer registration now locks Province to "Oriental Mindoro" and
-- offers a Municipality/Barangay dropdown scoped to that province.
-- Status: ALTER EXISTING TABLE

ALTER TABLE `customer_tbl`
  ADD COLUMN `province` VARCHAR(50) NOT NULL DEFAULT 'Oriental Mindoro' AFTER `municipality`;
