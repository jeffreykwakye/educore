<?php

namespace Jeffrey\Educore\Controllers\Api\RBAC;

use Jeffrey\Educore\Services\RBAC\RolePermissionService;

class RolePermissionApiController
{
    /**
     * Assigns named permissions to a role.
     *
     * Expects payload:
     * {
     *   "role_id": int,
     *   "permissions": [ "view.dashboard.master_admin", ... ]
     * }
     *
     * Note: Permissions must be passed as names, not IDs.
     */
    public function assignPermissionsToRole(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $roleId = $input['role_id'] ?? null;
        $permissions = $input['permissions'] ?? [];

        if (!$roleId || !is_array($permissions)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input']);
            return;
        }

        $service = new RolePermissionService();
        $success = $service->assign((int)$roleId, $permissions);

        echo json_encode([
            'status' => $success ? 'success' : 'error',
            'message' => $success ? 'Permissions assigned.' : 'Assignment failed.'
        ]);
    }
}