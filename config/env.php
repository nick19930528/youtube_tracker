<?php
/**
 * 環境變數與執行環境（本機 Docker / Cloud Run）
 */

function app_env($name, $default = '')
{
    $v = getenv($name);
    if (($v === false || $v === '') && isset($_SERVER[$name])) {
        $v = $_SERVER[$name];
    }
    if (($v === false || $v === '') && isset($_ENV[$name])) {
        $v = $_ENV[$name];
    }
    if ($v === false || $v === '') {
        return $default;
    }
    return (string) $v;
}

function app_is_cli()
{
    return PHP_SAPI === 'cli';
}

/** Cloud Run 會注入 K_SERVICE；若 Apache 沒把變數傳進 PHP，改看 /cloudsql 掛載 */
function app_is_cloud_run()
{
    if (app_env('K_SERVICE') !== '' || app_env('K_REVISION') !== '') {
        return true;
    }
    return is_dir('/cloudsql');
}

/**
 * Cloud Run 由負載平衡器終止 TLS，容器內常見 HTTP；需看 X-Forwarded-Proto。
 */
function app_is_https()
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    if (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443') {
        return true;
    }
    $xf = '';
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $xf = strtolower(trim(explode(',', (string) $_SERVER['HTTP_X_FORWARDED_PROTO'])[0]));
    }
    return $xf === 'https';
}

function app_define_from_env($const, $envName, $fallback)
{
    if (defined($const)) {
        return;
    }
    define($const, app_env($envName, $fallback));
}
