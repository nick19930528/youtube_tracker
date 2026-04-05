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

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
            $this->conn = new PDO($dsn, $username, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            die("❌ 資料庫連線失敗: " . $exception->getMessage());
        }
        return $this->conn;
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
