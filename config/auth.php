<?php
/**
 * 會員：Session、登入、註冊、登出
 * 相容 PHP 5.6+（無 7.1 的可空回傳型別、無 7.3 的 session 陣列參數）
 */
require_once __DIR__ . '/mail.php';
function auth_bootstrap_session()
{
    if (function_exists('session_status')) {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }
    }
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
        session_set_cookie_params(array(
            'lifetime' => 0,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ));
    } else {
        session_set_cookie_params(0, '/', '', $secure, true);
    }
    session_name('YTTRACKER_SID');
    session_start();
}

/**
 * @return array|null
 */
function auth_user()
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return array(
        'id' => (int)$_SESSION['user_id'],
        'email' => isset($_SESSION['user_email']) ? (string)$_SESSION['user_email'] : '',
        'name' => isset($_SESSION['user_name']) ? (string)$_SESSION['user_name'] : '',
    );
}

function auth_user_id()
{
    $u = auth_user();
    return $u ? $u['id'] : 0;
}

function auth_check()
{
    return auth_user_id() > 0;
}

function auth_require_login()
{
    if (auth_check()) {
        return;
    }
    $next = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'index.php';
    header('Location: index.php?page=login&redirect=' . rawurlencode($next));
    exit;
}

function auth_login(PDO $pdo, $email, $password)
{
    $email = trim($email);
    if ($email === '' || $password === '') {
        return false;
    }
    $stmt = $pdo->prepare('SELECT id, email, name, password_hash, COALESCE(dash_auto_load, 1) AS dash_auto_load, email_verified_at FROM users WHERE email = ? LIMIT 1');
    $stmt->execute(array($email));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || !password_verify($password, $row['password_hash'])) {
        return false;
    }
    $_SESSION['user_id'] = (int)$row['id'];
    $_SESSION['user_email'] = $row['email'];
    $_SESSION['user_name'] = $row['name'];
    $_SESSION['dash_auto_load'] = isset($row['dash_auto_load']) ? ((int)$row['dash_auto_load'] ? 1 : 0) : 1;
    $ev = isset($row['email_verified_at']) && $row['email_verified_at'] !== null && $row['email_verified_at'] !== '';
    $_SESSION['email_verified'] = $ev ? 1 : 0;
    return true;
}

function auth_logout()
{
    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/**
 * 確保存在免費方案，回傳 plan id
 */
function auth_ensure_free_plan(PDO $pdo)
{
    $stmt = $pdo->query("SELECT id FROM subscription_plans WHERE slug = 'free' LIMIT 1");
    $id = $stmt ? (int)$stmt->fetchColumn() : 0;
    if ($id > 0) {
        return $id;
    }
    $pdo->exec("INSERT INTO subscription_plans (name, slug, price_cents, currency, billing_interval, is_active, sort_order)
        VALUES ('免費', 'free', 0, 'TWD', 'free', 1, 0)");
    return (int)$pdo->lastInsertId();
}

/**
 * @return bool|string
 */
function auth_register(PDO $pdo, $name, $email, $gender, $password, $password2)
{
    $name = trim($name);
    $email = trim($email);
    if ($name === '' || $email === '') {
        return '請填寫姓名與 Email。';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Email 格式不正確。';
    }
    if (strlen($password) < 8) {
        return '密碼至少 8 個字元。';
    }
    if ($password !== $password2) {
        return '兩次密碼不一致。';
    }
    $allowedG = array('', 'm', 'f', 'other');
    $gender = ($gender !== null && in_array($gender, $allowedG, true)) ? ($gender === '' ? null : $gender) : null;

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute(array($email));
    if ($stmt->fetchColumn()) {
        return '此 Email 已註冊。';
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, name, gender) VALUES (?, ?, ?, ?)');
    $stmt->execute(array($email, $hash, $name, $gender));
    $uid = (int)$pdo->lastInsertId();

    $planId = auth_ensure_free_plan($pdo);
    $stmt = $pdo->prepare('INSERT INTO subscriptions (user_id, plan_id, status) VALUES (?, ?, ?)');
    $stmt->execute(array($uid, $planId, 'active'));

    $_SESSION['user_id'] = $uid;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $name;
    $_SESSION['dash_auto_load'] = 1;
    $_SESSION['email_verified'] = 0;

    if (auth_email_issue_and_send($pdo, $uid, $email, $name)) {
        $_SESSION['email_verification_sent'] = 1;
    } else {
        $_SESSION['email_verification_send_failed'] = 1;
    }

    return true;
}

function auth_is_email_verified()
{
    if (!isset($_SESSION['email_verified'])) {
        return true;
    }
    return (int)$_SESSION['email_verified'] === 1;
}

/**
 * @return bool 是否成功寄出（權杖已寫入 DB 時仍可重送）
 */
function auth_email_issue_and_send(PDO $pdo, $userId, $email, $name)
{
    $userId = (int)$userId;
    if ($userId < 1) {
        return false;
    }
    try {
        if (function_exists('random_bytes')) {
            $raw = random_bytes(32);
        } else {
            $raw = openssl_random_pseudo_bytes(32);
        }
        $token = bin2hex($raw);
    } catch (Exception $e) {
        return false;
    }

    try {
        $stmt = $pdo->prepare('UPDATE users SET email_verification_token = ?, email_verification_expires_at = DATE_ADD(NOW(), INTERVAL 48 HOUR) WHERE id = ? AND email_verified_at IS NULL');
        $stmt->execute(array($token, $userId));
        if ($stmt->rowCount() < 1) {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }

    return mail_send_verification_email($email, $name, $token);
}

/**
 * @return bool
 */
function auth_verify_email_by_token(PDO $pdo, $token)
{
    $token = trim((string)$token);
    if (strlen($token) !== 64 || !ctype_xdigit($token)) {
        return false;
    }

    try {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email_verification_token = ? AND email_verification_expires_at > NOW() LIMIT 1');
        $stmt->execute(array($token));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return false;
    }
    if (!$row) {
        return false;
    }
    $uid = (int)$row['id'];

    try {
        $stmt = $pdo->prepare('UPDATE users SET email_verified_at = NOW(), email_verification_token = NULL, email_verification_expires_at = NULL WHERE id = ? LIMIT 1');
        $stmt->execute(array($uid));
    } catch (Exception $e) {
        return false;
    }

    if (auth_check() && (int)auth_user_id() === $uid) {
        $_SESSION['email_verified'] = 1;
    }

    return true;
}
