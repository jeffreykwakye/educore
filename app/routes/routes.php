<?php
declare(strict_types=1);

use Jeffrey\Educore\Controllers\HomeController;
use Jeffrey\Educore\Controllers\SchoolController;
use Jeffrey\Educore\Controllers\UserController;
use Jeffrey\Educore\Controllers\AuthController;
use Jeffrey\Educore\Controllers\DashboardController;
use Jeffrey\Educore\Middleware\School\SchoolValidationMiddleware;
use Jeffrey\Educore\Middleware\User\LoginValidationMiddleware;
use Jeffrey\Educore\Middleware\User\AuthMiddleware;


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



// Protected routes
$r->addRoute('GET', '/dashboard', [
    'handler' => [DashboardController::class, 'showDashboard'],
    'middleware' => [AuthMiddleware::class] // Protect this route
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
