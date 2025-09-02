<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Controllers\Api\Auth;

use Jeffrey\Educore\Services\Auth\SessionService;

class LogoutApiController
{
    public function logout(): void
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

        if (!$token) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing Bearer token']);
            return;
        }

        $service = new SessionService();
        $revoked = $service->revokeToken($token);

        if ($revoked) {
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Session revoked']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Failed to revoke session or token already revoked']);
        }
    }
}