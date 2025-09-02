<?php 

declare(strict_types=1);

namespace Jeffrey\Educore\Controllers\Api\Auth;

use Jeffrey\Educore\Services\Auth\LoginService;
use Jeffrey\Educore\Services\Auth\SessionService;

class LoginApiController
{
    public function login(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $phone = $input['phone_number'] ?? null;
        $password = $input['password'] ?? null;

        if (!$phone || !$password) {
            http_response_code(400);
            echo json_encode(['error' => 'Phone number and password are required']);
            return;
        }

        $loginService = new LoginService();
        $user = $loginService->attempt($phone, $password);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

        $sessionService = new SessionService();
        $session = $sessionService->createSession(
            $user['user_id'],
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? null
        );

        echo json_encode([
            'status' => 'success',
            'token' => $session['token'],
            'expires_at' => $session['expires_at'],
            'user' => $user
        ]);
    }
}