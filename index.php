<?php
require_once __DIR__ . '/config/database.php';

$page = $_GET['page'] ?? 'home';

/* =========================
   🔀 路由區（非首頁）
========================= */
if ($page !== 'home') {

    switch ($page) {

        case 'videos':
            require __DIR__ . '/views/videos/list.php';
            break;

        case 'videos_by_category':
            require __DIR__ . '/views/videos/by_category.php';
            break;

        case 'add':
            require __DIR__ . '/views/videos/add.php';
            break;

        case 'channels':
            require __DIR__ . '/views/channels/list.php';
            break;

        case 'channel_categories':
            require __DIR__ . '/views/channels/categories.php';
            break;

        default:
            echo "❌ 頁面不存在";
            break;
    }

    exit;
}
$pdo = (new Database())->getConnection();

$allowedQuickNotices = ['channel_ok', 'channel_err', 'video_ok', 'video_err'];
$quickNotice = isset($_GET['notice']) && in_array($_GET['notice'], $allowedQuickNotices, true)
    ? $_GET['notice']
    : null;

if ($page === 'home' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/controllers/ChannelController.php';
    require_once __DIR__ . '/controllers/VideoController.php';

    $redirectHome = function (string $notice) {
        $q = ['notice' => $notice];
        if (!empty($_POST['home_category_id'])) {
            $cid = (int)$_POST['home_category_id'];
            if ($cid > 0) {
                $q['category_id'] = $cid;
            }
        }
        header('Location: index.php?' . http_build_query($q));
        exit;
    };

    if (isset($_POST['quick_add_channel'])) {
        $controller = new ChannelController($pdo);
        $input = trim($_POST['channel_input'] ?? '');
        $categoryId = !empty($_POST['channel_category_id']) ? (int)$_POST['channel_category_id'] : null;

        $channelId = null;
        $name = '';
        $url = '';

        if ($input !== '') {
            if (strpos($input, '@') === 0) {
                $handle = substr($input, 1);
                $apiUrl = "https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics&forHandle={$handle}&key=" . YOUTUBE_API_KEY;
                $json = @file_get_contents($apiUrl);
                $data = json_decode($json, true);

                if (!empty($data['items'][0])) {
                    $item = $data['items'][0];
                    $channelId = $item['id'];
                    $name = $item['snippet']['title'];
                    $url = "https://www.youtube.com/@{$handle}";
                }
            } elseif (preg_match('/(youtube\\.com\/(channel|@[^\/]+))/', $input)) {
                $url = $input;

                if (preg_match('/\/channel\/([a-zA-Z0-9_-]+)/', $input, $matches)) {
                    $channelId = $matches[1];
                } elseif (preg_match('/youtube\\.com\/@([a-zA-Z0-9._-]+)/', $input, $matches)) {
                    $handle = $matches[1];
                    $apiUrl = "https://www.googleapis.com/youtube/v3/search?part=snippet&type=channel&q={$handle}&key=" . YOUTUBE_API_KEY;
                    $json = @file_get_contents($apiUrl);
                    $data = json_decode($json, true);
                    if (!empty($data['items'][0])) {
                        $channelId = $data['items'][0]['snippet']['channelId'];
                        $name = $data['items'][0]['snippet']['title'];
                        $url = "https://www.youtube.com/channel/{$channelId}";
                    }
                }
            } elseif (preg_match('/^UC[a-zA-Z0-9_-]{20,}$/', $input)) {
                $channelId = $input;
                $url = "https://www.youtube.com/channel/{$channelId}";
            }

            if ($channelId && $name === '') {
                $apiUrl = "https://www.googleapis.com/youtube/v3/channels?part=snippet&id={$channelId}&key=" . YOUTUBE_API_KEY;
                $json = @file_get_contents($apiUrl);
                $data = json_decode($json, true);
                if (!empty($data['items'][0])) {
                    $name = $data['items'][0]['snippet']['title'];
                }
            }

            if ($channelId && $name && $url) {
                if ($controller->add($name, $channelId, $url, $categoryId)) {
                    $redirectHome('channel_ok');
                }
                $redirectHome('channel_err');
            }
        }
        $redirectHome('channel_err');
    }

    if (isset($_POST['quick_add_video'])) {
        $videoController = new VideoController($pdo);
        $url = trim($_POST['video_url'] ?? '');

        $extractVideoId = function ($u) {
            if (preg_match('/(?:v=|youtu\.be\/)([A-Za-z0-9_\-]+)/', $u, $matches)) {
                return $matches[1];
            }
            return null;
        };

        $videoId = $extractVideoId($url);
        if (!$videoId) {
            $redirectHome('video_err');
        }

        $ytApi = "https://www.googleapis.com/youtube/v3/videos?part=snippet,statistics,contentDetails&id={$videoId}&key=" . YOUTUBE_API_KEY;
        $ytJson = @file_get_contents($ytApi);
        $ytData = json_decode($ytJson, true);
        $item = $ytData['items'][0] ?? null;

        if (!$item) {
            $redirectHome('video_err');
        }

        $title = $item['snippet']['title'];
        $publishedAt = $item['snippet']['publishedAt'] ?? null;
        $thumbnailUrl = $item['snippet']['thumbnails']['default']['url'] ?? null;
        $channelName = $item['snippet']['channelTitle'] ?? null;
        $viewCount = $item['statistics']['viewCount'] ?? 0;
        $likeCount = $item['statistics']['likeCount'] ?? 0;
        $commentCount = $item['statistics']['commentCount'] ?? 0;
        $durationIso = $item['contentDetails']['duration'] ?? null;
        $duration = null;
        if ($durationIso) {
            try {
                $interval = new DateInterval($durationIso);
                $duration = ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
            } catch (Exception $e) {
                $duration = null;
            }
        }

        $summary = '';
        if ($title && defined('OPENAI_API_KEY') && OPENAI_API_KEY) {
            $ch = curl_init("https://api.openai.com/v1/chat/completions");
            $payload = [
                "model" => "gpt-3.5-turbo",
                "messages" => [
                    ["role" => "user", "content" => "請簡單摘要這段影片標題的內容：「{$title}」"]
                ],
                "temperature" => 0.7
            ];
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json",
                    "Authorization: Bearer " . OPENAI_API_KEY
                ],
                CURLOPT_POSTFIELDS => json_encode($payload)
            ]);
            $response = curl_exec($ch);
            $result = json_decode($response, true);
            curl_close($ch);
            $summary = $result['choices'][0]['message']['content'] ?? '';
        }

        $watchedAt = date('Y-m-d H:i:s');
        if ($videoController->add(
                $title,
                $url,
                $summary,
                $publishedAt,
                $viewCount,
                $likeCount,
                $commentCount,
                $thumbnailUrl,
                $channelName,
                $duration,
                1,
                $watchedAt
            )) {
            $redirectHome('video_ok');
        }
        $redirectHome('video_err');
    }
}

/* =========================
   📊 KPI
========================= */
$unwatched = $pdo->query("SELECT COUNT(*) FROM videos WHERE is_watched = 0")->fetchColumn();
$watched = $pdo->query("SELECT COUNT(*) FROM videos WHERE is_watched = 1")->fetchColumn();
$channels = $pdo->query("SELECT COUNT(*) FROM channels")->fetchColumn();

$todaySeconds = $pdo->query("
    SELECT SUM(duration) FROM videos 
    WHERE is_watched = 1 AND DATE(watched_at) = CURDATE()
")->fetchColumn();

$todaySeconds = (int)$todaySeconds;
$todayTime = $todaySeconds >= 3600
    ? gmdate("H:i:s", $todaySeconds)
    : gmdate("i:s", $todaySeconds);

/* =========================
   🎬 最新未看影片
========================= */
$latestVideos = $pdo->query("
    SELECT * FROM videos 
    WHERE is_watched = 0 
    ORDER BY published_at DESC 
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

$latestWatchedVideos = $pdo->query("
    SELECT * FROM videos 
    WHERE is_watched = 1 
    ORDER BY COALESCE(watched_at, added_at) DESC 
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   📺 已訂閱頻道（Dashboard 區塊）
========================= */
$filterCategoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$filterCategoryName = null;
if ($filterCategoryId > 0) {
    $stmt = $pdo->prepare("SELECT name FROM channel_categories WHERE id = ?");
    $stmt->execute([$filterCategoryId]);
    $filterCategoryName = $stmt->fetchColumn();
    if ($filterCategoryName === false) {
        $filterCategoryId = 0;
        $filterCategoryName = null;
    }
}

$subscribedSql = "
    SELECT c.id, c.name, c.url, c.thumbnail_url, c.subscriber_count, c.video_count, c.published_at,
           c.is_favorite, cc.name AS category_name
    FROM channels c
    LEFT JOIN channel_categories cc ON c.category_id = cc.id
";
if ($filterCategoryId > 0) {
    $subscribedSql .= " WHERE c.category_id = ? ";
}
$subscribedSql .= " ORDER BY c.name ASC";

if ($filterCategoryId > 0) {
    $stmt = $pdo->prepare($subscribedSql);
    $stmt->execute([$filterCategoryId]);
    $subscribedChannels = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $subscribedChannels = $pdo->query($subscribedSql)->fetchAll(PDO::FETCH_ASSOC);
}

$dashTabSubscribed = ($filterCategoryId > 0);

/* =========================
   ⭐ 我的最愛頻道（channels.is_favorite = 1）
========================= */
$favoriteChannels = $pdo->query("
    SELECT c.name, c.url,
           (SELECT COUNT(*) FROM videos v
            WHERE v.channel_name COLLATE utf8mb4_unicode_ci = c.name COLLATE utf8mb4_unicode_ci
           ) AS video_total
    FROM channels c
    WHERE c.is_favorite = 1
    ORDER BY c.name ASC
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   📂 分類
========================= */
$categories = $pdo->query("
    SELECT cc.*, COUNT(c.id) as total
    FROM channel_categories cc
    LEFT JOIN channels c ON cc.id = c.category_id
    GROUP BY cc.id
    ORDER BY cc.sort_order ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Dashboard</title>

<style>
body {
    font-family: Arial;
    background: #f5f7fa;
    padding: 30px;
}

/* KPI */
.cards {
    display: flex;
    gap: 20px;
    margin-bottom: 25px;
}
.card {
    flex: 1;
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.05);
}
.card h2 { margin: 0; }
.card p { color: #888; }

/* layout */
.grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

.section {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
}

/* 影片 */
.video {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
}
.video img {
    width: 120px;
    border-radius: 6px;
}
.video a {
    text-decoration: none;
    color: #333;
    font-weight: bold;
}

.section-head {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 16px;
}
.section-head h3 { margin: 0; }

.video-tab-toggle {
    display: inline-flex;
    flex-wrap: wrap;
    border: 1px solid #cce0f0;
    border-radius: 8px;
    overflow: hidden;
    background: #f0f6fb;
}
.video-tab {
    border: none;
    background: transparent;
    color: #333;
    padding: 8px 14px;
    font-size: 14px;
    cursor: pointer;
    font-family: inherit;
}
.video-tab:hover { background: rgba(0,119,204,0.08); }
.video-tab.active {
    background: #0077cc;
    color: #fff;
}
.video-tab.active:hover { background: #0066b3; }

.video-empty {
    color: #888;
    font-size: 14px;
    margin: 0;
}

/* 已訂閱頻道：圖上名下、多欄填滿 */
.subscribed-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 16px 12px;
}
.channel-card {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    text-align: center;
    min-width: 0;
}
.channel-card-thumb {
    width: 100%;
    aspect-ratio: 1;
    object-fit: cover;
    border-radius: 8px;
    background: #e8ecf0;
    display: block;
}
.channel-card-thumb--empty {
    background: linear-gradient(145deg, #e8ecf0, #dde3ea);
}
.channel-card-body {
    margin-top: 8px;
    min-height: 2.6em;
}
.channel-card-name {
    display: block;
    font-size: 13px;
    font-weight: bold;
    line-height: 1.35;
    color: #333;
    text-decoration: none;
    word-break: break-word;
}
.channel-card-name:hover { color: #0077cc; }
.channel-card-cat {
    display: block;
    margin-top: 4px;
    font-size: 12px;
    color: #888;
    line-height: 1.3;
}

.channel-card-media {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
}
.channel-card-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.82);
    color: #f1f5f9;
    font-size: 11px;
    line-height: 1.45;
    padding: 10px 8px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    gap: 6px;
    text-align: left;
    opacity: 0;
    transition: opacity 0.2s ease;
    pointer-events: none;
}
.channel-card-media:hover .channel-card-overlay {
    opacity: 1;
}
.channel-card-overlay-main {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
    justify-content: center;
    min-height: 0;
}
.channel-card-overlay-actions {
    align-self: flex-end;
    margin-top: auto;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    justify-content: flex-end;
    pointer-events: auto;
}
.channel-card-btn {
    border: none;
    cursor: pointer;
    font-size: 12px;
    line-height: 1;
    padding: 6px 8px;
    border-radius: 6px;
    font-family: inherit;
    background: rgba(255, 255, 255, 0.15);
    color: #f8fafc;
}
.channel-card-btn:hover {
    background: rgba(255, 255, 255, 0.28);
}
.channel-card-btn--fav.channel-card-btn--on {
    background: rgba(234, 179, 8, 0.35);
}
.channel-card-btn--del:hover {
    background: rgba(239, 68, 68, 0.45);
}
.channel-card-stat {
    display: flex;
    align-items: flex-start;
    gap: 6px;
}
.channel-card-stat-label {
    flex: 0 0 auto;
    color: #94a3b8;
}
.channel-card-stat-value {
    flex: 1;
    min-width: 0;
    word-break: break-word;
}

/* 按鈕 */
.btn {
    background: #0077cc;
    color: white;
    padding: 6px 10px;
    border-radius: 6px;
    text-decoration: none;
}
.btn:hover { background: #005fa3; }

.quick-actions-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    margin-bottom: 8px;
}
.quick-notice {
    padding: 10px 12px;
    border-radius: 8px;
    margin: 0 0 14px;
    font-size: 14px;
}
.quick-notice--ok {
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #065f46;
}
.quick-notice--err {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
}
.quick-forms {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
}
.quick-form-block h4 {
    margin: 0 0 10px;
    font-size: 15px;
    font-weight: bold;
}
.quick-form-block label {
    display: block;
    font-size: 13px;
    color: #555;
    margin-bottom: 4px;
}
.quick-form-block input[type="text"],
.quick-form-block select {
    width: 100%;
    max-width: 480px;
    padding: 8px 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    box-sizing: border-box;
    font-family: inherit;
    font-size: 14px;
}
.quick-form-block .form-row { margin-bottom: 10px; }
.quick-form-block button[type="submit"] {
    background: #0077cc;
    color: #fff;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
}
.quick-form-block button[type="submit"]:hover { background: #0066b3; }
.quick-form-hint {
    font-size: 12px;
    color: #888;
    margin-top: 6px;
}

/* 分類 */
.category {
    display: inline-block;
    background: #eee;
    color: #333;
    padding: 6px 10px;
    margin: 5px;
    border-radius: 6px;
    text-decoration: none;
}
.category:hover { background: #e0e0e0; }
.category.category--active {
    background: #0077cc;
    color: #fff;
}
.category.category--active:hover { background: #0066b3; }

.category-filter-banner {
    font-size: 14px;
    color: #475569;
    margin: 0 0 14px;
    padding: 10px 12px;
    background: #f1f5f9;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}
.category-filter-banner strong { color: #0f172a; }
.category-filter-clear {
    margin-left: 10px;
    font-size: 13px;
    color: #0077cc;
    text-decoration: none;
}
.category-filter-clear:hover { text-decoration: underline; }

/* 右側分類標籤（可編輯／拖曳） */
.category-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: flex-start;
}
.category-item {
    display: inline-block;
    vertical-align: top;
}
.category-tags--edit .category-item {
    cursor: grab;
    transform: translateY(-3px);
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
    border-radius: 8px;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.category-tags--edit .category-item:active {
    cursor: grabbing;
}
.category-item.dragging {
    opacity: 0.55;
    transform: scale(0.98);
}
.category--editor {
    display: none;
    align-items: center;
    gap: 6px;
    background: #eef6fc;
    border: 1px dashed #0077cc;
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 14px;
}
.category-tags--edit .category--nav {
    display: none !important;
}
.category-tags--edit .category--editor {
    display: inline-flex !important;
}
.category-editor-grip {
    color: #64748b;
    font-size: 12px;
    user-select: none;
    line-height: 1;
}
.category-editor-input {
    width: 6.5em;
    min-width: 4em;
    max-width: 12em;
    padding: 4px 6px;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    font-size: 13px;
    font-family: inherit;
}
.category-editor-meta {
    color: #64748b;
    font-size: 12px;
    white-space: nowrap;
}

.btn-outline {
    background: #fff;
    color: #0077cc;
    border: 1px solid #0077cc;
}
.btn-outline:hover {
    background: #f0f7fc;
}
.btn-outline.is-active {
    background: #0077cc;
    color: #fff;
}
.btn-outline.is-active:hover {
    background: #0066b3;
}

/* 頻道 */
.channel {
    margin-bottom: 8px;
}
</style>

</head>
<body>

<h1>🎬 YouTube Dashboard</h1>

<!-- KPI -->
<div class="cards">
    <div class="card"><h2><?= $unwatched ?></h2><p>📋 未看</p></div>
    <div class="card"><h2><?= $watched ?></h2><p>✅ 已看</p></div>
    <div class="card"><h2><?= $channels ?></h2><p>📺 頻道</p></div>
    <div class="card"><h2><?= $todayTime ?></h2><p>⏱ 今日觀看</p></div>
</div>

<!-- 快速操作 -->
<div class="section" style="margin-bottom:20px;">
    <h3>⚡ 快速操作</h3>

    <?php if ($quickNotice): ?>
        <?php
        $noticeTexts = [
            'channel_ok' => '✅ 頻道已成功新增。',
            'channel_err' => '⚠️ 無法新增頻道（頻道已存在、網址格式錯誤或無法從 YouTube 取得資料）。',
            'video_ok' => '✅ 影片已加入已看清單。',
            'video_err' => '⚠️ 無法新增影片（連結無效、影片已存在或無法取得影片資訊）。',
        ];
        $noticeOk = in_array($quickNotice, ['channel_ok', 'video_ok'], true);
        ?>
        <p class="quick-notice <?= $noticeOk ? 'quick-notice--ok' : 'quick-notice--err' ?>">
            <?= htmlspecialchars($noticeTexts[$quickNotice] ?? '') ?>
        </p>
    <?php endif; ?>

    <div class="quick-actions-bar">
        <a class="btn" href="index.php?page=videos&watched=0">📋 待看清單</a>
        <a class="btn" href="index.php?page=videos&watched=1">✅ 已看清單</a>
        <a class="btn" href="index.php?page=channels">📺 頻道管理</a>
        <a class="btn" href="index.php?page=channel_categories">📂 分類管理</a>
        <a class="btn" href="scripts/fetch_new_videos.php" target="_blank" rel="noopener noreferrer">📡 抓新影片</a>
        <button type="button" class="btn btn-outline" id="btnCategoryTagEdit" aria-pressed="false" title="切換後可拖曳排序、點名稱修改">
            ✏️ 編輯分類標籤
        </button>
    </div>

    <div class="quick-forms">
        <div class="quick-form-block">
            <h4>➕ 新增頻道</h4>
            <form method="post" action="index.php">
                <input type="hidden" name="home_category_id" value="<?= $filterCategoryId > 0 ? (int)$filterCategoryId : '' ?>">
                <div class="form-row">
                    <label for="channel_input">頻道網址 / @handle / Channel ID</label>
                    <input type="text" name="channel_input" id="channel_input" placeholder="例如：https://www.youtube.com/@handle 或 @handle" required autocomplete="off">
                </div>
                <div class="form-row">
                    <label for="channel_category_id">分類（選填）</label>
                    <select name="channel_category_id" id="channel_category_id">
                        <option value="">未指定</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>"<?= ($filterCategoryId > 0 && (int)$cat['id'] === $filterCategoryId) ? ' selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="quick_add_channel" value="1">新增頻道</button>
            </form>
            <p class="quick-form-hint">與頻道管理頁相同：支援網址、@帳號或 UC開頭的頻道 ID。</p>
        </div>

        <div class="quick-form-block">
            <h4>➕ 新增影片</h4>
            <form method="post" action="index.php">
                <input type="hidden" name="home_category_id" value="<?= $filterCategoryId > 0 ? (int)$filterCategoryId : '' ?>">
                <div class="form-row">
                    <label for="video_url">YouTube 影片網址</label>
                    <input type="text" name="video_url" id="video_url" placeholder="https://www.youtube.com/watch?v=…" required autocomplete="off">
                </div>
                <button type="submit" name="quick_add_video" value="1">加入已看清單</button>
            </form>
            <p class="quick-form-hint">會透過 API 取得資訊並加入「已看」；與舊版<a href="index.php?page=add">新增影片頁</a>相同邏輯。</p>
        </div>
    </div>
</div>

<div class="grid">

<!-- 左邊：影片 -->
<div class="section">
    <div class="section-head">
        <h3>🎬 最新影片</h3>
        <div class="video-tab-toggle" role="tablist" aria-label="待看、已看與已訂閱頻道">
            <button type="button" class="video-tab<?= !$dashTabSubscribed ? ' active' : '' ?>" role="tab" aria-selected="<?= $dashTabSubscribed ? 'false' : 'true' ?>" data-panel="unwatched" id="tab-unwatched">📋 待看</button>
            <button type="button" class="video-tab" role="tab" aria-selected="false" data-panel="watched" id="tab-watched">✅ 已看</button>
            <button type="button" class="video-tab<?= $dashTabSubscribed ? ' active' : '' ?>" role="tab" aria-selected="<?= $dashTabSubscribed ? 'true' : 'false' ?>" data-panel="subscribed" id="tab-subscribed">📺 已訂閱</button>
        </div>
    </div>

    <div id="panel-unwatched" class="video-panel" role="tabpanel" aria-labelledby="tab-unwatched"<?= $dashTabSubscribed ? ' hidden' : '' ?>>
        <?php if (empty($latestVideos)): ?>
            <p class="video-empty">目前沒有待看影片。</p>
        <?php else: ?>
            <?php foreach ($latestVideos as $v): ?>
                <div class="video">
                    <?php if ($v['thumbnail_url']): ?>
                        <img src="<?= htmlspecialchars($v['thumbnail_url']) ?>" alt="">
                    <?php endif; ?>

                    <div>
                        <a href="<?= htmlspecialchars($v['youtube_url']) ?>" target="_blank" rel="noopener noreferrer">
                            <?= htmlspecialchars($v['title']) ?>
                        </a>
                        <br>
                        <small><?= htmlspecialchars($v['channel_name'] ?? '') ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div id="panel-watched" class="video-panel" role="tabpanel" aria-labelledby="tab-watched" hidden>
        <?php if (empty($latestWatchedVideos)): ?>
            <p class="video-empty">目前沒有已看影片。</p>
        <?php else: ?>
            <?php foreach ($latestWatchedVideos as $v): ?>
                <div class="video">
                    <?php if ($v['thumbnail_url']): ?>
                        <img src="<?= htmlspecialchars($v['thumbnail_url']) ?>" alt="">
                    <?php endif; ?>

                    <div>
                        <a href="<?= htmlspecialchars($v['youtube_url']) ?>" target="_blank" rel="noopener noreferrer">
                            <?= htmlspecialchars($v['title']) ?>
                        </a>
                        <br>
                        <small><?= htmlspecialchars($v['channel_name'] ?? '') ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div id="panel-subscribed" class="video-panel" role="tabpanel" aria-labelledby="tab-subscribed"<?= $dashTabSubscribed ? '' : ' hidden' ?>>
        <?php if ($filterCategoryId > 0 && $filterCategoryName): ?>
            <p class="category-filter-banner">
                目前分類：<strong><?= htmlspecialchars($filterCategoryName) ?></strong>
                <a class="category-filter-clear" href="index.php">顯示全部頻道</a>
            </p>
        <?php endif; ?>
        <?php if (empty($subscribedChannels)): ?>
            <?php if ($filterCategoryId > 0): ?>
                <p class="video-empty">此分類尚無已訂閱頻道。<a href="index.php">顯示全部</a> 或 <a href="index.php?page=channels">頻道管理</a></p>
            <?php else: ?>
                <p class="video-empty">尚未加入任何頻道。<a href="index.php?page=channels">前往頻道管理</a></p>
            <?php endif; ?>
        <?php else: ?>
            <div class="subscribed-grid">
                <?php foreach ($subscribedChannels as $ch): ?>
                    <?php
                    $subN = (int)($ch['subscriber_count'] ?? 0);
                    $vidN = (int)($ch['video_count'] ?? 0);
                    if ($subN >= 100000000) {
                        $subStr = round($subN / 100000000, 1) . ' 億';
                    } elseif ($subN >= 10000) {
                        $subStr = round($subN / 10000, 1) . ' 萬';
                    } else {
                        $subStr = number_format($subN);
                    }
                    $vidStr = $vidN >= 10000 ? round($vidN / 10000, 1) . ' 萬' : number_format($vidN);
                    $yearsStr = '—';
                    if (!empty($ch['published_at'])) {
                        try {
                            $pub = new DateTime($ch['published_at']);
                            $y = $pub->diff(new DateTime())->y;
                            $yearsStr = $y >= 1 ? ('創立 ' . $y . ' 年') : '未滿 1 年';
                        } catch (Exception $e) {
                            $yearsStr = '—';
                        }
                    }
                    ?>
                    <?php $isFav = !empty($ch['is_favorite']); ?>
                    <article class="channel-card" data-channel-id="<?= (int)$ch['id'] ?>">
                        <div class="channel-card-media">
                            <?php if (!empty($ch['thumbnail_url'])): ?>
                                <img class="channel-card-thumb" src="<?= htmlspecialchars($ch['thumbnail_url']) ?>" alt="">
                            <?php else: ?>
                                <span class="channel-card-thumb channel-card-thumb--empty" role="img" aria-label="無頻道圖片"></span>
                            <?php endif; ?>
                            <div class="channel-card-overlay">
                                <div class="channel-card-overlay-main">
                                    <div class="channel-card-stat">
                                        <span class="channel-card-stat-label">訂閱</span>
                                        <span class="channel-card-stat-value"><?= htmlspecialchars($subStr) ?></span>
                                    </div>
                                    <div class="channel-card-stat">
                                        <span class="channel-card-stat-label">影片</span>
                                        <span class="channel-card-stat-value"><?= htmlspecialchars($vidStr) ?></span>
                                    </div>
                                    <div class="channel-card-stat">
                                        <span class="channel-card-stat-label">成立</span>
                                        <span class="channel-card-stat-value"><?= htmlspecialchars($yearsStr) ?></span>
                                    </div>
                                </div>
                                <div class="channel-card-overlay-actions">
                                    <button type="button" class="channel-card-btn channel-card-btn--fav<?= $isFav ? ' channel-card-btn--on' : '' ?>"
                                            data-channel-id="<?= (int)$ch['id'] ?>"
                                            data-is-favorite="<?= $isFav ? '1' : '0' ?>"
                                            title="我的最愛"><?= $isFav ? '⭐ 最愛' : '☆ 最愛' ?></button>
                                    <button type="button" class="channel-card-btn channel-card-btn--del"
                                            data-channel-id="<?= (int)$ch['id'] ?>"
                                            title="從訂閱清單刪除">🗑 刪除</button>
                                </div>
                            </div>
                        </div>
                        <div class="channel-card-body">
                            <a class="channel-card-name" href="<?= htmlspecialchars($ch['url']) ?>" target="_blank" rel="noopener noreferrer">
                                <?= htmlspecialchars($ch['name']) ?>
                            </a>
                            <?php if (!empty($ch['category_name'])): ?>
                                <span class="channel-card-cat"><?= htmlspecialchars($ch['category_name']) ?></span>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    var tabs = document.querySelectorAll('.video-tab');
    var panels = document.querySelectorAll('.video-panel');
    if (!tabs.length || !panels.length) return;

    function switchPanel(key) {
        tabs.forEach(function (b) {
            var on = b.getAttribute('data-panel') === key;
            b.classList.toggle('active', on);
            b.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        panels.forEach(function (p) {
            p.hidden = p.id !== 'panel-' + key;
        });
    }

    tabs.forEach(function (btn) {
        btn.addEventListener('click', function () {
            switchPanel(btn.getAttribute('data-panel'));
        });
    });

    if (new URLSearchParams(window.location.search).get('category_id')) {
        switchPanel('subscribed');
    }
})();
</script>

<script>
(function () {
    var api = 'scripts/channel_card_api.php';
    var grid = document.querySelector('#panel-subscribed .subscribed-grid');
    if (!grid) return;

    function postJson(body) {
        return fetch(api, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        }).then(function (r) { return r.json(); });
    }

    grid.addEventListener('click', function (e) {
        var fav = e.target.closest('.channel-card-btn--fav');
        var del = e.target.closest('.channel-card-btn--del');
        if (fav) {
            e.preventDefault();
            e.stopPropagation();
            var id = parseInt(fav.getAttribute('data-channel-id'), 10);
            postJson({ action: 'toggle_favorite', channel_id: id }).then(function (res) {
                if (!res || !res.ok) return;
                var on = res.is_favorite === 1;
                fav.setAttribute('data-is-favorite', on ? '1' : '0');
                fav.textContent = on ? '⭐ 最愛' : '☆ 最愛';
                fav.classList.toggle('channel-card-btn--on', on);
            });
            return;
        }
        if (del) {
            e.preventDefault();
            e.stopPropagation();
            if (!confirm('確定要從訂閱清單刪除此頻道？（不會影響 YouTube 上的頻道）')) return;
            var cid = parseInt(del.getAttribute('data-channel-id'), 10);
            postJson({ action: 'delete_channel', channel_id: cid }).then(function (res) {
                if (!res || !res.ok) {
                    alert('刪除失敗');
                    return;
                }
                var card = del.closest('.channel-card');
                if (card) card.remove();
                if (!grid.querySelector('.channel-card')) {
                    window.location.reload();
                }
            }).catch(function () {
                alert('刪除失敗');
            });
        }
    });
})();
</script>

<!-- 右邊 -->
<div>

    <!-- 我的最愛頻道 -->
    <div class="section">
        <h3>⭐ 我的最愛頻道</h3>
        <?php if (empty($favoriteChannels)): ?>
            <p class="video-empty">尚無最愛頻道。請在「📺 已訂閱」頻道卡游標移到圖片上，於右下角按「☆ 最愛」；亦可至 <a href="index.php?page=channels">頻道管理</a> 操作。</p>
        <?php else: ?>
            <?php foreach ($favoriteChannels as $c): ?>
                <div class="channel">
                    <a href="<?= htmlspecialchars($c['url']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($c['name']) ?></a>
                    （<?= (int)$c['video_total'] ?>）
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- 分類 -->
    <div class="section">
        <h3 style="margin-top:0;">📂 分類</h3>
        <p class="video-empty" style="margin:0 0 10px;font-size:13px;">在「快速操作」可開啟 <strong>編輯分類標籤</strong>，標籤會浮起並可拖曳排序、直接改名。</p>
        <div class="category-tags" id="categoryTags">
            <?php foreach ($categories as $c): ?>
                <?php
                $cid = (int)$c['id'];
                $ctotal = (int)$c['total'];
                $isActive = ($filterCategoryId > 0 && $cid === $filterCategoryId);
                ?>
                <div class="category-item" data-id="<?= $cid ?>" data-name="<?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>" data-total="<?= $ctotal ?>" draggable="false">
                    <a class="category category--nav<?= $isActive ? ' category--active' : '' ?>" href="index.php?category_id=<?= $cid ?>">
                        <?= htmlspecialchars($c['name']) ?> (<?= $ctotal ?>)
                    </a>
                    <div class="category category--editor">
                        <span class="category-editor-grip" title="拖曳排序">⠿</span>
                        <input type="text" class="category-editor-input" value="<?= htmlspecialchars($c['name']) ?>" maxlength="100" aria-label="分類名稱">
                        <span class="category-editor-meta">(<?= $ctotal ?>)</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

</div>

<script>
(function () {
    var wrap = document.getElementById('categoryTags');
    var btn = document.getElementById('btnCategoryTagEdit');
    var api = 'scripts/category_dashboard_api.php';
    if (!wrap || !btn) return;

    var editMode = false;

    function setNavLabel(item, name, total) {
        var a = item.querySelector('.category--nav');
        if (a) a.textContent = name + ' (' + total + ')';
    }

    function postJson(body) {
        return fetch(api, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        }).then(function (r) { return r.json(); });
    }

    function saveOrder() {
        var order = [];
        wrap.querySelectorAll('.category-item').forEach(function (el) {
            order.push(parseInt(el.getAttribute('data-id'), 10));
        });
        return postJson({ action: 'reorder', order: order });
    }

    btn.addEventListener('click', function () {
        editMode = !editMode;
        wrap.classList.toggle('category-tags--edit', editMode);
        btn.classList.toggle('is-active', editMode);
        btn.setAttribute('aria-pressed', editMode ? 'true' : 'false');

        wrap.querySelectorAll('.category-item').forEach(function (el) {
            el.setAttribute('draggable', editMode ? 'true' : 'false');
        });

        if (!editMode) {
            saveOrder().then(function (res) {
                if (res && res.ok) {
                    window.location.reload();
                }
            }).catch(function () {
                window.location.reload();
            });
        }
    });

    var dragEl = null;
    wrap.addEventListener('dragstart', function (e) {
        if (!editMode) return;
        var row = e.target.closest('.category-item');
        if (!row || !wrap.contains(row)) return;
        dragEl = row;
        row.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', String(row.getAttribute('data-id')));
    });

    wrap.addEventListener('dragend', function (e) {
        var row = e.target.closest('.category-item');
        if (row) row.classList.remove('dragging');
        dragEl = null;
    });

    wrap.addEventListener('dragover', function (e) {
        if (!editMode || !dragEl) return;
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        var row = e.target.closest('.category-item');
        if (!row || row === dragEl || !wrap.contains(row)) return;
        var rect = row.getBoundingClientRect();
        var mid = rect.top + rect.height / 2;
        if (e.clientY < mid) {
            wrap.insertBefore(dragEl, row);
        } else {
            wrap.insertBefore(dragEl, row.nextSibling);
        }
    });

    wrap.addEventListener('drop', function (e) {
        e.preventDefault();
    });

    wrap.querySelectorAll('.category-editor-input').forEach(function (input) {
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                input.blur();
            }
        });
        input.addEventListener('blur', function () {
            if (!editMode) return;
            var item = input.closest('.category-item');
            if (!item) return;
            var id = parseInt(item.getAttribute('data-id'), 10);
            var total = parseInt(item.getAttribute('data-total'), 10) || 0;
            var prev = item.getAttribute('data-name') || '';
            var name = input.value.trim();
            if (name === '' || name === prev) {
                input.value = prev;
                return;
            }
            postJson({ action: 'rename', id: id, name: name }).then(function (res) {
                if (res && res.ok) {
                    item.setAttribute('data-name', name);
                    setNavLabel(item, name, total);
                } else {
                    input.value = prev;
                }
            }).catch(function () {
                input.value = prev;
            });
        });
    });
})();
</script>

</body>
</html>