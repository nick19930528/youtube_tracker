<?php
class CategoryController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->query("
            SELECT cc.*, COUNT(c.id) AS channel_count
            FROM channel_categories cc
            LEFT JOIN channels c ON cc.id = c.category_id
            GROUP BY cc.id
            ORDER BY cc.sort_order ASC, cc.name ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }




    public function add($name) {
        $stmt = $this->pdo->prepare("INSERT INTO channel_categories (name) VALUES (?)");
        return $stmt->execute([$name]);
    }

    public function update($id, $name, $sortOrder) {
        $stmt = $this->pdo->prepare("UPDATE channel_categories SET name = ?, sort_order = ? WHERE id = ?");
        return $stmt->execute([$name, $sortOrder, $id]);
    }

    public function delete($id) {
        // 先檢查是否有頻道使用這個分類
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM channels WHERE category_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            return false;
        }

        $stmt = $this->pdo->prepare("DELETE FROM channel_categories WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
