<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/legal_links.inc.php';

$isLoggedIn = auth_check();
$backHref = $isLoggedIn ? 'index.php' : 'index.php?page=login';
$backLabel = $isLoggedIn ? '回首頁' : '返回登入';
$docUpdated = '2026/05/16';
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
    <title>消費者權益 — TubeLog</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: "Segoe UI", system-ui, -apple-system, "PingFang TC", "Microsoft JhengHei", sans-serif;
            margin: 0;
            min-height: 100vh;
            padding: 0 20px 48px;
            color: #0f172a;
            background-color: #f1f5f9;
            background-image:
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%2394a3b8' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E"),
                radial-gradient(ellipse 90% 70% at 100% 0%, rgba(37, 99, 235, 0.08), transparent 55%),
                linear-gradient(165deg, #f8fafc 0%, #f1f5f9 100%);
        }
        .legal-top { max-width: 760px; margin: 0 auto; padding: 16px 0 8px; display: flex; flex-wrap: wrap; align-items: center; gap: 8px 16px; }
        .legal-back { display: inline-flex; align-items: center; gap: 6px; font-size: 0.95rem; font-weight: 600; color: #2563eb; text-decoration: none; padding: 8px 4px; border-radius: 10px; transition: background 0.15s, color 0.15s; }
        .legal-back:hover { background: rgba(37, 99, 235, 0.08); color: #1d4ed8; }
        .wrap { max-width: 760px; margin: 0 auto; }
        .page-head { margin-bottom: 20px; }
        h1 { font-size: 1.55rem; font-weight: 700; margin: 0 0 8px; letter-spacing: -0.02em; }
        .meta { color: #64748b; font-size: 0.88rem; margin: 0 0 12px; }
        .intro { color: #475569; font-size: 0.95rem; line-height: 1.65; margin: 0; }
        .card { background: rgba(255, 255, 255, 0.97); border: 1px solid rgba(226, 232, 240, 0.95); border-radius: 16px; padding: 22px 24px 26px; margin-bottom: 16px; box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.06), 0 10px 24px -8px rgba(15, 23, 42, 0.08); }
        .card h2 { font-size: 1.05rem; font-weight: 700; margin: 0 0 12px; color: #0f172a; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0; }
        .card p, .card li { font-size: 0.92rem; line-height: 1.75; color: #334155; }
        .card p { margin: 0 0 10px; }
        .card p:last-child { margin-bottom: 0; }
        .card ol, .card ul { margin: 8px 0 0; padding-left: 1.35rem; }
        .card li { margin-bottom: 6px; }
        .card li:last-child { margin-bottom: 0; }
        .legal-footer { margin-top: 8px; padding-top: 16px; border-top: 1px solid rgba(148, 163, 184, 0.35); font-size: 0.88rem; color: #64748b; line-height: 1.6; }
        .legal-footer a { color: #2563eb; font-weight: 600; text-decoration: none; }
        .legal-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<header class="legal-top">
    <a class="legal-back" href="<?= htmlspecialchars($backHref, ENT_QUOTES, 'UTF-8') ?>"><span aria-hidden="true">←</span> <?= htmlspecialchars($backLabel, ENT_QUOTES, 'UTF-8') ?></a>
    <?php if ($isLoggedIn): ?>
        <a class="legal-back" href="index.php?page=account">會員中心</a>
    <?php endif; ?>
</header>
<div class="wrap">
    <div class="page-head">
        <h1>TubeLog 消費者權益說明</h1>
        <p class="meta">最後更新：<?= htmlspecialchars($docUpdated, ENT_QUOTES, 'UTF-8') ?></p>
        <p class="intro">為保障您的消費權益，以下說明使用 TubeLog 付費或免費方案時之重要資訊與申訴管道。內容以網站最新公告為準。</p>
    </div>

    <article class="card">
        <h2>一、適用範圍</h2>
        <p>本說明適用於您透過 TubeLog（<strong>https://tubelog.xyz</strong>）訂閱或使用付費、免費方案時，作為消費者所得主張之權益參考。本說明為補充說明，未排除法律賦予您的權利；與<a href="index.php?page=terms">服務條款</a>、<a href="index.php?page=refund">退款政策</a>併同適用。</p>
    </article>

    <article class="card">
        <h2>二、方案與費用資訊</h2>
        <ol>
            <li>各方案名稱、月費或年費、可追蹤頻道數、清單筆數等，以登入前首頁、會員中心或付款頁當時公告為準。</li>
            <li>付費方案之計費週期、自動續訂與扣款方式，將於您完成付款前於訂單或金流頁面顯示，請確認後再付款。</li>
            <li>營運者得依營運需要調整方案內容或價格，調整前將以網站公告或其他適當方式告知；依法令或契約應另行通知者，從其規定。</li>
        </ol>
    </article>

    <article class="card">
        <h2>三、交易流程與契約成立</h2>
        <ol>
            <li>您於本服務選擇方案並送出付款後，將導向金流服務完成交易；系統確認付款成功後，方案權益即依公告內容開通或延長。</li>
            <li>若因金流、網路或系統因素致交易未完成，本公司不視為契約成立，您無需負擔該筆費用。</li>
            <li>交易紀錄（訂單時間、方案、金額等）得於會員中心或 Email 通知中查詢，請妥善保存。</li>
        </ol>
    </article>

    <article class="card">
        <h2>四、發票與收據</h2>
        <p>若您需要發票或收據，請於付款後透過客服提出申請。開立方式與格式依當時法令及本公司作業規定辦理。</p>
    </article>

    <article class="card">
        <h2>五、客服與申訴管道</h2>
        <p>若對方案內容、扣款、帳號或功能有疑問，請優先透過首頁頁尾之<strong>客服（LINE 官方帳號）</strong>聯繫，並提供註冊 Email、訂單時間與問題說明，以利查證處理。</p>
    </article>

    <article class="card">
        <h2>六、消費爭議處理</h2>
        <ol>
            <li>您得先向本公司客服申訴；若未能解決，得向主管機關或消費爭議調解機構尋求協助。</li>
            <li>您亦可撥打行政院消費者保護專線 <strong>1950</strong>，或至 <a href="https://cpc.ey.gov.tw/" target="_blank" rel="noopener noreferrer">行政院消費者保護會</a> 網站查詢相關資訊。</li>
            <li>本說明不影響您依消費者保護法及其他法令所得主張之權利。</li>
        </ol>
    </article>

    <article class="card">
        <h2>七、其他權益提醒</h2>
        <ul>
            <li>個人資料之蒐集與利用，請參閱<a href="index.php?page=privacy">隱私權政策</a>。</li>
            <li>退款、取消訂閱與扣款爭議，請參閱<a href="index.php?page=refund">退款政策</a>。</li>
            <li>請勿將帳號出借他人，並定期變更密碼以維護權益。</li>
        </ul>
    </article>

    <p class="legal-footer">
        相關說明：<?= legal_related_links_html() ?>。
        如有疑問，請透過
        <?php if ($isLoggedIn): ?>
            <a href="index.php#support">首頁頁尾之客服（LINE 官方帳號）</a>
        <?php else: ?>
            登入後於首頁頁尾之客服（LINE 官方帳號）
        <?php endif; ?>
        與我們聯繫。
    </p>
</div>
</body>
</html>
