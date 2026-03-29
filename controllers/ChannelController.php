<?php
// controllers/ChannelController.php

require_once __DIR__ . '/../models/Channel.php';

class ChannelController {
    private $channel;

    public function __construct($pdo) {
        $this->pdo = $pdo; // ✅ 加上這一行
        $this->channel = new Channel($pdo);
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
        $stmt = $this->pdo->query("
            SELECT c.*, COUNT(ch.id) AS channel_count
            FROM channel_categories c
            LEFT JOIN channels ch ON ch.category_id = c.id
            GROUP BY c.id
            ORDER BY c.sort_order ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function updateCategory($channelId, $categoryId) {
        return $this->channel->updateCategory($channelId, $categoryId);
    }

    
}

