<?php

namespace Garest\ApiGuard\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Garest\ApiGuard\Exceptions\TooManyAttemptsException;

class AuthAttemptLimiter
{
    protected const CACHE_KEY = 'ag:aal:';
  
    /**
     * Determine whether authentication attempts are currently locked for the given IP.
     *
     * @param string $ip
     * @return bool
     */
    public function isLocked(string $ip): bool
    {
        // If authentication attempt limits are disabled, locking is never applied
        if (!config('api-guard.auth_attempt_limits.enabled', false)) return false;

        // Check if a lock key exists for this IP address
        return Cache::has($this->lockCacheKey($ip));
    }

    /**
     * Check whether authentication is locked for the given IP and throw an exception if so.
     *
     * @param string $ip
     * @return void
     *
     * @throws TooManyAttemptsException
     */
    public function checkLock(string $ip): void
    {
        // If no lock is active, continue request processing
        if (!$this->isLocked($ip)) return;

        // Authentication attempts are temporarily blocked
        throw new TooManyAttemptsException();
    }

    /**
     * Sets forced blocking for IP.
     * 
     * @param string $ip
     */
    public function lock(string $ip): void
    {
        Cache::put($this->lockCacheKey($ip), true, Carbon::now()->addSeconds(config('api-guard.auth_attempt_limits.lock_duration', 300)));
    }

    /**
     * Get the current count of failed attempts for an IP.
     *
     * @param string $ip
     * @return int
     */
    public function getAttempts(string $ip): int
    {
        return (int) Cache::get($this->attemptsCacheKey($ip), 0);
    }

    /**
     * Increase attempt counter.
     *
     * @param string $ip
     * @return int
     */
    public function incrementAttempts(string $ip): int
    {
        $attempts = $this->getAttempts($ip) + 1;

        Cache::put($this->attemptsCacheKey($ip), $attempts, Carbon::now()->addSeconds(config('api-guard.auth_attempt_limits.decay', 60)));

        return $attempts;
    }

    /**
     * Checks whether the maximum number of attempts has been exceeded
     *
     * @param int $attempts
     * @return bool
     */
    public function isExceeded(int $attempts): bool
    {
        return $attempts >= config('api-guard.auth_attempt_limits.max_attempts', 3);
    }

    /**
     * Resets the attempt counter and removes the lock.
     * @param string $ip
     */
    public function reset(string $ip): void
    {
        foreach ([$this->attemptsCacheKey($ip), $this->lockCacheKey($ip)] as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Generates a unique cache key to track the number of authentication attempts.
     * 
     * @param string $ip
     * @return string
     */
    public function attemptsCacheKey(string $ip): string
    {
        return self::CACHE_KEY . 'attempts:' .  md5($ip);
    }

    /**
     * Generates a unique cache key to indicate that a specific IP address has been blocked.
     *
     * @param string $ip
     * @return string
     */
    public function lockCacheKey(string $ip): string
    {
        return self::CACHE_KEY . 'lock:' . md5($ip);
    }
}
