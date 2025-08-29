<?php
declare(strict_types=1);

use Jeffrey\Educore\Controllers\HomeController;
use Jeffrey\Educore\Controllers\SchoolController;
use Jeffrey\Educore\Controllers\UserController;
use Jeffrey\Educore\Controllers\AuthController;
use Jeffrey\Educore\Middleware\School\SchoolValidationMiddleware;
use Jeffrey\Educore\Middleware\User\LoginValidationMiddleware;

// Public routes
$r->addRoute('GET', '/', [
    'handler' => [HomeController::class, 'index']
]);

$r->addRoute('GET', '/register', [
    'handler' => [SchoolController::class, 'showRegistrationForm']
]);

$r->addRoute('GET', '/login', [
    'handler' => [AuthController::class, 'showLoginForm']
]);

// API routes
$r->addRoute('POST', '/register', [
    'handler' => [SchoolController::class, 'processRegistration'],
    'middleware' => [SchoolValidationMiddleware::class]
]);

$r->addRoute('POST', '/login', [
    'handler' => [AuthController::class, 'processLogin'],
    'middleware' => [LoginValidationMiddleware::class]
]);

$r->addRoute('POST', '/logout', [
    'handler' => [AuthController::class, 'logout'],
]);