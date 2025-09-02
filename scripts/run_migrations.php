<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Jeffrey\Educore\Core\Database;

$db = Database::getInstance()->getConnection();
$migrationsDir = __DIR__ . '/../database/migrations';

$files = glob($migrationsDir . '/*.sql');
sort($files);

foreach ($files as $file) {
    echo "Running migration: " . basename($file) . PHP_EOL;
    $sql = file_get_contents($file);

    try {
        $db->exec($sql);
        echo "Success\n";
    } catch (PDOException $e) {
        echo "Failed: " . $e->getMessage() . "\n";
    }
}