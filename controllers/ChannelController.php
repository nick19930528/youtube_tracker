<?php
require_once __DIR__ . '/../models/Channel.php';

class ChannelController {
    private $channel;
    private $pdo;
    private $userId;

    public function __construct($pdo, int $userId) {
        $this->pdo = $pdo;
        $this->userId = $userId;
        $this->channel = new Channel($pdo, $userId);
    }

    public function add($name, $channelId, $url, $categoryId = null) {
        return $this->channel->add($name, $channelId, $url, $categoryId);
    }

    public function delete($id) {
        return $this->channel->delete($id);
    }

    public function list($categoryId = null, $keyword = null) {
        return $this->channel->getAll($categoryId, $keyword);
    }

    public function exists($channelId) {
        return $this->channel->exists($channelId);
    }

    public function getCategoriesWithCount() {
        $stmt = $this->pdo->prepare("
            SELECT c.*, COUNT(ch.id) AS channel_count
            FROM channel_categories c
            LEFT JOIN channels ch ON ch.category_id = c.id AND ch.user_id = c.user_id
            WHERE c.user_id = ?
            GROUP BY c.id
            ORDER BY c.sort_order ASC
        ");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateCategory($channelId, $categoryId) {
        return $this->channel->updateCategory($channelId, $categoryId);
    }

    public function toggleFavorite($id) {
        return $this->channel->toggleFavorite($id);
    }
}
