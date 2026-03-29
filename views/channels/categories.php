<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/CategoryController.php';

$pdo = (new Database())->getConnection();
$controller = new CategoryController($pdo);

// 表單處理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $controller->add(trim($_POST['new_name']));
    } elseif (isset($_POST['update'])) {
        $controller->update($_POST['id'], trim($_POST['name']), (int)$_POST['sort_order']);
    } elseif (isset($_POST['delete'])) {
        $success = $controller->delete($_POST['id']);
        if (!$success) {
            echo "<p style='color:red;'>⚠️ 此分類已被使用，無法刪除。</p>";
        }
    }
}

$categories = $controller->getAll();
?>

<h2>📂 頻道分類管理</h2>
<form method="get" action="index.php" style="margin: 10px 0;">
    <input type="hidden" name="page" value="channels">
    <button type="submit">🔙 返回頻道清單</button>
</form>

<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>分類名稱</th>
            <th>排序</th>
            <th>頻道數</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $cat): ?>
            <tr>
                <form method="post">
                    <td>
                        <input type="text" name="name" value="<?= htmlspecialchars($cat['name']) ?>">
                    </td>
                    <td>
                        <input type="number" name="sort_order" value="<?= $cat['sort_order'] ?>" style="width:60px;">
                    </td>
                    <td><?= $cat['channel_count'] ?></td>
                    <td>
                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                        <button type="submit" name="update">💾 儲存</button>
                        <button type="submit" name="delete" onclick="return confirm('確定要刪除？')">🗑️ 刪除</button>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h3>➕ 新增分類</h3>
<form method="post">
    <input type="text" name="new_name" placeholder="輸入新分類名稱" required>
    <button type="submit" name="add">新增</button>
</form>
