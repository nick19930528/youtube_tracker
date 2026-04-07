<?php
require_once __DIR__ . '/../../config/bootstrap.php';
auth_require_admin();

require_once __DIR__ . '/../../config/subscription_sync.php';
require_once __DIR__ . '/../../controllers/AccountController.php';
require_once __DIR__ . '/../../controllers/AdminController.php';

$pdo = (new Database())->getConnection();
$acct = new AccountController($pdo);
$admin = new AdminController($pdo);

$targetId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($targetId < 1) {
    header('Location: index.php?page=admin');
    exit;
}

$profile = $acct->getProfile($targetId);
if (!$profile) {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="zh-Hant"><head><meta charset="UTF-8"><title>找不到</title></head><body><p>找不到此會員。</p><p><a href="index.php?page=admin">返回列表</a></p></body></html>';
    exit;
}

$subCurrent = $acct->getSubscriptionWithPlan($targetId);
$subsAll = $admin->listAllSubscriptionsForUser($targetId);
$channels = $admin->listChannelsForUser($targetId);
$vCounts = $admin->getVideoCountsForUser($targetId);
$recentVideos = $admin->listRecentVideosForUser($targetId, 40);

function admin_member_gender_label($g)
{
    if ($g === 'm') {
        return '男';
    }
    if ($g === 'f') {
        return '女';
    }
    if ($g === 'other') {
        return '其他';
    }

    return '未填寫';
}

function admin_member_billing_label($iv)
{
    $map = array('free' => '免費', 'month' => '月繳', 'year' => '年繳');

    return isset($map[$iv]) ? $map[$iv] : $iv;
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <title>後台 — 會員 #<?= (int) $targetId ?></title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: "Segoe UI", system-ui, -apple-system, "PingFang TC", "Microsoft JhengHei", sans-serif;
            margin: 0;
            min-height: 100vh;
            padding: 0 20px 48px;
            color: #0f172a;
            background: #f1f5f9;
        }
        .admin-wrap { max-width: 1100px; margin: 0 auto; padding-top: 20px; }
        .admin-top {
            display: flex; flex-wrap: wrap; align-items: center; gap: 12px;
            justify-content: space-between; margin-bottom: 20px;
        }
        .admin-top h1 { margin: 0; font-size: 1.25rem; }
        .admin-nav a { color: #0369a1; margin-right: 14px; text-decoration: none; }
        .admin-nav a:hover { text-decoration: underline; }
        section {
            background: #fff; border-radius: 12px; padding: 16px 18px; margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(15,23,42,.08);
        }
        section h2 { margin: 0 0 12px; font-size: 1.05rem; color: #334155; }
        .dl-grid {
            display: grid; grid-template-columns: 140px 1fr; gap: 6px 12px; font-size: 14px;
        }
        .dl-grid dt { color: #64748b; margin: 0; }
        .dl-grid dd { margin: 0; }
        table.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .data-table th, .data-table td {
            padding: 8px 10px; text-align: left; border-bottom: 1px solid #e2e8f0; vertical-align: top;
        }
        .data-table th { background: #f8fafc; color: #475569; font-weight: 600; }
        .data-table a { color: #0369a1; word-break: break-all; }
        .pill {
            display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 12px;
            background: #e0f2fe; color: #0369a1;
        }
        .pill-watched { background: #dcfce7; color: #166534; }
        .pill-unwatched { background: #fef3c7; color: #92400e; }
        .muted { color: #94a3b8; font-size: 13px; }
    </style>
</head>
<body>
<div class="admin-wrap">
    <div class="admin-top">
        <h1>會員詳情 #<?= (int) $targetId ?> — <?= htmlspecialchars($profile['name'] !== '' ? $profile['name'] : $profile['email'], ENT_QUOTES, 'UTF-8') ?></h1>
        <nav class="admin-nav">
            <a href="index.php?page=admin">← 會員列表</a>
            <a href="index.php">首頁</a>
        </nav>
    </div>

    <section>
        <h2>基本資料</h2>
        <dl class="dl-grid">
            <dt>Email</dt>
            <dd><?= htmlspecialchars($profile['email'], ENT_QUOTES, 'UTF-8') ?></dd>
            <dt>姓名</dt>
            <dd><?= htmlspecialchars($profile['name'], ENT_QUOTES, 'UTF-8') ?></dd>
            <dt>性別</dt>
            <dd><?= htmlspecialchars(admin_member_gender_label($profile['gender'] ?? null), ENT_QUOTES, 'UTF-8') ?></dd>
            <dt>首頁載入</dt>
            <dd><?= (isset($profile['dash_auto_load']) && (int) $profile['dash_auto_load'] === 0) ? '一次載入全部' : '捲動分頁' ?></dd>
            <dt>抓新影片</dt>
            <dd>
                最近 <?= (int) ($profile['fetch_max_age_days'] ?? 7) ?> 天內；
                每頻道每次最多 <?= (int) ($profile['fetch_max_per_channel'] ?? 1) ?> 支
            </dd>
            <dt>Email 驗證</dt>
            <dd><?= !empty($profile['email_verified_at']) ? htmlspecialchars($profile['email_verified_at'], ENT_QUOTES, 'UTF-8') : '未驗證' ?></dd>
            <dt>註冊時間</dt>
            <dd><?= htmlspecialchars($profile['created_at'] ?? '—', ENT_QUOTES, 'UTF-8') ?></dd>
        </dl>
    </section>

    <section>
        <h2>待看／已看（影片清單筆數）</h2>
        <dl class="dl-grid">
            <dt>📋 待看（未看）</dt>
            <dd><strong><?= (int) $vCounts['unwatched'] ?></strong></dd>
            <dt>✅ 已看</dt>
            <dd><strong><?= (int) $vCounts['watched'] ?></strong></dd>
        </dl>
        <p class="muted">與首頁「待看／已看」分頁相同，以 <code>videos.is_watched</code> 區分。</p>
    </section>

    <section>
        <h2>訂閱方案紀錄</h2>
        <?php if ($subCurrent): ?>
            <p><strong>目前有效（列表優先）</strong>：
                <?= htmlspecialchars($subCurrent['plan_name'], ENT_QUOTES, 'UTF-8') ?>
                （<?= htmlspecialchars($subCurrent['slug'], ENT_QUOTES, 'UTF-8') ?>）
                — <?= htmlspecialchars(subscription_status_label_member($subCurrent['status'], isset($subCurrent['slug']) ? $subCurrent['slug'] : ''), ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php else: ?>
            <p>無訂閱紀錄。</p>
        <?php endif; ?>

        <?php if (!empty($subsAll)): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>方案</th>
                        <th>狀態</th>
                        <th>計費</th>
                        <th>期間</th>
                        <th>建立／更新</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subsAll as $s): ?>
                        <tr>
                            <td><?= (int) $s['id'] ?></td>
                            <td><?= htmlspecialchars($s['plan_name'], ENT_QUOTES, 'UTF-8') ?> <span class="muted">(<?= htmlspecialchars($s['slug'], ENT_QUOTES, 'UTF-8') ?>)</span></td>
                            <td><?= htmlspecialchars(subscription_status_label_member($s['status'], isset($s['slug']) ? $s['slug'] : ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(admin_member_billing_label($s['billing_interval'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if (!empty($s['current_period_start']) || !empty($s['current_period_end'])): ?>
                                    <?= htmlspecialchars(trim(($s['current_period_start'] ?? '') . ' ~ ' . ($s['current_period_end'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="muted"><?= htmlspecialchars($s['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                                <?php if (!empty($s['updated_at'])): ?>
                                    <br><span class="muted">更：<?= htmlspecialchars($s['updated_at'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section>
        <h2>已訂閱頻道（<?= count($channels) ?>）</h2>
        <?php if (empty($channels)): ?>
            <p>尚無頻道。</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>頻道名稱</th>
                        <th>分類</th>
                        <th>訂閱時間</th>
                        <th>連結</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($channels as $ch): ?>
                        <tr>
                            <td><?= htmlspecialchars($ch['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($ch['category_name'] ?? ($ch['category_id'] ? '#' . $ch['category_id'] : '未分類'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($ch['subscribed_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><a href="<?= htmlspecialchars($ch['url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">開啟</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section>
        <h2>最近加入的影片（最多 40 筆）</h2>
        <?php if (empty($recentVideos)): ?>
            <p>尚無影片。</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>狀態</th>
                        <th>標題</th>
                        <th>頻道</th>
                        <th>加入時間</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentVideos as $v): ?>
                        <tr>
                            <td>
                                <?php if ((int) ($v['is_watched'] ?? 0) === 1): ?>
                                    <span class="pill pill-watched">已看</span>
                                <?php else: ?>
                                    <span class="pill pill-unwatched">待看</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($v['youtube_url'])): ?>
                                    <a href="<?= htmlspecialchars($v['youtube_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($v['title'], ENT_QUOTES, 'UTF-8') ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($v['title'], ENT_QUOTES, 'UTF-8') ?>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($v['channel_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="muted"><?= htmlspecialchars($v['added_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</div>
</body>
</html>
