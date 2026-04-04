<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/fetch_new_videos_core.php';

if (php_sapi_name() === 'cli') {
    $uid = isset($argv[1]) ? (int) $argv[1] : 0;
    if ($uid < 1) {
        fwrite(STDERR, "用法: php fetch_new_videos.php <user_id>\n");
        fwrite(STDERR, "（全部會員請用：php fetch_new_videos_all_users.php）\n");
        exit(1);
    }
} else {
    auth_require_login();
    $uid = auth_user_id();
}

$pdo = (new Database())->getConnection();

$output = fetch_new_videos_run_for_user($pdo, $uid);

include __DIR__ . '/../views/scripts/fetch_result.php';
exit;
