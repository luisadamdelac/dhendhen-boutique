-- Direct reseller registrations (reseller_tbl) had no way to persist a
-- rejection reason — the controller read admin_remarks from POST but only
-- ever used it for the rejection email. This adds the column so the reason
-- is stored and can be shown back to admins reviewing rejected sign-ups.
ALTER TABLE `reseller_tbl`
  ADD COLUMN `admin_remarks` TEXT NULL DEFAULT NULL AFTER `status`;
