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
    echo json_encode(['ok' => false, 'error' => 'method'], JSON_UNESCAPED_UNICODE);
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
        echo json_encode(['ok' => false], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $stmt = $pdo->prepare('SELECT sort_order FROM channel_categories WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $uid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo json_encode(['ok' => false], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $ok = $ctrl->update($id, $name, (int)$row['sort_order']);
    echo json_encode(['ok' => (bool)$ok], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'add') {
    $name = trim((string) ($input['name'] ?? ''));
    if ($name === '') {
        echo json_encode(['ok' => false, 'error' => 'empty'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $newId = $ctrl->addReturningId($name);
    if ($newId === false) {
        echo json_encode(['ok' => false, 'error' => 'duplicate'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    echo json_encode(['ok' => true, 'id' => $newId, 'name' => $name], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'delete') {
    $id = (int) ($input['id'] ?? 0);
    if ($id < 1) {
        echo json_encode(['ok' => false], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($ctrl->delete($id)) {
        echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
        exit;
    }
    echo json_encode(['ok' => false, 'error' => 'not_found'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'reorder') {
    $order = $input['order'] ?? null;
    if (!is_array($order)) {
        echo json_encode(['ok' => false], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $order = array_map('intval', $order);
    $order = array_values(array_filter($order, function ($x) {
        return $x > 0;
    }));
    if ($order === []) {
        echo json_encode(['ok' => false], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $i = 1;
    foreach ($order as $id) {
        $stmt = $pdo->prepare('UPDATE channel_categories SET sort_order = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$i++, $id, $uid]);
    }
    echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['ok' => false, 'error' => 'action'], JSON_UNESCAPED_UNICODE);
