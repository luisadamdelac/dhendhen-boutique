-- do_verify_otp() allowed unlimited guesses against a 6-digit code within
-- its 15-minute window — brute-forceable. Mirrors migration 033's withdrawal
-- OTP lockout: track failed attempts per reset request and cancel it after
-- too many wrong guesses instead of leaving it open to unlimited retries.

ALTER TABLE `password_reset_tbl`
  ADD COLUMN `otp_attempts` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `otp_code`;
