-- 方案額度：寫入資料表，供會員中心與 plan_limits 共用

ALTER TABLE `subscription_plans`
  ADD COLUMN `quota_max_channels` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '可訂閱頻道數上限' AFTER `sort_order`,
  ADD COLUMN `quota_max_videos_per_list` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '待看／已看單邊清單筆數上限' AFTER `quota_max_channels`;

UPDATE `subscription_plans` SET `quota_max_channels` = 200, `quota_max_videos_per_list` = 10000 WHERE `slug` = 'free';
UPDATE `subscription_plans` SET `quota_max_channels` = 50, `quota_max_videos_per_list` = 500 WHERE `slug` = 'go';
