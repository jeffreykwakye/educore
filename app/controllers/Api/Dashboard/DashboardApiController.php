<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Controllers\Api\Dashboard;

use Jeffrey\Educore\Services\Dashboard\DashboardService;

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
            echo json_encode([
                'status' => 'success',
                'dashboard' => $type
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