<?php

namespace Garest\ApiGuard;

use Illuminate\Support\Facades\Cache;
use Garest\ApiGuard\Cache\CacheKey;
use Garest\ApiGuard\Exceptions\TooManyAttemptsException;

class Auth
{
    /**
     * Determine whether authentication attempts are currently locked for the given IP.
     *
     * @param string $ip
     * @return bool
     */
    public static function isLocked(string $ip): bool
    {
        // If authentication attempt limits are disabled, locking is never applied
        if (!config('api-guard.auth_attempt_limits.enabled', false)) return false;

        // Check if a lock key exists for this IP address
        return Cache::has(CacheKey::authLock($ip));
    }

    /**
     * Check whether authentication is locked for the given IP and throw an exception if so.
     *
     * @param string $ip
     * @return void
     *
     * @throws TooManyAttemptsException
     */
    public static function checkLock(string $ip): void
    {
        // If no lock is active, continue request processing
        if (!static::isLocked($ip)) return;

        // Authentication attempts are temporarily blocked
        throw new TooManyAttemptsException();
    }
}
