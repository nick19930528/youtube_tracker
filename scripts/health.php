<?php
/**
 * Cloud Run 健康檢查（不開 session）
 *   GET /scripts/health.php      → 行程活著
 *   GET /scripts/health.php?db=1 → 再 ping Cloud SQL
 */
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

$checkDb = isset($_GET['db']) && (string) $_GET['db'] !== '' && (string) $_GET['db'] !== '0';
if (!$checkDb) {
    echo "ok\n";
    exit;
}

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = (new Database())->getConnection(false);
    $pdo->query('SELECT 1');
    echo "ok db\n";
} catch (Throwable $e) {
    http_response_code(503);
    echo "db fail\n";
    exit(1);
}
