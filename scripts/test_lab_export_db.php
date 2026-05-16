<?php

auth_require_test_lab();

require_once __DIR__ . '/../lib/DatabaseExporter.php';

$dbName = getenv('DB_NAME');
if ($dbName === false || $dbName === '') {
    $dbName = 'youtube_tracker';
}

$safeDb = preg_replace('/[^a-zA-Z0-9_]/', '_', $dbName);
$filename = 'tubelog_' . $safeDb . '_' . date('Ymd_His') . '.sql';

header('Content-Type: application/sql; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

if (function_exists('set_time_limit')) {
    @set_time_limit(0);
}

$exporter = new DatabaseExporter($pdo, $dbName);
$exporter->stream(fopen('php://output', 'w'));
exit;
