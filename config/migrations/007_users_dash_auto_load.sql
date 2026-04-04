-- 會員偏好：首頁「最新影片」載入方式（1=首屏＋捲動載入 0=一次載入至方案上限）
-- 執行前請備份；若欄位已存在請勿重複執行。

ALTER TABLE `users`
  ADD COLUMN `dash_auto_load` tinyint(1) NOT NULL DEFAULT 1 COMMENT '首頁 1=捲動分頁 0=一次載入全部' AFTER `gender`;
