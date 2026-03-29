<?php
/**
 * Dashboard 頻道卡片：切換最愛、刪除頻道
 */
require_once __DIR__ . '/../config/database.php';
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
$controller = new ChannelController($pdo);

if ($action === 'toggle_favorite') {
    if (!$controller->toggleFavorite($id)) {
        echo json_encode(['ok' => false]);
        exit;
    }
    $stmt = $pdo->prepare('SELECT is_favorite FROM channels WHERE id = ?');
    $stmt->execute([$id]);
    $isFav = (int)$stmt->fetchColumn();
    echo json_encode(['ok' => true, 'is_favorite' => $isFav]);
    exit;
}

if ($action === 'delete_channel') {
    $ok = $controller->delete($id);
    echo json_encode(['ok' => (bool)$ok]);
    exit;
}

echo json_encode(['ok' => false, 'error' => 'action']);
