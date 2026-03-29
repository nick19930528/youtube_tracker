<?php
class Channel {
    private $pdo;
    private $userId;

    public function __construct($pdo, int $userId) {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function getAll($categoryId = null, $keyword = null) {
        $sql = "
            SELECT c.*, cat.name AS category_name
            FROM channels c
            LEFT JOIN channel_categories cat ON c.category_id = cat.id AND cat.user_id = c.user_id
            WHERE c.user_id = ?
        ";
        $params = [$this->userId];

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

        $subscriberCount = 0;
        $videoCount = 0;
        $description = null;
        $publishedAt = null;
        $thumbnail = null;

        $apiUrl = "https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics&id={$channelId}&key=" . YOUTUBE_API_KEY;
        $json = file_get_contents($apiUrl);
        $data = json_decode($json, true);

        if (!empty($data['items'][0])) {
            $item = $data['items'][0];

            $subscriberCount = (int)($item['statistics']['subscriberCount'] ?? 0);
            $videoCount = (int)($item['statistics']['videoCount'] ?? 0);
            $description = $item['snippet']['description'] ?? null;
            $thumbnail = $item['snippet']['thumbnails']['default']['url'] ?? null;

            if (!empty($item['snippet']['publishedAt'])) {
                $timestamp = strtotime($item['snippet']['publishedAt']);
                if ($timestamp !== false) {
                    $publishedAt = date('Y-m-d H:i:s', $timestamp);
                }
            }
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO channels (
                user_id, name, channel_id, url, subscribed_at, category_id,
                subscriber_count, video_count, description, published_at, thumbnail_url
            )
            VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $this->userId,
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
        $stmt = $this->pdo->prepare("DELETE FROM channels WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $this->userId]);
    }

    public function getCategories() {
        $stmt = $this->pdo->prepare("SELECT * FROM channel_categories WHERE user_id = ? ORDER BY name");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function exists($channelId) {
        $stmt = $this->pdo->prepare("SELECT 1 FROM channels WHERE channel_id = ? AND user_id = ?");
        $stmt->execute([$channelId, $this->userId]);
        return $stmt->fetchColumn() !== false;
    }

    public function updateCategory($channelId, $categoryId) {
        $stmt = $this->pdo->prepare("UPDATE channels SET category_id = ? WHERE id = ? AND user_id = ?");
        return $stmt->execute([$categoryId, $channelId, $this->userId]);
    }

    public function toggleFavorite($id) {
        $id = (int)$id;
        if ($id < 1) {
            return false;
        }
        $stmt = $this->pdo->prepare("UPDATE channels SET is_favorite = IF(is_favorite = 1, 0, 1) WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $this->userId]);
    }
}
