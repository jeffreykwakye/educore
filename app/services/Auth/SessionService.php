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

        $stmt = $db->prepare("
            INSERT INTO sessions (user_id, token_hash, user_agent, ip, expires_at)
            VALUES (:user_id, :token_hash, :user_agent, :ip, :expires_at)
        ");
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

    public function validateToken(string $rawToken): ?int
    {
        if ($rawToken === '') {
            return null;
        }

        $db = Database::getInstance()->getConnection();
        $tokenHash = hash('sha256', $rawToken);

        $stmt = $db->prepare("
            SELECT user_id, revoked_at, expires_at
            FROM sessions
            WHERE token_hash = :token_hash
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

        return (int)$row['user_id'];
    }

    public function revokeToken(string $rawToken): bool
    {
        $db = Database::getInstance()->getConnection();
        $tokenHash = hash('sha256', $rawToken);

        $stmt = $db->prepare("
            UPDATE sessions
            SET revoked_at = NOW()
            WHERE token_hash = :token_hash AND revoked_at IS NULL
        ");
        return $stmt->execute(['token_hash' => $tokenHash]);
    }
}