<?php
/**
 * 共用載入：資料庫設定 + Session + 認證函式
 */
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin.php';

/** Dashboard：非 DB 標籤，篩選 category_id IS NULL 的頻道／影片 */
if (!defined('FILTER_CATEGORY_UNCATEGORIZED')) {
    define('FILTER_CATEGORY_UNCATEGORIZED', -1);
}

auth_bootstrap_session();
