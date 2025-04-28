<?php

namespace PHPico\Core;

use PDO;
use PDOException;
use PHPico\Exceptions\DatabaseException;


class Database
{
    protected PDO $pdo;

    public function __construct(array $config, ?callable $pdoFactory = null)
    {
        $dsn      = $this->buildDsn($config);
        $options  = $this->pdoOptions();
        $username = $config['username'] ?? null;
        $password = $config['password'] ?? null;

        try {
            $pdoFactory ??= fn() => new PDO($dsn, $username, $password, $options);
            $this->pdo = $pdoFactory();
        } catch (PDOException $e) {
            throw new DatabaseException('Database connection failed: ' . $e->getMessage());
        }
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Erzeugt den DSN je nach Treiber.
     */
    protected function buildDsn(array $config): string
    {
        $driver = $config['driver'] ?? 'mysql';

        if ($driver === 'sqlite') {
            return $this->buildSqliteDsn($config);
        }

        return sprintf(
            '%s:host=%s;dbname=%s;charset=%s',
            $driver,
            $config['host'] ?? '127.0.0.1',
            $config['database'] ?? '',
            $config['charset'] ?? 'utf8mb4'
        );
    }

    /**
     * DSN für SQLite, unterstützt Memory & Datei.
     */
    protected function buildSqliteDsn(array $config): string
    {
        $database = $config['database'] ?? ':memory:';

        return $database === ':memory:'
            ? 'sqlite::memory:'
            : 'sqlite:' . $database;
    }

    /**
     * Standardmäßige PDO-Options.
     */
    private function pdoOptions(): array
    {
        return [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
    }
}
