<?php

namespace Jeffrey\Educore\Core;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    private function __construct()
    {
        $configPath = dirname(__DIR__, 2) . '/.env';

        $config = parse_ini_file($configPath);

        if (!$config) {
            AppLogger::getInstance()->getLogger()->error("Failed to load .env file at $configPath");
            die("Environment configuration error.");
        }


        $dsn = "mysql:host={$config['DB_HOST']};port={$config['DB_PORT']};dbname={$config['DB_NAME']};charset=utf8mb4";

        try {
            $this->connection = new PDO($dsn, $config['DB_USER'], $config['DB_PASS'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            AppLogger::getInstance()->getLogger()->error("Database connection failed: " . $e->getMessage());
            die("Database connection error.");
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}