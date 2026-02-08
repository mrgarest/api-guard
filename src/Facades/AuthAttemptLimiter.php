<?php

namespace Garest\ApiGuard\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isLocked(string $ip)
 * @method static void checkLock(string $ip)
 * @method static void lock(string $ip)
 * @method static int getAttempts(string $ip)
 * @method static int incrementAttempts(string $ip)
 * @method static bool isExceeded(int $attempts)
 * @method static void reset(string $ip)
 * @method static string attemptsCacheKey(string $ip)
 * @method static string lockCacheKey(string $ip)
 *
 * @see \Garest\ApiGuard\Support\AuthAttemptLimiter
 */
class AuthAttemptLimiter extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'ag.auth_attempt_limiter';
    }
}
