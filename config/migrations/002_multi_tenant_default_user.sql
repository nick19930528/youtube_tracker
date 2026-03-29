-- 多租戶：users、訂閱表，並將既有 channel_categories / channels / videos 併入 user_id = 1
-- 執行前請備份資料庫；僅需執行一次（若 user_id 等欄位已存在請勿重跑）。
-- 在 phpMyAdmin 或 mysql 客戶端執行一次。
--
-- 預設帳號（請登入後立即變更密碼與 Email）：
--   Email： owner@youtube-tracker.local
--   密碼：  password
--   （password_hash 為 PHP password_hash('password', PASSWORD_DEFAULT) 相容之 bcrypt）

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- 1) 會員與訂閱（預留訂閱制）
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `gender` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'm / f / other / 留空',
  `email_verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `subscription_plans` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price_cents` int(11) NOT NULL DEFAULT 0,
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TWD',
  `billing_interval` enum('free','month','year') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'free',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_subscription_plans_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `plan_id` int(10) UNSIGNED NOT NULL,
  `status` enum('trialing','active','past_due','canceled','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `current_period_start` datetime DEFAULT NULL,
  `current_period_end` datetime DEFAULT NULL,
  `cancel_at_period_end` tinyint(1) NOT NULL DEFAULT 0,
  `external_subscription_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_subscriptions_user` (`user_id`),
  KEY `idx_subscriptions_plan` (`plan_id`),
  CONSTRAINT `fk_subscriptions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_subscriptions_plan` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `subscription_plans` (`id`, `name`, `slug`, `price_cents`, `currency`, `billing_interval`, `is_active`, `sort_order`)
VALUES (1, '免費', 'free', 0, 'TWD', 'free', 1, 0)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

INSERT INTO `users` (`id`, `email`, `password_hash`, `name`, `gender`)
VALUES (
  1,
  'owner@youtube-tracker.local',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  '原資料擁有者',
  NULL
)
ON DUPLICATE KEY UPDATE `email` = VALUES(`email`);

INSERT INTO `subscriptions` (`user_id`, `plan_id`, `status`)
SELECT 1, 1, 'active'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `subscriptions` WHERE `user_id` = 1 LIMIT 1);

ALTER TABLE `users` AUTO_INCREMENT = 2;

-- ---------------------------------------------------------------------------
-- 2) 業務表：user_id = 1（舊資料）
-- ---------------------------------------------------------------------------

-- channel_categories
ALTER TABLE `channel_categories`
  ADD COLUMN `user_id` int(10) UNSIGNED NULL AFTER `id`;

UPDATE `channel_categories` SET `user_id` = 1 WHERE `user_id` IS NULL;

ALTER TABLE `channel_categories`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL,
  ADD CONSTRAINT `fk_channel_categories_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD UNIQUE KEY `uq_channel_categories_user_name` (`user_id`, `name`);

-- channels
ALTER TABLE `channels`
  ADD COLUMN `user_id` int(10) UNSIGNED NULL AFTER `id`;

UPDATE `channels` SET `user_id` = 1 WHERE `user_id` IS NULL;

ALTER TABLE `channels`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL,
  ADD KEY `idx_channels_user` (`user_id`),
  ADD CONSTRAINT `fk_channels_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- videos：改為 (user_id, youtube_url) 唯一
ALTER TABLE `videos` DROP INDEX `youtube_url`;

ALTER TABLE `videos`
  ADD COLUMN `user_id` int(10) UNSIGNED NULL AFTER `id`;

UPDATE `videos` SET `user_id` = 1 WHERE `user_id` IS NULL;

ALTER TABLE `videos`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL,
  ADD UNIQUE KEY `uq_videos_user_youtube` (`user_id`, `youtube_url`(191)),
  ADD KEY `idx_videos_user` (`user_id`),
  ADD CONSTRAINT `fk_videos_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;

-- search_history 請見同資料夾 003_search_history_user_id.sql（僅在資料庫有該表時執行）
