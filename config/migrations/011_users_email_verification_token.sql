-- Email 驗證權杖（驗證信連結用）
-- 執行後將既有帳號標為已驗證，僅之後新註冊需點信內連結。

ALTER TABLE `users`
  ADD COLUMN `email_verification_token` varchar(64) DEFAULT NULL AFTER `email_verified_at`,
  ADD COLUMN `email_verification_expires_at` datetime DEFAULT NULL AFTER `email_verification_token`;

ALTER TABLE `users`
  ADD UNIQUE KEY `uq_users_email_verification_token` (`email_verification_token`);

UPDATE `users` SET `email_verified_at` = `created_at` WHERE `email_verified_at` IS NULL;
