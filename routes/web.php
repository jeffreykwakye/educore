<?php
declare(strict_types=1);

// Core Controllers
use Jeffrey\Educore\Controllers\Core\HomeController;

// API Controllers
use Jeffrey\Educore\Controllers\Api\RBAC\RolePermissionApiController;


$r->addRoute('GET', '/', [
    'handler' => [HomeController::class, 'index']
]);


// API POST Routes
$r->addRoute('POST', '/api/role-permissions', [
    'handler' => [RolePermissionApiController::class, 'assignPermissionsToRole'],
    'middleware' => [] // Add AuthMiddleware, PermissionMiddleware later
]);