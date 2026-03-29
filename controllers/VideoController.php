<?php
require_once __DIR__ . '/../models/Video.php';

class VideoController {
    private $pdo;
    private $video;
    private $userId;

    public function __construct($pdo, int $userId) {
        $this->pdo = $pdo;
        $this->userId = $userId;
        $this->video = new Video($pdo, $userId);
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
        if (!$this->video->exists($url)) {
            return $this->video->add(
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
                $isWatched,
                $watchedAt
            );
        }
        return false;
    }

    public function list($isWatched = 0, $orderBy = 'added_at', $orderDir = 'desc') {
        $orderBy = in_array($orderBy, ['added_at', 'published_at']) ? $orderBy : 'added_at';
        $orderDir = ($orderDir === 'asc') ? 'asc' : 'desc';

        $stmt = $this->pdo->prepare("SELECT * FROM videos WHERE user_id = ? AND is_watched = ? ORDER BY {$orderBy} {$orderDir}");
        $stmt->execute([$this->userId, $isWatched]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search($isWatched = 0, $keyword = '') {
        $sql = "SELECT * FROM videos
                WHERE user_id = :uid AND is_watched = :watched
                  AND (title LIKE :kw OR summary LIKE :kw OR channel_name LIKE :kw)
                ORDER BY published_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':uid', $this->userId, PDO::PARAM_INT);
        $stmt->bindValue(':watched', $isWatched, PDO::PARAM_INT);
        $stmt->bindValue(':kw', '%' . $keyword . '%', PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTodayWatchedDuration() {
        $stmt = $this->pdo->prepare("SELECT SUM(duration) as total FROM videos WHERE user_id = ? AND is_watched = 1 AND DATE(watched_at) = CURDATE()");
        $stmt->execute([$this->userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM videos WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $this->userId]);
    }

    public function markWatched($id) {
        $stmt = $this->pdo->prepare("UPDATE videos SET is_watched = 1, watched_at = NOW() WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $this->userId]);
    }
}
