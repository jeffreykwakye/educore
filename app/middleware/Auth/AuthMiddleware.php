<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Middleware\Auth;

use Jeffrey\Educore\Services\Auth\SessionService;
use Jeffrey\Educore\Services\RBAC\RolePermissionService;
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
            // Updated: validateToken should now return full user record or null
            $user = $service->validateToken($token);

            if ($user !== null && isset($user['id'], $user['role_id'], $user['role_name'])) 
            {
                RequestContext::$userId = (int)$user['id'];
                RequestContext::$token = $token; // raw token from header
                RequestContext::$ip = $_SERVER['REMOTE_ADDR'] ?? null;
                RequestContext::$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
                RequestContext::$roleId = (int)$user['role_id'];
                RequestContext::$role = $user['role_name'];

                // Preload permissions
                $rbac = new RolePermissionService();
                RequestContext::$permissions = $rbac->getPermissionsForRole(RequestContext::$roleId);

                // Log token preview for debugging
                $logger = \Jeffrey\Educore\Core\AppLogger::getInstance()->getLogger();
                $logger->debug('AuthMiddleware populated RequestContext', [
                    'user_id' => RequestContext::$userId,
                    'token_preview' => substr(RequestContext::$token ?? '', 0, 10) . '...',
                    'role_id' => RequestContext::$roleId,
                    'role' => RequestContext::$role
                ]);

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