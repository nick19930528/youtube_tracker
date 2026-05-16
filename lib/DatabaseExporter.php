<?php

/**
 * 以 PDO 將 MySQL 資料庫匯出為 .sql（結構 + 資料）
 */
class DatabaseExporter
{
    /** @var PDO */
    private $pdo;

    /** @var string */
    private $dbName;

    public function __construct(PDO $pdo, $dbName)
    {
        $this->pdo = $pdo;
        $this->dbName = (string) $dbName;
    }

    /**
     * @param resource $out
     */
    public function stream($out)
    {
        if (!is_resource($out)) {
            throw new InvalidArgumentException('stream resource required');
        }

        $this->writeLine($out, '-- TubeLog database export');
        $this->writeLine($out, '-- Database: ' . $this->dbName);
        $this->writeLine($out, '-- Generated: ' . date('c'));
        $this->writeLine($out, '');
        $this->writeLine($out, 'SET NAMES utf8mb4;');
        $this->writeLine($out, 'SET FOREIGN_KEY_CHECKS=0;');
        $this->writeLine($out, 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";');
        $this->writeLine($out, 'SET time_zone = "+00:00";');
        $this->writeLine($out, '');

        foreach ($this->listTables() as $table) {
            $this->exportTable($out, $table);
        }

        $this->writeLine($out, 'SET FOREIGN_KEY_CHECKS=1;');
        $this->writeLine($out, '');
    }

    /**
     * @return string[]
     */
    private function listTables()
    {
        $stmt = $this->pdo->query('SHOW TABLES');
        $tables = array();
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            if (!empty($row[0])) {
                $tables[] = (string) $row[0];
            }
        }

        return $tables;
    }

    /**
     * @param resource $out
     * @param string   $table
     */
    private function exportTable($out, $table)
    {
        $qTable = $this->quoteIdentifier($table);

        $this->writeLine($out, '--');
        $this->writeLine($out, '-- Table structure for ' . $table);
        $this->writeLine($out, '--');
        $this->writeLine($out, 'DROP TABLE IF EXISTS ' . $qTable . ';');

        $createStmt = $this->pdo->query('SHOW CREATE TABLE ' . $qTable);
        $createRow = $createStmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($createRow['Create Table'])) {
            $this->writeLine($out, $createRow['Create Table'] . ';');
        }
        $this->writeLine($out, '');

        $this->writeLine($out, '--');
        $this->writeLine($out, '-- Dumping data for table ' . $table);
        $this->writeLine($out, '--');

        $select = $this->pdo->query('SELECT * FROM ' . $qTable);
        $colCount = $select->columnCount();
        if ($colCount === 0) {
            $this->writeLine($out, '');

            return;
        }

        $columns = array();
        for ($i = 0; $i < $colCount; $i++) {
            $meta = $select->getColumnMeta($i);
            $name = isset($meta['name']) ? (string) $meta['name'] : ('col' . $i);
            $columns[] = $this->quoteIdentifier($name);
        }
        $colList = implode(', ', $columns);

        while ($row = $select->fetch(PDO::FETCH_ASSOC)) {
            $values = array();
            foreach ($row as $value) {
                if ($value === null) {
                    $values[] = 'NULL';
                } else {
                    $values[] = $this->pdo->quote($value);
                }
            }
            $this->writeLine(
                $out,
                'INSERT INTO ' . $qTable . ' (' . $colList . ') VALUES (' . implode(', ', $values) . ');'
            );
        }

        $this->writeLine($out, '');
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function quoteIdentifier($name)
    {
        return '`' . str_replace('`', '``', $name) . '`';
    }

    /**
     * @param resource $out
     * @param string   $line
     */
    private function writeLine($out, $line)
    {
        fwrite($out, $line . "\n");
    }
}
