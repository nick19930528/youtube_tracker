<?php
require_once __DIR__ . '/../../config/bootstrap.php';
auth_require_login();
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
} elseif ($notice === 'dash_pref_ok') {
    $noticeText = '已更新首頁載入方式。';
} elseif ($notice === 'dash_pref_err') {
    $noticeText = '無法更新首頁載入方式，請稍後再試。';
} elseif ($notice === 'fetch_pref_ok') {
    $noticeText = '已更新抓新影片設定。';
} elseif ($notice === 'fetch_pref_err') {
    $noticeText = '無法更新抓新影片設定，請稍後再試。';
}

$selGender = array_key_exists('gender', $profile) && $profile['gender'] !== null && $profile['gender'] !== ''
    ? $profile['gender']
    : '';
$dashAutoLoad = (isset($profile['dash_auto_load']) && (int)$profile['dash_auto_load'] === 0) ? 0 : 1;
$fetchMaxAgeDays = isset($profile['fetch_max_age_days']) ? (int) $profile['fetch_max_age_days'] : 7;
$fetchMaxPerChannel = isset($profile['fetch_max_per_channel']) ? (int) $profile['fetch_max_per_channel'] : 1;
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f1f5f9">
    <title>會員中心 — YouTube Tracker</title>
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
    </style>
</head>
<body>
<header class="account-top">
    <a class="account-back" href="index.php"><span aria-hidden="true">←</span> 回首頁</a>
</header>
<div class="wrap">
    <div class="page-head">
        <h1>會員中心</h1>
        <p class="sub">管理個人資料、密碼與查看訂閱方案</p>
    </div>

    <?php if ($noticeText !== ''): ?>
        <div class="alert <?= (strpos($notice, 'pwd:') === 0 || $notice === 'profile_err' || $notice === 'dash_pref_err' || $notice === 'fetch_pref_err') ? 'alert--err' : 'alert--ok' ?>">
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
            <input type="number" class="input-narrow" id="fetch_max_age_days" name="fetch_max_age_days" min="1" max="730" required value="<?= (int) $fetchMaxAgeDays ?>">
            <p class="hint">只處理此天數內發布的影片（預設 7）。</p>
            <label for="fetch_max_per_channel" style="margin-top: 14px;">每個頻道每次最多新增</label>
            <input type="number" class="input-narrow" id="fetch_max_per_channel" name="fetch_max_per_channel" min="1" max="100" required value="<?= (int) $fetchMaxPerChannel ?>">
            <p class="hint">單次執行時，每個頻道最多成功加入幾支新影片（預設 1）。</p>
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
            <p style="margin: 0;">
                <a class="btn-upgrade" href="index.php?page=pay">升級 Go（MPG 一次付清 · 最小單元）</a>
            </p>
            <p class="hint" style="margin-top: 10px; margin-bottom: 0;">使用藍新 MPG 幕前一次付清；請先執行資料庫 migration <code>006_payment_orders_minimal.sql</code> 並設定 <code>MPG_*</code> 金鑰。</p>
        <?php elseif ($slugNow === 'free'): ?>
            <p class="hint" style="margin-top: 16px; margin-bottom: 0;">付費升級請在 <code>config/payment_minimal.php</code> 或環境變數設定商店代號與 Hash Key／IV。</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
