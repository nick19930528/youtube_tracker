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
    <h3>🎬 最新未看影片</h3>

    <?php foreach ($latestVideos as $v): ?>
        <div class="video">
            <?php if ($v['thumbnail_url']): ?>
                <img src="<?= htmlspecialchars($v['thumbnail_url']) ?>">
            <?php endif; ?>

            <div>
                <a href="<?= $v['youtube_url'] ?>" target="_blank">
                    <?= htmlspecialchars($v['title']) ?>
                </a>
                <br>
                <small><?= htmlspecialchars($v['channel_name']) ?></small>
            </div>
        </div>
    <?php endforeach; ?>
</div>

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