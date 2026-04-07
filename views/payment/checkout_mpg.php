<?php
require_once __DIR__ . '/../../config/bootstrap.php';
auth_require_login();
require_once __DIR__ . '/../../config/payment_minimal.php';
require_once __DIR__ . '/../../lib/PaymentMinimal.php';

$pdo = (new Database())->getConnection();
$uid = auth_user_id();
$user = auth_user();

$uiTheme = (isset($_SESSION['ui_theme']) && $_SESSION['ui_theme'] === 'dark') ? 'dark' : 'light';

$error = '';
$form = null;

$requestedSlug = isset($_GET['plan']) ? trim((string) $_GET['plan']) : 'go';
if ($requestedSlug === '' || !preg_match('/^[a-z0-9_-]{1,64}$/', $requestedSlug)) {
    $requestedSlug = 'go';
}

if (!payment_minimal_is_configured()) {
    $error = '金流尚未設定：請在 config/payment_minimal.php 或環境變數設定 MPG_MERCHANT_ID、MPG_HASH_KEY、MPG_HASH_IV。';
} elseif (!$user || trim((string)$user['email']) === '' || !filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
    $error = '請確認會員 Email 可收信。';
} else {
    $stmt = $pdo->prepare('SELECT id, name, slug, price_cents, is_active FROM subscription_plans WHERE slug = ? LIMIT 1');
    $stmt->execute(array($requestedSlug));
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$plan || !(int)$plan['is_active']) {
        $error = '找不到此方案或已停用（' . htmlspecialchars($requestedSlug) . '）。';
    } else {
        $priceCents = (int)$plan['price_cents'];
        if ($priceCents <= 0) {
            $error = '此方案為免費或金額未設定，無法線上付款。';
        } else {
            $amt = (int)round($priceCents / 100);
            if ($amt < 1) {
                $error = '金額需至少 1 元。';
            } else {
                $orderNo = 'p' . $uid . '_' . gmdate('ymdHis') . '_' . substr(bin2hex(random_bytes(4)), 0, 8);
                if (strlen($orderNo) > 30) {
                    $orderNo = substr($orderNo, 0, 30);
                }
                $planSlug = (string) $plan['slug'];
                try {
                    $ins = $pdo->prepare('INSERT INTO payment_orders (user_id, merchant_order_no, amt, plan_slug, status) VALUES (?, ?, ?, ?, ?)');
                    $ins->execute(array($uid, $orderNo, $amt, $planSlug, 'pending'));
                } catch (Exception $e) {
                    try {
                        $ins = $pdo->prepare('INSERT INTO payment_orders (user_id, merchant_order_no, amt, status) VALUES (?, ?, ?, ?)');
                        $ins->execute(array($uid, $orderNo, $amt, 'pending'));
                    } catch (Exception $e2) {
                        $error = '無法建立訂單（請執行 migrations/006_payment_orders_minimal.sql）。';
                    }
                }
                if ($error === '') {
                    $itemDesc = (string)$plan['name'];
                    if (mb_strlen($itemDesc, 'UTF-8') > 50) {
                        $itemDesc = mb_substr($itemDesc, 0, 50, 'UTF-8');
                    }
                    $params = array(
                        'MerchantID' => MPG_MERCHANT_ID,
                        'RespondType' => 'JSON',
                        'TimeStamp' => (string) time(),
                        'Version' => MPG_VERSION,
                        'MerchantOrderNo' => $orderNo,
                        'Amt' => $amt,
                        'ItemDesc' => $itemDesc,
                        'ReturnURL' => payment_minimal_return_url(),
                        'NotifyURL' => payment_minimal_notify_url(),
                        'Email' => trim($user['email']),
                        'LoginType' => 0,
                    );
                    $tradeInfo = PaymentMinimal::encryptTradeInfo($params);
                    if ($tradeInfo === false) {
                        $error = '加密失敗。';
                    } else {
                        $tradeSha = PaymentMinimal::buildTradeSha($tradeInfo);
                        $form = array(
                            'action' => payment_minimal_mpg_url(),
                            'merchant_id' => MPG_MERCHANT_ID,
                            'trade_info' => $tradeInfo,
                            'trade_sha' => $tradeSha,
                            'version' => MPG_VERSION,
                            'test_mode' => MPG_TEST_MODE,
                        );
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">
    <link rel="manifest" href="site.webmanifest">
    <meta name="apple-mobile-web-app-title" content="TubeLog">
    <meta name="application-name" content="TubeLog">
    <title>MPG 付款 — TubeLog</title>
    <?php if ($form && !headers_sent()) {
        header('Cache-Control: no-store');
    } ?>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f7fa; margin: 0; padding: 24px; color: #333; }
        .wrap { max-width: 520px; margin: 0 auto; }
        .card { background: #fff; border-radius: 12px; padding: 22px 24px; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
        .err { color: #991b1b; }
        .muted { color: #64748b; font-size: 14px; margin-top: 10px; }
        .btn { display: inline-block; margin-top: 16px; padding: 14px 22px; background: #0369a1; color: #fff !important; border: none; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; }

        body[data-theme="dark"] { background: #0b1220; color: #e2e8f0; }
        body[data-theme="dark"] .card { background: rgba(15, 23, 42, 0.92); box-shadow: 0 2px 12px rgba(0,0,0,.35); }
        body[data-theme="dark"] a { color: #93c5fd; }
        body[data-theme="dark"] .muted { color: rgba(226,232,240,0.72); }
    </style>
</head>
<body data-theme="<?= htmlspecialchars($uiTheme, ENT_QUOTES, 'UTF-8') ?>">
<div class="wrap">
    <p><a href="index.php?page=account">← 會員中心</a></p>
    <div class="card">
        <?php if ($error !== ''): ?>
            <h1 style="font-size:1.15rem;">無法前往付款</h1>
            <p class="err"><?= htmlspecialchars($error) ?></p>
        <?php else: ?>
            <h1 style="font-size:1.15rem;">前往藍新 MPG 刷卡</h1>
            <p class="muted">請按下方按鈕前往安全付款頁（一次付清，最小單元串接）。</p>
            <p class="muted" style="font-size:12px;">閘道：<?= !empty($form['test_mode']) ? '測試 ccore' : '正式 core' ?></p>
            <form id="mpg-form" method="post" action="<?= htmlspecialchars($form['action']) ?>" accept-charset="UTF-8">
                <input type="hidden" name="MerchantID" value="<?= htmlspecialchars($form['merchant_id']) ?>">
                <input type="hidden" name="TradeInfo" value="<?= htmlspecialchars($form['trade_info']) ?>">
                <input type="hidden" name="TradeSha" value="<?= htmlspecialchars($form['trade_sha']) ?>">
                <input type="hidden" name="Version" value="<?= htmlspecialchars($form['version']) ?>">
                <button type="submit" class="btn">前往藍新付款</button>
            </form>
            <script>
            window.setTimeout(function () {
                var f = document.getElementById('mpg-form');
                if (f) { try { f.submit(); } catch (e) {} }
            }, 400);
            </script>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
