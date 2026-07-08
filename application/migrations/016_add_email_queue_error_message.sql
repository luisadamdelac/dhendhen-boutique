-- Migration: store the exact SMTP/exception error text for a failed queued
-- email. Needed so admin/settings/email_logs.php can show why an email
-- failed instead of only a generic 'failed' status.
-- Status: ALTER EXISTING TABLE

ALTER TABLE `email_queue_tbl`
  ADD COLUMN `error_message` TEXT NULL AFTER `last_attempt`;
