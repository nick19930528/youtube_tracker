<?php
/**
 * Google Cloud Storage：Cloud Run 用 metadata token；本機可設 GCS_KEY_FILE（選用）。
 * 未設定 GCS_BUCKET 時由 UserAvatar 改存 uploads/。
 */
require_once __DIR__ . '/../config/env.php';

class GcsClient
{
    /** @var string */
    private $bucket;

    public function __construct($bucket)
    {
        $this->bucket = trim((string) $bucket);
    }

    public static function fromEnv()
    {
        $bucket = app_env('GCS_BUCKET');
        if ($bucket === '') {
            return null;
        }
        return new self($bucket);
    }

    public function bucket()
    {
        return $this->bucket;
    }

    /**
     * @return string 公開讀取網址
     */
    public function publicUrl($objectName)
    {
        $base = rtrim(app_env('GCS_PUBLIC_BASE'), '/');
        if ($base === '') {
            $base = 'https://storage.googleapis.com/' . rawurlencode($this->bucket);
        }
        $parts = explode('/', str_replace('\\', '/', (string) $objectName));
        $enc = array();
        foreach ($parts as $p) {
            if ($p === '') {
                continue;
            }
            $enc[] = rawurlencode($p);
        }
        return $base . '/' . implode('/', $enc);
    }

    /**
     * @return true|string
     */
    public function upload($objectName, $bytes, $contentType)
    {
        $token = $this->accessToken();
        if ($token === '') {
            return '無法取得 Cloud Storage 授權（請確認 Cloud Run 服務帳號有 Storage 權限）。';
        }
        $url = 'https://storage.googleapis.com/upload/storage/v1/b/'
            . rawurlencode($this->bucket)
            . '/o?uploadType=media&name=' . rawurlencode($objectName);
        $res = $this->httpJson('POST', $url, $bytes, $token, $contentType);
        if ($res['ok']) {
            return true;
        }
        return '上傳 Cloud Storage 失敗：' . $res['error'];
    }

    /**
     * @return bool
     */
    public function delete($objectName)
    {
        $objectName = trim((string) $objectName);
        if ($objectName === '') {
            return true;
        }
        $token = $this->accessToken();
        if ($token === '') {
            return false;
        }
        $url = 'https://storage.googleapis.com/storage/v1/b/'
            . rawurlencode($this->bucket)
            . '/o/' . rawurlencode($objectName);
        $res = $this->httpJson('DELETE', $url, null, $token, null);
        return $res['ok'] || (isset($res['code']) && (int) $res['code'] === 404);
    }

    private function accessToken()
    {
        static $cached = null;
        if (is_string($cached)) {
            return $cached;
        }
        $ctx = stream_context_create(array(
            'http' => array(
                'method' => 'GET',
                'header' => "Metadata-Flavor: Google\r\n",
                'timeout' => 3,
                'ignore_errors' => true,
            ),
        ));
        $raw = @file_get_contents(
            'http://metadata.google.internal/computeMetadata/v1/instance/service-accounts/default/token',
            false,
            $ctx
        );
        if ($raw === false || $raw === '') {
            $cached = '';
            return $cached;
        }
        $j = json_decode($raw, true);
        $cached = (is_array($j) && !empty($j['access_token'])) ? (string) $j['access_token'] : '';
        return $cached;
    }

    /**
     * @return array{ok:bool,error:string,code:int}
     */
    private function httpJson($method, $url, $body, $token, $contentType)
    {
        $headers = array(
            'Authorization: Bearer ' . $token,
        );
        if ($contentType !== null && $contentType !== '') {
            $headers[] = 'Content-Type: ' . $contentType;
        }
        $ch = curl_init($url);
        $opts = array(
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 60,
        );
        if ($body !== null) {
            $opts[CURLOPT_POSTFIELDS] = $body;
        }
        curl_setopt_array($ch, $opts);
        $out = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $cerr = curl_error($ch);
        curl_close($ch);
        if ($out === false) {
            return array('ok' => false, 'error' => $cerr !== '' ? $cerr : 'curl failed', 'code' => $code);
        }
        if ($code >= 200 && $code < 300) {
            return array('ok' => true, 'error' => '', 'code' => $code);
        }
        $msg = $cerr;
        $j = json_decode((string) $out, true);
        if (is_array($j) && !empty($j['error']['message'])) {
            $msg = (string) $j['error']['message'];
        } elseif ($msg === '') {
            $msg = 'HTTP ' . $code;
        }
        return array('ok' => false, 'error' => $msg, 'code' => $code);
    }
}
