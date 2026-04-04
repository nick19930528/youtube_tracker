<?php
require_once __DIR__ . '/../config/bootstrap.php';
auth_require_login();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>測試｜YouTube Tracker</title>
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
</style>
</head>
<body>
<nav class="nav-top">
    <a href="index.php">🏠 首頁</a>
    <a href="index.php?page=account">會員中心</a>
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
