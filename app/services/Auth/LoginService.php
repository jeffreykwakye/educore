<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Services\Auth;

use Jeffrey\Educore\Core\Database;
use Jeffrey\Educore\Core\AppLogger;

class LoginService
{
    public function attempt(string $phoneNumber, string $password): ?array
    {
        $db = Database::getInstance()->getConnection();
        $logger = AppLogger::getInstance()->getLogger();

        try {
            $stmt = $db->prepare("SELECT * FROM users WHERE phone_number = :phone LIMIT 1");
            $stmt->execute(['phone' => $phoneNumber]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            $success = false;

            if ($user && password_verify($password, $user['password_hash'])) {
                $success = true;
            }

            // Log the attempt
            $logStmt = $db->prepare("
                INSERT INTO login_attempts (user_id, ip_address, was_successful)
                VALUES (:user_id, :ip, :success)
            ");
            $logStmt->execute([
                'user_id' => $user['id'] ?? 0,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'success' => $success ? 1 : 0
            ]);

            if ($success) {
                
                if (!empty($user['id'])) {
                    $updateStmt = $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id");
                    $updateStmt->execute(['id' => $user['id']]);
                }

                return [
                    'user_id' => (int)$user['id'],
                    'school_id' => (int)$user['school_id'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'phone_number' => $user['phone_number']
                ];
            }

            return null;
        } catch (\PDOException $e) {
            $logger->error("Login failed for {$phoneNumber}: " . $e->getMessage());
            return null;
        }
    }
}