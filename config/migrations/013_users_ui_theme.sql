-- 會員偏好：外觀模式（light=淺色 dark=深色）
-- 預設 light。執行前請備份；若欄位已存在請勿重複執行。

ALTER TABLE `users`
  ADD COLUMN `ui_theme` varchar(10) NOT NULL DEFAULT 'light' COMMENT '外觀模式 light/dark' AFTER `fetch_max_per_channel`;

