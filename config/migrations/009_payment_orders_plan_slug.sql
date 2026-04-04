-- 付款訂單對應要開通的 subscription_plans.slug（藍新 Notify 依此寫入訂閱）

ALTER TABLE `payment_orders`
  ADD COLUMN `plan_slug` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'go' COMMENT 'subscription_plans.slug' AFTER `amt`;
