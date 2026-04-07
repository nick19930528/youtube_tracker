<?php
require_once __DIR__ . '/../../config/bootstrap.php';
auth_require_login();

$assetBase = 'assets/line/';
$qrSets = array(
    array(
        'file' => 'line-qr-large.png',
        'title' => '大尺寸',
        'hint' => '適合海報、看板、實體文宣列印。',
    ),
    array(
        'file' => 'line-qr-medium.png',
        'title' => '中尺寸',
        'hint' => '適合網頁內文、電子報、簡報等。',
    ),
    array(
        'file' => 'line-qr-small.png',
        'title' => '小尺寸',
        'hint' => '適合名片、頁尾、側欄等精簡區塊。',
    ),
);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#06c755">
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <title>客服資訊 — YouTube Tracker</title>
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
                radial-gradient(ellipse 90% 70% at 100% 0%, rgba(6, 199, 85, 0.1), transparent 55%),
                linear-gradient(165deg, #f8fafc 0%, #f1f5f9 100%);
        }
        .account-top {
            max-width: 960px;
            margin: 0 auto;
            padding: 16px 0 8px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px 16px;
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
        .wrap { max-width: 960px; margin: 0 auto; }
        .page-head { margin-bottom: 20px; }
        h1 { font-size: 1.5rem; font-weight: 700; margin: 0 0 8px; letter-spacing: -0.02em; }
        .sub { color: #64748b; font-size: 0.95rem; margin: 0; line-height: 1.55; max-width: 52rem; }
        .line-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            font-weight: 700;
            color: #fff;
            background: #06c755;
            padding: 4px 10px;
            border-radius: 999px;
            letter-spacing: 0.02em;
        }
        .card {
            background: rgba(255, 255, 255, 0.97);
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 16px;
            padding: 22px 24px;
            margin-bottom: 16px;
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.06), 0 10px 24px -8px rgba(15, 23, 42, 0.08);
        }
        .card h2 {
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0 0 14px;
            color: #0f172a;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 12px;
        }
        .qr-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 8px;
        }
        .qr-item {
            text-align: center;
            padding: 16px;
            border-radius: 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .qr-item h3 {
            margin: 0 0 6px;
            font-size: 1rem;
            color: #0f172a;
        }
        .qr-item .hint {
            margin: 0 0 14px;
            font-size: 0.8rem;
            color: #64748b;
            line-height: 1.45;
        }
        .qr-item img {
            width: 100%;
            max-width: 240px;
            height: auto;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
        }
        .steps {
            margin: 0;
            padding-left: 1.25rem;
            font-size: 0.92rem;
            line-height: 1.75;
            color: #334155;
        }
        .steps li { margin-bottom: 6px; }
        .muted { color: #94a3b8; font-size: 0.85rem; line-height: 1.5; margin-top: 12px; }
    </style>
</head>
<body>
<header class="account-top">
    <a class="account-back" href="index.php"><span aria-hidden="true">←</span> 回首頁</a>
    <a class="account-back" href="index.php?page=account">會員中心</a>
    <?php if (auth_is_admin()): ?>
        <a class="account-back" href="index.php?page=admin">後台會員</a>
    <?php endif; ?>
</header>
<div class="wrap">
    <div class="page-head">
        <p style="margin:0 0 10px;"><span class="line-badge">LINE 官方帳號</span></p>
        <h1>客服資訊</h1>
        <p class="sub">若有使用問題、帳務或功能建議，歡迎透過 LINE 官方帳號與我們聯繫。請使用手機 LINE 掃描下方任一格 QR Code，即可加入好友並傳送訊息。</p>
    </div>

    <div class="card">
        <h2>加入好友 QR Code（三種尺寸）</h2>
        <p class="muted" style="margin-top:0;">內容相同，僅解析度與檔案大小不同；請依您的使用場景選擇合適圖檔下載或截圖使用。</p>
        <div class="qr-grid">
            <?php foreach ($qrSets as $item): ?>
                <?php
                $src = $assetBase . $item['file'];
                $pathFs = __DIR__ . '/../../' . $src;
                $hasImg = is_file($pathFs);
                ?>
                <div class="qr-item">
                    <h3><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="hint"><?= htmlspecialchars($item['hint'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php if ($hasImg): ?>
                        <img src="<?= htmlspecialchars($src, ENT_QUOTES, 'UTF-8') ?>" width="240" height="240" alt="LINE 官方帳號 QR Code（<?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?>）" loading="lazy">
                    <?php else: ?>
                        <p class="hint" style="color:#991b1b;">圖檔未就緒：請將 <code><?= htmlspecialchars($item['file'], ENT_QUOTES, 'UTF-8') ?></code> 放於專案 <code>assets/line/</code> 目錄。</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card">
        <h2>使用方式</h2>
        <ol class="steps">
            <li>開啟手機上的 LINE 應用程式。</li>
            <li>點選「加入好友」→「行動條碼」，掃描上方 QR Code。</li>
            <li>加入官方帳號後，即可在聊天室留言，我們會於服務時間內回覆。</li>
        </ol>
        <p class="muted">若掃描失敗，請確認網路連線正常，或改試其他尺寸的 QR 圖檔。</p>
    </div>
</div>
</body>
</html>
