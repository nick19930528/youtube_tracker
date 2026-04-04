<?php
/**
 * Dashboard 頻道卡片：切換最愛、刪除頻道
 */
require_once __DIR__ . '/../config/bootstrap.php';
auth_require_login();
require_once __DIR__ . '/../controllers/ChannelController.php';

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
$id = (int)($input['channel_id'] ?? 0);
if ($id < 1) {
    echo json_encode(['ok' => false, 'error' => 'id']);
    exit;
}

$pdo = (new Database())->getConnection();
$uid = auth_user_id();
$controller = new ChannelController($pdo, $uid);

if ($action === 'toggle_favorite') {
    if (!$controller->toggleFavorite($id)) {
        echo json_encode(['ok' => false]);
        exit;
    }
    $stmt = $pdo->prepare('SELECT is_favorite FROM channels WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $uid]);
    $isFav = (int)$stmt->fetchColumn();
    echo json_encode(['ok' => true, 'is_favorite' => $isFav]);
    exit;
}

if ($action === 'delete_channel') {
    $ok = $controller->delete($id);
    echo json_encode(['ok' => (bool)$ok]);
    exit;
}

if ($action === 'update_category') {
    $rawCat = $input['category_id'] ?? null;
    if ($rawCat === '' || $rawCat === null) {
        $newCatId = null;
    } else {
        $newCatId = (int) $rawCat;
        if ($newCatId < 1) {
            echo json_encode(['ok' => false, 'error' => 'category'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $stmt = $pdo->prepare('SELECT id FROM channel_categories WHERE id = ? AND user_id = ?');
        $stmt->execute([$newCatId, $uid]);
        if ($stmt->fetchColumn() === false) {
            echo json_encode(['ok' => false, 'error' => 'category'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    if (!$controller->updateCategory($id, $newCatId)) {
        echo json_encode(['ok' => false], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $catName = null;
    if ($newCatId !== null) {
        $stmt = $pdo->prepare('SELECT name FROM channel_categories WHERE id = ? AND user_id = ?');
        $stmt->execute([$newCatId, $uid]);
        $catName = $stmt->fetchColumn();
    }
    echo json_encode([
        'ok' => true,
        'category_id' => $newCatId,
        'category_name' => ($catName !== false && $catName !== null && $catName !== '') ? $catName : null,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['ok' => false, 'error' => 'action']);
