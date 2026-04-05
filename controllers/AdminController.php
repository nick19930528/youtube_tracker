<?php

class AdminController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function countUsers($keyword = null)
    {
        $kw = $keyword !== null ? trim((string) $keyword) : '';
        if ($kw === '') {
            $stmt = $this->pdo->query('SELECT COUNT(*) FROM users');

            return (int) $stmt->fetchColumn();
        }
        $like = '%' . $kw . '%';
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE email LIKE ? OR name LIKE ?');
        $stmt->execute(array($like, $like));

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listUsers($page, $perPage, $keyword = null)
    {
        $page = max(1, (int) $page);
        $perPage = max(1, min(100, (int) $perPage));
        $offset = ($page - 1) * $perPage;
        $kw = $keyword !== null ? trim((string) $keyword) : '';

        if ($kw === '') {
            $sql = 'SELECT id, email, name, gender, created_at FROM users ORDER BY id DESC LIMIT ? OFFSET ?';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
            $stmt->bindValue(2, $offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $like = '%' . $kw . '%';
            $sql = 'SELECT id, email, name, gender, created_at FROM users WHERE email LIKE ? OR name LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(1, $like, PDO::PARAM_STR);
            $stmt->bindValue(2, $like, PDO::PARAM_STR);
            $stmt->bindValue(3, $perPage, PDO::PARAM_INT);
            $stmt->bindValue(4, $offset, PDO::PARAM_INT);
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 該會員所有訂閱紀錄（含歷史）
     *
     * @return array<int, array<string, mixed>>
     */
    public function listAllSubscriptionsForUser($userId)
    {
        $uid = (int) $userId;
        if ($uid < 1) {
            return array();
        }
        $stmt = $this->pdo->prepare("
            SELECT s.id, s.status, s.current_period_start, s.current_period_end, s.cancel_at_period_end,
                   s.external_subscription_id, s.created_at, s.updated_at,
                   p.name AS plan_name, p.slug, p.price_cents, p.currency, p.billing_interval
            FROM subscriptions s
            INNER JOIN subscription_plans p ON p.id = s.plan_id
            WHERE s.user_id = ?
            ORDER BY s.id DESC
        ");
        $stmt->execute(array($uid));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listChannelsForUser($userId)
    {
        $uid = (int) $userId;
        if ($uid < 1) {
            return array();
        }
        $stmt = $this->pdo->prepare("
            SELECT c.id, c.name, c.url, c.channel_id, c.subscribed_at, c.category_id,
                   cat.name AS category_name
            FROM channels c
            LEFT JOIN channel_categories cat ON cat.id = c.category_id AND cat.user_id = c.user_id
            WHERE c.user_id = ?
            ORDER BY c.subscribed_at DESC
        ");
        $stmt->execute(array($uid));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array{unwatched:int, watched:int}
     */
    public function getVideoCountsForUser($userId)
    {
        $uid = (int) $userId;
        if ($uid < 1) {
            return array('unwatched' => 0, 'watched' => 0);
        }
        $stmt = $this->pdo->prepare('
            SELECT
                SUM(CASE WHEN is_watched = 0 THEN 1 ELSE 0 END) AS unwatched,
                SUM(CASE WHEN is_watched = 1 THEN 1 ELSE 0 END) AS watched
            FROM videos
            WHERE user_id = ?
        ');
        $stmt->execute(array($uid));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return array(
            'unwatched' => (int) ($row['unwatched'] ?? 0),
            'watched' => (int) ($row['watched'] ?? 0),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listRecentVideosForUser($userId, $limit = 40)
    {
        $uid = (int) $userId;
        $limit = max(1, min(100, (int) $limit));
        if ($uid < 1) {
            return array();
        }
        $stmt = $this->pdo->prepare("
            SELECT id, title, youtube_url, is_watched, watched_at, added_at, channel_name
            FROM videos
            WHERE user_id = ?
            ORDER BY added_at DESC
            LIMIT {$limit}
        ");
        $stmt->execute(array($uid));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
