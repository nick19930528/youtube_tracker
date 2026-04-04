-- 會員偏好：抓新影片 RSS（最近 N 天、每頻道最多 M 支）
-- 預設 N=7、M=1。執行前請備份；若欄位已存在請勿重複執行。

ALTER TABLE `users`
  ADD COLUMN `fetch_max_age_days` smallint UNSIGNED NOT NULL DEFAULT 7 COMMENT 'RSS 只處理最近幾天內發布的影片' AFTER `dash_auto_load`,
  ADD COLUMN `fetch_max_per_channel` smallint UNSIGNED NOT NULL DEFAULT 1 COMMENT '每頻道每次執行最多新增幾支' AFTER `fetch_max_age_days`;
