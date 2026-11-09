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

    public function open(string $path, string $name): bool
    {
        $this->ensureTable();
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $this->ensureTable();
        $stmt = $this->pdo->prepare('SELECT data FROM php_sessions WHERE id = ? LIMIT 1');
        $stmt->execute(array($id));
        $data = $stmt->fetchColumn();
        if ($data === false || $data === null) {
            return '';
        }
        return (string) $data;
    }

    public function write(string $id, string $data): bool
    {
        $this->ensureTable();
        $now = time();
        $stmt = $this->pdo->prepare(
            'INSERT INTO php_sessions (id, data, last_activity) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE data = ?, last_activity = ?'
        );
        return $stmt->execute(array($id, $data, $now, $data, $now));
    }

    public function destroy(string $id): bool
    {
        $this->ensureTable();
        $stmt = $this->pdo->prepare('DELETE FROM php_sessions WHERE id = ?');
        return $stmt->execute(array($id));
    }

    public function gc(int $max_lifetime): int|false
    {
        $this->ensureTable();
        $stmt = $this->pdo->prepare('DELETE FROM php_sessions WHERE last_activity < ?');
        if (!$stmt->execute(array(time() - $max_lifetime))) {
            return false;
        }
        return $stmt->rowCount();
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
