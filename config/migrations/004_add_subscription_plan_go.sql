-- 新增 Go 方案（slug = go）。將使用者的 subscriptions.plan_id 改為此列 id 即套用 Go 額度。
-- 因 slug 唯一，可安全重跑。

INSERT IGNORE INTO `subscription_plans` (`name`, `slug`, `price_cents`, `currency`, `billing_interval`, `is_active`, `sort_order`)
VALUES ('Go', 'go', 0, 'TWD', 'month', 1, 1);
