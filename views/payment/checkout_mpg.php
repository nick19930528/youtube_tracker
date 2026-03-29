<?php
require_once __DIR__ . '/../../config/bootstrap.php';
auth_require_login();
require_once __DIR__ . '/../../config/payment_minimal.php';
require_once __DIR__ . '/../../lib/PaymentMinimal.php';

$pdo = (new Database())->getConnection();
$uid = auth_user_id();
$user = auth_user();

$error = '';
$form = null;

if (!payment_minimal_is_configured()) {
    $error = '金流尚未設定：請在 config/payment_minimal.php 或環境變數設定 MPG_MERCHANT_ID、MPG_HASH_KEY、MPG_HASH_IV。';
} elseif (!$user || trim((string)$user['email']) === '' || !filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
    $error = '請確認會員 Email 可收信。';
} else {
    $stmt = $pdo->prepare('SELECT id, name, slug, price_cents, is_active FROM subscription_plans WHERE slug = ? LIMIT 1');
    $stmt->execute(array('go'));
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$plan || !(int)$plan['is_active']) {
        $error = '找不到 Go 方案或已停用。';
    } else {
        $priceCents = (int)$plan['price_cents'];
        if ($priceCents <= 0) {
            $error = 'Go 方案金額未設定（price_cents）。';
        } else {
            $amt = (int)round($priceCents / 100);
            if ($amt < 1) {
                $error = '金額需至少 1 元。';
            } else {
                $orderNo = 'p' . $uid . '_' . gmdate('ymdHis') . '_' . substr(bin2hex(random_bytes(4)), 0, 8);
                if (strlen($orderNo) > 30) {
                    $orderNo = substr($orderNo, 0, 30);
                }
                try {
                    $ins = $pdo->prepare('INSERT INTO payment_orders (user_id, merchant_order_no, amt, status) VALUES (?, ?, ?, ?)');
                    $ins->execute(array($uid, $orderNo, $amt, 'pending'));
                } catch (Exception $e) {
                    $error = '無法建立訂單（請執行 migrations/006_payment_orders_minimal.sql）。';
                }
                if ($error === '') {
                    $itemDesc = (string)$plan['name'];
                    if (mb_strlen($itemDesc, 'UTF-8') > 50) {
                        $itemDesc = mb_substr($itemDesc, 0, 50, 'UTF-8');
                    }
                    // 藍新 MPG：須明確啟用信用卡（CREDIT=1）。未帶時行為依商店後台／預設可能只出現部分付款方式。
                    // 「分期」是否出現：主要由藍新商店後台「信用卡—分期」與收單合約決定；程式未送 InstFlag＝不指定分期期數（一次付清為主）。
                    // 若畫面仍只有分期，請至藍新後台檢查是否僅啟用分期或未啟用一次付清。
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
                        'CREDIT' => 1,
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
    <title>MPG 付款 — YouTube Tracker</title>
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
    </style>
</head>
<body>
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
