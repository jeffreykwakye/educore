<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Core;

/**
 * Holds per-request context for the authenticated user.
 * Populated by AuthMiddleware after token validation.
 * Cleared at the start of each request via reset().
 */
class RequestContext
{
    /** @var int|null Authenticated user's ID */
    public static ?int $userId = null;

    /** @var string|null Raw Bearer token from the request */
    public static ?string $token = null;

    /** @var string|null IP address of the request origin */
    public static ?string $ip = null;

    /** @var string|null User-Agent string from the request */
    public static ?string $userAgent = null;

    /** @var int|null Role ID from the roles table */
    public static ?int $roleId = null;

    /** @var string|null Role name from the roles table */
    public static ?string $role = null;

    /**
     * @var string[] List of permission keys assigned to the user's role
     * e.g. ["view.sessions.all", "revoke.sessions.all"]
     */
    public static array $permissions = [];

    /**
     * Reset all context values — call at the start of each request.
     */
    public static function reset(): void
    {
        self::$userId = null;
        self::$token = null;
        self::$ip = null;
        self::$userAgent = null;
        self::$roleId = null;
        self::$role = null;
        self::$permissions = [];
    }
}