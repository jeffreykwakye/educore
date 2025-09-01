<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Middleware\Auth;

class AuthMiddleware
{
    public static function handle(): bool
    {
        // For now, we assume user context is passed via header or session
        // Later, replace with token/session validation
        $headers = getallheaders();
        if (!isset($headers['X-User-ID'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return false;
        }

        return true;
    }
}