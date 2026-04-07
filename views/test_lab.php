<?php
require_once __DIR__ . '/../config/bootstrap.php';
auth_require_test_lab();
$uiTheme = (isset($_SESSION['ui_theme']) && $_SESSION['ui_theme'] === 'dark') ? 'dark' : 'light';
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0f172a">
<link rel="icon" href="favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="apple-touch-icon.png">
<link rel="manifest" href="site.webmanifest">
<meta name="apple-mobile-web-app-title" content="TubeLog">
<meta name="application-name" content="TubeLog">
<title>測試｜TubeLog</title>
<style>
body { font-family: Arial, sans-serif; background: #f5f7fa; padding: 24px; max-width: 560px; margin: 0 auto; }
h1 { font-size: 1.25rem; margin: 0 0 8px; }
.lead { color: #64748b; font-size: 14px; margin: 0 0 20px; line-height: 1.5; }
.tools { display: flex; flex-direction: column; gap: 10px; }
.tools a {
    display: block;
    padding: 12px 16px;
    background: #0077cc;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    font-size: 15px;
}
.tools a:hover { background: #005fa3; }
.nav-top { margin-bottom: 20px; font-size: 14px; }
.nav-top a { color: #0077cc; margin-right: 14px; text-decoration: none; }
.nav-top a:hover { text-decoration: underline; }

body[data-theme="dark"] { background: #0b1220; color: #e2e8f0; }
body[data-theme="dark"] .lead { color: rgba(226,232,240,0.72); }
body[data-theme="dark"] .nav-top a { color: #93c5fd; }
</style>
</head>
<body data-theme="<?= htmlspecialchars($uiTheme, ENT_QUOTES, 'UTF-8') ?>">
<nav class="nav-top">
    <a href="index.php">🏠 首頁</a>
    <a href="index.php?page=account">會員中心</a>
    <?php if (auth_is_admin()): ?>
    <a href="index.php?page=admin">後台會員</a>
    <?php endif; ?>
    <a href="index.php?page=test_lab">測試</a>
    <a href="index.php?page=logout">登出</a>
</nav>
<h1>🧪 測試</h1>
<p class="lead">此頁用來集中測試與實驗功能；以下為常用連結，之後可在此擴充。</p>
<div class="tools">
    <a href="index.php?page=videos&watched=0">📋 待看清單</a>
    <a href="index.php?page=videos&watched=1">✅ 已看清單</a>
    <a href="index.php?page=channels">📺 頻道管理</a>
    <a href="index.php?page=channel_categories">📂 分類管理</a>
</div>
</body>
</html>
