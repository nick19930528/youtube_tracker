-- 我的最愛頻道：在已訂閱的 channels 上標記
-- 請在 phpMyAdmin 或 mysql 客戶端執行一次

ALTER TABLE `channels`
  ADD COLUMN `is_favorite` TINYINT(1) NOT NULL DEFAULT 0
  AFTER `video_count`;
