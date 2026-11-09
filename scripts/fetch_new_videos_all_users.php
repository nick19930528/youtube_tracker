<?php
/**
 * CLI：依序對「每一位會員」執行抓新影片（各會員讀取自己的 fetch_max_age_days / fetch_max_per_channel）。
 *
 * 用法：
 *   php fetch_new_videos_all_users.php
 *
 * 排程範例：
 *   本機 cron：0 * * * * php scripts/fetch_new_videos_all_users.php
 *   Cloud Scheduler（HTTP）：POST/GET https://<網域>/scripts/fetch_new_videos_all_users.php
 *     Header: X-Cron-Secret: <與 Cloud Run 環境變數 CRON_SECRET 相同>
 *
 * 注意：會員與頻道多時執行時間與 YouTube API 配額會累加；Cloud Run 請把逾時調到足夠（建議 300 秒以上）。
 */
require_once __DIR__ . '/../config/cron_auth.php';
cron_require_authorized();
define('APP_SKIP_SESSION', true);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/fetch_new_videos_core.php';

$pdo = (new Database())->getConnection();

$stmt = $pdo->query('SELECT id FROM users ORDER BY id ASC');
$ids = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN, 0) : [];

if ($ids === []) {
    fwrite(STDERR, "沒有任何會員。\n");
    exit(0);
}

$started = microtime(true);
echo "開始時間：" . date('Y-m-d H:i:s') . "\n";
echo "共 " . count($ids) . " 位會員\n";
echo str_repeat('=', 60) . "\n\n";

foreach ($ids as $rawId) {
    $uid = (int) $rawId;
    echo "\n>>> 會員 user_id = {$uid} <<<\n";
    try {
        echo fetch_new_videos_run_for_user($pdo, $uid);
    } catch (Throwable $e) {
        echo '⚠️ 錯誤：' . $e->getMessage() . "\n";
    }
    echo "\n";
}

$sec = round(microtime(true) - $started, 2);
echo str_repeat('=', 60) . "\n";
echo "全部完成。耗時 {$sec} 秒\n";

exit(0);
