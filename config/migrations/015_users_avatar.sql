-- 會員大頭照：公開網址 + Cloud Storage object 路徑（更換／刪除用）
-- 若欄位已存在請勿重複執行。應用程式首次上傳時也會嘗試自動加欄。

ALTER TABLE `users`
  ADD COLUMN `avatar_url` varchar(512) DEFAULT NULL COMMENT '大頭照公開網址' AFTER `name`,
  ADD COLUMN `avatar_object` varchar(255) DEFAULT NULL COMMENT 'GCS 或本機物件路徑' AFTER `avatar_url`;
