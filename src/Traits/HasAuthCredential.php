<?php

namespace Garest\ApiGuard\Traits;

use Garest\ApiGuard\Exceptions\InvalidTokenException;
use Illuminate\Support\Facades\Cache;

trait HasAuthCredential
{
    /**
     * Checks whether the key was revoked manually.
     *
     * @return bool
     */
    public function isRevoked(): bool
    {
        return (bool) $this->revoked;
    }

    /**
     * Checks if the key has expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get credentials and validate
     * @param array $queryParams
     * @throws InvalidTokenException
     * @return static
     */
    public static function fetchCredential(array $queryParams): static
    {
        $ttl = config('api-guard.model_cache_ttl', 0);

        $cacheKey = static::getCacheKey($queryParams);

        // Getting data if it's not in the cache
        $fetcher = fn() => static::where($queryParams)->first()?->getAttributes();

        // Get raw attributes from cache or database
        $attributes = $ttl > 0 ? Cache::remember($cacheKey, $ttl, $fetcher) : $fetcher();

        // If nothing was found, error
        if (!$attributes) throw new InvalidTokenException();

        // Convert attributes to model
        $credential = (new static)->newFromBuilder($attributes);

        // Validity check
        if ($credential->isRevoked() || $credential->isExpired()) {
            if ($ttl > 0) Cache::forget($cacheKey);
            throw new InvalidTokenException();
        }

        return $credential;
    }

    public static function getCacheKey(array $queryParams): string
    {
        return 'ag:' . class_basename(static::class) . ':' . md5(implode(':', $queryParams));
    }
}
