<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Controllers\Api\Auth;

use Jeffrey\Educore\Services\Auth\SessionService;
use Jeffrey\Educore\Core\RequestContext;

class SessionApiController
{
    private function authorize(): array
    {
        $userId = RequestContext::$userId ?? 0;
        $roleId = RequestContext::$roleId ?? 0;
        $role   = RequestContext::$role ?? null;
        $perms  = RequestContext::$permissions ?? [];

        if (!$userId || !$roleId || !$role) {
            $this->errorResponse(401, 'Unauthorized');
        }

        return [$userId, $roleId, $role, $perms];
    }

    private function errorResponse(int $code, string $message): void
    {
        http_response_code($code);
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit;
    }

    public function listActive(): void
    {
        [$userId, , , $perms] = $this->authorize();
        $service = new SessionService();

        if (in_array('view.sessions.all', $perms, true)) {
            $sessions = $service->getAllActiveSessions();
        } elseif (in_array('view.sessions.own', $perms, true)) {
            $sessions = $service->getActiveSessionsByUser($userId);
        } else {
            $this->errorResponse(403, 'Forbidden');
        }

        $safe = array_map(function ($s) {
            $hash = $s['token_hash'] ?? '';
            $s['token_preview'] = strlen($hash) >= 8
                ? substr($hash, 0, 4) . '...' . substr($hash, -4)
                : 'hidden';
            unset($s['token_hash']);
            return $s;
        }, $sessions);

        echo json_encode([
            'status' => 'success',
            'sessions' => $safe
        ]);
    }

    public function revokeOne(): void
    {
        [$userId, , , $perms] = $this->authorize();
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $sessionId = (int)($input['session_id'] ?? 0);

        if ($sessionId <= 0) {
            $this->errorResponse(400, 'session_id is required');
        }

        $service = new SessionService();
        if (in_array('revoke.sessions.all', $perms, true)) {
            $ok = $service->revokeSessionByIdGlobal($sessionId);
        } elseif (in_array('revoke.sessions.own', $perms, true)) {
            $ok = $service->revokeSessionById($sessionId, $userId);
        } else {
            $this->errorResponse(403, 'Forbidden');
        }

        echo json_encode($ok
            ? ['status' => 'success', 'message' => 'Session revoked']
            : ['status' => 'error', 'message' => 'Session not found or already revoked']
        );
    }

    public function revokeAll(): void
    {
        [$userId, , , $perms] = $this->authorize();
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true) ?? [];

        $logger = \Jeffrey\Educore\Core\AppLogger::getInstance()->getLogger();
        $logger->debug('revokeAll() called', [
            'raw_input' => $rawInput,
            'parsed_input' => $input,
            'permissions' => $perms
        ]);

        $service = new SessionService();

        if (in_array('revoke.sessions.all', $perms, true)) {
            $keepCurrent = (bool)($input['keep_current'] ?? false);
            $exceptToken = $keepCurrent ? (RequestContext::$token ?? null) : null;

            if (!empty($input['target_user_id'])) {
                if (!ctype_digit((string)$input['target_user_id'])) {
                    $this->errorResponse(400, 'Invalid target_user_id');
                }
                $count = $service->revokeAllSessions((int)$input['target_user_id'], $exceptToken);
            } else {
                if ($exceptToken) {
                    $count = $service->revokeAllSessionsGlobalExcept($exceptToken);
                } else {
                    $count = $service->revokeAllSessionsGlobal();
                }
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Sessions revoked',
                'revoked_count' => $count,
                'kept_current' => $keepCurrent
            ]);

        } elseif (in_array('revoke.sessions.own', $perms, true)) {
            // New branch for restricted users revoking their own sessions
            $keepCurrent = (bool)($input['keep_current'] ?? false);
            $exceptToken = $keepCurrent ? (RequestContext::$token ?? null) : null;
            $count = $service->revokeAllSessions($userId, $exceptToken);

            echo json_encode([
                'status' => 'success',
                'message' => 'Own sessions revoked',
                'revoked_count' => $count,
                'kept_current' => $keepCurrent
            ]);

        } else {
            $this->errorResponse(403, 'Forbidden');
        }
    }
}