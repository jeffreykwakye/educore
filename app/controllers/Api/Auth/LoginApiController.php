<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Controllers\Api\Auth;

use Jeffrey\Educore\Services\Auth\LoginService;

class LoginApiController
{
    public function login(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['phone_number']) || empty($input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing credentials']);
            return;
        }

        $service = new LoginService();
        $user = $service->attempt($input['phone_number'], $input['password']);

        if ($user) {
            echo json_encode([
                'status' => 'success',
                'user' => $user
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ]);
        }
    }
}