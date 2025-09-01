<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Jeffrey\Educore\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    echo "✅ Database connection successful.";
} catch (Exception $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}