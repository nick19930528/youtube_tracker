-- 在匯入 schema 後可選擇執行：建立「免費」方案 + 預設管理帳號（舊資料 migration 請用 migrations/002）
-- 密碼：password（請登入後立即修改）

INSERT IGNORE INTO `subscription_plans` (`id`, `name`, `slug`, `price_cents`, `currency`, `billing_interval`, `is_active`, `sort_order`)
VALUES (1, '免費', 'free', 0, 'TWD', 'free', 1, 0);

INSERT IGNORE INTO `users` (`id`, `email`, `password_hash`, `name`, `gender`)
VALUES (
  1,
  'owner@youtube-tracker.local',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  '預設使用者',
  NULL
);

INSERT IGNORE INTO `subscriptions` (`user_id`, `plan_id`, `status`)
VALUES (1, 1, 'active');
