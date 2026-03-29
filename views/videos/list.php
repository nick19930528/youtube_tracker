<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/VideoController.php';

$pdo = (new Database())->getConnection();
$controller = new VideoController($pdo);

$isWatched = isset($_GET['watched']) ? (int)$_GET['watched'] : 0;
$keyword = trim($_GET['keyword'] ?? '');
$orderBy = $_GET['sort'] ?? 'added_at';
$orderDir = $_GET['dir'] ?? 'asc';

$validColumns = ['added_at', 'published_at'];
if (!in_array($orderBy, $validColumns)) $orderBy = 'added_at';
if (!in_array($orderDir, ['asc', 'desc'])) $orderDir = 'desc';

$toggleDir = ($orderDir === 'asc') ? 'desc' : 'asc';
$baseUrl = "index.php?page=videos&watched={$isWatched}" . ($keyword ? "&keyword=" . urlencode($keyword) : "");

$videos = $keyword
    ? $controller->search($isWatched, $keyword)
    : $controller->list($isWatched, $orderBy, $orderDir);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id']) && isset($_POST['delete'])) {
        $controller->delete($_POST['id']);
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    } elseif (isset($_POST['id'])) {
        $controller->markWatched($_POST['id']);
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}

function formatDuration($seconds) {
    $seconds = (int)$seconds;
    return $seconds >= 3600
        ? gmdate("H:i:s", $seconds)
        : gmdate("i:s", $seconds);
}

// 將影片依頻道群組
$groupedVideos = [];
foreach ($videos as $video) {
    $channel = $video['channel_name'] ?? '未命名頻道';
    $groupedVideos[$channel][] = $video;
}

// 依照頻道影片數排序（從少到多）
uasort($groupedVideos, function ($a, $b) {
    return count($a) - count($b);
});

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= $isWatched ? '✅ 已看清單' : '📋 待看清單' ?></title>
    <style>
        body { font-family: Arial; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        img { max-width: 120px; }
        .controls { margin-bottom: 20px; }
        .group-header { background-color: #f0f0f0; font-weight: bold; }
    </style>
</head>
<body>
    <h1><?= $isWatched ? '✅ 已看清單' : '📋 待看清單' ?></h1>

    <div class="controls">
        <form method="get" action="index.php" style="margin-bottom: 20px;">
            <input type="hidden" name="page" value="videos">
            <input type="hidden" name="watched" value="<?= $isWatched ?>">
            <input type="text" name="keyword" placeholder="搜尋標題 / 摘要 / 頻道名稱"
                   value="<?= htmlspecialchars($keyword) ?>" size="40">
            <button type="submit">🔍 搜尋</button>
            <?php if ($keyword): ?>
                <a href="index.php?page=videos&watched=<?= $isWatched ?>">❌ 清除搜尋</a>
            <?php endif; ?>
        </form>

        <p>目前顯示 <?= count($videos) ?> 部影片</p>

        <?php if ($isWatched): ?>
            <?php
                $totalSeconds = $controller->getTodayWatchedDuration();
                $formattedTime = $totalSeconds >= 3600
                    ? gmdate("H:i:s", $totalSeconds)
                    : gmdate("i:s", $totalSeconds);
            ?>
            <p>🕒 今日已觀看總時長：<?= $formattedTime ?></p>
        <?php endif; ?>

        <a href="index.php?page=videos&watched=0">📋 待看</a> |
        <a href="index.php?page=videos&watched=1">✅ 已看</a> |
        <a href="index.php">🏠 回首頁</a> |
        <a href="add.php">➕ 新增影片</a>
    </div>

    <?php if (empty($videos)): ?>
        <p>目前清單為空。</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>縮圖</th>
                    <th>標題</th>
                    <th>頻道名稱</th>
                    <th>
                        <a href="<?= $baseUrl ?>&sort=published_at&dir=<?= $toggleDir ?>">
                            發布時間 <?= $orderBy === 'published_at' ? ($orderDir === 'asc' ? '▲' : '▼') : '' ?>
                        </a>
                    </th>
                    <th>📊 觀看次數</th>
                    <th>👍 喜歡數</th>
                    <th>💬 留言數</th>
                    <th>⏱ 長度</th>
                    <th>觀看連結</th>
                    <th>摘要</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                    <?php
                    $channelIndex = 1;
                    foreach ($groupedVideos as $channelName => $channelVideos):
                        $groupId = md5($channelName);
                    ?>
                    <tr class="group-header">
                        <td colspan="12" style="text-align: center;">
                            <?= $channelIndex++ ?>. <?= htmlspecialchars($channelName) ?>（共 <?= count($channelVideos) ?> 部）
                            <button onclick="toggleGroup('<?= $groupId ?>')">▶️ 展開 / 收合</button>
                        </td>
                    </tr>


                    <?php foreach ($channelVideos as $index => $video): ?>
                        <tr class="group-<?= $groupId ?>" style="display: none;">
                            <td><?= $index + 1 ?></td>

                            <td>
                                <?php if (!empty($video['thumbnail_url'])): ?>
                                    <img src="<?= htmlspecialchars($video['thumbnail_url']) ?>" alt="縮圖">
                                <?php else: ?> -
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($video['title']) ?></td>
                            <td><?= htmlspecialchars($video['channel_name'] ?? '-') ?></td>
                            <td><?= $video['published_at'] ? date('Y-m-d', strtotime($video['published_at'])) : '-' ?></td>
                            <td><?= number_format($video['view_count'] ?? 0) ?></td>
                            <td><?= number_format($video['like_count'] ?? 0) ?></td>
                            <td><?= number_format($video['comment_count'] ?? 0) ?></td>
                            <td><?= isset($video['duration']) ? formatDuration($video['duration']) : '-' ?></td>
                            <td><a href="<?= htmlspecialchars($video['youtube_url']) ?>" target="_blank">▶️ 前往</a></td>
                            <td><?= $video['summary'] ? nl2br(htmlspecialchars($video['summary'])) : '-' ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $video['id'] ?>">
                                    <?php if (!$video['is_watched']): ?>
                                        <button type="submit">✅ 標記為已看</button>
                                    <?php else: ?>
                                        已看
                                    <?php endif; ?>
                                </form>

                                <form method="post" style="display:inline;" onsubmit="return confirm('確定要刪除這部影片嗎？');">
                                    <input type="hidden" name="id" value="<?= $video['id'] ?>">
                                    <input type="hidden" name="delete" value="1">
                                    <button type="submit">🗑 刪除</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <script>
    function toggleGroup(groupId) {
        const rows = document.querySelectorAll('.group-' + groupId);
        rows.forEach(row => {
            row.style.display = (row.style.display === 'none') ? '' : 'none';
        });
    }
    </script>
</body>
</html>
