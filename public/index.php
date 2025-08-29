<?php
declare(strict_types=1);

use Jeffrey\Educore\Core\AppLogger;
use Jeffrey\Educore\Core\Database;
use Jeffrey\Educore\Core\Router;


// Start logging system
$logger = AppLogger::getInstance()->getLogger();
$logger->info("Application started.");

try {
    Database::getInstance()->getConnection();
    $logger->info("Database connection established.");
} catch (\PDOException $e) {
    $logger->error("Database connection failed: " . $e->getMessage());
    echo "<h1>Maintenance Mode</h1><p>The application is currently unavailable. Please try again later.</p>";
    exit;
}

// Instantiate our new Router class, pointing to the routes.php file
$router = new Router(__DIR__ . '/../app/core/routes/routes.php');

// Fetch method and URI from the request
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Dispatch the request
$router->dispatch($httpMethod, $uri);

$logger->info("Request dispatched. Application finished.");