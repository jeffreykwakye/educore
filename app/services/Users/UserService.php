<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Services\Users;

use Jeffrey\Educore\Core\Database;
use Jeffrey\Educore\Core\AppLogger;

class UserService
{
    public function create(array $data): ?int
    {
        $db = Database::getInstance()->getConnection();
        $logger = AppLogger::getInstance()->getLogger();

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("
                INSERT INTO users (phone_number, password_hash, first_name, last_name, other_names, school_id)
                VALUES (:phone_number, :password_hash, :first_name, :last_name, :other_names, :school_id)
            ");

            $stmt->execute([
                'phone_number'   => $data['phone_number'],
                'password_hash'  => password_hash($data['password'], PASSWORD_DEFAULT),
                'first_name'     => $data['first_name'] ?? null,
                'last_name'      => $data['last_name'] ?? null,
                'other_names'    => $data['other_names'] ?? null,
                'school_id'      => $data['school_id'] ?? null,
            ]);

            $userId = (int)$db->lastInsertId();

            if (!empty($data['role_id'])) {
                $roleStmt = $db->prepare("
                    INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)
                ");
                $roleStmt->execute([
                    'user_id' => $userId,
                    'role_id' => $data['role_id'],
                ]);
            }

            $db->commit();
            $logger->info("Created user {$userId} with role_id {$data['role_id']}");
            return $userId;
        } catch (\PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $logger->error("User creation failed: " . $e->getMessage());
            return null;
        }
    }
}