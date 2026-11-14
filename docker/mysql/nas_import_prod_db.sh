#!/bin/sh
# 在 NAS 上把正式站 SQL 備份灌進測試容器 yttracker-db。
# 請把本檔複製到 backups/youtube_tracker/ 再執行，不要留在網站目錄。
# 用法：控制台 → 工作排程 → 使用者定義的指令碼（使用者 root）貼下面，或 SSH 執行本檔。
# 警告：會刪掉目前 NAS 測試庫 youtube_tracker，再換成正式站資料。不要設成每天自動跑。

set -e

BACKUP_GZ="/volume1/backups/youtube_tracker/cloudsql/youtube_tracker.gz"
DB_CONTAINER="yttracker-db"
MYSQL_ROOT_PASSWORD="devroot"
DATABASE="youtube_tracker"

if [ ! -f "$BACKUP_GZ" ]; then
  echo "找不到備份檔：$BACKUP_GZ"
  echo "請先確認 Cloud Sync 已同步，或改成你 NAS 上的實際路徑。"
  exit 1
fi

/usr/local/bin/docker exec "$DB_CONTAINER" mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e "DROP DATABASE IF EXISTS $DATABASE; CREATE DATABASE $DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;"

/usr/local/bin/docker cp "$BACKUP_GZ" "$DB_CONTAINER:/tmp/youtube_tracker.gz"

/usr/local/bin/docker exec "$DB_CONTAINER" sh -c "gunzip -c /tmp/youtube_tracker.gz | mysql -uroot -p$MYSQL_ROOT_PASSWORD $DATABASE"

/usr/local/bin/docker exec "$DB_CONTAINER" rm -f /tmp/youtube_tracker.gz

echo "匯入完成。請用正式站帳號登入 http://NAS的IP:8888 確認。"
