<?php
/**
 * 會員大頭照：驗證、縮圖、上傳 GCS（Cloud Run）或本機 uploads/（開發）
 */
require_once __DIR__ . '/GcsClient.php';
require_once __DIR__ . '/../config/env.php';

class UserAvatar
{
    const MAX_BYTES = 2097152;
    const MAX_EDGE = 512;

    /**
     * @return bool 欄位可用才為 true（先確認再上傳，避免桶裡有檔、資料庫沒網址）
     */
    public static function ensureColumns(PDO $pdo)
    {
        static $ok = null;
        if ($ok === true) {
            return true;
        }
        try {
            $pdo->query('SELECT avatar_url, avatar_object FROM users LIMIT 1');
            $ok = true;
            return true;
        } catch (Throwable $e) {
        }
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN avatar_url varchar(512) NULL DEFAULT NULL");
        } catch (Throwable $e) {
        }
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN avatar_object varchar(255) NULL DEFAULT NULL");
        } catch (Throwable $e) {
        }
        try {
            $pdo->query('SELECT avatar_url, avatar_object FROM users LIMIT 1');
            $ok = true;
            return true;
        } catch (Throwable $e) {
            $ok = false;
            return false;
        }
    }

    /**
     * @param array $file $_FILES['avatar']
     * @return true|string
     */
    public static function saveFromUpload(PDO $pdo, $userId, array $file)
    {
        $userId = (int) $userId;
        if ($userId < 1) {
            return '無法辨識會員。';
        }
        $err = isset($file['error']) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;
        if ($err === UPLOAD_ERR_NO_FILE || $err === UPLOAD_ERR_NO_TMP_DIR) {
            return '請選擇要上傳的圖片。';
        }
        if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
            return '檔案太大（上限 2MB）。';
        }
        if ($err !== UPLOAD_ERR_OK) {
            return '上傳失敗，請再試一次。';
        }
        $size = isset($file['size']) ? (int) $file['size'] : 0;
        if ($size < 32 || $size > self::MAX_BYTES) {
            return '圖片需介於合理大小，且不可超過 2MB。';
        }
        $tmp = isset($file['tmp_name']) ? (string) $file['tmp_name'] : '';
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return '無效的上傳檔案。';
        }

        $processed = self::processImage($tmp);
        if (is_string($processed)) {
            return $processed;
        }

        if (!self::ensureColumns($pdo)) {
            return '資料庫還沒有大頭照欄位。請先在 Cloud SQL 執行 config/migrations/015_users_avatar.sql（avatar_url、avatar_object）。';
        }
        $old = self::currentObject($pdo, $userId);

        $object = 'avatars/' . $userId . '/' . bin2hex(random_bytes(8)) . '.' . $processed['ext'];
        $stored = self::store($object, $processed['bytes'], $processed['mime']);
        if (is_string($stored)) {
            return $stored;
        }
        $publicUrl = $stored['url'];

        try {
            $stmt = $pdo->prepare('UPDATE users SET avatar_url = ?, avatar_object = ? WHERE id = ?');
            $stmt->execute(array($publicUrl, $object, $userId));
            $check = $pdo->prepare('SELECT avatar_url FROM users WHERE id = ? LIMIT 1');
            $check->execute(array($userId));
            $saved = (string) $check->fetchColumn();
            if ($saved === '') {
                throw new PDOException('avatar_url 寫入後仍為空白');
            }
        } catch (Throwable $e) {
            self::removeStored($object);
            return '照片已傳到雲端，但無法寫入資料庫網址：' . $e->getMessage();
        }

        $_SESSION['user_avatar_url'] = $publicUrl;

        if ($old !== '' && $old !== $object) {
            self::removeStored($old);
        }
        return true;
    }

    /**
     * @return true|string
     */
    public static function clear(PDO $pdo, $userId)
    {
        $userId = (int) $userId;
        self::ensureColumns($pdo);
        $old = self::currentObject($pdo, $userId);
        $stmt = $pdo->prepare('UPDATE users SET avatar_url = NULL, avatar_object = NULL WHERE id = ?');
        $stmt->execute(array($userId));
        $_SESSION['user_avatar_url'] = '';
        if ($old !== '') {
            self::removeStored($old);
        }
        return true;
    }

    public static function urlFromSession()
    {
        if (empty($_SESSION['user_avatar_url'])) {
            return '';
        }
        return (string) $_SESSION['user_avatar_url'];
    }

    private static function currentObject(PDO $pdo, $userId)
    {
        try {
            $stmt = $pdo->prepare('SELECT avatar_object FROM users WHERE id = ? LIMIT 1');
            $stmt->execute(array($userId));
            $v = $stmt->fetchColumn();
            return $v ? (string) $v : '';
        } catch (Throwable $e) {
            return '';
        }
    }

    /**
     * @return array{bytes:string,mime:string,ext:string}|string
     */
    private static function processImage($tmpPath)
    {
        $info = @getimagesize($tmpPath);
        if (!is_array($info) || empty($info['mime'])) {
            return '請選擇圖片檔（JPG、PNG、WebP 或 GIF）。';
        }
        $map = array(
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        );
        $mime = (string) $info['mime'];
        if (!isset($map[$mime])) {
            return '僅支援 JPG、PNG、WebP、GIF。';
        }

        if (!function_exists('imagecreatetruecolor')) {
            $bytes = file_get_contents($tmpPath);
            if ($bytes === false || $bytes === '') {
                return '讀取圖片失敗。';
            }
            return array('bytes' => $bytes, 'mime' => $mime, 'ext' => $map[$mime]);
        }

        $src = self::gdLoad($tmpPath, $mime);
        if (!$src) {
            return '無法處理此圖片，請換一張再試。';
        }
        $w = imagesx($src);
        $h = imagesy($src);
        if ($w < 1 || $h < 1) {
            imagedestroy($src);
            return '圖片尺寸無效。';
        }
        $edge = max($w, $h);
        $scale = ($edge > self::MAX_EDGE) ? (self::MAX_EDGE / $edge) : 1.0;
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));
        $dst = imagecreatetruecolor($nw, $nh);
        if (!$dst) {
            imagedestroy($src);
            return '無法縮放圖片。';
        }
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefilledrectangle($dst, 0, 0, $nw, $nh, $white);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($src);

        ob_start();
        imagejpeg($dst, null, 85);
        $bytes = ob_get_clean();
        imagedestroy($dst);
        if ($bytes === false || $bytes === '') {
            return '轉存圖片失敗。';
        }
        return array('bytes' => $bytes, 'mime' => 'image/jpeg', 'ext' => 'jpg');
    }

    private static function gdLoad($path, $mime)
    {
        if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
            return @imagecreatefromjpeg($path);
        }
        if ($mime === 'image/png' && function_exists('imagecreatefrompng')) {
            return @imagecreatefrompng($path);
        }
        if ($mime === 'image/gif' && function_exists('imagecreatefromgif')) {
            return @imagecreatefromgif($path);
        }
        if ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
            return @imagecreatefromwebp($path);
        }
        return false;
    }

    /**
     * @return array{url:string}|string 成功為 ['url'=>公開網址]，失敗為錯誤字串
     */
    private static function store($objectName, $bytes, $mime)
    {
        $gcs = GcsClient::fromEnv();
        if ($gcs) {
            $r = $gcs->upload($objectName, $bytes, $mime);
            if ($r !== true) {
                return $r;
            }
            return array('url' => $gcs->publicUrl($objectName));
        }
        if (app_is_cloud_run()) {
            return '尚未設定 GCS_BUCKET，無法在 Cloud Run 儲存大頭照。請建立 Cloud Storage 儲存桶並填入環境變數。';
        }
        $rel = 'uploads/' . str_replace('\\', '/', $objectName);
        $absDir = dirname(__DIR__) . '/' . dirname($rel);
        if (!is_dir($absDir) && !@mkdir($absDir, 0755, true) && !is_dir($absDir)) {
            return '無法建立本機上傳目錄。';
        }
        $abs = dirname(__DIR__) . '/' . $rel;
        if (file_put_contents($abs, $bytes) === false) {
            return '寫入本機檔案失敗。';
        }
        return array('url' => $rel);
    }

    private static function removeStored($objectName)
    {
        $objectName = str_replace('\\', '/', (string) $objectName);
        if ($objectName === '' || strpos($objectName, 'avatars/') !== 0) {
            return;
        }
        $gcs = GcsClient::fromEnv();
        if ($gcs) {
            $gcs->delete($objectName);
            return;
        }
        $abs = dirname(__DIR__) . '/uploads/' . $objectName;
        if (is_file($abs)) {
            @unlink($abs);
        }
    }
}
