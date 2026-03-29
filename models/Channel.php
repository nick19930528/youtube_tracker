<?php
class Channel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAll($categoryId = null, $keyword = null) {
        $sql = "
            SELECT c.*, cat.name AS category_name
            FROM channels c
            LEFT JOIN channel_categories cat ON c.category_id = cat.id
            WHERE 1
        ";
        $params = [];

        if ($categoryId) {
            $sql .= " AND c.category_id = ?";
            $params[] = $categoryId;
        }

        if ($keyword) {
            $sql .= " AND c.name LIKE ?";
            $params[] = '%' . $keyword . '%';
        }

        $sql .= " ORDER BY c.subscribed_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    public function add($name, $channelId, $url, $categoryId = null) {
        if ($this->exists($channelId)) {
            return false;
        }

        // 初始化欄位
        $subscriberCount = 0;
        $videoCount = 0;
        $description = null;
        $publishedAt = null;
        $thumbnail = null;

        // 取得額外資料
        $apiUrl = "https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics&id={$channelId}&key=" . YOUTUBE_API_KEY;
        $json = file_get_contents($apiUrl);
        $data = json_decode($json, true);

        if (!empty($data['items'][0])) {
            $item = $data['items'][0];

            $subscriberCount = (int)($item['statistics']['subscriberCount'] ?? 0);
            $videoCount = (int)($item['statistics']['videoCount'] ?? 0);
            $description = $item['snippet']['description'] ?? null;
            $thumbnail = $item['snippet']['thumbnails']['default']['url'] ?? null;

            // 轉換 publishedAt 格式
            if (!empty($item['snippet']['publishedAt'])) {
                $timestamp = strtotime($item['snippet']['publishedAt']);
                if ($timestamp !== false) {
                    $publishedAt = date('Y-m-d H:i:s', $timestamp);
                }
            }
        }

        // 寫入資料
        $stmt = $this->pdo->prepare("
            INSERT INTO channels (
                name, channel_id, url, subscribed_at, category_id,
                subscriber_count, video_count, description, published_at, thumbnail_url
            )
            VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $name,
            $channelId,
            $url,
            $categoryId,
            $subscriberCount,
            $videoCount,
            $description,
            $publishedAt,
            $thumbnail
        ]);
    }
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM channels WHERE id = ?");
        return $stmt->execute([$id]);
    }



    public function getCategories() {
        $stmt = $this->pdo->query("SELECT * FROM channel_categories ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function exists($channelId) {
        $stmt = $this->pdo->prepare("SELECT 1 FROM channels WHERE channel_id = ?");
        $stmt->execute([$channelId]);
        return $stmt->fetchColumn() !== false;
    }
    public function updateCategory($channelId, $categoryId) {
        $stmt = $this->pdo->prepare("UPDATE channels SET category_id = ? WHERE id = ?");
        return $stmt->execute([$categoryId, $channelId]);
    }

    
}
