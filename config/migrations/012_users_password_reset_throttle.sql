-- 忘記密碼：節流寄信（程式內冷卻時間 180 秒）
ALTER TABLE `users`
  ADD COLUMN `password_reset_last_sent_at` datetime DEFAULT NULL AFTER `password_hash`;

