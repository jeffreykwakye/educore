<?php

namespace Jeffrey\Educore\Models;

use Jeffrey\Educore\Core\Model;

class LoginAttemptModel extends Model
{
    private $table = 'login_attempts';

    /**
     * Creates a new login attempt record.
     *
     * @param array $data Associative array of data for the new record.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        return $this->insert($this->table, $data);
    }

    /**
     * Counts failed login attempts for a user within a given timeframe.
     *
     * @param int $userId The ID of the user.
     * @param int $timeframe The timeframe in seconds (e.g., 15 * 60 for 15 minutes).
     * @return int The number of failed attempts.
     */
    public function countFailedAttempts(int $userId, int $timeframe): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}
                WHERE user_id = :user_id
                AND was_successful = 0
                AND attempt_at >= (NOW() - INTERVAL :timeframe SECOND)";
        
        $stmt = $this->query($sql, [
            'user_id' => $userId, 
            'timeframe' => $timeframe
        ]);

        return (int)$stmt->fetchColumn();
    }
}