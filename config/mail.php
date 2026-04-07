<?php
/**
 * PHPMailer：寄送 Email 驗證信（SMTP 設定來自環境變數）
 */

function mail_public_base_url()
{
    $env = getenv('APP_BASE_URL');
    if ($env !== false && $env !== '') {
        return rtrim($env, '/');
    }
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $scheme = $secure ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? (string)$_SERVER['HTTP_HOST'] : 'localhost';
    return $scheme . '://' . $host;
}

function mail_smtp_password_normalized()
{
    $p = getenv('MAIL_SMTP_PASSWORD');
    if ($p === false || $p === '') {
        return '';
    }
    return preg_replace('/\s+/', '', (string)$p);
}

function mail_is_configured()
{
    $u = getenv('MAIL_SMTP_USER');
    if ($u === false || trim((string)$u) === '') {
        return false;
    }
    return mail_smtp_password_normalized() !== '';
}

/**
 * @return bool
 */
function mail_phpmailer_autoload()
{
    $autoload = dirname(__DIR__) . '/vendor/autoload.php';
    if (!is_readable($autoload)) {
        return false;
    }
    require_once $autoload;
    return true;
}

/**
 * @return bool
 */
function mail_send_verification_email($toEmail, $toName, $verifyToken)
{
    if (!mail_is_configured()) {
        return false;
    }
    if (!mail_phpmailer_autoload()) {
        return false;
    }

    $base = mail_public_base_url();
    $link = $base . '/index.php?page=verify_email&token=' . rawurlencode($verifyToken);

    $fromAddr = getenv('MAIL_FROM_ADDRESS');
    if ($fromAddr === false || trim((string)$fromAddr) === '') {
        $fromAddr = getenv('MAIL_SMTP_USER');
    }
    $fromAddr = trim((string)$fromAddr);
    $fromName = getenv('MAIL_FROM_NAME');
    $fromName = ($fromName !== false && trim((string)$fromName) !== '') ? trim((string)$fromName) : 'TubeLog';

    $host = getenv('MAIL_SMTP_HOST');
    if ($host === false || trim((string)$host) === '') {
        $host = 'smtp.gmail.com';
    } else {
        $host = trim((string)$host);
    }
    $port = getenv('MAIL_SMTP_PORT');
    $port = ($port !== false && (string)$port !== '') ? (int)$port : 587;

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = trim((string)getenv('MAIL_SMTP_USER'));
        $mail->Password = mail_smtp_password_normalized();
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $port;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($fromAddr, $fromName);
        $mail->addAddress($toEmail, $toName !== '' ? $toName : $toEmail);
        $mail->isHTML(true);
        $mail->Subject = '請驗證您的 Email — TubeLog';
        $safeName = htmlspecialchars($toName !== '' ? $toName : '使用者', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeLink = htmlspecialchars($link, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $mail->Body = '<p>' . $safeName . ' 您好，</p>'
            . '<p>請點擊下方連結完成 Email 驗證（若您未註冊本網站，請忽略此信）：</p>'
            . '<p><a href="' . $safeLink . '">' . $safeLink . '</a></p>'
            . '<p>連結有效期限約 48 小時。</p>';
        $mail->AltBody = ($toName !== '' ? $toName : '您好') . "\n\n請開啟以下連結完成驗證：\n" . $link . "\n";
        $mail->send();
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * 忘記密碼：寄送暫時登入密碼
 *
 * @return bool
 */
function mail_send_temporary_password($toEmail, $toName, $plainPassword)
{
    if (!mail_is_configured()) {
        return false;
    }
    if (!mail_phpmailer_autoload()) {
        return false;
    }

    $fromAddr = getenv('MAIL_FROM_ADDRESS');
    if ($fromAddr === false || trim((string)$fromAddr) === '') {
        $fromAddr = getenv('MAIL_SMTP_USER');
    }
    $fromAddr = trim((string)$fromAddr);
    $fromName = getenv('MAIL_FROM_NAME');
    $fromName = ($fromName !== false && trim((string)$fromName) !== '') ? trim((string)$fromName) : 'TubeLog';

    $host = getenv('MAIL_SMTP_HOST');
    if ($host === false || trim((string)$host) === '') {
        $host = 'smtp.gmail.com';
    } else {
        $host = trim((string)$host);
    }
    $port = getenv('MAIL_SMTP_PORT');
    $port = ($port !== false && (string)$port !== '') ? (int)$port : 587;

    $base = mail_public_base_url();
    $loginUrl = $base . '/index.php?page=login';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = trim((string)getenv('MAIL_SMTP_USER'));
        $mail->Password = mail_smtp_password_normalized();
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $port;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($fromAddr, $fromName);
        $mail->addAddress($toEmail, $toName !== '' ? $toName : $toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'TubeLog — 暫時登入密碼';

        $safeName = htmlspecialchars($toName !== '' ? $toName : '使用者', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safePass = htmlspecialchars($plainPassword, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeLogin = htmlspecialchars($loginUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $mail->Body = '<p>' . $safeName . ' 您好，</p>'
            . '<p>您申請重設密碼，系統已為您產生<strong>暫時密碼</strong>如下（請勿轉寄他人）：</p>'
            . '<p style="font-size:1.15em;letter-spacing:0.05em;"><code>' . $safePass . '</code></p>'
            . '<p>請使用此密碼<a href="' . $safeLogin . '">登入</a>後，盡快到<strong>會員中心</strong>變更為您自己的密碼。</p>'
            . '<p>若您未申請重設密碼，請忽略此信並確認帳號安全。</p>';

        $mail->AltBody = ($toName !== '' ? $toName : '您好') . "\n\n"
            . "您的暫時登入密碼：\n" . $plainPassword . "\n\n"
            . "請登入後至會員中心變更密碼：\n" . $loginUrl . "\n";

        $mail->send();
        return true;
    } catch (Throwable $e) {
        return false;
    }
}
