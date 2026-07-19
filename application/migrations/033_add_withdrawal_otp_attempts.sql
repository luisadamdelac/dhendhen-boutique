-- Tracks failed OTP verification attempts per withdrawal request so
-- verify_withdrawal_otp() can lock the request out after 3 wrong guesses
-- instead of allowing unlimited retries within the 10-minute OTP window.

ALTER TABLE `withdrawal_tbl`
  ADD COLUMN `otp_attempts` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `otp_verified`;
