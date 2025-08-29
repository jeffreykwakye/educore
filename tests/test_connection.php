<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Jeffrey\Educore\Core\Database;
use Jeffrey\Educore\Core\AppLogger;

$logger = AppLogger::getInstance()->getLogger();

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    if ($pdo) {
        $logger->info("Connection to MySQL database was successful.");
        echo "✅ Connection to MySQL database was successful!\n";
    }

} catch (PDOException $e) {
    $logger->error("Failed to connect to MySQL database.", ['error' => $e->getMessage()]);
    echo "❌ Failed to connect to MySQL database.\n";
    echo "Error: " . $e->getMessage() . "\n";
}