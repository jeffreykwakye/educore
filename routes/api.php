<?php
declare(strict_types=1);

use Jeffrey\Educore\Controllers\Api\Auth\SessionApiController;
use Jeffrey\Educore\Middleware\Auth\AuthMiddleware;

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