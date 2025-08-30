<?php

require_once __DIR__ . '/../../vendor/autoload.php';


use Jeffrey\Educore\Core\Database;
use Jeffrey\Educore\Core\AppLogger;

// Define the root directory of the application
define('APP_ROOT', dirname(__DIR__, 2));

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
$dotenv->load();

$logger = AppLogger::getInstance()->getLogger();

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $logger->info("Executing database setup script.");

    // Read the SQL commands from the external file
    $sqlFilePath = __DIR__ . '/../../database/schema.sql';
    $sql = file_get_contents($sqlFilePath);

    // Check if the SQL file was read successfully
    if ($sql === false) {
        throw new Exception("Unable to read schema.sql file at {$sqlFilePath}");
    }

    $pdo->exec($sql);
    $logger->info("Tables `schools` and `school_branding` created successfully.");
    echo "Database tables created successfully!\n";

} catch (PDOException $e) {
    $logger->error("Database setup failed.", ['error' => $e->getMessage()]);
    echo "Database setup failed.\n";
    echo "Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    $logger->error("File error: " . $e->getMessage());
    echo "File error: " . $e->getMessage() . "\n";
}