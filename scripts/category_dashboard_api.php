<?php
/**
 * Dashboard 分類標籤：重新命名、拖曳排序（sort_order）
 */
require_once __DIR__ . '/../config/bootstrap.php';
auth_require_login();
require_once __DIR__ . '/../controllers/CategoryController.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method']);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
    $input = $_POST;
}

$action = $input['action'] ?? '';
$pdo = (new Database())->getConnection();
$uid = auth_user_id();
$ctrl = new CategoryController($pdo, $uid);

if ($action === 'rename') {
    $id = (int)($input['id'] ?? 0);
    $name = trim((string)($input['name'] ?? ''));
    if ($id < 1 || $name === '') {
        echo json_encode(['ok' => false]);
        exit;
    }
    $stmt = $pdo->prepare('SELECT sort_order FROM channel_categories WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $uid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo json_encode(['ok' => false]);
        exit;
    }
    $ok = $ctrl->update($id, $name, (int)$row['sort_order']);
    echo json_encode(['ok' => (bool)$ok]);
    exit;
}

if ($action === 'reorder') {
    $order = $input['order'] ?? null;
    if (!is_array($order)) {
        echo json_encode(['ok' => false]);
        exit;
    }
    $order = array_map('intval', $order);
    $order = array_values(array_filter($order, function ($x) {
        return $x > 0;
    }));
    if ($order === []) {
        echo json_encode(['ok' => false]);
        exit;
    }
    $i = 1;
    foreach ($order as $id) {
        $stmt = $pdo->prepare('UPDATE channel_categories SET sort_order = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$i++, $id, $uid]);
    }
    echo json_encode(['ok' => true]);
    exit;
}

echo json_encode(['ok' => false, 'error' => 'action']);
