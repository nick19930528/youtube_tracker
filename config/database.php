<?php
// config/database.php
require_once __DIR__ . '/env.php';

// API 金鑰：優先環境變數（Cloud Run Secret），未設定時沿用本機既有值
app_define_from_env(
    'YOUTUBE_API_KEY',
    'YOUTUBE_API_KEY',
    'AIzaSyBERv3BEkfKJ1Q5cFKXfRbj2SoQyUi9kpg'
);
app_define_from_env(
    'OPENAI_API_KEY',
    'OPENAI_API_KEY',
    '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'
);

/**
 * 解析 Cloud SQL Unix socket。
 * Unix socket 不是一般檔案（is_file() 會是 false），FUSE 掛載有時看起來像目錄。
 */
function database_resolve_socket_path($socketPath)
{
    $socketPath = rtrim((string) $socketPath, '/');
    if ($socketPath === '') {
        return '';
    }
    $candidates = array($socketPath, $socketPath . '/mysqld.sock');
    foreach ($candidates as $p) {
        if (file_exists($p) && !is_dir($p)) {
            return $p;
        }
    }
    if (is_dir($socketPath)) {
        $entries = @scandir($socketPath);
        if (is_array($entries)) {
            foreach ($entries as $name) {
                if ($name === '.' || $name === '..') {
                    continue;
                }
                $p = $socketPath . '/' . $name;
                if (file_exists($p) && !is_dir($p)) {
                    return $p;
                }
            }
        }
    }
    // Cloud Run：即使當下掃不到檔案，仍把 /cloudsql/<連線名稱> 交給 PDO
    return $socketPath;
}

/** Cloud Run 已掛載 Cloud SQL 且未填連線名稱時，若只有一個 instance 就自動使用 */
function database_autodetect_cloudsql_socket()
{
    if (!is_dir('/cloudsql')) {
        return '';
    }
    $found = array();
    $entries = @scandir('/cloudsql');
    if (!is_array($entries)) {
        return '';
    }
    foreach ($entries as $name) {
        if ($name === '.' || $name === '..') {
            continue;
        }
        $found[] = '/cloudsql/' . $name;
    }
    if (count($found) === 1) {
        return $found[0];
    }
    return '';
}

class Database {
    public $conn;

    /**
     * @param bool $dieOnError true=維持舊行為直接結束；false=丟例外（健康檢查用）
     * @return PDO
     */
    public function getConnection($dieOnError = true) {
        $this->conn = null;
        $cloudRun = app_is_cloud_run();

        $host = app_env('DB_HOST', $cloudRun ? '127.0.0.1' : 'localhost');
        $port = app_env('DB_PORT', $cloudRun ? '3306' : '3307');
        $dbName = app_env('DB_NAME', 'youtube_tracker');
        $username = app_env('DB_USER', $cloudRun ? '' : 'root');
        $passwordEnv = getenv('DB_PASSWORD');
        if ($passwordEnv === false) {
            $password = $cloudRun ? '' : '0000';
        } else {
            $password = (string) $passwordEnv;
        }

        if ($cloudRun && ($username === '' || $dbName === '')) {
            $msg = 'Cloud Run 請設定環境變數 DB_USER、DB_PASSWORD、DB_NAME，並在服務加上 Cloud SQL 連線。';
            if ($dieOnError) {
                die('❌ 資料庫連線失敗: ' . $msg);
            }
            throw new PDOException($msg);
        }

        // Cloud SQL（Cloud Run）：優先 Unix socket
        // - DB_SOCKET=/cloudsql/<INSTANCE_CONNECTION_NAME>
        // - CLOUD_SQL_CONNECTION_NAME=<INSTANCE_CONNECTION_NAME>
        // - DB_HOST=/cloudsql/<INSTANCE_CONNECTION_NAME>
        // - 或 Cloud Run 只掛了一個 instance 時自動偵測 /cloudsql/*
        $socket = app_env('DB_SOCKET');
        if ($socket === '') {
            $cs = trim(app_env('CLOUD_SQL_CONNECTION_NAME'));
            if ($cs !== '') {
                $socket = (strpos($cs, '/cloudsql/') === 0) ? $cs : ('/cloudsql/' . $cs);
            }
        }
        $hostTrim = trim((string) $host);
        $configuredSocket = '';
        if ($socket !== '') {
            $configuredSocket = $socket;
        } elseif (strpos($hostTrim, '/cloudsql/') === 0) {
            $configuredSocket = $hostTrim;
        } elseif ($cloudRun) {
            $configuredSocket = database_autodetect_cloudsql_socket();
        }

        $wantSocket = ($configuredSocket !== '');
        $socketPath = $wantSocket ? database_resolve_socket_path($configuredSocket) : '';
        if ($wantSocket && $socketPath === '') {
            $socketPath = $configuredSocket;
        }

        $explicitHost = app_env('DB_HOST');
        $useTcp = ($explicitHost !== '' && strpos($explicitHost, '/cloudsql/') !== 0);
        $useSocket = $wantSocket && (!$cloudRun || !$useTcp);

        if ($cloudRun && !$useSocket && !$useTcp) {
            $hint = $configuredSocket !== ''
                ? $configuredSocket
                : '/cloudsql/<PROJECT>:<REGION>:<INSTANCE>';
            $fail = 'Cloud Run 找不到 Cloud SQL socket（' . $hint . '）。請在服務「連線」勾選 Cloud SQL 執行個體，並設定 CLOUD_SQL_CONNECTION_NAME。';
            if ($dieOnError) {
                die('❌ 資料庫連線失敗: ' . $fail);
            }
            throw new PDOException($fail);
        }

        if ($useSocket) {
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

        $sslCa = app_env('DB_SSL_CA');
        if ($sslCa !== '' && is_readable($sslCa)) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

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

        $raw = $lastException ? $lastException->getMessage() : 'unknown';
        if ($useSocket && (stripos($raw, '2002') !== false || stripos($raw, 'refused') !== false || stripos($raw, 'No such file') !== false)) {
            $fail = $raw . '（socket=' . $socketPath . '）。請確認：1) Cloud Run 已連接 apps-db-dev；2) IAM 幫 Cloud Run 服務帳號加上「Cloud SQL 用戶端」roles/cloudsql.client。';
        } elseif ($wantSocket && !$useSocket) {
            $fail = '找不到 Cloud SQL socket（' . $configuredSocket . '）。請在 Cloud Run 服務「連線」裡勾選該 Cloud SQL 執行個體，或設定 CLOUD_SQL_CONNECTION_NAME。';
        } else {
            $fail = $raw;
        }
        if ($dieOnError) {
            die('❌ 資料庫連線失敗: ' . $fail);
        }
        throw new PDOException($fail, 0, $lastException);
    }
}

// 僅 APP_DEBUG=1 時允許直接開此檔做連線／金鑰測試（正式環境請勿開啟）
$scriptName = isset($_SERVER['SCRIPT_FILENAME']) ? basename((string) $_SERVER['SCRIPT_FILENAME']) : '';
if (app_env('APP_DEBUG') === '1' && $scriptName !== '' && basename(__FILE__) === $scriptName) {
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
    echo "<h3>🔍 DEBUG：金鑰開頭為：" . htmlspecialchars(substr(OPENAI_API_KEY, 0, 10)) . "...</h3>";

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
