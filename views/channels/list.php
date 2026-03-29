<?php
require_once __DIR__ . '/../../config/bootstrap.php';
auth_require_login();
require_once __DIR__ . '/../../controllers/ChannelController.php';

$pdo = (new Database())->getConnection();
$uid = auth_user_id();
$controller = new ChannelController($pdo, $uid);


// ✨ 表單送出處理（必須在任何輸出前）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 刪除頻道
    if (isset($_POST['delete_channel_id'])) {
        $controller->delete($_POST['delete_channel_id']);
        header("Location: index.php?page=channels" . (isset($_GET['category_id']) ? "&category_id=" . urlencode($_GET['category_id']) : ""));
        exit;
    }


    // 切換最愛
    if (isset($_POST['toggle_favorite_id'])) {
        $controller->toggleFavorite((int)$_POST['toggle_favorite_id']);
        $redirect = "index.php?page=channels";
        if (isset($_GET['category_id'])) {
            $redirect .= "&category_id=" . urlencode($_GET['category_id']);
        }
        if (!empty($_GET['keyword'])) {
            $redirect .= "&keyword=" . urlencode($_GET['keyword']);
        }
        header("Location: $redirect");
        exit;
    }

    // 分類變更
    if (isset($_POST['channel_id'], $_POST['new_category_id'])) {
        $controller->updateCategory($_POST['channel_id'], $_POST['new_category_id'] ?: null);

        $redirect = "index.php?page=channels";
        if (isset($_GET['category_id'])) {
            $redirect .= "&category_id=" . urlencode($_GET['category_id']);
        }
        header("Location: $redirect");
        exit;
    }

    // 新增頻道
    if (isset($_POST['input'])) {
        $input = trim($_POST['input']);
        $categoryId = $_POST['category_id'] ?: null;

        $channelId = null;
        $name = '';
        $url = '';

        if (strpos($input, '@') === 0) {
            $handle = substr($input, 1);
            // ✅ 改為精準查詢
            $apiUrl = "https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics&forHandle={$handle}&key=" . YOUTUBE_API_KEY;
            $json = file_get_contents($apiUrl);
            $data = json_decode($json, true);

            if (!empty($data['items'][0])) {
                $item = $data['items'][0];
                $channelId = $item['id'];
                $name = $item['snippet']['title'];
                $url = "https://www.youtube.com/@{$handle}"; // 保留原始輸入格式
            }
        } elseif (preg_match('/(youtube\\.com\/(channel|@[^\/]+))/', $input)) {
            $url = $input;

            if (preg_match('/\/channel\/([a-zA-Z0-9_-]+)/', $input, $matches)) {
                $channelId = $matches[1];
            } elseif (preg_match('/youtube\\.com\/@([a-zA-Z0-9._-]+)/', $input, $matches)) {
                $handle = $matches[1];
                $apiUrl = "https://www.googleapis.com/youtube/v3/search?part=snippet&type=channel&q={$handle}&key=" . YOUTUBE_API_KEY;
                $json = file_get_contents($apiUrl);
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

        if ($channelId && empty($name)) {
            $apiUrl = "https://www.googleapis.com/youtube/v3/channels?part=snippet&id={$channelId}&key=" . YOUTUBE_API_KEY;
            $json = file_get_contents($apiUrl);
            $data = json_decode($json, true);
            if (!empty($data['items'][0])) {
                $name = $data['items'][0]['snippet']['title'];
            }
        }

        if ($channelId && $name && $url) {
            if ($controller->add($name, $channelId, $url, $categoryId)) {
                header("Location: index.php?page=channels"); // ← 不帶 category_id
                exit;
            }
            require_once __DIR__ . '/../../config/plan_limits.php';
            if (!plan_limits_can_add_channel($pdo, $uid)) {
                echo '<p>⚠️ 免費版最多 ' . (int)PLAN_FREE_MAX_CHANNELS . ' 個頻道。</p>';
            } else {
                echo "<p>⚠️ 頻道已存在或新增失敗。</p>";
            }
        }
    }
}

$selectedCategoryId = $_GET['category_id'] ?? null;
$keyword = $_GET['keyword'] ?? null;
$channels = $controller->list($selectedCategoryId, $keyword);
$categories = $controller->getCategoriesWithCount();
?>

<!-- ✅ HTML 區塊開始 -->
<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
    <h2 style="margin: 0;">📺 頻道清單</h2>
    <p>📊 總頻道數：<?= count($channels) ?> 個</p>
    <form method="get" action="index.php" style="margin: 0;">
        <input type="hidden" name="page" value="channel_categories">
        <button type="submit">🗂️ 新增分類</button>
    </form>    
    <form method="get" action="index.php" style="margin: 0;">
        <input type="hidden" name="page" value="channels">
        <label>
            📂
            <select name="category_id" onchange="this.form.submit()">
                <option value="">全部分類</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($selectedCategoryId == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>（<?= $cat['channel_count'] ?? 0 ?>）
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    </form>

    <form method="get" action="index.php" style="margin: 0;">
        <input type="hidden" name="page" value="channels">
        <input type="hidden" name="category_id" value="<?= htmlspecialchars($selectedCategoryId) ?>">
        <label>🔍
            <input type="text" name="keyword" placeholder="搜尋頻道名稱" value="<?= htmlspecialchars($keyword) ?>" style="width: 200px;">
        </label>
        <button type="submit">搜尋</button>
    </form>




    <form method="get" action="index.php" style="margin: 0;">
        <button type="submit">🏠 回首頁</button>
    </form>
</div>

<h3>➕ 新增頻道</h3>
<form method="post" action="">
    <label>輸入網址 / @handle / channel ID：
        <input type="text" name="input" required style="width:400px;">
    </label><br>
    <label>分類：
        <select name="category_id">
            <option value="">請選擇</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>
    <button type="submit">新增</button>
</form>

<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>縮圖</th>
            <th>名稱</th>
            <th>訂閱數</th>
            <th>影片總數</th>
            <th>網址</th>
            <th>創立時間</th>
            <th>頻道 ID</th>
            <th>訂閱時間</th>
            <th>最愛</th>
            <th>操作</th> <!-- 將「分類（可變更）」改為「操作」 -->
        </tr>
    </thead>

<tbody>
    <?php foreach ($channels as $channel): ?>
        <tr>
            <td>
                <?php if (!empty($channel['thumbnail_url'])): ?>
                    <img src="<?= htmlspecialchars($channel['thumbnail_url']) ?>" alt="縮圖" width="80">
                <?php else: ?>-
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($channel['name']) ?></td>
            <td>
                <?php
                $count = $channel['subscriber_count'];
                if ($count >= 1000000) {
                    echo round($count / 1000000, 1) . '百萬';
                } elseif ($count >= 10000) {
                    echo round($count / 10000, 1) . '萬';
                } else {
                    echo number_format($count);
                }
                ?>
            </td>
            <td><?= number_format($channel['video_count']) ?></td>
            <td><a href="<?= htmlspecialchars($channel['url']) ?>" target="_blank">前往</a></td>
            <td><?= $channel['published_at'] ? date('Y-m-d', strtotime($channel['published_at'])) : '-' ?></td>
            <td><?= htmlspecialchars($channel['channel_id']) ?></td>
            <td><?= date('Y-m-d', strtotime($channel['subscribed_at'])) ?></td>
            <td>
                <form method="post" style="margin:0;">
                    <input type="hidden" name="toggle_favorite_id" value="<?= (int)$channel['id'] ?>">
                    <button type="submit" title="切換我的最愛"><?= !empty($channel['is_favorite']) ? '⭐' : '☆' ?></button>
                </form>
            </td>
            <td>
                <!-- 將分類與刪除按鈕整合在一起 -->
                <form method="post" style="margin:0; display: inline-block;">
                    <input type="hidden" name="channel_id" value="<?= $channel['id'] ?>">
                    <select name="new_category_id" onchange="this.form.submit()">
                        <option value="">未分類</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($channel['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <form method="post" onsubmit="return confirm('確定要刪除此頻道嗎？');" style="display:inline;">
                    <input type="hidden" name="delete_channel_id" value="<?= $channel['id'] ?>">
                    <button type="submit">🗑</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
