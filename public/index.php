<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Jeffrey\Educore\Core\Router;

$router = new Router(__DIR__ . '/../routes/web.php');
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);