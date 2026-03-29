<?php
/**
 * 藍新 MPG 最小單元（一次付清）— 與定期定額分開，僅需 mpg_gateway + TradeInfo / TradeSha
 * 金鑰請與藍新後台「串接設定」一致（可與定期定額同一組）；建議正式站用環境變數 MPG_* 覆寫。
 */

if (!defined('MPG_MERCHANT_ID')) {
    $v = getenv('MPG_MERCHANT_ID');
    define('MPG_MERCHANT_ID', ($v !== false && $v !== '') ? $v : 'MS3262623440');
}
if (!defined('MPG_HASH_KEY')) {
    $v = getenv('MPG_HASH_KEY');
    define('MPG_HASH_KEY', ($v !== false && $v !== '') ? $v : 'dkSCsPBNzPlxk4qB8oXUsu1UF5BgrbKH');
}
if (!defined('MPG_HASH_IV')) {
    $v = getenv('MPG_HASH_IV');
    define('MPG_HASH_IV', ($v !== false && $v !== '') ? $v : 'PJCqCWlSGCN0B4PC');
}

if (!defined('MPG_TEST_MODE')) {
    $tm = getenv('MPG_TEST_MODE');
    if ($tm === false || $tm === '') {
        define('MPG_TEST_MODE', false);
    } else {
        $t = strtolower(trim((string)$tm));
        define('MPG_TEST_MODE', in_array($t, array('1', 'true', 'yes', 'sandbox'), true));
    }
}

if (!defined('MPG_PUBLIC_BASE_URL_DEFAULT')) {
    define('MPG_PUBLIC_BASE_URL_DEFAULT', 'https://miina.shop');
}

if (!defined('MPG_PUBLIC_BASE_URL')) {
    $fromEnv = getenv('MPG_PUBLIC_BASE_URL');
    if ($fromEnv !== false && $fromEnv !== '') {
        define('MPG_PUBLIC_BASE_URL', rtrim($fromEnv, '/'));
    } else {
        $host = '';
        if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $host = trim(explode(',', (string)$_SERVER['HTTP_X_FORWARDED_HOST'])[0]);
        }
        if ($host === '' && !empty($_SERVER['HTTP_HOST'])) {
            $host = (string)$_SERVER['HTTP_HOST'];
        }
        if ($host !== '') {
            $xfProto = '';
            if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
                $xfProto = strtolower(trim(explode(',', (string)$_SERVER['HTTP_X_FORWARDED_PROTO'])[0]));
            }
            $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (isset($_SERVER['SERVER_PORT']) && (string)$_SERVER['SERVER_PORT'] === '443')
                || $xfProto === 'https';
            $scheme = $https ? 'https' : 'http';
            $path = '';
            if (!empty($_SERVER['SCRIPT_NAME'])) {
                $dir = dirname(str_replace('\\', '/', (string)$_SERVER['SCRIPT_NAME']));
                if ($dir !== '/' && $dir !== '.') {
                    $path = rtrim($dir, '/');
                }
            }
            define('MPG_PUBLIC_BASE_URL', $scheme . '://' . $host . $path);
        } else {
            define('MPG_PUBLIC_BASE_URL', MPG_PUBLIC_BASE_URL_DEFAULT);
        }
    }
}

/** MPG API 版本（幕前交易常見 2.0） */
define('MPG_VERSION', '2.0');

function payment_minimal_mpg_url()
{
    return MPG_TEST_MODE
        ? 'https://ccore.newebpay.com/MPG/mpg_gateway'
        : 'https://core.newebpay.com/MPG/mpg_gateway';
}

function payment_minimal_is_configured()
{
    return MPG_MERCHANT_ID !== '' && MPG_HASH_KEY !== '' && MPG_HASH_IV !== '' && MPG_PUBLIC_BASE_URL !== '';
}

function payment_minimal_notify_url()
{
    return MPG_PUBLIC_BASE_URL . '/scripts/payment_notify.php';
}

function payment_minimal_return_url()
{
    return MPG_PUBLIC_BASE_URL . '/index.php?page=pay_return';
}
