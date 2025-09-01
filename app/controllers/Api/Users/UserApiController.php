<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Controllers\Api\Users;

use Jeffrey\Educore\Services\Users\UserService;

class UserApiController
{
    public function createUser(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['phone_number']) || empty($input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        $service = new UserService();
        $userId = $service->create($input);

        if ($userId) {
            echo json_encode([
                'status' => 'success',
                'user_id' => $userId
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'User creation failed'
            ]);
        }
    }
}