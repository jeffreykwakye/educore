<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Middleware\RBAC;

use Jeffrey\Educore\Core\Database;

class PermissionMiddleware
{
    public static function handle(string $requiredPermission): bool
    {
        $headers = getallheaders();
        $userId = $headers['X-User-ID'] ?? null;

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return false;
        }

        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT p.name FROM permissions p
            JOIN role_permissions rp ON rp.permission_id = p.id
            JOIN user_roles ur ON ur.role_id = rp.role_id
            WHERE ur.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);
        $permissions = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        if (!in_array($requiredPermission, $permissions, true)) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return false;
        }

        return true;
    }
}