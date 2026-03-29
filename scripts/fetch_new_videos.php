<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../models/Video.php';
require_once __DIR__ . '/../models/Channel.php';

if (php_sapi_name() === 'cli') {
    $uid = isset($argv[1]) ? (int)$argv[1] : 0;
    if ($uid < 1) {
        fwrite(STDERR, "用法: php fetch_new_videos.php <user_id>\n");
        exit(1);
    }
} else {
    auth_require_login();
    $uid = auth_user_id();
}

ob_start();
$pdo = (new Database())->getConnection();
$videoModel = new Video($pdo, $uid);
$channelModel = new Channel($pdo, $uid);

$channels = $channelModel->getAll();

foreach ($channels as $channel) {
    $channelId = $channel['channel_id'];
    echo "📺 處理頻道：{$channel['name']} ({$channelId})\n";

    $apiUrl = "https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics&id={$channelId}&key=" . YOUTUBE_API_KEY;
    $json = file_get_contents($apiUrl);
    $data = json_decode($json, true);

    if (!empty($data['items'][0])) {
        $item = $data['items'][0];

        $subscriberCount = (int)($item['statistics']['subscriberCount'] ?? 0);
        $videoCount = (int)($item['statistics']['videoCount'] ?? 0);
        $description = $item['snippet']['description'] ?? null;
        $publishedAt = !empty($item['snippet']['publishedAt']) ? date('Y-m-d H:i:s', strtotime($item['snippet']['publishedAt'])) : null;
        $thumbnail = $item['snippet']['thumbnails']['default']['url'] ?? null;

        $stmt = $pdo->prepare("
            UPDATE channels
            SET subscriber_count = ?, video_count = ?, description = ?, published_at = ?, thumbnail_url = ?
            WHERE channel_id = ? AND user_id = ?
        ");
        $stmt->execute([
            $subscriberCount,
            $videoCount,
            $description,
            $publishedAt,
            $thumbnail,
            $channelId,
            $uid
        ]);

        echo " 頻道資料更新 - 訂閱：{$subscriberCount}｜影片：{$videoCount}\n";
    } else {
        echo "⚠️ 無法取得頻道資料：{$channelId}\n";
    }

    $feedUrl = "https://www.youtube.com/feeds/videos.xml?channel_id={$channelId}";
    $xml = @simplexml_load_file($feedUrl);
    if ($xml === false) {
        echo "⚠️ 無法載入 RSS：$feedUrl\n";
        continue;
    }

    foreach ($xml->entry as $entry) {
        $title = (string)$entry->title;
        $url = (string)$entry->link['href'];

        $videoId = null;
        if (preg_match('/v=([a-zA-Z0-9_-]+)/', $url, $matches) ||
            preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $matches) ||
            preg_match('/embed\/([a-zA-Z0-9_-]+)/', $url, $matches) ||
            preg_match('/watch\\?v=([a-zA-Z0-9_-]+)/', $url, $matches) ||
            preg_match('/\\/v\\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            $videoId = $matches[1];
        } else {
            $videoId = basename(parse_url($url, PHP_URL_PATH));
        }

        $publishedAt = null;
        $ts = 0;
        if (!empty($entry->published)) {
            $ts = strtotime((string)$entry->published);
            $publishedAt = $ts !== false ? date('Y-m-d H:i:s', $ts) : null;
        }
        if ($ts < strtotime('-7 days')) {
            continue;
        }

        if ($videoModel->exists($url)) {
            continue;
        }

        $viewCount = 0;
        $likeCount = 0;
        $commentCount = 0;
        $thumbnailUrl = null;
        $duration = null;

        if ($videoId) {
            $videoApiUrl = "https://www.googleapis.com/youtube/v3/videos?part=snippet,statistics,contentDetails&id={$videoId}&key=" . YOUTUBE_API_KEY;
            $videoJson = file_get_contents($videoApiUrl);
            $videoData = json_decode($videoJson, true);
            if (!empty($videoData['items'][0])) {
                $item = $videoData['items'][0];

                $stats = $item['statistics'];
                $snippet = $item['snippet'];
                $contentDetails = $item['contentDetails'];

                $viewCount = (int)($stats['viewCount'] ?? 0);
                $likeCount = (int)($stats['likeCount'] ?? 0);
                $commentCount = (int)($stats['commentCount'] ?? 0);
                $thumbnailUrl = $snippet['thumbnails']['default']['url'] ?? null;

                if (!empty($contentDetails['duration'])) {
                    try {
                        $interval = new DateInterval($contentDetails['duration']);
                        $duration = ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
                    } catch (Exception $e) {
                        $duration = null;
                    }
                }
            }
        }
        if ($duration !== null && $duration < 180) {
            echo "❌略過太短的影片（{$duration} 秒）：$title\n";
            continue;
        }
        $channelName = $channel['name'];
        $summary = '';

        if ($videoModel->add($title, $url, $summary, $publishedAt, $viewCount, $likeCount, $commentCount, $thumbnailUrl, $channelName, $duration)) {
            echo "✅ 新增影片：$title\n";
        }
    }
}

echo "🎉 自動擷取完成。\n";

$output = ob_get_clean();

include __DIR__ . '/../views/scripts/fetch_result.php';
exit;
