<?php
/**
 * 批次：將所有「付費週期已結束」的訂閱改回免費（供 cron 每日執行）
 *
 * 一般使用者只要開啟網站，plan_limits 也會觸發同步；此腳本給不常登入的帳號用。
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/subscription_sync.php';

$pdo = (new Database())->getConnection();
$ids = $pdo->query('SELECT DISTINCT user_id FROM subscriptions')->fetchAll(PDO::FETCH_COLUMN);
foreach ($ids as $uid) {
    subscription_sync_expired_for_user($pdo, (int) $uid);
}
echo "OK " . count($ids) . " users checked.\n";
