<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Controllers\Api\Dashboard;

use Jeffrey\Educore\Services\Dashboard\DashboardService;
use Jeffrey\Educore\Services\Dashboard\DashboardViewService;


class DashboardApiController
{
    public function routeDashboard(): void
    {
        $headers = getallheaders();
        $userId = (int)($headers['X-User-ID'] ?? 0);

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $service = new DashboardService();
        $type = $service->getDashboardType($userId);

        if ($type) {

            $viewService = new DashboardViewService();

            switch ($type) {
                case 'master_admin':
                    $view = $viewService->getMasterAdminView($userId);
                    break;
                default:
                    http_response_code(403);
                    echo json_encode(['error' => 'Dashboard type not supported']);
                    return;
            }

            echo json_encode([
                'status' => 'success',
                'dashboard' => $type,
                'data' => $view
            ]);

        } else {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'No dashboard access'
            ]);
        }
    }
}