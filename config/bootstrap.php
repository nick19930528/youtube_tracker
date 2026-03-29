<?php
/**
 * 共用載入：資料庫設定 + Session + 認證函式
 */
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
auth_bootstrap_session();
