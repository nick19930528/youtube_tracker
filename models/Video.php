<?php

require_once __DIR__ . '/../config/plan_limits.php';

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

        $ok = $stmt->execute([
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
        if ($ok) {
            $this->enforceStorageCap((int) $isWatched);
        }

        return $ok;
    }

    /**
     * 待看／已看各依方案「單邊清單筆數」上限；超過則刪除該側最舊列（與 plan_limits_max_videos_per_list 一致）
     *
     * @param int $isWatched 0 待看, 1 已看
     */
    private function enforceStorageCap($isWatched) {
        $max = plan_limits_max_videos_per_list($this->pdo, $this->userId);
        if ($max === null || $max < 1) {
            return;
        }
        $w = ((int) $isWatched) ? 1 : 0;
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM videos WHERE user_id = ? AND is_watched = ?');
        $stmt->execute([$this->userId, $w]);
        $n = (int) $stmt->fetchColumn();
        if ($n <= $max) {
            return;
        }
        $delete = $n - $max;
        $del = $this->pdo->prepare('DELETE FROM videos WHERE user_id = ? AND is_watched = ? ORDER BY added_at ASC, id ASC LIMIT ?');
        $del->bindValue(1, $this->userId, PDO::PARAM_INT);
        $del->bindValue(2, $w, PDO::PARAM_INT);
        $del->bindValue(3, $delete, PDO::PARAM_INT);
        $del->execute();
    }

    private static $trimBothDone = array();

    /**
     * 依目前方案對「待看」「已看」兩側各別套用單邊上限（方案變更後可呼叫以立即收斂）
     */
    public function trimBothSidesToPlanLimits() {
        if (!empty(self::$trimBothDone[$this->userId])) {
            return;
        }
        self::$trimBothDone[$this->userId] = true;
        $this->enforceStorageCap(0);
        $this->enforceStorageCap(1);
    }

    public function exists($url) {
        $stmt = $this->pdo->prepare("SELECT 1 FROM videos WHERE user_id = ? AND youtube_url = ?");
        $stmt->execute([$this->userId, $url]);
        return $stmt->fetchColumn() !== false;
    }

    public function markWatched($id) {
        $stmt = $this->pdo->prepare("UPDATE videos SET is_watched = 1, watched_at = NOW() WHERE id = ? AND user_id = ? AND is_watched = 0");
        $ok = $stmt->execute([$id, $this->userId]);
        if ($ok && $stmt->rowCount() > 0) {
            $this->enforceStorageCap(1);
        }

        return $ok;
    }
}
