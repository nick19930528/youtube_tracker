<?php
require_once __DIR__ . '/../../config/bootstrap.php';
auth_require_login();
require_once __DIR__ . '/../../config/subscription_sync.php';
require_once __DIR__ . '/../../controllers/AccountController.php';

$pdo = (new Database())->getConnection();
$uid = auth_user_id();
$ctrl = new AccountController($pdo);

$notice = isset($_GET['notice']) ? (string)$_GET['notice'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_dash_pref'])) {
        $auto = (isset($_POST['dash_auto_load']) && (string)$_POST['dash_auto_load'] === '1') ? 1 : 0;
        if ($ctrl->updateDashAutoLoad($uid, $auto)) {
            $_SESSION['dash_auto_load'] = $auto;
            header('Location: index.php?page=account&notice=dash_pref_ok');
            exit;
        }
        header('Location: index.php?page=account&notice=dash_pref_err');
        exit;
    }
    if (isset($_POST['save_fetch_prefs'])) {
        $days = isset($_POST['fetch_max_age_days']) ? (int) $_POST['fetch_max_age_days'] : 7;
        $m = isset($_POST['fetch_max_per_channel']) ? (int) $_POST['fetch_max_per_channel'] : 1;
        if ($ctrl->updateFetchPrefs($uid, $days, $m)) {
            header('Location: index.php?page=account&notice=fetch_pref_ok');
            exit;
        }
        header('Location: index.php?page=account&notice=fetch_pref_err');
        exit;
    }
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
    if (isset($_POST['resend_verification'])) {
        $stmt = $pdo->prepare('SELECT email, name, email_verified_at FROM users WHERE id = ? LIMIT 1');
        $stmt->execute(array($uid));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && empty($row['email_verified_at'])) {
            $ok = auth_email_issue_and_send($pdo, $uid, $row['email'], $row['name']);
            header('Location: index.php?page=account&notice=' . ($ok ? 'verify_resent_ok' : 'verify_resent_fail'));
        } else {
            header('Location: index.php?page=account&notice=verify_resent_skip');
        }
        exit;
    }
}

$profile = $ctrl->getProfile($uid);
if (!$profile) {
    echo '無法讀取會員資料。';
    exit;
}

$sub = $ctrl->getSubscriptionWithPlan($uid);
$subHistory = $ctrl->listSubscriptionHistory($uid);

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

$noticeText = '';
if ($notice === 'profile_ok') {
    $noticeText = '已更新個人資料。';
} elseif ($notice === 'profile_err') {
    $noticeText = '無法更新個人資料（姓名不可空白）。';
} elseif ($notice === 'password_ok') {
    $noticeText = '密碼已變更。';
} elseif (strpos($notice, 'pwd:') === 0) {
    $noticeText = rawurldecode(substr($notice, 4));
} elseif ($notice === 'dash_pref_ok') {
    $noticeText = '已更新首頁載入方式。';
} elseif ($notice === 'dash_pref_err') {
    $noticeText = '無法更新首頁載入方式，請稍後再試。';
} elseif ($notice === 'fetch_pref_ok') {
    $noticeText = '已更新抓新影片設定。';
} elseif ($notice === 'fetch_pref_err') {
    $noticeText = '無法更新抓新影片設定，請稍後再試。';
} elseif ($notice === 'verify_resent_ok') {
    $noticeText = '已重新寄出驗證信，請至信箱點擊連結。';
} elseif ($notice === 'verify_resent_fail') {
    $noticeText = '無法寄出驗證信，請確認郵件設定（環境變數）或稍後再試。';
} elseif ($notice === 'verify_resent_skip') {
    $noticeText = '此帳號已驗證或無需重送。';
}

$selGender = array_key_exists('gender', $profile) && $profile['gender'] !== null && $profile['gender'] !== ''
    ? $profile['gender']
    : '';
$dashAutoLoad = (isset($profile['dash_auto_load']) && (int)$profile['dash_auto_load'] === 0) ? 0 : 1;
$fetchMaxAgeDays = isset($profile['fetch_max_age_days']) ? (int) $profile['fetch_max_age_days'] : 7;
$fetchMaxPerChannel = isset($profile['fetch_max_per_channel']) ? (int) $profile['fetch_max_per_channel'] : 1;

require_once __DIR__ . '/../../config/plan_limits.php';
require_once __DIR__ . '/../../config/payment_minimal.php';

$planRows = $pdo->query('SELECT id, name, slug, price_cents, currency, billing_interval, sort_order, quota_max_channels, quota_max_videos_per_list FROM subscription_plans WHERE is_active = 1 ORDER BY sort_order ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
$slugNow = plan_limits_get_active_slug($pdo, $uid);
$currentPlanSort = 0;
foreach ($planRows as $_pr) {
    if ($_pr['slug'] === $slugNow) {
        $currentPlanSort = (int) $_pr['sort_order'];
        break;
    }
}
$canMpg = payment_minimal_is_configured();

function account_center_plan_price(array $p)
{
    if ((int) $p['price_cents'] === 0 || (isset($p['billing_interval']) && $p['billing_interval'] === 'free')) {
        return '免費';
    }
    $amt = number_format((int) $p['price_cents'] / 100, 0);

    return $p['currency'] . ' ' . $amt . '／' . account_center_billing_unit($p['billing_interval']);
}

function account_center_billing_unit($iv)
{
    $m = array('month' => '月', 'year' => '年', 'free' => '—');

    return isset($m[$iv]) ? $m[$iv] : (string) $iv;
}

function account_center_plan_quota(array $p)
{
    $ch = isset($p['quota_max_channels']) ? (int) $p['quota_max_channels'] : 0;
    $vid = isset($p['quota_max_videos_per_list']) ? (int) $p['quota_max_videos_per_list'] : 0;
    if ($ch === 0 && $vid === 0) {
        $cfg = plan_limits_get_tier_limits_legacy((string) $p['slug']);
        if ($cfg === null) {
            return '額度請見方案說明（或洽管理員）';
        }
        $ch = (int) $cfg['channels'];
        $vid = (int) $cfg['videos'];
    }

    return '頻道最多 ' . $ch . ' 個；待看／已看各最多 ' . $vid . ' 筆';
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f1f5f9">
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">
    <link rel="manifest" href="site.webmanifest">
    <meta name="apple-mobile-web-app-title" content="TubeLog">
    <meta name="application-name" content="TubeLog">
    <title>會員中心 — TubeLog</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: "Segoe UI", system-ui, -apple-system, "PingFang TC", "Microsoft JhengHei", sans-serif;
            margin: 0;
            min-height: 100vh;
            padding: 0 20px 40px;
            color: #0f172a;
            background-color: #f1f5f9;
            background-image:
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%2394a3b8' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E"),
                radial-gradient(ellipse 90% 70% at 100% 0%, rgba(59, 130, 246, 0.14), transparent 55%),
                radial-gradient(ellipse 70% 55% at 0% 100%, rgba(14, 165, 233, 0.1), transparent 50%),
                linear-gradient(165deg, #f8fafc 0%, #f1f5f9 45%, #eef2f7 100%);
        }
        .account-top {
            max-width: 720px;
            margin: 0 auto;
            padding: 16px 0 8px;
        }
        .account-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.95rem;
            font-weight: 600;
            color: #2563eb;
            text-decoration: none;
            padding: 8px 4px;
            border-radius: 10px;
            transition: background 0.15s, color 0.15s;
        }
        .account-back:hover { background: rgba(37, 99, 235, 0.08); color: #1d4ed8; }
        .account-back span { font-size: 1.1rem; line-height: 1; }
        .wrap { max-width: 720px; margin: 0 auto; }
        .page-head { margin-bottom: 22px; }
        h1 { font-size: 1.5rem; font-weight: 700; margin: 0 0 6px; letter-spacing: -0.02em; }
        .sub { color: #64748b; font-size: 0.92rem; margin: 0; line-height: 1.45; }
        .card {
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 16px;
            padding: 22px 24px;
            margin-bottom: 16px;
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.06), 0 10px 24px -8px rgba(15, 23, 42, 0.08);
        }
        .card h2 {
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0 0 16px;
            color: #0f172a;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 12px;
        }
        .row { margin-bottom: 12px; font-size: 14px; line-height: 1.5; }
        .row strong { display: inline-block; min-width: 100px; color: #64748b; font-weight: 600; }
        label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: #475569; }
        input[type="text"], input[type="password"], input[type="number"], select {
            width: 100%;
            max-width: 400px;
            padding: 11px 13px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            color: #0f172a;
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        input:hover, select:hover { border-color: #cbd5e1; }
        input:focus, select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        select {
            appearance: none;
            -webkit-appearance: none;
            max-width: 400px;
            padding-right: 36px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            cursor: pointer;
        }
        input[type="number"].input-narrow { max-width: 120px; }
        button[type="submit"] {
            margin-top: 14px;
            padding: 11px 20px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.35);
            transition: transform 0.15s, box-shadow 0.15s;
        }
        button[type="submit"]:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
        }
        button[type="submit"]:active { transform: translateY(0); }
        .hint { font-size: 12px; color: #64748b; margin-top: 6px; line-height: 1.45; }
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.45;
        }
        .alert--ok { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
        .alert--err { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .plan-badge {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            padding: 4px 10px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
        }
        .muted { color: #94a3b8; font-size: 13px; }
        .btn-upgrade {
            display: inline-block;
            margin-top: 16px;
            padding: 10px 18px;
            background: linear-gradient(135deg, #0369a1 0%, #0c4a6e 100%);
            color: #fff !important;
            text-decoration: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(3, 105, 161, 0.35);
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .btn-upgrade:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(3, 105, 161, 0.4); }
        .plan-table-wrap { overflow-x: auto; margin: 0 0 18px; -webkit-overflow-scrolling: touch; border-radius: 12px; border: 1px solid #e2e8f0; background: #fff; }
        .plan-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .plan-table th, .plan-table td { padding: 12px 14px; text-align: left; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .plan-table th { color: #64748b; font-weight: 600; font-size: 12px; background: #f8fafc; white-space: nowrap; }
        .plan-table tr:last-child td { border-bottom: none; }
        .plan-table tr.plan-row--current td { background: #eff6ff; }
        .plan-table .col-name { font-weight: 600; color: #0f172a; min-width: 6rem; }
        .plan-table .col-action { white-space: nowrap; width: 1%; }
        .plan-badge--current { display: inline-block; background: #dbeafe; color: #1d4ed8; padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 600; }
        .btn-upgrade-sm {
            display: inline-block;
            padding: 7px 14px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #fff !important;
            text-decoration: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.35);
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .btn-upgrade-sm:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(37, 99, 235, 0.4); }
        .subscription-detail { margin-top: 8px; padding-top: 16px; border-top: 1px dashed #e2e8f0; }
        .subscription-history-wrap { margin-top: 20px; padding-top: 16px; border-top: 1px dashed #e2e8f0; }
        .subscription-history-wrap .plan-table { font-size: 13px; }
        .subscription-history-wrap .plan-table th,
        .subscription-history-wrap .plan-table td { padding: 10px 12px; }
        .subscription-history-wrap tr.sub-history-row--current td { background: #eff6ff; }
    </style>
</head>
<body>
<header class="account-top">
    <a class="account-back" href="index.php"><span aria-hidden="true">←</span> 回首頁</a>
    <?php if (auth_is_admin()): ?>
        <a class="account-back" href="index.php?page=admin">後台會員</a>
    <?php endif; ?>
</header>
<div class="wrap">
    <div class="page-head">
        <h1>會員中心</h1>
        <p class="sub">管理個人資料、密碼與查看訂閱方案</p>
    </div>

    <?php if ($noticeText !== ''): ?>
        <div class="alert <?= (strpos($notice, 'pwd:') === 0 || $notice === 'profile_err' || $notice === 'dash_pref_err' || $notice === 'fetch_pref_err' || $notice === 'verify_resent_fail') ? 'alert--err' : 'alert--ok' ?>">
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
            <form method="post" action="index.php?page=account" style="margin-top: 12px;">
                <input type="hidden" name="resend_verification" value="1">
                <button type="submit" class="btn-upgrade-sm" style="margin-top:0;">重送驗證信</button>
            </form>
            <p class="hint" style="margin-top: 8px;">Gmail 請使用應用程式密碼作為 SMTP 密碼；公開網址請設定 <code>APP_BASE_URL</code> 讓連結正確。</p>
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
        <h2>首頁載入方式</h2>
        <p class="muted" style="margin-top: 0;">設定回首頁時，左側「最新影片」（待看／已看／已訂閱）要如何載入內容。</p>
        <form method="post" action="index.php?page=account">
            <input type="hidden" name="save_dash_pref" value="1">
            <div class="row" style="margin-bottom: 10px;">
                <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer; font-weight: normal;">
                    <input type="radio" name="dash_auto_load" value="1"<?= $dashAutoLoad === 1 ? ' checked' : '' ?> style="margin-top: 3px;">
                    <span><strong>捲動自動載入</strong> — 先顯示一截，捲到接近頁面底部時再載入下一批（類似 YouTube）。</span>
                </label>
            </div>
            <div class="row" style="margin-bottom: 14px;">
                <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer; font-weight: normal;">
                    <input type="radio" name="dash_auto_load" value="0"<?= $dashAutoLoad === 0 ? ' checked' : '' ?> style="margin-top: 3px;">
                    <span><strong>一次載入全部</strong> — 開啟首頁時一次載入完整清單（受方案單邊筆數上限），不使用捲動分頁與「載入更多」。</span>
                </label>
            </div>
            <button type="submit">儲存載入方式</button>
        </form>
        <p class="hint" style="margin-bottom: 0;">變更後重新整理首頁即生效；若資料庫尚未執行 migration <code>007_users_dash_auto_load.sql</code>，請先由管理員新增欄位。</p>
    </div>

    <div class="card">
        <h2>抓新影片（RSS）</h2>
        <p class="muted" style="margin-top: 0;">執行「📡 抓新影片」時，依下列設定從各頻道 RSS 篩選；僅會加入<strong>待看</strong>清單中尚不存在的影片，且仍會略過短於 3 分鐘的影片。</p>
        <form method="post" action="index.php?page=account">
            <input type="hidden" name="save_fetch_prefs" value="1">
            <label for="fetch_max_age_days">最近幾天內發布</label>
            <input type="number" class="input-narrow" id="fetch_max_age_days" name="fetch_max_age_days" min="1" max="7" required value="<?= (int) $fetchMaxAgeDays ?>">
            <p class="hint">只處理此天數內發布的影片（上限 7；預設 7）。</p>
            <label for="fetch_max_per_channel" style="margin-top: 14px;">每個頻道每次最多新增</label>
            <input type="number" class="input-narrow" id="fetch_max_per_channel" name="fetch_max_per_channel" min="1" max="3" required value="<?= (int) $fetchMaxPerChannel ?>">
            <p class="hint">單次執行時，每個頻道最多成功加入幾支新影片（上限 3；預設 1）。</p>
            <button type="submit">儲存抓新影片設定</button>
        </form>
        <p class="hint" style="margin-bottom: 0;">若儲存失敗，請確認已執行 migration <code>008_users_fetch_prefs.sql</code>。</p>
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
        <p class="muted" style="margin-top: 0;">以下為目前啟用的方案；付費方案為藍新 MPG 一次付清。升級後會依 Notify 更新訂閱狀態。</p>

        <div class="plan-table-wrap">
            <table class="plan-table" role="table" aria-label="方案價目表">
                <thead>
                    <tr>
                        <th scope="col">方案</th>
                        <th scope="col">價格</th>
                        <th scope="col">額度摘要</th>
                        <th scope="col" class="col-action">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($planRows) === 0): ?>
                        <tr>
                            <td colspan="4" class="muted" style="text-align:center;padding:20px;">尚無啟用中的方案，請聯絡管理員。</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($planRows as $p): ?>
                        <?php
                        $pSlug = (string) $p['slug'];
                        $isPaid = (int) $p['price_cents'] > 0;
                        $isCurrent = ($pSlug === $slugNow);
                        $showUpgrade = $isPaid && $canMpg && ! $isCurrent && (int) $p['sort_order'] > $currentPlanSort;
                        ?>
                        <tr class="<?= $isCurrent ? 'plan-row--current' : '' ?>">
                            <td class="col-name">
                                <?= htmlspecialchars($p['name']) ?>
                                <span class="muted" style="font-size:12px;">（<?= htmlspecialchars($pSlug) ?>）</span>
                            </td>
                            <td><?= htmlspecialchars(account_center_plan_price($p)) ?></td>
                            <td style="color:#475569;font-size:13px;line-height:1.45;"><?= htmlspecialchars(account_center_plan_quota($p)) ?></td>
                            <td class="col-action">
                                <?php if ($isCurrent): ?>
                                    <span class="plan-badge--current">目前使用</span>
                                <?php elseif ($showUpgrade): ?>
                                    <a class="btn-upgrade-sm" href="index.php?page=pay&amp;plan=<?= urlencode($pSlug) ?>">升級</a>
                                <?php elseif ($isPaid && ! $canMpg): ?>
                                    <span class="muted" style="font-size:12px;">金流未設定</span>
                                <?php elseif ($isPaid && ! $showUpgrade && ! $isCurrent): ?>
                                    <span class="muted" style="font-size:12px;">—</span>
                                <?php else: ?>
                                    <span class="muted" style="font-size:12px;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($sub): ?>
            <div class="subscription-detail">
                <p style="margin:0 0 10px;font-size:13px;font-weight:600;color:#334155;">目前訂閱詳情</p>
                <p class="muted" style="margin:0 0 12px;line-height:1.5;">
                    <?php if (isset($sub['slug']) && $sub['slug'] === 'free'): ?>
                        免費方案無到期日；狀態僅表示帳號已啟用此方案額度。
                    <?php else: ?>
                        付費方案於「訂閱週期」結束後，若未續約，系統會自動改回免費方案。
                    <?php endif; ?>
                </p>
                <div class="row">
                    <strong>狀態</strong> <?= htmlspecialchars(subscription_status_label_member($sub['status'], isset($sub['slug']) ? $sub['slug'] : '')) ?>
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
            </div>
        <?php else: ?>
            <p class="muted" style="margin-bottom:0;">尚無訂閱紀錄。若您應享有方案，請聯絡管理員。</p>
        <?php endif; ?>

        <?php if (count($subHistory) > 0): ?>
            <div class="subscription-history-wrap">
                <p style="margin:0 0 10px;font-size:13px;font-weight:600;color:#334155;">歷史訂閱紀錄</p>
                <p class="muted" style="margin:0 0 12px;line-height:1.5;">以下為帳號內所有訂閱紀錄（含免費註冊與升級）；與上方「目前訂閱詳情」對應者列為淺藍底色。</p>
                <div class="plan-table-wrap">
                    <table class="plan-table" role="table" aria-label="歷史訂閱紀錄">
                        <thead>
                            <tr>
                                <th scope="col">方案</th>
                                <th scope="col">狀態</th>
                                <th scope="col">計費</th>
                                <th scope="col">訂閱週期</th>
                                <th scope="col">紀錄時間</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subHistory as $h): ?>
                                <?php
                                $hid = (int) $h['id'];
                                $isCurrentRow = $sub && $hid === (int) $sub['id'];
                                ?>
                                <tr class="<?= $isCurrentRow ? 'sub-history-row--current' : '' ?>">
                                    <td class="col-name">
                                        <?= htmlspecialchars($h['plan_name']) ?>
                                        <span class="muted" style="font-size:12px;">（<?= htmlspecialchars($h['slug']) ?>）</span>
                                    </td>
                                    <td><?= htmlspecialchars(subscription_status_label_member($h['status'], isset($h['slug']) ? $h['slug'] : '')) ?></td>
                                    <td><?= htmlspecialchars(account_billing_label($h['billing_interval'] ?? '')) ?></td>
                                    <td style="color:#475569;font-size:12px;line-height:1.45;">
                                        <?php if (!empty($h['current_period_start']) || !empty($h['current_period_end'])): ?>
                                            <?= htmlspecialchars($h['current_period_start'] ? $h['current_period_start'] : '—') ?>
                                            ～
                                            <?= htmlspecialchars($h['current_period_end'] ? $h['current_period_end'] : '—') ?>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                        <?php if (!empty($h['cancel_at_period_end'])): ?>
                                            <br><span class="muted">週期結束後取消續訂</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color:#475569;font-size:12px;line-height:1.45;">
                                        <?= htmlspecialchars($h['created_at'] ?? '') ?>
                                        <?php
                                        $ua = $h['updated_at'] ?? '';
                                        $ca = $h['created_at'] ?? '';
                                        if ($ua !== '' && $ua !== $ca):
                                        ?>
                                            <br><span class="muted">更新 <?= htmlspecialchars($ua) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($slugNow === 'free' && ! $canMpg): ?>
            <p class="hint" style="margin-top: 14px; margin-bottom: 0;">若要線上升級，請在環境變數或設定檔填寫藍新 <code>MPG_MERCHANT_ID</code>、<code>MPG_HASH_KEY</code>、<code>MPG_HASH_IV</code>；並執行 migration <code>006_payment_orders_minimal.sql</code>、<code>009_payment_orders_plan_slug.sql</code>。</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
