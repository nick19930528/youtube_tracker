<?php
/**
 * 首頁影片：單筆刪除、一次刪除全部未看
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

$pdo = (new Database())->getConnection();
$uid = auth_user_id();
$controller = new VideoController($pdo, $uid);

if ($action === 'delete_all_unwatched') {
    $n = $controller->deleteAllUnwatched();
    echo json_encode(['ok' => true, 'deleted' => $n], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action !== 'delete_video') {
    echo json_encode(['ok' => false, 'error' => 'action'], JSON_UNESCAPED_UNICODE);
    exit;
}

$id = (int)($input['video_id'] ?? 0);
if ($id < 1) {
    echo json_encode(['ok' => false, 'error' => 'id'], JSON_UNESCAPED_UNICODE);
    exit;
}

$ok = $controller->delete($id);
echo json_encode(['ok' => (bool)$ok], JSON_UNESCAPED_UNICODE);
