-- 會員大頭照
-- avatar_url：網站顯示用的公開網址
-- avatar_object：桶內路徑（例如 avatars/1/xxx.jpg），更換或移除大頭照時用來刪舊檔
-- 若欄位已存在請勿重複執行。應用程式首次上傳時也會嘗試自動加欄。

ALTER TABLE `users`
  ADD COLUMN `avatar_url` varchar(512) DEFAULT NULL COMMENT '大頭照公開網址' AFTER `name`,
  ADD COLUMN `avatar_object` varchar(255) DEFAULT NULL COMMENT 'GCS 或本機物件路徑' AFTER `avatar_url`;
