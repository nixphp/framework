<?php

namespace PHPico\Core;

class Database
{

    public function __construct(array $config)
    {
        $dsn = sprintf(
            '%s:host=%s;dbname=%s;charset=%s',
            $config['driver'] ?? 'mysql',
            $config['host'],
            $config['database'],
            $config['charset'] ?? 'utf8mb4'
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->pdo;
    }

}