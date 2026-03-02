<?php

namespace Garest\ApiGuard\Support;

use Garest\ApiGuard\Exceptions\TooManyAttemptsException;
use Illuminate\Support\Facades\RateLimiter;

class Limiter
{
    public function getAttemptsKey(string $key): string
    {
        return 'ag:attempts:' . $key;
    }

    public function getBlockedKey(string $key): string
    {
        return 'ag:blocked:' . $key;
    }

    /**
     * Checks whether the key is blocked.
     * @param string $key
     * @return bool
     */
    public function isBlocked(string $key): bool
    {
        return RateLimiter::tooManyAttempts($this->getBlockedKey($key), 1);
    }

    /**
     * Checks whether the key is blocked. If it is blocked, it throws an error.
     * @param string $key
     * @throws TooManyAttemptsException
     */
    public function checkBlocked(string $key): void
    {
        if ($this->isBlocked($key)) {
            throw new TooManyAttemptsException();
        }
    }

    /**
     * Method for checking limits and activating blocking.
     * @param string $key
     * @param int $maxAttempts
     * @param int $blockedTime
     * @throws TooManyAttemptsException
     */
    public function check(string $key, int $maxAttempts, int $blockedTime): void
    {
        // Checks whether the key is blocked
        $this->checkBlocked($key);

        $attemptsKey = $this->getAttemptsKey($key);

        // Limit check
        if (RateLimiter::tooManyAttempts($attemptsKey, $maxAttempts)) {
            // Activate the ban
            RateLimiter::hit($this->getBlockedKey($key), $blockedTime);

            // Clearing the attempt counter
            RateLimiter::clear($attemptsKey);

            throw new TooManyAttemptsException();
        }

        RateLimiter::hit($attemptsKey, 60);
    }
}
