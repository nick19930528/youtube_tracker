<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/legal_links.inc.php';
require_once __DIR__ . '/legal_theme.inc.php';

$uiTheme = legal_ui_theme();
$isLoggedIn = auth_check();
$backHref = $isLoggedIn ? 'index.php' : 'index.php?page=login';
$backLabel = $isLoggedIn ? '回首頁' : '返回登入';
$policyUpdated = '2026/05/16';
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
    <title>隱私權政策 — TubeLog</title>
    <?php legal_print_styles(true); ?>
</head>
<body data-theme="<?= htmlspecialchars($uiTheme, ENT_QUOTES, 'UTF-8') ?>">
<header class="legal-top">
    <a class="legal-back" href="<?= htmlspecialchars($backHref, ENT_QUOTES, 'UTF-8') ?>"><span aria-hidden="true">←</span> <?= htmlspecialchars($backLabel, ENT_QUOTES, 'UTF-8') ?></a>
    <?php if ($isLoggedIn): ?>
        <a class="legal-back" href="index.php?page=account">會員中心</a>
    <?php endif; ?>
</header>
<div class="wrap">
    <div class="page-head">
        <h1>TubeLog 隱私權政策</h1>
        <p class="meta">最後更新：<?= htmlspecialchars($policyUpdated, ENT_QUOTES, 'UTF-8') ?></p>
        <p class="intro">
            歡迎您使用 TubeLog（以下簡稱「本服務」），網域包含但不限於 <strong>https://tubelog.xyz</strong>。
            為協助您了解我們如何蒐集、處理及利用個人資料，以及您所享有的權利，請詳閱本隱私權政策。
            當您註冊或使用本服務，即表示您已閱讀並理解本政策內容。
        </p>
    </div>

    <article class="card">
        <h2>一、適用範圍</h2>
        <ol>
            <li>本政策適用於您使用本服務時，營運者所蒐集、處理及利用之個人資料；我們將以誠實信用方式為之，除法律另有規定外，不逾越特定目的之必要範圍。</li>
            <li>本服務可能提供第三方網站或服務之連結（例如 YouTube、金流服務）。您於該等網站提供之資料，適用各該第三方的隱私政策，不在本政策範圍內。</li>
        </ol>
    </article>

    <article class="card">
        <h2>二、個人資料之蒐集、處理及利用</h2>
        <h3>（一）蒐集目的</h3>
        <p>為提供會員管理、身分驗證、訂閱方案與付款、客服聯繫、系統維運與安全、統計分析及服务改善等目的，依法令許可之特定目的項目進行蒐集與利用。</p>
        <h3>（二）蒐集項目</h3>
        <p>視您使用之功能，我們可能蒐集下列資料：</p>
        <ul>
            <li><strong>辨識個人者</strong>：姓名、Email、性別、帳號識別資訊等註冊或會員中心所填資料。</li>
            <li><strong>辨識財務者</strong>：若您使用付費方案，可能經金流服務處理交易所需資訊（本公司不儲存完整信用卡號）。</li>
            <li><strong>使用與技術資訊</strong>：登入時間、IP 位址、瀏覽器類型、操作紀錄、Cookie 或類似技術所產生之資料。</li>
            <li><strong>內容管理資訊</strong>：您於本服務所管理之頻道、影片清單、分類與觀看進度等使用資料。</li>
        </ul>
        <h3>（三）利用期間、地區、對象及方式</h3>
        <ul>
            <li><strong>期間</strong>：於營運期間、法令規定之保存期間或契約關係存續期間內利用。</li>
            <li><strong>地區</strong>：主要於中華民國境內；若使用境外主機或第三方服務，將依適當保護措施處理。</li>
            <li><strong>對象</strong>：除法令要求外，不向無關第三人提供；必要時提供予協力廠商（如主機、Email、金流）以完成服務。</li>
            <li><strong>方式</strong>：以自動化系統、Email 或其他符合當時科技之適當方式處理。</li>
        </ul>
        <h3>（四）第三方服務</h3>
        <p>為提供完整功能，本服務可能使用下列第三方服務，其將依各自政策處理資料：</p>
        <ul>
            <li>YouTube／Google API（頻道與影片資訊擷取）</li>
            <li>電子郵件寄送服務（如 Gmail SMTP，用於驗證信與通知）</li>
            <li>金流服務（付費方案交易）</li>
            <li>主機與雲端基礎設施（網站託管與資料庫）</li>
        </ul>
        <h3>（五）例外提供</h3>
        <p>除經您同意外，我們不會出售、交換或出租您的個人資料。下列情形不在此限：</p>
        <ul>
            <li>您以書面或電子方式明確同意時；</li>
            <li>為維護本服務安全、調查違規或進行法律程序所必要時；</li>
            <li>司法或主管機關依法要求時；</li>
            <li>為防止重大利益侵害或符合公共利益所必要時。</li>
        </ul>
    </article>

    <article class="card">
        <h2>三、您對個人資料之權利</h2>
        <p>依個人資料保護法，您得向營運者行使下列權利：</p>
        <ol>
            <li>查詢或請求閱覽；</li>
            <li>請求製給複製本；</li>
            <li>請求補充或更正；</li>
            <li>請求停止蒐集、處理或利用；</li>
            <li>請求刪除。</li>
        </ol>
        <p>若您申請停止或刪除部分必要資料，可能無法繼續使用完整服務，或我們得暫停、終止您的帳號。請透過客服管道提出申請，我們將於合理期間內回覆；必要時得請您提供身分證明以完成驗證。</p>
    </article>

    <article class="card">
        <h2>四、不提供資料之影響</h2>
        <p>您可自由選擇是否提供個人資料。若拒絕提供註冊或服務所必要之資料，可能無法建立帳號、完成 Email 驗證、使用付費方案或接收相關通知。</p>
    </article>

    <article class="card">
        <h2>五、個人資料之保密義務</h2>
        <ol>
            <li>請妥善保管帳號、密碼，使用完畢請登出；若與他人共用裝置，請關閉瀏覽器。</li>
            <li>您於公開區域主動揭露之資訊，可能被他人蒐集，請自行評估風險。</li>
            <li>若發現帳號遭盜用，請立即通知我們；除可歸責於營運者之事由外，相關損害由您自行承擔。</li>
        </ol>
    </article>

    <article class="card">
        <h2>六、電子郵件與行銷訊息</h2>
        <p>我們可能寄送與帳號、驗證、交易或服務異動相關之 Email。若寄送行銷訊息，將提供退訂或拒絕方式；您亦可依信件說明停止接收。</p>
    </article>

    <article class="card">
        <h2>七、資訊安全</h2>
        <p>我們採取合理之技術與管理措施（如存取控管、傳輸加密等）保護您的資料。惟網際網路傳輸無法保證百分之百安全，請您亦注意使用環境風險。</p>
    </article>

    <article class="card">
        <h2>八、Cookie 與類似技術</h2>
        <p>為維持登入狀態、記住偏好設定及分析使用情形，我們可能使用 Cookie 或類似技術。您可透過瀏覽器設定管理 Cookie；若拒絕全部 Cookie，部分功能可能無法正常運作。</p>
    </article>

    <article class="card">
        <h2>九、政策修訂</h2>
        <p>我們得不時修訂本政策，並公布於本服務網站。修訂後您繼續使用，視為同意修訂內容；若不同意，請停止使用並得依前開權利請求停止利用個人資料。</p>
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
