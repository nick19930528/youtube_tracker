<?php
// controllers/VideoController.php

require_once __DIR__ . '/../models/Video.php';

class VideoController {
    private $pdo;
    private $video;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->video = new Video($pdo); // ← ✅ 加這行
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

        $stmt = $this->pdo->prepare("SELECT * FROM videos WHERE is_watched = ? ORDER BY {$orderBy} {$orderDir}");
        $stmt->execute([$isWatched]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search($isWatched = 0, $keyword = '') {
        $sql = "SELECT * FROM videos
                WHERE is_watched = :watched
                  AND (title LIKE :kw OR summary LIKE :kw OR channel_name LIKE :kw)
                ORDER BY published_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':watched', $isWatched, PDO::PARAM_INT);
        $stmt->bindValue(':kw', '%' . $keyword . '%', PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTodayWatchedDuration() {
        $stmt = $this->pdo->prepare("SELECT SUM(duration) as total FROM videos WHERE is_watched = 1 AND DATE(watched_at) = CURDATE()");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }



    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM videos WHERE id = ?");
        return $stmt->execute([$id]);
    }
    public function markWatched($id) {
        $stmt = $this->pdo->prepare("UPDATE videos SET is_watched = 1, watched_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

}
