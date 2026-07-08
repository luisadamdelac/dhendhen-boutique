-- Adds a 6-digit OTP code to the existing token-link password reset flow.
-- The OTP gates access to the existing token-link reset page: once verified,
-- the user is redirected to auth/reset/{token} and the rest of the flow
-- (reset_password view, do_reset()) is unchanged.
ALTER TABLE `password_reset_tbl` ADD COLUMN `otp_code` VARCHAR(6) NULL AFTER `token_hash`;
