<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/legal_links.inc.php';

$isLoggedIn = auth_check();
$backHref = $isLoggedIn ? 'index.php' : 'index.php?page=login';
$backLabel = $isLoggedIn ? '回首頁' : '返回登入';
$termsUpdated = '2026/05/16';
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
    <title>服務條款 — TubeLog</title>
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
        .legal-top {
            max-width: 760px;
            margin: 0 auto;
            padding: 16px 0 8px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px 16px;
        }
        .legal-back {
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
        .legal-back:hover { background: rgba(37, 99, 235, 0.08); color: #1d4ed8; }
        .wrap { max-width: 760px; margin: 0 auto; }
        .page-head { margin-bottom: 20px; }
        h1 { font-size: 1.55rem; font-weight: 700; margin: 0 0 8px; letter-spacing: -0.02em; }
        .meta { color: #64748b; font-size: 0.88rem; margin: 0 0 12px; }
        .intro { color: #475569; font-size: 0.95rem; line-height: 1.65; margin: 0; }
        .card {
            background: rgba(255, 255, 255, 0.97);
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 16px;
            padding: 22px 24px 26px;
            margin-bottom: 16px;
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.06), 0 10px 24px -8px rgba(15, 23, 42, 0.08);
        }
        .card h2 {
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0 0 12px;
            color: #0f172a;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .card p, .card li { font-size: 0.92rem; line-height: 1.75; color: #334155; }
        .card p { margin: 0 0 10px; }
        .card p:last-child { margin-bottom: 0; }
        .card ol, .card ul { margin: 8px 0 0; padding-left: 1.35rem; }
        .card li { margin-bottom: 6px; }
        .card li:last-child { margin-bottom: 0; }
        .legal-footer {
            margin-top: 8px;
            padding-top: 16px;
            border-top: 1px solid rgba(148, 163, 184, 0.35);
            font-size: 0.88rem;
            color: #64748b;
            line-height: 1.6;
        }
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
        <h1>TubeLog 會員服務條款</h1>
        <p class="meta">最後更新：<?= htmlspecialchars($termsUpdated, ENT_QUOTES, 'UTF-8') ?></p>
        <p class="intro">
            歡迎您使用 TubeLog（以下簡稱「本服務」），網域包含但不限於
            <strong>https://tubelog.xyz</strong> 及其相關頁面。本服務提供 YouTube 頻道與影片之訂閱管理、待看／已看清單、RSS 抓新等功能。
            當您完成註冊或開始使用本服務，即表示您已閱讀、理解並同意遵守本條款；若不同意，請停止使用本服務。
        </p>
    </div>

    <article class="card">
        <h2>壹、認知與同意</h2>
        <ol>
            <li>本服務由營運者提供；條款適用於以自然人或法人身份註冊之會員。</li>
            <li>營運者得隨時修訂本條款，修訂後公布於本服務網站即生效力。您於修訂後繼續使用，視為同意修訂內容。</li>
            <li>若您為無行為能力人或限制行為能力人，應由法定代理人閱讀、理解並同意本條款後，方得註冊或使用本服務。</li>
        </ol>
    </article>

    <article class="card">
        <h2>貳、會員註冊與義務</h2>
        <ol>
            <li>您應提供正確、最新且完整之註冊資料，並妥善維護；不得冒用他人名義或重複註冊多個帳號以規避方案限制。</li>
            <li>若資料不實、不完整或違反本條款，營運者得暫停或終止您的帳號，並拒絕全部或部分服務，無須事先通知。</li>
            <li>本服務可能寄送與帳號、驗證、方案或功能相關之 Email；若您不同意接收行銷訊息，可依信件說明或聯繫客服退訂。</li>
        </ol>
    </article>

    <article class="card">
        <h2>參、隱私與個人資料</h2>
        <p>為提供會員管理、Email 驗證、訂閱方案、付款與客服等服務，本服務將依個人資料保護法及相關法令蒐集、處理及利用您提供之資料。詳細內容請參閱<a href="index.php?page=privacy">隱私權政策</a>。</p>
        <p>您得依法令向營運者請求查詢、閱覽、製給複製本、補充、更正、停止處理或刪除個人資料；請透過本服務之客服管道提出。</p>
    </article>

    <article class="card">
        <h2>肆、帳號、密碼與安全</h2>
        <ol>
            <li>您應妥善保管帳號與密碼，並對以該帳號進行之一切行為負責。</li>
            <li>帳號、密碼及會員權益僅供您個人使用，不得出借、轉讓或與他人共用。</li>
            <li>若發現帳號遭盜用或有安全疑慮，請立即通知營運者；使用完畢請登出帳號。</li>
            <li>營運者對於因您未妥善保管帳密，或無法辨識是否為本人操作所致之損害，不負賠償責任。</li>
        </ol>
    </article>

    <article class="card">
        <h2>伍、服務內容與使用規範</h2>
        <p>本服務協助您整理 YouTube 頻道與影片資訊；影片、頻道名稱、縮圖等內容之著作權及商標權屬於各權利人（含 Google／YouTube 及創作者），您僅得在個人合理使用範圍內透過本服務管理清單，不得以此從事重製、散布、公開傳輸或其他侵害智慧財產權之行為。</p>
        <p>您同意不得利用本服務從事下列行為（包括但不限於）：</p>
        <ul>
            <li>違反中華民國法律、YouTube 服務條款或國際網際網路使用慣例之行為；</li>
            <li>上傳、散布誹謗、威脅、猥褻、侵害他人隱私或智慧財產權之內容；</li>
            <li>以他人名義使用、傳送病毒、未經授權之商業行為、垃圾訊息或干擾系統運作；</li>
            <li>嘗試破解、逆向工程、大量爬取或干擾本服務與第三方 API 之正常運作；</li>
            <li>其他營運者認定不當、或與本服務目的不符之使用方式。</li>
        </ul>
    </article>

    <article class="card">
        <h2>陸、訂閱方案與付費</h2>
        <ol>
            <li>本服務提供免費及付費方案；各方案之頻道數、清單筆數、費用與計費週期以網站或會員中心當時公告為準。</li>
            <li>付費交易經由營運者指定之金流服務處理；您應確認訂單內容後再付款。除法令或方案另有規定外，數位服務一經啟用，原則上不提供退款。</li>
            <li>若因您違反本條款或濫用服務致營運者受損，營運者得取消交易、終止方案或永久停權，並保留法律追訴權。</li>
        </ol>
    </article>

    <article class="card">
        <h2>柒、服務暫停、中斷與變更</h2>
        <p>營運者將以合理技術維持服務運作，但於下列情況得暫停或中斷服務，且不負因此對您或第三人所致之損害賠償責任：</p>
        <ul>
            <li>系統維護、升級、搬遷或設備故障；</li>
            <li>第三方服務（含 YouTube API、RSS、主機、金流）異常或中斷；</li>
            <li>天災、戰爭、疫情、政府命令或其他不可抗力。</li>
        </ul>
        <p>營運者得調整功能、方案內容或計費方式，並以網站公告、Email 或其他適當方式通知。</p>
    </article>

    <article class="card">
        <h2>捌、免責與責任限制</h2>
        <ol>
            <li>本服務依「現況」提供，營運者不就特定目的、不中斷、無錯誤、資料正確性或安全性等為明示或默示之保證。</li>
            <li>透過本服務取得之 YouTube 或第三方資訊，其正確性與可用性應由您自行判斷；因第三方政策變更導致功能受限，營運者不負賠償責任。</li>
            <li>對於因使用或無法使用本服務所生之直接、間接、衍生或特別損害（含資料遺失、營收損失），營運者在法律允許之範圍內不負責任。</li>
            <li>您同意以本服務系統所留存之電子紀錄為準據；若有爭議，以該紀錄為重要參考。</li>
        </ol>
    </article>

    <article class="card">
        <h2>玖、智慧財產權</h2>
        <p>本服務之軟體、介面設計、標誌、文案及資料庫結構等，除另有標示外，其智慧財產權屬營運者或合法權利人所有，未經同意不得重製、改作、散布或為營利使用。</p>
    </article>

    <article class="card">
        <h2>拾、終止使用</h2>
        <p>若您違反本條款，營運者得終止您的帳號或停止使用權，並刪除相關資料（法令另有保存義務者除外）。您亦可依會員中心或客服管道申請停用帳號。</p>
    </article>

    <article class="card">
        <h2>拾壹、準據法與管轄</h2>
        <p>本條款之解釋與適用，以中華民國法律為準據法。因本條款所生爭議，雙方同意以臺灣高雄地方法院為第一審管轄法院。</p>
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
