<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Services\Auth;

use Jeffrey\Educore\Core\Database;
use Jeffrey\Educore\Core\AppLogger;

class SessionService
{
    private int $ttlSeconds;

    
    public function __construct(int $ttlSeconds = 604800) // 7 days
    {
        $this->ttlSeconds = $ttlSeconds;
    }

    
    public function createSession(int $userId, ?string $userAgent, ?string $ip): array
    {
        $db = Database::getInstance()->getConnection();
        $logger = AppLogger::getInstance()->getLogger();

        $rawToken = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = (new \DateTimeImmutable("+{$this->ttlSeconds} seconds"))->format('Y-m-d H:i:s');

        $stmt = $db->prepare("INSERT INTO sessions 
            (user_id, token_hash, user_agent, ip, expires_at)
            VALUES (:user_id, :token_hash, :user_agent, :ip, :expires_at)"
        );
        
        $ok = $stmt->execute([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'user_agent' => $userAgent,
            'ip' => $ip,
            'expires_at' => $expiresAt,
        ]);

        if (!$ok) {
            $logger->error('Failed to create session', ['user_id' => $userId]);
            throw new \RuntimeException('Failed to create session');
        }

        return [
            'token' => $rawToken,
            'expires_at' => $expiresAt,
        ];
    }


    public function validateToken(string $rawToken): ?array
    {
        if ($rawToken === '') {
            return null;
        }

        $db = Database::getInstance()->getConnection();
        $tokenHash = hash('sha256', $rawToken);

        $stmt = $db->prepare("
            SELECT 
                u.id AS id,
                ur.role_id,
                r.name AS role_name,
                s.revoked_at,
                s.expires_at
            FROM sessions s
            INNER JOIN users u 
                ON s.user_id = u.id
            INNER JOIN user_roles ur 
                ON u.id = ur.user_id
            INNER JOIN roles r 
                ON ur.role_id = r.id
            WHERE s.token_hash = :token_hash
            LIMIT 1
        ");
        $stmt->execute(['token_hash' => $tokenHash]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        if ($row['revoked_at'] !== null) {
            return null;
        }

        if (new \DateTimeImmutable($row['expires_at']) < new \DateTimeImmutable('now')) {
            return null;
        }

        // Return full context for AuthMiddleware
        return [
            'id'        => (int)$row['id'],
            'role_id'   => (int)$row['role_id'],
            'role_name' => $row['role_name']
        ];
    }



    
    public function revokeToken(string $rawToken): bool
    {
        $db = Database::getInstance()->getConnection();
        $tokenHash = hash('sha256', $rawToken);

        $stmt = $db->prepare(
            "UPDATE sessions
            SET revoked_at = NOW()
            WHERE token_hash = :token_hash 
            AND revoked_at IS NULL"
        );
        return $stmt->execute(['token_hash' => $tokenHash]);
    }


    public function getActiveSessionsByUser(int $userId): array
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare(
            "SELECT id, token_hash, user_agent, ip, created_at, expires_at, revoked_at
            FROM sessions
            WHERE user_id = :uid
              AND revoked_at IS NULL
              AND expires_at > NOW()
            ORDER BY created_at DESC"
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }


    public function getAllActiveSessions(): array
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->query(
            "SELECT id, user_id, token_hash, user_agent, ip, created_at, expires_at, revoked_at
            FROM sessions
            WHERE revoked_at IS NULL
            AND expires_at > NOW()
            ORDER BY created_at DESC"
        );
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }



    public function getCurrentSession(int $userId, string $token): array
    {
        $db = Database::getInstance()->getConnection();
        $tokenHash = hash('sha256', $token);

        $stmt = $db->prepare(
            "SELECT 
                `id`, 
                `token_hash`, 
                `user_agent`, 
                `ip`, 
                `created_at`, 
                `expires_at`, 
                `revoked_at`
            FROM `sessions`
            WHERE 
                `user_id` = :uid
                AND `token_hash` = :hash
                AND `revoked_at` IS NULL
                AND `expires_at` > NOW()
            LIMIT 1"
        );
        $stmt->execute(['uid' => $userId, 'hash' => $tokenHash]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }


    public function revokeSessionByIdGlobal(int $sessionId): bool
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare(
            "UPDATE `sessions`
            SET `revoked_at` = NOW()
            WHERE `id` = :sid
            AND `revoked_at` IS NULL"
        );
        $stmt->execute(['sid' => $sessionId]);
        return $stmt->rowCount() > 0;
    }

    
    public function revokeSessionById(int $sessionId, int $userId): bool
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare(
            "UPDATE `sessions`
            SET `revoked_at` = NOW()
            WHERE `id` = :sid
              AND `user_id` = :uid
              AND `revoked_at` IS NULL"
        );
        
        $stmt->execute(['sid' => $sessionId, 'uid' => $userId]);
        return $stmt->rowCount() > 0;
    }


    public function revokeAllSessions(int $userId, ?string $exceptToken = null): int
    {
        $db = Database::getInstance()->getConnection();
        $logger = \Jeffrey\Educore\Core\AppLogger::getInstance()->getLogger();

        // Always log what we received
        $logger->debug('RevokeAllSessions called', [
            'user_id' => $userId,
            'except_token_present' => $exceptToken !== null,
            'except_token_preview' => $exceptToken ? substr($exceptToken, 0, 10) . '...' : null
        ]);

        if ($exceptToken) {
            $exceptHash = hash('sha256', $exceptToken);
            $logger->debug('RevokeAllSessions keep_current hash', [
                'except_hash' => $exceptHash
            ]);

            $stmt = $db->prepare("
                UPDATE `sessions`
                SET `revoked_at` = NOW()
                WHERE `user_id` = :uid
                AND `revoked_at` IS NULL
                AND `expires_at` > NOW()
                AND `token_hash` != :hash
            ");
            $stmt->execute(['uid' => $userId, 'hash' => $exceptHash]);
        } else {
            $stmt = $db->prepare("
                UPDATE `sessions`
                SET `revoked_at` = NOW()
                WHERE `user_id` = :uid
                AND `revoked_at` IS NULL
                AND `expires_at` > NOW()
            ");
            $stmt->execute(['uid' => $userId]);
        }

        $affected = $stmt->rowCount();
        $logger->debug('RevokeAllSessions affected rows', [
            'user_id' => $userId,
            'affected_rows' => $affected
        ]);

        return $affected;
    }



    public function revokeAllSessionsGlobal(): int
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare(
            "UPDATE `sessions`
            SET `revoked_at` = NOW()
            WHERE `revoked_at` IS NULL
            AND `expires_at` > NOW();"
        );
        $stmt->execute();
        return $stmt->rowCount();
    }


    public function revokeAllSessionsGlobalExcept(?string $exceptToken = null): int
    {
        $db = Database::getInstance()->getConnection();

        if ($exceptToken) {
            $exceptHash = hash('sha256', $exceptToken);
            $stmt = $db->prepare("
                UPDATE sessions
                SET revoked_at = NOW()
                WHERE revoked_at IS NULL
                AND expires_at > NOW()
                AND token_hash != :hash
            ");
            $stmt->execute(['hash' => $exceptHash]);
        } else {
            $stmt = $db->prepare("
                UPDATE sessions
                SET revoked_at = NOW()
                WHERE revoked_at IS NULL
                AND expires_at > NOW()
            ");
            $stmt->execute();
        }

        return $stmt->rowCount();
    }

    


}