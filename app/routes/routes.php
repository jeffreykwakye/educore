<?php
declare(strict_types=1);


use Jeffrey\Educore\Controllers\Core\HomeController;
use Jeffrey\Educore\Controllers\Schools\SchoolController;
use Jeffrey\Educore\Controllers\Auth\AuthController;
use Jeffrey\Educore\Controllers\Core\DashboardController;
use Jeffrey\Educore\Controllers\Auth\UserController;

// Middleware
use Jeffrey\Educore\Middleware\School\SchoolValidationMiddleware;
use Jeffrey\Educore\Middleware\User\LoginValidationMiddleware;
use Jeffrey\Educore\Middleware\User\AuthMiddleware;
use Jeffrey\Educore\Middleware\User\AutoLogoutMiddleware;
use Jeffrey\Educore\Middleware\User\Admin\MasterAdminMiddleware;

use Jeffrey\Educore\Models\RoleModel;
use Jeffrey\Educore\Models\SchoolModel;


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



// API routes for Master Admin
$r->addRoute('GET', '/api/roles', [
    'handler' => function() {
        $roleModel = new RoleModel();
        header('Content-Type: application/json');
        echo json_encode($roleModel->getAllRoles());
    },
    'middleware' => [AuthMiddleware::class, MasterAdminMiddleware::class]
]);

$r->addRoute('GET', '/api/schools', [
    'handler' => function() {
        $schoolModel = new SchoolModel();
        header('Content-Type: application/json');
        echo json_encode($schoolModel->getAllSchools());
    },
    'middleware' => [AuthMiddleware::class, MasterAdminMiddleware::class]
]);

$r->addRoute('POST', '/api/users', [
    'handler' => [UserController::class, 'processUserCreation'],
    'middleware' => [AuthMiddleware::class, MasterAdminMiddleware::class]
]);
