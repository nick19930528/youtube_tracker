<?php
require_once __DIR__ . '/../../config/bootstrap.php';

$error = '';
$redirect = isset($_POST['redirect']) ? (string)$_POST['redirect'] : (isset($_GET['redirect']) ? (string)$_GET['redirect'] : 'index.php');

$authPage = (isset($_GET['page']) && $_GET['page'] === 'register') ? 'register' : 'login';

if (auth_check()) {
    header('Location: ' . (preg_match('#^index\.php#', $redirect) ? $redirect : 'index.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($authPage === 'register') {
        $pdo = (new Database())->getConnection();
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $gender = isset($_POST['gender']) ? (string)$_POST['gender'] : '';
        $password = (string)($_POST['password'] ?? '');
        $password2 = (string)($_POST['password2'] ?? '');
        $result = auth_register($pdo, $name, $email, $gender, $password, $password2);
        if ($result === true) {
            header('Location: index.php');
            exit;
        }
        $error = is_string($result) ? $result : '註冊失敗。';
    } else {
        $pdo = (new Database())->getConnection();
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        if (auth_login($pdo, $email, $password)) {
            $target = $redirect;
            if ($target === '' || strpos($target, 'page=login') !== false || !preg_match('#^index\.php#', $target)) {
                $target = 'index.php';
            }
            header('Location: ' . $target);
            exit;
        }
        $error = 'Email 或密碼錯誤。';
    }
}

$loginTabHref = 'index.php?page=login';
if ($redirect !== '') {
    $loginTabHref .= '&redirect=' . rawurlencode($redirect);
}
$registerTabHref = 'index.php?page=register';
$pageTitle = $authPage === 'register' ? '註冊' : '登入';
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <title><?= htmlspecialchars($pageTitle) ?> — YouTube Tracker</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", system-ui, -apple-system, "PingFang TC", "Microsoft JhengHei", sans-serif;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            position: relative;
            overflow-x: hidden;
        }
        .auth-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 10%, rgba(59, 130, 246, 0.35), transparent 55%),
                radial-gradient(ellipse 70% 50% at 85% 80%, rgba(236, 72, 153, 0.22), transparent 50%),
                radial-gradient(ellipse 50% 40% at 50% 50%, rgba(34, 211, 238, 0.12), transparent 45%),
                linear-gradient(165deg, #0f172a 0%, #1e293b 40%, #334155 100%);
        }
        .auth-bg::after {
            content: "";
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.6;
            pointer-events: none;
        }
        .auth-shell {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            animation: auth-in 0.55s ease-out;
        }
        .auth-shell.auth-shell--wide { max-width: 460px; }
        @keyframes auth-in {
            from {
                opacity: 0;
                transform: translateY(16px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .auth-card {
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 28px 28px 26px;
            box-shadow:
                0 4px 6px -1px rgba(15, 23, 42, 0.12),
                0 24px 48px -12px rgba(15, 23, 42, 0.35),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.65);
        }
        .auth-brand {
            text-align: center;
            margin-bottom: 18px;
        }
        .auth-brand-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 10px;
            border-radius: 16px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 45%, #b91c1c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            line-height: 1;
            box-shadow: 0 10px 24px -6px rgba(220, 38, 38, 0.55);
        }
        .auth-brand h1 {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #0f172a;
        }
        .auth-brand p {
            margin: 6px 0 0;
            font-size: 0.88rem;
            color: #64748b;
            line-height: 1.45;
        }
        .auth-tabs {
            display: flex;
            margin: 0 0 20px;
            padding: 4px;
            border-radius: 14px;
            background: #f1f5f9;
            gap: 4px;
        }
        .auth-tab {
            flex: 1;
            text-align: center;
            padding: 10px 12px;
            font-size: 0.95rem;
            font-weight: 600;
            color: #64748b;
            text-decoration: none;
            border-radius: 11px;
            transition: color 0.2s, background 0.2s, box-shadow 0.2s;
        }
        .auth-tab:hover {
            color: #334155;
            background: rgba(255, 255, 255, 0.7);
        }
        .auth-tab.is-active {
            color: #0f172a;
            background: #fff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.12);
        }
        .auth-panel-title {
            margin: 0 0 14px;
            font-size: 1rem;
            font-weight: 600;
            color: #334155;
        }
        .auth-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            margin-bottom: 16px;
            border-radius: 12px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            font-size: 0.9rem;
            line-height: 1.4;
            animation: shake 0.45s ease;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-4px); }
            40% { transform: translateX(4px); }
            60% { transform: translateX(-2px); }
            80% { transform: translateX(2px); }
        }
        .auth-alert span:first-child { flex-shrink: 0; }
        label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #475569;
            letter-spacing: 0.02em;
        }
        .field { margin-bottom: 14px; }
        .field:last-of-type { margin-bottom: 0; }
        .field-hint {
            margin: -6px 0 0;
            padding-left: 2px;
            font-size: 0.75rem;
            color: #94a3b8;
            line-height: 1.4;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 12px 14px;
            font-size: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
            color: #0f172a;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        select {
            appearance: none;
            -webkit-appearance: none;
            padding-right: 40px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            cursor: pointer;
        }
        input::placeholder { color: #94a3b8; }
        input:hover,
        select:hover { border-color: #cbd5e1; }
        input:focus,
        select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        .btn-submit {
            width: 100%;
            margin-top: 16px;
            padding: 14px 18px;
            font-size: 1rem;
            font-weight: 600;
            font-family: inherit;
            color: #fff;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #1e40af 100%);
            box-shadow: 0 4px 14px -2px rgba(37, 99, 235, 0.55);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px -4px rgba(37, 99, 235, 0.5);
        }
        .btn-submit:active { transform: translateY(0); }
        .auth-panels { position: relative; }
        .auth-panel { display: none; }
        .auth-panel.is-active { display: block; }
    </style>
</head>
<body>
    <div class="auth-bg" aria-hidden="true"></div>
    <main class="auth-shell<?= $authPage === 'register' ? ' auth-shell--wide' : '' ?>">
        <div class="auth-card">
            <div class="auth-brand">
                <div class="auth-brand-icon" aria-hidden="true">▶</div>
                <h1>YouTube Tracker</h1>
                <p>登入或註冊，管理訂閱與待看清單</p>
            </div>
            <nav class="auth-tabs" role="tablist" aria-label="登入或註冊">
                <a
                    id="tab-login"
                    class="auth-tab<?= $authPage === 'login' ? ' is-active' : '' ?>"
                    href="<?= htmlspecialchars($loginTabHref) ?>"
                    role="tab"
                    aria-selected="<?= $authPage === 'login' ? 'true' : 'false' ?>"
                    aria-controls="panel-login"
                >登入</a>
                <a
                    id="tab-register"
                    class="auth-tab<?= $authPage === 'register' ? ' is-active' : '' ?>"
                    href="<?= htmlspecialchars($registerTabHref) ?>"
                    role="tab"
                    aria-selected="<?= $authPage === 'register' ? 'true' : 'false' ?>"
                    aria-controls="panel-register"
                >註冊</a>
            </nav>

            <?php if ($error !== ''): ?>
                <div class="auth-alert" role="alert">
                    <span aria-hidden="true">⚠️</span>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <div class="auth-panels">
                <div
                    id="panel-login"
                    class="auth-panel<?= $authPage === 'login' ? ' is-active' : '' ?>"
                    role="tabpanel"
                    aria-labelledby="tab-login"
                    <?= $authPage === 'login' ? '' : 'hidden' ?>
                >
                    <p class="auth-panel-title">歡迎回來</p>
                    <form method="post" action="<?= htmlspecialchars($loginTabHref) ?>" autocomplete="on">
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                        <div class="field">
                            <label for="login-email">Email</label>
                            <input type="email" id="login-email" name="email" required autocomplete="username" placeholder="name@example.com" value="<?= htmlspecialchars($authPage === 'login' ? ($_POST['email'] ?? '') : '') ?>">
                        </div>
                        <div class="field">
                            <label for="login-password">密碼</label>
                            <input type="password" id="login-password" name="password" required autocomplete="current-password" placeholder="請輸入密碼">
                        </div>
                        <button type="submit" class="btn-submit">登入</button>
                    </form>
                </div>

                <div
                    id="panel-register"
                    class="auth-panel<?= $authPage === 'register' ? ' is-active' : '' ?>"
                    role="tabpanel"
                    aria-labelledby="tab-register"
                    <?= $authPage === 'register' ? '' : 'hidden' ?>
                >
                    <p class="auth-panel-title">建立新帳號</p>
                    <form method="post" action="<?= htmlspecialchars($registerTabHref) ?>" autocomplete="on">
                        <div class="field">
                            <label for="reg-name">姓名</label>
                            <input type="text" id="reg-name" name="name" required maxlength="191" autocomplete="name" placeholder="您的顯示名稱" value="<?= htmlspecialchars($authPage === 'register' ? ($_POST['name'] ?? '') : '') ?>">
                        </div>
                        <div class="field">
                            <label for="reg-gender">性別</label>
                            <select id="reg-gender" name="gender">
                                <option value="">不填寫</option>
                                <option value="m"<?= ($authPage === 'register' && ($_POST['gender'] ?? '') === 'm') ? ' selected' : '' ?>>男</option>
                                <option value="f"<?= ($authPage === 'register' && ($_POST['gender'] ?? '') === 'f') ? ' selected' : '' ?>>女</option>
                                <option value="other"<?= ($authPage === 'register' && ($_POST['gender'] ?? '') === 'other') ? ' selected' : '' ?>>其他</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="reg-email">Email</label>
                            <input type="email" id="reg-email" name="email" required autocomplete="email" placeholder="name@example.com" value="<?= htmlspecialchars($authPage === 'register' ? ($_POST['email'] ?? '') : '') ?>">
                        </div>
                        <div class="field">
                            <label for="reg-password">密碼</label>
                            <input type="password" id="reg-password" name="password" required minlength="8" autocomplete="new-password" placeholder="至少 8 個字元">
                            <p class="field-hint">密碼需至少 8 個字元</p>
                        </div>
                        <div class="field">
                            <label for="reg-password2">確認密碼</label>
                            <input type="password" id="reg-password2" name="password2" required minlength="8" autocomplete="new-password" placeholder="再次輸入密碼">
                        </div>
                        <button type="submit" class="btn-submit">建立帳號</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
