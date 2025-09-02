<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Controllers\Api\Auth;

use Jeffrey\Educore\Core\Database;
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


        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT id, password_hash FROM users WHERE phone_number = :phone LIMIT 1");
        $stmt->execute(['phone' => $phone]);

        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

        $service = new SessionService();
        $session = $service->createSession(
            (int)$user['id'],
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? null
        );

        echo json_encode([
            'status' => 'success',
            'token' => $session['token'],
            'expires_at' => $session['expires_at']
        ]);
    }
}