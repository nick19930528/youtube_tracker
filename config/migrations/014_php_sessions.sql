-- Cloud Run：PHP session 改存 Cloud SQL（多實例無法共用本機檔案）
-- 若應用程式已自動 CREATE TABLE，此檔可略過。

CREATE TABLE IF NOT EXISTS `php_sessions` (
  `id` varchar(128) NOT NULL,
  `data` mediumblob NOT NULL,
  `last_activity` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_php_sessions_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
