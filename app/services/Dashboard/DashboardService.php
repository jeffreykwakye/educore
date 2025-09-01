<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Services\Dashboard;

use Jeffrey\Educore\Core\Database;

class DashboardService
{
    public function getDashboardType(int $userId): ?string
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT p.name FROM permissions p
            JOIN role_permissions rp ON rp.permission_id = p.id
            JOIN user_roles ur ON ur.role_id = rp.role_id
            WHERE ur.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);
        $permissions = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($permissions as $perm) {
            if (str_starts_with($perm, 'view.dashboard.')) {
                return substr($perm, strlen('view.dashboard.')); // returns 'master_admin', 'teacher', etc.
            }
        }


        return null;
    }
}