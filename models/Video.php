<?php
// models/Video.php

class Video {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAll($isWatched = 0) {
        $stmt = $this->pdo->prepare("SELECT * FROM videos WHERE is_watched = ? ORDER BY added_at DESC");
        $stmt->execute([$isWatched]);
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
            title, youtube_url, summary, is_watched, added_at, watched_at, published_at,
            view_count, like_count, comment_count, thumbnail_url, channel_name, duration
        ) VALUES (
            ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?
        )
    ");

    return $stmt->execute([
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




     //檢查是否已存在
    public function exists($url) {
        $stmt = $this->pdo->prepare("SELECT 1 FROM videos WHERE youtube_url = ?");
        $stmt->execute([$url]);
        return $stmt->fetchColumn() !== false;
    }

    public function markWatched($id) {
        $stmt = $this->pdo->prepare("UPDATE videos SET is_watched = 1, watched_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }




}
