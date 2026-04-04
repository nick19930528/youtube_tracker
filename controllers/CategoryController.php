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
        return $this->addReturningId($name) !== false;
    }

    /**
     * @return int|false 新分類 id，失敗（名稱空白或重複等）回傳 false
     */
    public function addReturningId($name) {
        $name = trim((string) $name);
        if ($name === '') {
            return false;
        }
        $stmt = $this->pdo->prepare('SELECT IFNULL(MAX(sort_order), 0) FROM channel_categories WHERE user_id = ?');
        $stmt->execute([$this->userId]);
        $next = (int) $stmt->fetchColumn() + 1;
        $stmt = $this->pdo->prepare('INSERT INTO channel_categories (user_id, name, sort_order) VALUES (?, ?, ?)');
        if (!$stmt->execute([$this->userId, $name, $next])) {
            return false;
        }
        return (int) $this->pdo->lastInsertId();
    }

    public function update($id, $name, $sortOrder) {
        $stmt = $this->pdo->prepare("UPDATE channel_categories SET name = ?, sort_order = ? WHERE id = ? AND user_id = ?");
        return $stmt->execute([$name, $sortOrder, $id, $this->userId]);
    }

    public function delete($id) {
        $id = (int) $id;
        if ($id < 1) {
            return false;
        }
        $stmt = $this->pdo->prepare('UPDATE channels SET category_id = NULL WHERE category_id = ? AND user_id = ?');
        $stmt->execute([$id, $this->userId]);
        $stmt = $this->pdo->prepare('DELETE FROM channel_categories WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $this->userId]);
        return $stmt->rowCount() > 0;
    }
}
