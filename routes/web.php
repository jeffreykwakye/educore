<?php
declare(strict_types=1);

// Core Controllers
use Jeffrey\Educore\Controllers\Core\HomeController;

// API Controllers
use Jeffrey\Educore\Controllers\Api\RBAC\RolePermissionApiController;
use Jeffrey\Educore\Controllers\Api\Users\UserApiController;
use Jeffrey\Educore\Controllers\Api\Auth\LoginApiController;
use Jeffrey\Educore\Controllers\Api\Auth\LogoutApiController;
use Jeffrey\Educore\Controllers\Api\Dashboard\DashboardApiController;
use Jeffrey\Educore\Controllers\Api\Auth\SessionApiController;


// Middleware
use Jeffrey\Educore\Middleware\Auth\AuthMiddleware;
use Jeffrey\Educore\Middleware\RBAC\PermissionMiddleware;


$r->addRoute('GET', '/', [
    'handler' => [HomeController::class, 'index']
]);


$r->addRoute('GET', '/api/dashboard', [
    'handler' => [DashboardApiController::class, 'routeDashboard'],
    'middleware' => [
        [
            'class' => AuthMiddleware::class,
            'method' => 'handle',
            'args' => []
        ]
    ]
]);


$r->addRoute('GET', '/api/sessions', [
    'handler' => [SessionApiController::class, 'listActive'],
    'middleware' => [
        ['class' => AuthMiddleware::class, 'method' => 'handle', 'args' => []],
    ],
]);

$r->addRoute('POST', '/api/sessions/revoke', [
    'handler' => [SessionApiController::class, 'revokeOne'],
    'middleware' => [
        ['class' => AuthMiddleware::class, 'method' => 'handle', 'args' => []],
    ],
]);

$r->addRoute('POST', '/api/sessions/revoke-all', [
    'handler' => [SessionApiController::class, 'revokeAll'],
    'middleware' => [
        ['class' => AuthMiddleware::class, 'method' => 'handle', 'args' => []],
    ],
]);






// API POST Routes
$r->addRoute('POST', '/api/role-permissions', [
    'handler' => [RolePermissionApiController::class, 'assignPermissionsToRole'],
    'middleware' => [] // Add AuthMiddleware, PermissionMiddleware later
]);


$r->addRoute('POST', '/api/users', [
    'handler' => [UserApiController::class, 'createUser'],
    'middleware' => [
        [
            'class' => AuthMiddleware::class,
            'method' => 'handle',
            'args' => []
        ],
        [
            'class' => PermissionMiddleware::class,
            'method' => 'handle',
            'args' => ['user.create']
        ]
    ]
]);

$r->addRoute('POST', '/api/login', [
    'handler' => [LoginApiController::class, 'login'],
    'middleware' => [] // Add rate limiting or lockout logic later
]);

$r->addRoute('POST', '/api/logout', [
    'handler' => [LogoutApiController::class, 'logout'],
    'middleware' => [
        [
            'class' => AuthMiddleware::class,
            'method' => 'handle',
            'args' => []
        ]
    ]
]);





