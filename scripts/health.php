<?php
/**
 * Cloud Run 健康檢查（不開 session）
 *   GET /scripts/health.php      → 行程活著
 *   GET /scripts/health.php?db=1 → ping Cloud SQL
 */
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

$checkDb = isset($_GET['db']) && (string) $_GET['db'] !== '' && (string) $_GET['db'] !== '0';
if (!$checkDb) {
    echo "ok\n";
    exit;
}

require_once __DIR__ . '/../config/database.php';

function health_diag_line($label, $value)
{
    echo $label . ': ' . $value . "\n";
}

echo "K_SERVICE=" . app_env('K_SERVICE') . "\n";
echo "CLOUD_SQL_CONNECTION_NAME=" . app_env('CLOUD_SQL_CONNECTION_NAME') . "\n";
echo "DB_NAME=" . app_env('DB_NAME') . "\n";
echo "DB_USER=" . app_env('DB_USER') . "\n";
echo "DB_HOST=" . app_env('DB_HOST') . "\n";
echo "cloudsql_isdir=" . (is_dir('/cloudsql') ? 'yes' : 'no') . "\n";
if (is_dir('/cloudsql')) {
    $entries = @scandir('/cloudsql');
    echo "cloudsql_list=" . (is_array($entries) ? implode(',', $entries) : '(unreadable)') . "\n";
}
$cs = trim(app_env('CLOUD_SQL_CONNECTION_NAME'));
$sock = $cs !== '' ? ('/cloudsql/' . $cs) : '';
if ($sock !== '') {
    echo "socket_path=" . $sock . "\n";
    echo "socket_exists=" . (file_exists($sock) ? 'yes' : 'no') . "\n";
    echo "socket_isdir=" . (is_dir($sock) ? 'yes' : 'no') . "\n";
    echo "socket_filetype=" . (@filetype($sock) ?: 'unknown') . "\n";
}

try {
    $pdo = (new Database())->getConnection(false);
    $pdo->query('SELECT 1');
    echo "ok db\n";
} catch (Throwable $e) {
    http_response_code(503);
    echo "db fail: " . $e->getMessage() . "\n";
    exit(1);
}
