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
    <title>退款政策 — TubeLog</title>
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
        <h1>TubeLog 退款政策</h1>
        <p class="meta">最後更新：<?= htmlspecialchars($docUpdated, ENT_QUOTES, 'UTF-8') ?></p>
        <p class="intro">請於付款前詳閱本政策。TubeLog 提供訂閱制數位服務，退款條件依本政策及中華民國相關法令辦理。</p>
    </div>

    <article class="card">
        <h2>一、適用範圍</h2>
        <p>本政策適用於您於 TubeLog 購買之<strong>付費訂閱方案</strong>。免費方案不涉及退款。本政策為<a href="index.php?page=terms">服務條款</a>之補充；與消費者權益說明併同適用。</p>
    </article>

    <article class="card">
        <h2>二、數位服務之特性</h2>
        <p>TubeLog 提供線上帳號管理、清單與訂閱額度等<strong>數位服務</strong>。付款成功並開通後，您即可立即使用方案權益；因此除本政策或法令另有規定外，原則上不提供因個人因素（如不喜歡、未使用、誤選方案等）之退款。</p>
    </article>

    <article class="card">
        <h2>三、原則上不提供退款之情形</h2>
        <ul>
            <li>方案已開通且權益已可使用或已屆計費期間者；</li>
            <li>因您違反服務條款遭停權或終止服務者；</li>
            <li>因可歸責於您之設備、網路或操作因素致無法使用者。</li>
        </ul>
    </article>

    <article class="card">
        <h2>四、得申請退款或退費之情形</h2>
        <p>下列情形得於知悉後<strong>14 日內</strong>（或法令規定之期限內）向客服提出申請，我們將依查證結果處理：</p>
        <ol>
            <li>重複扣款或同一期間重複購買同一方案；</li>
            <li>已付款但系統未開通方案權益，且經查證屬實者；</li>
            <li>因本公司或金流系統錯誤致多扣款項者；</li>
            <li>其他依法令或主管機關認定應退款者。</li>
        </ol>
    </article>

    <article class="card">
        <h2>五、訂閱取消與到期</h2>
        <ol>
            <li>若方案為自動續訂，您應於下一期扣款前依會員中心或金流平台提供之方式取消，避免下期扣款。</li>
            <li>取消續訂後，已付費之當期權益通常可使用至該期屆滿，期滿後改為免費方案或依公告處理，<strong>當期已付費用原則不退</strong>。</li>
            <li>具體取消路徑以網站、會員中心或金流頁面當時說明為準。</li>
        </ol>
    </article>

    <article class="card">
        <h2>六、申請方式與處理時程</h2>
        <ol>
            <li>請透過<strong>客服（LINE 官方帳號）</strong>提出，並提供註冊 Email、付款日期、金額、方案名稱及申請理由。</li>
            <li>本公司將於收件後合理期間內查證並回覆；若同意退款，將依原付款方式或雙方同意之方式退還，實際入帳時間視金融機構作業而定。</li>
            <li>若需補件，將另行通知；逾期未補件者得視為撤回申請。</li>
        </ol>
    </article>

    <article class="card">
        <h2>七、政策修訂</h2>
        <p>本公司得不時修訂本政策並公布於網站。修訂後您繼續使用付費服務，視為同意修訂內容；若不同意，請停止使用付費方案並依前開方式聯繫客服。</p>
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
