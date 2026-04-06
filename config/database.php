<?php
// config/database.php

// 🔑 API 金鑰設定
define('YOUTUBE_API_KEY', 'AIzaSyBERv3BEkfKJ1Q5cFKXfRbj2SoQyUi9kpg');    // ← 替換為你的 YouTube 金鑰
define('OPENAI_API_KEY', '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');      // ← 替換為你的 OpenAI 金鑰

class Database {
    public $conn;

    public function getConnection() {
        $this->conn = null;
        $host = getenv('DB_HOST');
        if ($host === false || $host === '') {
            $host = 'localhost';
        }
        $port = getenv('DB_PORT');
        if ($port === false || $port === '') {
            $port = '3307';
        }
        $dbName = getenv('DB_NAME');
        if ($dbName === false || $dbName === '') {
            $dbName = 'youtube_tracker';
        }
        $username = getenv('DB_USER');
        if ($username === false || $username === '') {
            $username = 'root';
        }
        $password = getenv('DB_PASSWORD');
        if ($password === false) {
            $password = '0000';
        }

        // Cloud SQL（Cloud Run）：優先使用 Unix socket
        // - DB_SOCKET=/cloudsql/<INSTANCE_CONNECTION_NAME>
        // - 或 CLOUD_SQL_CONNECTION_NAME=<INSTANCE_CONNECTION_NAME>
        // - 或 DB_HOST=/cloudsql/<INSTANCE_CONNECTION_NAME>
        $socket = getenv('DB_SOCKET');
        if ($socket === false || $socket === '') {
            $cs = getenv('CLOUD_SQL_CONNECTION_NAME');
            if ($cs !== false && trim((string)$cs) !== '') {
                $socket = '/cloudsql/' . trim((string)$cs);
            }
        }
        $hostTrim = trim((string)$host);
        $socketPath = '';
        if ($socket !== false && trim((string)$socket) !== '') {
            $socketPath = trim((string)$socket);
        } elseif (strpos($hostTrim, '/cloudsql/') === 0) {
            $socketPath = $hostTrim;
        }

        // 只有在 socket 路徑真的存在時才使用 unix_socket，避免本機/未掛載 Cloud SQL 時直接噴 2002 No such file.
        // Cloud Run 若已在「Cloud SQL 連接」選了 instance，平台會掛載 /cloudsql/<INSTANCE_CONNECTION_NAME>（目錄）。
        $socketExists = ($socketPath !== '') && (file_exists($socketPath) || is_dir($socketPath));
        $wantSocket = ($socketPath !== '');

        if ($socketExists) {
            $dsn = "mysql:unix_socket={$socketPath};dbname={$dbName};charset=utf8mb4";
        } else {
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
        }

        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        );
        if (defined('PDO::ATTR_TIMEOUT')) {
            $options[PDO::ATTR_TIMEOUT] = 60;
        }

        $sslCa = getenv('DB_SSL_CA');
        if ($sslCa !== false && $sslCa !== '' && is_readable($sslCa)) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        // 遠端連線時降低「MySQL server has gone away」(2006) 機率
        if (function_exists('ini_set')) {
            @ini_set('default_socket_timeout', '120');
            @ini_set('mysqlnd.net_read_timeout', '120');
        }

        $maxAttempts = 3;
        $lastException = null;
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $this->conn = new PDO($dsn, $username, $password, $options);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $this->conn;
            } catch (PDOException $exception) {
                $lastException = $exception;
                $msg = $exception->getMessage();
                $retryable = (stripos($msg, 'gone away') !== false
                    || stripos($msg, '2006') !== false
                    || stripos($msg, 'Lost connection') !== false);
                if ($retryable && $attempt < $maxAttempts) {
                    usleep(150000 * $attempt);
                    continue;
                }
                break;
            }
        }

        // 若有設定 /cloudsql 但 socket 沒掛載，給出更直覺提示（常見於 Cloud Run 未勾選 Cloud SQL 連接、或 instance/region 不一致）
        if ($wantSocket && !$socketExists) {
            die("❌ 資料庫連線失敗: 找不到 Cloud SQL socket（{$socketPath}）。請確認 Cloud Run 服務已設定 Cloud SQL 連接，或改用 DB_HOST/DB_PORT（IP/內網）連線。");
        }
        die("❌ 資料庫連線失敗: " . ($lastException ? $lastException->getMessage() : 'unknown'));
    }
}

// ✅ 測試區（僅開發測試用）
// 可用來確認資料庫與 API 金鑰都設定正確
if (php_sapi_name() === 'cli-server' || basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    echo "<h2>🧪 系統測試中...</h2>";

    echo "<h3>✅ 資料庫連線測試：</h3>";
    $db = (new Database())->getConnection();
    echo "資料庫連線成功！<br>";

    echo "<h3>✅ YouTube API 測試：</h3>";
    $videoId = "dQw4w9WgXcQ";
    $ytUrl = "https://www.googleapis.com/youtube/v3/videos?part=snippet&id={$videoId}&key=" . YOUTUBE_API_KEY;
    $ytJson = file_get_contents($ytUrl);
    $ytData = json_decode($ytJson, true);
    if (!empty($ytData['items'][0]['snippet']['title'])) {
        echo "YouTube OK - 標題: " . htmlspecialchars($ytData['items'][0]['snippet']['title']) . "<br>";
    } else {
        echo "❌ YouTube API 金鑰錯誤或已超過配額<br>";
    }

    echo "<h3>✅ OpenAI API 測試：</h3>";
    echo "<h3>🔍 DEBUG：金鑰開頭為：" . substr(OPENAI_API_KEY, 0, 10) . "...</h3>";

    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    $data = [
        "model" => "gpt-3.5-turbo",
        "messages" => [
            ["role" => "user", "content" => "請簡單介紹一下 YouTube 是什麼。"]
        ],
        "temperature" => 0.5
    ];
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer " . OPENAI_API_KEY
        ],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);
    $response = curl_exec($ch);
    if ($response === false) {
        echo "❌ CURL 錯誤：" . curl_error($ch) . "<br>";
    } else {
        $result = json_decode($response, true);
        if (!empty($result['choices'][0]['message']['content'])) {
            echo "OpenAI OK - 回覆內容：<br><blockquote>" . nl2br(htmlspecialchars($result['choices'][0]['message']['content'])) . "</blockquote>";
        } else {
            echo "❌ OpenAI API 錯誤訊息：<br><pre>" . htmlspecialchars($response) . "</pre>";
        }
    }
    curl_close($ch);


    exit;
}
