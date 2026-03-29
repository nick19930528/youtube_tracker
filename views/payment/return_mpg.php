<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/payment_minimal.php';
require_once __DIR__ . '/../../lib/PaymentMinimal.php';

$tradeHex = '';
if (isset($_POST['TradeInfo'])) {
    $tradeHex = (string)$_POST['TradeInfo'];
} elseif (isset($_GET['TradeInfo'])) {
    $tradeHex = (string)$_GET['TradeInfo'];
}

$ok = false;
$msg = '';

if ($tradeHex !== '' && payment_minimal_is_configured()) {
    $data = PaymentMinimal::decryptTradeInfoToJson($tradeHex);
    if (is_array($data)) {
        $st = isset($data['Status']) ? (string)$data['Status'] : '';
        $msg = isset($data['Message']) ? (string)$data['Message'] : '';
        $ok = ($st === 'SUCCESS');
    }
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>付款結果</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f7fa; margin: 0; padding: 24px; color: #333; }
        .wrap { max-width: 520px; margin: 0 auto; }
        .card { background: #fff; border-radius: 12px; padding: 22px 24px; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
        .muted { color: #64748b; font-size: 14px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <?php if ($tradeHex === ''): ?>
            <h1 style="font-size:1.15rem;">已返回網站</h1>
            <p class="muted">未收到 TradeInfo。若已付款，請以會員中心方案為準（Notify 通知）。</p>
        <?php elseif ($ok): ?>
            <h1 style="font-size:1.15rem; color:#065f46;">付款成功</h1>
            <p class="muted"><?= $msg !== '' ? htmlspecialchars($msg) : '感謝您的付款，方案將依 Notify 更新。' ?></p>
        <?php else: ?>
            <h1 style="font-size:1.15rem; color:#991b1b;">付款未完成</h1>
            <p class="muted"><?= $msg !== '' ? htmlspecialchars($msg) : '請稍後至會員中心確認。' ?></p>
        <?php endif; ?>
    </div>
    <p style="margin-top:16px;"><a href="index.php">回首頁</a> · <a href="index.php?page=account">會員中心</a></p>
</div>
</body>
</html>
