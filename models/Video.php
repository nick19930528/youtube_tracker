<?php
class Video {
    private $pdo;
    private $userId;

    public function __construct($pdo, int $userId) {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function getAll($isWatched = 0) {
        $stmt = $this->pdo->prepare("SELECT * FROM videos WHERE user_id = ? AND is_watched = ? ORDER BY added_at DESC");
        $stmt->execute([$this->userId, $isWatched]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add(
        $title,
        $url,
        $summary = '',
        $publishedAt = null,
        $viewCount = 0,
        $likeCount = 0,
        $commentCount = 0,
        $thumbnailUrl = null,
        $channelName = null,
        $duration = null,
        $isWatched = 0,
        $watchedAt = null
    ) {
        $publishedAt = $publishedAt ? date('Y-m-d H:i:s', strtotime($publishedAt)) : null;
        $watchedAt = $watchedAt ? date('Y-m-d H:i:s', strtotime($watchedAt)) : null;

        $stmt = $this->pdo->prepare("
            INSERT INTO videos (
                user_id, title, youtube_url, summary, is_watched, added_at, watched_at, published_at,
                view_count, like_count, comment_count, thumbnail_url, channel_name, duration
            ) VALUES (
                ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?
            )
        ");

        return $stmt->execute([
            $this->userId,
            $title,
            $url,
            $summary,
            $isWatched,
            $watchedAt,
            $publishedAt,
            $viewCount,
            $likeCount,
            $commentCount,
            $thumbnailUrl,
            $channelName,
            $duration
        ]);
    }

    public function exists($url) {
        $stmt = $this->pdo->prepare("SELECT 1 FROM videos WHERE user_id = ? AND youtube_url = ?");
        $stmt->execute([$this->userId, $url]);
        return $stmt->fetchColumn() !== false;
    }

    public function markWatched($id) {
        $stmt = $this->pdo->prepare("UPDATE videos SET is_watched = 1, watched_at = NOW() WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $this->userId]);
    }
}
