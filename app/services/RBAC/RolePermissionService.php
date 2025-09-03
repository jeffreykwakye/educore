<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Services\RBAC;

use Jeffrey\Educore\Core\Database;
use Jeffrey\Educore\Core\AppLogger;

/**
 * Service for assigning permissions to a role.
 */
class RolePermissionService
{
    /**
     * Assign the given permission names to the role.
     * - Clears existing links for the role.
     * - Inserts only permissions that exist by name.
     * 
     * Skips unknown names silently  — consider logging or throwing in future.
     *
     * @param int   $roleId
     * @param array $permissionNames e.g. ["user.create","user.read"]
     * @return bool True on successful assignment (at least one insert), false otherwise.
     */
    public function assign(int $roleId, array $permissionNames): bool
    {
        $db = Database::getInstance()->getConnection();
        $logger = AppLogger::getInstance()->getLogger();

        try {
            if (empty($permissionNames)) {
                $logger->warning("No permissions provided for role_id {$roleId}");
                return false;
            }

            // Build a map: permission name => id
            $stmt = $db->query("SELECT `name`, `id` FROM permissions");
            $permissionMap = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

            if (empty($permissionMap)) {
                $logger->warning("Permissions table returned no rows; cannot assign for role_id {$roleId}");
                return false;
            }

            $logger->info("Assigning permissions to role_id {$roleId}");
            $logger->info("Incoming permissions: " . implode(', ', $permissionNames));

            $db->beginTransaction();

            // Clear existing links
            $deleteStmt = $db->prepare("DELETE FROM role_permissions WHERE role_id = :role_id");
            $deleteStmt->execute(['role_id' => $roleId]);

            // Insert new links
            $insertStmt = $db->prepare(
                "INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)"
            );

            $insertCount = 0;

            foreach ($permissionNames as $name) {
                if (isset($permissionMap[$name])) {
                    $insertStmt->execute([
                        'role_id' => $roleId,
                        'permission_id' => $permissionMap[$name],
                    ]);
                    $insertCount++;
                } else {
                    $logger->warning("Unknown permission name '{$name}' skipped for role_id {$roleId}");
                }
            }

            $db->commit();

            if ($insertCount === 0) {
                $logger->warning("No valid permissions were assigned to role_id {$roleId}");
                return false;
            }

            $logger->info("Assigned {$insertCount} permissions to role_id {$roleId}");
            return true;
        } catch (\PDOException $e) {
            if (method_exists($db, 'inTransaction') && $db->inTransaction()) {
                $db->rollBack();
            }
            $logger->error("Permission assignment failed for role_id {$roleId}: " . $e->getMessage());
            return false;
        }
    }



    public function roleHasPermission(int $roleId, string $permissionName): bool
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM role_permissions rp
            INNER JOIN permissions p ON rp.permission_id = p.id
            WHERE rp.role_id = :role_id
            AND p.name = :perm_name
            LIMIT 1
        ");
        $stmt->execute([
            'role_id' => $roleId,
            'perm_name' => $permissionName
        ]);

        return (bool)$stmt->fetchColumn();
    }


    public function getPermissionsForRole(int $roleId): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT p.name
            FROM role_permissions rp
            INNER JOIN permissions p ON rp.permission_id = p.id
            WHERE rp.role_id = :role_id
        ");
        
        $stmt->execute(['role_id' => $roleId]);
        return array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'name');
    }

}