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
$subscribedChannels = $pdo->query("
    SELECT c.id, c.name, c.url, c.thumbnail_url, cc.name AS category_name
    FROM channels c
    LEFT JOIN channel_categories cc ON c.category_id = cc.id
    ORDER BY c.name ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   📺 熱門頻道（影片最多）
========================= */
$topChannels = $pdo->query("
    SELECT channel_name, COUNT(*) as total
    FROM videos
    GROUP BY channel_name
    ORDER BY total DESC
    LIMIT 5
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

/* 按鈕 */
.btn {
    background: #0077cc;
    color: white;
    padding: 6px 10px;
    border-radius: 6px;
    text-decoration: none;
}
.btn:hover { background: #005fa3; }

/* 分類 */
.category {
    display: inline-block;
    background: #eee;
    padding: 6px 10px;
    margin: 5px;
    border-radius: 6px;
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
    <a class="btn" href="index.php?page=videos&watched=0">📋 待看清單</a>
    <a class="btn" href="index.php?page=videos&watched=1">✅ 已看清單</a>
    <a class="btn" href="index.php?page=channels">📺 頻道管理</a>
    
    <a class="btn" href="index.php?page=add">➕ 新增影片</a>
    <a class="btn" href="scripts/fetch_new_videos.php" target="_blank">📡 抓新影片</a>
</div>

<div class="grid">

<!-- 左邊：影片 -->
<div class="section">
    <div class="section-head">
        <h3>🎬 最新影片</h3>
        <div class="video-tab-toggle" role="tablist" aria-label="待看、已看與已訂閱頻道">
            <button type="button" class="video-tab active" role="tab" aria-selected="true" data-panel="unwatched" id="tab-unwatched">📋 待看</button>
            <button type="button" class="video-tab" role="tab" aria-selected="false" data-panel="watched" id="tab-watched">✅ 已看</button>
            <button type="button" class="video-tab" role="tab" aria-selected="false" data-panel="subscribed" id="tab-subscribed">📺 已訂閱</button>
        </div>
    </div>

    <div id="panel-unwatched" class="video-panel" role="tabpanel" aria-labelledby="tab-unwatched">
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

    <div id="panel-subscribed" class="video-panel" role="tabpanel" aria-labelledby="tab-subscribed" hidden>
        <?php if (empty($subscribedChannels)): ?>
            <p class="video-empty">尚未加入任何頻道。<a href="index.php?page=channels">前往頻道管理</a></p>
        <?php else: ?>
            <div class="subscribed-grid">
                <?php foreach ($subscribedChannels as $ch): ?>
                    <article class="channel-card">
                        <?php if (!empty($ch['thumbnail_url'])): ?>
                            <img class="channel-card-thumb" src="<?= htmlspecialchars($ch['thumbnail_url']) ?>" alt="">
                        <?php else: ?>
                            <span class="channel-card-thumb channel-card-thumb--empty" role="img" aria-label="無頻道圖片"></span>
                        <?php endif; ?>
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

    tabs.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var key = btn.getAttribute('data-panel');
            tabs.forEach(function (b) {
                var on = b.getAttribute('data-panel') === key;
                b.classList.toggle('active', on);
                b.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            panels.forEach(function (p) {
                p.hidden = p.id !== 'panel-' + key;
            });
        });
    });
})();
</script>

<!-- 右邊 -->
<div>

    <!-- 熱門頻道 -->
    <div class="section">
        <h3>🔥 常看頻道</h3>
        <?php foreach ($topChannels as $c): ?>
            <div class="channel">
                <?= htmlspecialchars($c['channel_name']) ?>（<?= $c['total'] ?>）
            </div>
        <?php endforeach; ?>
    </div>

    <!-- 分類 -->
    <div class="section">

        <a class="btn" href="index.php?page=channel_categories">📂 分類管理</a>
        
        <?php foreach ($categories as $c): ?>
            <a class="category" href="index.php?page=channels&category_id=<?= $c['id'] ?>">
                <?= $c['name'] ?> (<?= $c['total'] ?>)
            </a>
        <?php endforeach; ?>
    </div>

</div>

</div>

</body>
</html>