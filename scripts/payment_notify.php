<?php
/**
 * 藍新 MPG — NotifyURL（幕後通知）
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/payment_minimal.php';
require_once __DIR__ . '/../lib/PaymentMinimal.php';

header('Content-Type: text/plain; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'METHOD_NOT_ALLOWED';
    exit;
}

if (!payment_minimal_is_configured()) {
    http_response_code(503);
    echo 'NOT_CONFIGURED';
    exit;
}

$tradeInfoHex = isset($_POST['TradeInfo']) ? (string)$_POST['TradeInfo'] : '';
$tradeSha = isset($_POST['TradeSha']) ? (string)$_POST['TradeSha'] : '';

if ($tradeInfoHex === '' || !PaymentMinimal::verifyTradeSha($tradeInfoHex, $tradeSha)) {
    http_response_code(400);
    echo 'BAD_SHA';
    exit;
}

$data = PaymentMinimal::decryptTradeInfoToJson($tradeInfoHex);
if ($data === null) {
    http_response_code(400);
    echo 'DECRYPT_FAIL';
    exit;
}

$status = isset($data['Status']) ? (string)$data['Status'] : '';
$result = isset($data['Result']) ? $data['Result'] : null;
if (is_string($result)) {
    $result = json_decode($result, true);
}
if (!is_array($result)) {
    $result = array();
}

$merchantOrderNo = isset($result['MerchantOrderNo']) ? (string)$result['MerchantOrderNo'] : '';
if ($merchantOrderNo === '' && isset($data['MerchantOrderNo'])) {
    $merchantOrderNo = (string)$data['MerchantOrderNo'];
}
if ($merchantOrderNo === '') {
    echo 'OK';
    exit;
}

$pdo = (new Database())->getConnection();
$stmt = $pdo->prepare('SELECT * FROM payment_orders WHERE merchant_order_no = ? LIMIT 1');
$stmt->execute(array($merchantOrderNo));
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    echo 'OK';
    exit;
}

if ($status !== 'SUCCESS') {
    if ($order['status'] === 'pending') {
        $pdo->prepare("UPDATE payment_orders SET status = 'failed', updated_at = NOW() WHERE id = ?")->execute(array($order['id']));
    }
    echo 'OK';
    exit;
}

$amt = isset($result['Amt']) ? (int)$result['Amt'] : 0;
if ($amt > 0 && (int)$order['amt'] !== $amt) {
    echo 'OK';
    exit;
}

if ($order['status'] === 'paid') {
    echo 'OK';
    exit;
}

$pdo->beginTransaction();
try {
    $u = $pdo->prepare("UPDATE payment_orders SET status = 'paid', updated_at = NOW() WHERE id = ? AND status = 'pending'");
    $u->execute(array($order['id']));
    if ($u->rowCount() === 0) {
        $pdo->rollBack();
        echo 'OK';
        exit;
    }

    $uid = (int)$order['user_id'];
    $planSlug = 'go';
    if (isset($order['plan_slug']) && (string)$order['plan_slug'] !== '') {
        $planSlug = (string)$order['plan_slug'];
    }
    $pst = $pdo->prepare('SELECT id, billing_interval FROM subscription_plans WHERE slug = ? AND is_active = 1 LIMIT 1');
    $pst->execute(array($planSlug));
    $planRow = $pst->fetch(PDO::FETCH_ASSOC);
    if ($planRow) {
        $planId = (int) $planRow['id'];
        $billing = isset($planRow['billing_interval']) ? (string) $planRow['billing_interval'] : 'month';
        $sid = $pdo->query('SELECT id FROM subscriptions WHERE user_id = ' . (int)$uid . ' ORDER BY id DESC LIMIT 1')->fetchColumn();
        if ($sid) {
            $sid = (int) $sid;
            if ($billing === 'year') {
                $u = $pdo->prepare("UPDATE subscriptions SET plan_id = ?, status = 'active', current_period_start = NOW(), current_period_end = DATE_ADD(NOW(), INTERVAL 1 YEAR), updated_at = NOW() WHERE id = ?");
            } elseif ($billing === 'month') {
                $u = $pdo->prepare("UPDATE subscriptions SET plan_id = ?, status = 'active', current_period_start = NOW(), current_period_end = DATE_ADD(NOW(), INTERVAL 1 MONTH), updated_at = NOW() WHERE id = ?");
            } else {
                $u = $pdo->prepare("UPDATE subscriptions SET plan_id = ?, status = 'active', current_period_start = NOW(), current_period_end = NULL, updated_at = NOW() WHERE id = ?");
            }
            $u->execute(array($planId, $sid));
        } else {
            if ($billing === 'year') {
                $ins = $pdo->prepare("INSERT INTO subscriptions (user_id, plan_id, status, current_period_start, current_period_end) VALUES (?, ?, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR))");
            } elseif ($billing === 'month') {
                $ins = $pdo->prepare("INSERT INTO subscriptions (user_id, plan_id, status, current_period_start, current_period_end) VALUES (?, ?, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH))");
            } else {
                $ins = $pdo->prepare("INSERT INTO subscriptions (user_id, plan_id, status, current_period_start, current_period_end) VALUES (?, ?, 'active', NOW(), NULL)");
            }
            $ins->execute(array($uid, $planId));
        }
    }
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo 'DB_ERROR';
    exit;
}

echo 'OK';
