<?php
/**
 * Cloud Scheduler / 本機 CLI 共用授權
 * HTTP：Header X-Cron-Secret、Authorization: Bearer、或 ?token=
 */
require_once __DIR__ . '/env.php';

function cron_require_authorized()
{
    if (app_is_cli()) {
        return;
    }

    if (function_exists('set_time_limit')) {
        @set_time_limit(0);
    }
    ignore_user_abort(true);
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store');

    $secret = trim(app_env('CRON_SECRET'));
    if ($secret === '') {
        http_response_code(403);
        echo "CRON_SECRET 未設定，已拒絕 HTTP 排程。請在 Cloud Run 設定該環境變數，並用 Cloud Scheduler 帶入相同密鑰。\n";
        exit(1);
    }

    $got = '';
    if (!empty($_SERVER['HTTP_X_CRON_SECRET'])) {
        $got = (string) $_SERVER['HTTP_X_CRON_SECRET'];
    } elseif (!empty($_GET['token'])) {
        $got = (string) $_GET['token'];
    } elseif (!empty($_SERVER['HTTP_AUTHORIZATION']) && preg_match('/^Bearer\s+(\S+)/i', (string) $_SERVER['HTTP_AUTHORIZATION'], $m)) {
        $got = $m[1];
    }

    if ($got === '' || !hash_equals($secret, $got)) {
        http_response_code(403);
        echo "forbidden\n";
        exit(1);
    }
}
