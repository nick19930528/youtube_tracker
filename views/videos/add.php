<?php
require_once __DIR__ . '/../../config/bootstrap.php';
auth_require_login();
require_once __DIR__ . '/../../controllers/VideoController.php';

$pdo = (new Database())->getConnection();
$uid = auth_user_id();
$controller = new VideoController($pdo, $uid);

$uiTheme = (isset($_SESSION['ui_theme']) && $_SESSION['ui_theme'] === 'dark') ? 'dark' : 'light';

$message = "";

// 擷取 YouTube 影片 ID
function extractVideoId($url) {
    if (preg_match('/(?:v=|youtu\.be\/)([A-Za-z0-9_\-]+)/', $url, $matches)) {
        return $matches[1];
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = trim($_POST['youtube_url']);
    $videoId = extractVideoId($url);

    if ($videoId) {
        $ytApi = "https://www.googleapis.com/youtube/v3/videos?part=snippet,statistics,contentDetails&id={$videoId}&key=" . YOUTUBE_API_KEY;
        $ytJson = file_get_contents($ytApi);
        $ytData = json_decode($ytJson, true);
        $item = $ytData['items'][0] ?? null;

        if ($item) {
            $title = $item['snippet']['title'];
            $publishedAt = $item['snippet']['publishedAt'] ?? null;
            $thumbnailUrl = $item['snippet']['thumbnails']['default']['url'] ?? null;
            $channelName = $item['snippet']['channelTitle'] ?? null;
            $viewCount = $item['statistics']['viewCount'] ?? 0;
            $likeCount = $item['statistics']['likeCount'] ?? 0;
            $commentCount = $item['statistics']['commentCount'] ?? 0;
            // ⏱️ 轉換 duration 格式（PT#H#M#S → 秒）
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
            // 使用 OpenAI 自動摘要
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
            if ($controller->add(
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
                    1,              // isWatched
                    $watchedAt      // watchedAt
                )) {
                $message = "✅ 影片已成功加入已看清單";
            } else {
                $message = "⚠️ 這部影片已經存在於清單中";
            }
        } else {
            $message = "⚠️ 無法取得影片資訊";
        }
    } else {
        $message = "❌ 無效的 YouTube 連結";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">
    <link rel="manifest" href="site.webmanifest">
    <meta name="apple-mobile-web-app-title" content="TubeLog">
    <meta name="application-name" content="TubeLog">
    <title>新增影片</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        body[data-theme="dark"] { background: #0b1220; color: #e2e8f0; }
        body[data-theme="dark"] a { color: #93c5fd; }
        body[data-theme="dark"] input, body[data-theme="dark"] button {
            background: rgba(2, 6, 23, 0.85);
            color: #e2e8f0;
            border: 1px solid rgba(51, 65, 85, 0.9);
        }
    </style>
</head>
<body data-theme="<?= htmlspecialchars($uiTheme, ENT_QUOTES, 'UTF-8') ?>">
    <h1>➕ 新增 YouTube 影片</h1>

    <!-- ✅ 導覽按鈕 -->
    <div style="margin-bottom: 20px;">
        <a href="index.php?page=videos&watched=1">← 回已看清單</a> |
        <a href="index.php">🏠 返回首頁</a>
    </div>

    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="youtube_url">YouTube 影片網址：</label><br>
        <input type="text" name="youtube_url" id="youtube_url" size="60" required><br><br>
        <button type="submit">加入清單</button>
    </form>
</body>
</html>
