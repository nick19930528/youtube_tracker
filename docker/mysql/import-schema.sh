#!/bin/bash
set -e
# 首次建立資料庫後匯入結構（MYSQL_DATABASE 已由官方 entrypoint 建立）
if [ -f /schema-import/youtube_tracker.sql ]; then
  echo "Importing youtube_tracker schema..."
  mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" youtube_tracker < /schema-import/youtube_tracker.sql
  echo "Schema import done."
else
  echo "No /schema-import/youtube_tracker.sql found, skip import."
fi
