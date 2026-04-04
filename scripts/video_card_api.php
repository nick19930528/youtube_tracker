<?php
/**
 * 首頁影片卡片：從清單刪除
 */
require_once __DIR__ . '/../config/bootstrap.php';
auth_require_login();
require_once __DIR__ . '/../controllers/VideoController.php';

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
$id = (int)($input['video_id'] ?? 0);
if ($id < 1) {
    echo json_encode(['ok' => false, 'error' => 'id']);
    exit;
}

$pdo = (new Database())->getConnection();
$uid = auth_user_id();
$controller = new VideoController($pdo, $uid);

if ($action === 'delete_video') {
    $ok = $controller->delete($id);
    echo json_encode(['ok' => (bool)$ok]);
    exit;
}

echo json_encode(['ok' => false, 'error' => 'action']);
