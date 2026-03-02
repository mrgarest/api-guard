<?php

namespace Garest\ApiGuard\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string getAttemptsKey(string $key)
 * @method static string getBlockedKey(string $key)
 * @method static bool isBlocked(string $key)
 * @method static void checkBlocked(string $key)
 * @method static void check(string $key, int $maxAttempts, int $blockedTime)
 *
 * @see \Garest\ApiGuard\Support\Limiter
 */
class Limiter extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'ag.limiter';
    }
}

