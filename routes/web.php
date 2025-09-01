<?php
declare(strict_types=1);

// Core Controllers
use Jeffrey\Educore\Controllers\Core\HomeController;

// API Controllers
use Jeffrey\Educore\Controllers\Api\RBAC\RolePermissionApiController;
use Jeffrey\Educore\Controllers\Api\Users\UserApiController;


$r->addRoute('GET', '/', [
    'handler' => [HomeController::class, 'index']
]);


// API POST Routes
$r->addRoute('POST', '/api/role-permissions', [
    'handler' => [RolePermissionApiController::class, 'assignPermissionsToRole'],
    'middleware' => [] // Add AuthMiddleware, PermissionMiddleware later
]);


$r->addRoute('POST', '/api/users', [
    'handler' => [UserApiController::class, 'createUser'],
    'middleware' => [] // Add AuthMiddleware later
]);