<?php
require_once __DIR__ . '/../../config/bootstrap.php';

$error = '';
$redirect = isset($_POST['redirect']) ? (string)$_POST['redirect'] : (isset($_GET['redirect']) ? (string)$_GET['redirect'] : 'index.php');

if (auth_check()) {
    header('Location: ' . (preg_match('#^index\.php#', $redirect) ? $redirect : 'index.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>登入 — YouTube Tracker</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f7fa; padding: 40px 20px; }
        .box { max-width: 400px; margin: 0 auto; background: #fff; padding: 28px; border-radius: 12px; box-shadow: 0 3px 12px rgba(0,0,0,.08); }
        h1 { font-size: 1.25rem; margin: 0 0 20px; }
        label { display: block; margin-bottom: 6px; font-size: 14px; color: #444; }
        input[type="email"], input[type="password"] { width: 100%; box-sizing: border-box; padding: 10px 12px; margin-bottom: 16px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; }
        button { width: 100%; padding: 12px; background: #0077cc; color: #fff; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; }
        button:hover { background: #0066b3; }
        .err { color: #b91c1c; font-size: 14px; margin-bottom: 12px; }
        .links { margin-top: 18px; font-size: 14px; text-align: center; }
        .links a { color: #0077cc; }
    </style>
</head>
<body>
<div class="box">
    <h1>登入 YouTube Tracker</h1>
    <?php if ($error !== ''): ?><p class="err"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post" action="index.php?page=login">
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autocomplete="username" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <label for="password">密碼</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">
        <button type="submit">登入</button>
    </form>
    <p class="links">還沒有帳號？<a href="index.php?page=register">註冊</a></p>
</div>
</body>
</html>
