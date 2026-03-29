-- 僅在資料庫已有 `search_history` 表時執行（與 002 銜接，將舊紀錄併入 user_id = 1）
-- 若無此表可略過。

SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `search_history`
  ADD COLUMN `user_id` int(10) UNSIGNED NULL AFTER `id`;

UPDATE `search_history` SET `user_id` = 1 WHERE `user_id` IS NULL;

ALTER TABLE `search_history`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL,
  ADD KEY `idx_search_history_user` (`user_id`),
  ADD CONSTRAINT `fk_search_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
