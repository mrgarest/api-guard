<?php

namespace Garest\ApiGuard\Cache;

class CacheKey
{
    protected const ROOT = 'mrag:';

    public static function attemptLimits(string $ip): string
    {
        return self::ROOT . 'auth:attempt_limits:' . $ip;
    }
    
    public static function authLock(string $ip): string
    {
        return self::ROOT . 'auth:lock:' . $ip;
    }
}
