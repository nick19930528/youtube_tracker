<?php
require_once __DIR__ . '/../../config/bootstrap.php';
auth_require_login();
require_once __DIR__ . '/../../controllers/AccountController.php';

$pdo = (new Database())->getConnection();
$uid = auth_user_id();
$ctrl = new AccountController($pdo);

$notice = isset($_GET['notice']) ? (string)$_GET['notice'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_profile'])) {
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $gender = isset($_POST['gender']) ? $_POST['gender'] : '';
        if ($ctrl->updateProfile($uid, $name, $gender)) {
            $_SESSION['user_name'] = trim($name);
            header('Location: index.php?page=account&notice=profile_ok');
            exit;
        }
        header('Location: index.php?page=account&notice=profile_err');
        exit;
    }
    if (isset($_POST['change_password'])) {
        $cur = isset($_POST['current_password']) ? (string)$_POST['current_password'] : '';
        $nw = isset($_POST['new_password']) ? (string)$_POST['new_password'] : '';
        $nw2 = isset($_POST['new_password2']) ? (string)$_POST['new_password2'] : '';
        $r = $ctrl->changePassword($uid, $cur, $nw, $nw2);
        if ($r === true) {
            header('Location: index.php?page=account&notice=password_ok');
            exit;
        }
        header('Location: index.php?page=account&notice=' . rawurlencode('pwd:' . $r));
        exit;
    }
}

$profile = $ctrl->getProfile($uid);
if (!$profile) {
    echo '無法讀取會員資料。';
    exit;
}

$sub = $ctrl->getSubscriptionWithPlan($uid);

function account_gender_label($g)
{
    if ($g === 'm') {
        return '男';
    }
    if ($g === 'f') {
        return '女';
    }
    if ($g === 'other') {
        return '其他';
    }
    return '未填寫';
}

function account_billing_label($iv)
{
    $map = array('free' => '免費', 'month' => '月繳', 'year' => '年繳');
    return isset($map[$iv]) ? $map[$iv] : $iv;
}

function account_status_label($s)
{
    $map = array(
        'trialing' => '試用中',
        'active' => '使用中',
        'past_due' => '付款逾期',
        'canceled' => '已取消',
        'expired' => '已到期',
    );
    return isset($map[$s]) ? $map[$s] : $s;
}

$noticeText = '';
if ($notice === 'profile_ok') {
    $noticeText = '已更新個人資料。';
} elseif ($notice === 'profile_err') {
    $noticeText = '無法更新個人資料（姓名不可空白）。';
} elseif ($notice === 'password_ok') {
    $noticeText = '密碼已變更。';
} elseif (strpos($notice, 'pwd:') === 0) {
    $noticeText = rawurldecode(substr($notice, 4));
}

$selGender = array_key_exists('gender', $profile) && $profile['gender'] !== null && $profile['gender'] !== ''
    ? $profile['gender']
    : '';
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>會員中心 — YouTube Tracker</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f7fa; margin: 0; padding: 24px; color: #333; }
        .wrap { max-width: 720px; margin: 0 auto; }
        .nav { margin-bottom: 20px; font-size: 14px; }
        .nav a { color: #0077cc; margin-right: 12px; }
        h1 { font-size: 1.35rem; margin: 0 0 8px; }
        .sub { color: #666; font-size: 14px; margin-bottom: 24px; }
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 22px 24px;
            margin-bottom: 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }
        .card h2 { font-size: 1.05rem; margin: 0 0 16px; color: #1e293b; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; }
        .row { margin-bottom: 12px; font-size: 14px; }
        .row strong { display: inline-block; min-width: 100px; color: #64748b; font-weight: 600; }
        label { display: block; margin-bottom: 6px; font-size: 13px; color: #475569; }
        input[type="text"], input[type="password"], select {
            width: 100%; max-width: 360px; box-sizing: border-box; padding: 9px 11px;
            border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px;
        }
        button[type="submit"] {
            margin-top: 12px; padding: 10px 18px; background: #0077cc; color: #fff; border: none;
            border-radius: 8px; font-size: 14px; cursor: pointer;
        }
        button[type="submit"]:hover { background: #0066b3; }
        .hint { font-size: 12px; color: #64748b; margin-top: 6px; }
        .alert { padding: 10px 14px; border-radius: 8px; margin-bottom: 18px; font-size: 14px; }
        .alert--ok { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
        .alert--err { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .plan-badge { display: inline-block; background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 13px; }
        .muted { color: #94a3b8; font-size: 13px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="nav">
        <a href="index.php">回首頁</a>
        <a href="index.php?page=channels">頻道管理</a>
        <a href="index.php?page=logout">登出</a>
    </div>

    <h1>會員中心</h1>
    <p class="sub">管理個人資料、密碼與查看訂閱方案</p>

    <?php if ($noticeText !== ''): ?>
        <div class="alert <?= (strpos($notice, 'pwd:') === 0 || $notice === 'profile_err') ? 'alert--err' : 'alert--ok' ?>">
            <?= htmlspecialchars($noticeText) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>個人資料</h2>
        <div class="row"><strong>Email</strong> <?= htmlspecialchars($profile['email']) ?></div>
        <div class="row"><strong>註冊時間</strong> <?= htmlspecialchars($profile['created_at']) ?></div>
        <?php if (!empty($profile['email_verified_at'])): ?>
            <div class="row"><strong>Email 驗證</strong> <?= htmlspecialchars($profile['email_verified_at']) ?></div>
        <?php else: ?>
            <div class="row"><strong>Email 驗證</strong> <span class="muted">尚未驗證</span></div>
        <?php endif; ?>

        <form method="post" action="index.php?page=account" style="margin-top: 20px;">
            <input type="hidden" name="save_profile" value="1">
            <label for="name">姓名</label>
            <input type="text" id="name" name="name" required maxlength="191" value="<?= htmlspecialchars($profile['name']) ?>">

            <label for="gender" style="margin-top: 14px;">性別</label>
            <select id="gender" name="gender">
                <option value=""<?= $selGender === '' ? ' selected' : '' ?>>未填寫</option>
                <option value="m"<?= $selGender === 'm' ? ' selected' : '' ?>>男</option>
                <option value="f"<?= $selGender === 'f' ? ' selected' : '' ?>>女</option>
                <option value="other"<?= $selGender === 'other' ? ' selected' : '' ?>>其他</option>
            </select>
            <p class="hint">Email 如需變更請洽管理員（目前系統未開放自行改 Email）。</p>
            <button type="submit">儲存個人資料</button>
        </form>
    </div>

    <div class="card">
        <h2>變更密碼</h2>
        <form method="post" action="index.php?page=account" autocomplete="off">
            <input type="hidden" name="change_password" value="1">
            <label for="current_password">目前密碼</label>
            <input type="password" id="current_password" name="current_password" required autocomplete="current-password">

            <label for="new_password" style="margin-top: 14px;">新密碼</label>
            <input type="password" id="new_password" name="new_password" required minlength="8" autocomplete="new-password">
            <p class="hint">至少 8 個字元</p>

            <label for="new_password2" style="margin-top: 8px;">確認新密碼</label>
            <input type="password" id="new_password2" name="new_password2" required minlength="8" autocomplete="new-password">

            <button type="submit">更新密碼</button>
        </form>
    </div>

    <div class="card">
        <h2>訂閱方案</h2>
        <?php if ($sub): ?>
            <div class="row">
                <strong>方案</strong>
                <span class="plan-badge"><?= htmlspecialchars($sub['plan_name']) ?></span>
                <span class="muted">（<?= htmlspecialchars($sub['slug']) ?>）</span>
            </div>
            <div class="row"><strong>狀態</strong> <?= htmlspecialchars(account_status_label($sub['status'])) ?></div>
            <div class="row"><strong>計費週期</strong> <?= htmlspecialchars(account_billing_label($sub['billing_interval'])) ?></div>
            <div class="row"><strong>金額</strong>
                <?php
                if ((int)$sub['price_cents'] === 0 || $sub['billing_interval'] === 'free') {
                    echo '免費';
                } else {
                    $amt = number_format((int)$sub['price_cents'] / 100, 0);
                    echo htmlspecialchars($sub['currency'] . ' ' . $amt);
                }
                ?>
            </div>
            <?php if (!empty($sub['current_period_start']) || !empty($sub['current_period_end'])): ?>
                <div class="row"><strong>目前週期</strong>
                    <?= htmlspecialchars($sub['current_period_start'] ? $sub['current_period_start'] : '—') ?>
                    ～
                    <?= htmlspecialchars($sub['current_period_end'] ? $sub['current_period_end'] : '—') ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($sub['cancel_at_period_end'])): ?>
                <div class="row"><strong>續訂</strong> 將於本週期結束後取消</div>
            <?php endif; ?>
        <?php else: ?>
            <p class="muted">尚無訂閱紀錄。若您應享有方案，請聯絡管理員。</p>
        <?php endif; ?>
        <?php
        require_once __DIR__ . '/../../config/payment_minimal.php';
        $slugNow = $sub ? (string)$sub['slug'] : 'free';
        $canMpg = payment_minimal_is_configured();
        ?>
        <?php if ($slugNow === 'free' && $canMpg): ?>
            <p style="margin-top: 16px; margin-bottom: 0;">
                <a href="index.php?page=pay" style="display:inline-block;padding:10px 16px;background:#0369a1;color:#fff;text-decoration:none;border-radius:8px;font-size:14px;">升級 Go（MPG 一次付清 · 最小單元）</a>
            </p>
            <p class="hint" style="margin-top: 10px; margin-bottom: 0;">使用藍新 MPG 幕前一次付清；請先執行資料庫 migration <code>006_payment_orders_minimal.sql</code> 並設定 <code>MPG_*</code> 金鑰。</p>
        <?php elseif ($slugNow === 'free'): ?>
            <p class="hint" style="margin-top: 16px; margin-bottom: 0;">付費升級請在 <code>config/payment_minimal.php</code> 或環境變數設定商店代號與 Hash Key／IV。</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
