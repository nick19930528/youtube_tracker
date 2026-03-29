<?php
class CategoryController {
    private $pdo;
    private $userId;

    public function __construct($pdo, int $userId) {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("
            SELECT cc.*, COUNT(c.id) AS channel_count
            FROM channel_categories cc
            LEFT JOIN channels c ON cc.id = c.category_id AND c.user_id = cc.user_id
            WHERE cc.user_id = ?
            GROUP BY cc.id
            ORDER BY cc.sort_order ASC, cc.name ASC
        ");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add($name) {
        $stmt = $this->pdo->prepare("INSERT INTO channel_categories (user_id, name) VALUES (?, ?)");
        return $stmt->execute([$this->userId, $name]);
    }

    public function update($id, $name, $sortOrder) {
        $stmt = $this->pdo->prepare("UPDATE channel_categories SET name = ?, sort_order = ? WHERE id = ? AND user_id = ?");
        return $stmt->execute([$name, $sortOrder, $id, $this->userId]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM channels WHERE category_id = ? AND user_id = ?");
        $stmt->execute([$id, $this->userId]);
        if ($stmt->fetchColumn() > 0) {
            return false;
        }

        $stmt = $this->pdo->prepare("DELETE FROM channel_categories WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $this->userId]);
    }
}
