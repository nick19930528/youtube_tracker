<?php
/**
 * 將 PHP session 存在 MySQL（Cloud Run 多實例／臨時磁碟無法共用檔案 session）
 */
class DbSessionHandler implements SessionHandlerInterface
{
    /** @var PDO */
    private $pdo;

    /** @var bool */
    private $tableReady = false;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function open($savePath, $sessionName)
    {
        $this->ensureTable();
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        $this->ensureTable();
        $stmt = $this->pdo->prepare('SELECT data FROM php_sessions WHERE id = ? LIMIT 1');
        $stmt->execute(array((string) $id));
        $data = $stmt->fetchColumn();
        if ($data === false || $data === null) {
            return '';
        }
        return (string) $data;
    }

    public function write($id, $data)
    {
        $this->ensureTable();
        $now = time();
        $stmt = $this->pdo->prepare(
            'INSERT INTO php_sessions (id, data, last_activity) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE data = ?, last_activity = ?'
        );
        return $stmt->execute(array((string) $id, $data, $now, $data, $now));
    }

    public function destroy($id)
    {
        $this->ensureTable();
        $stmt = $this->pdo->prepare('DELETE FROM php_sessions WHERE id = ?');
        return $stmt->execute(array((string) $id));
    }

    public function gc($maxlifetime)
    {
        $this->ensureTable();
        $stmt = $this->pdo->prepare('DELETE FROM php_sessions WHERE last_activity < ?');
        return $stmt->execute(array(time() - (int) $maxlifetime));
    }

    private function ensureTable()
    {
        if ($this->tableReady) {
            return;
        }
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS php_sessions (
                id VARCHAR(128) NOT NULL PRIMARY KEY,
                data MEDIUMBLOB NOT NULL,
                last_activity INT UNSIGNED NOT NULL,
                INDEX idx_php_sessions_last_activity (last_activity)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
        $this->tableReady = true;
    }
}
