<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Middleware\Auth;

use Jeffrey\Educore\Services\Auth\SessionService;
use Jeffrey\Educore\Core\RequestContext;

class AuthMiddleware
{
    public static function handle(): bool
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!$authHeader && function_exists('apache_request_headers')) {
            $apacheHeaders = apache_request_headers();
            $authHeader = $apacheHeaders['Authorization'] ?? '';
        }

        $token = null;
        if (stripos($authHeader, 'Bearer ') === 0) {
            $token = trim(substr($authHeader, 7));
        }

        if ($token) {
            $service = new SessionService();
            $userId = $service->validateToken($token);

            if ($userId !== null) {
                RequestContext::$userId = $userId;
                return true;
            }

            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired session']);
            return false;
        }

        http_response_code(401);
        echo json_encode(['error' => 'Missing Bearer token']);
        return false;
    }
}