<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Video.php';
require_once __DIR__ . '/../models/Channel.php';
ob_start(); // 開啟輸出緩衝區
$pdo = (new Database())->getConnection();
$videoModel = new Video($pdo);
$channelModel = new Channel($pdo);

$channels = $channelModel->getAll();

foreach ($channels as $channel) {
    $channelId = $channel['channel_id'];
    echo "📺 處理頻道：{$channel['name']} ({$channelId})\n";

    // 更新頻道統計與描述
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
            WHERE channel_id = ?
        ");
        $stmt->execute([
            $subscriberCount,
            $videoCount,
            $description,
            $publishedAt,
            $thumbnail,
            $channelId
        ]);

        echo " 頻道資料更新 - 訂閱：{$subscriberCount}｜影片：{$videoCount}\n";
    } else {
        echo "⚠️ 無法取得頻道資料：{$channelId}\n";
    }

    // 讀取 RSS
    $feedUrl = "https://www.youtube.com/feeds/videos.xml?channel_id={$channelId}";
    $xml = @simplexml_load_file($feedUrl);
    if ($xml === false) {
        echo "⚠️ 無法載入 RSS：$feedUrl\n";
        continue;
    }

    foreach ($xml->entry as $entry) {
        $title = (string)$entry->title;
        $url = (string)$entry->link['href'];

        // 取得 videoId
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

        // 發布時間與過濾太舊影片
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

        // 呼叫 YouTube API 取得影片資訊（含統計、縮圖、時長）
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
        // ✅ 過濾 3 分鐘以下影片
        if ($duration !== null && $duration < 180) {
            echo "❌略過太短的影片（{$duration} 秒）：$title\n";
            continue;
        }
        $channelName = $channel['name'];
        $summary = '';

        // 儲存影片
        if ($videoModel->add($title, $url, $summary, $publishedAt, $viewCount, $likeCount, $commentCount, $thumbnailUrl, $channelName, $duration)) {
            echo "✅ 新增影片：$title\n";
        }
    }
}



// 👇（中間 echo 那段保持不變，會被捕捉）

echo "🎉 自動擷取完成。\n";

$output = ob_get_clean(); // 取得所有 echo 的內容

// 將 $output 丟進畫面
include __DIR__ . '/../views/scripts/fetch_result.php';
exit;
