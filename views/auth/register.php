<?php
require_once __DIR__ . '/../../config/bootstrap.php';

$error = '';

if (auth_check()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>註冊 — YouTube Tracker</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f7fa; padding: 40px 20px; }
        .box { max-width: 400px; margin: 0 auto; background: #fff; padding: 28px; border-radius: 12px; box-shadow: 0 3px 12px rgba(0,0,0,.08); }
        h1 { font-size: 1.25rem; margin: 0 0 20px; }
        label { display: block; margin-bottom: 6px; font-size: 14px; color: #444; }
        input[type="text"], input[type="email"], input[type="password"], select { width: 100%; box-sizing: border-box; padding: 10px 12px; margin-bottom: 16px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; }
        button { width: 100%; padding: 12px; background: #0077cc; color: #fff; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; }
        button:hover { background: #0066b3; }
        .err { color: #b91c1c; font-size: 14px; margin-bottom: 12px; }
        .links { margin-top: 18px; font-size: 14px; text-align: center; }
        .links a { color: #0077cc; }
        .hint { font-size: 12px; color: #666; margin-top: -10px; margin-bottom: 14px; }
    </style>
</head>
<body>
<div class="box">
    <h1>註冊新帳號</h1>
    <?php if ($error !== ''): ?><p class="err"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post" action="index.php?page=register">
        <label for="name">姓名</label>
        <input type="text" id="name" name="name" required maxlength="191" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        <label for="gender">性別</label>
        <select id="gender" name="gender">
            <option value="">不填寫</option>
            <option value="m"<?= (($_POST['gender'] ?? '') === 'm') ? ' selected' : '' ?>>男</option>
            <option value="f"<?= (($_POST['gender'] ?? '') === 'f') ? ' selected' : '' ?>>女</option>
            <option value="other"<?= (($_POST['gender'] ?? '') === 'other') ? ' selected' : '' ?>>其他</option>
        </select>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autocomplete="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <label for="password">密碼</label>
        <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
        <p class="hint">至少 8 個字元</p>
        <label for="password2">確認密碼</label>
        <input type="password" id="password2" name="password2" required minlength="8" autocomplete="new-password">
        <button type="submit">建立帳號</button>
    </form>
    <p class="links">已有帳號？<a href="index.php?page=login">登入</a></p>
</div>
</body>
</html>
