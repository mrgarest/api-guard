<?php

namespace Garest\ApiGuard;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Garest\ApiGuard\Helper;
use Garest\ApiGuard\Cache\HmacCacheKey;
use Garest\ApiGuard\DTO\HmacData;
use Garest\ApiGuard\Exceptions\InvalidTimestampException;
use Garest\ApiGuard\Exceptions\InvalidAccessKeyException;
use Garest\ApiGuard\Exceptions\InvalidSignatureException;
use Garest\ApiGuard\Exceptions\ReplayDetectedException;
use Garest\ApiGuard\Models\HmacKey;

class Hmac
{
    /**
     * Checking the HMAC signature.
     * @param HmacData $data
     * @param string $method
     * @param string $path
     * @param string $derivedKey
     * 
     * @throws InvalidSignatureException
     */
    public function checkSignature(HmacData $data, string $method, string $path, string $derivedKey): void
    {
        $canonical = $this->canonicalString($method, $path, $data->timestamp, $data->nonce);
        $expected = $this->sign($canonical, $derivedKey);

        if (!hash_equals($expected, $data->signature)) {
            throw new InvalidSignatureException();
        }
    }

    /**
     * Protection against replay attacks via nonce.
     * @param string $accessKey
     * @param string $nonce
     * 
     * @throws ReplayDetectedException
     */
    public function checkReplay(string $accessKey, string $nonce): void
    {
        $ttl = config('api-guard.nonce_ttl', 60);
        if ($ttl === null) return;
        $key = HmacCacheKey::nonce($accessKey, $nonce);
        if (Cache::has($key)) {
            throw new ReplayDetectedException();
        }

        Cache::put($key, true, Carbon::now()->addSeconds($ttl));
    }

    /**
     * Checking that the request is fresh.
     * @param int $timestamp
     * 
     * @throws InvalidTimestampException
     */
    public function checkTime(int $timestamp): void
    {
        $now = Carbon::now()->timestamp;
        $window = config('api-guard.time_window', 30);

        if (abs($now - $timestamp) > $window) {
            throw new InvalidTimestampException();
        }
    }

    /**
     * Build canonical string.
     * @param string $method HTTP method (GET, POST, PUT, DELETE etc.).
     * @param string $path Request path without domain.
     * @param int $timestamp
     * @param string $nonce
     * 
     * @return string
     */
    public function canonicalString(string $method, string $path, int $timestamp, string $nonce): string
    {
        $path = trim($path);
        $normalizedPath = str_starts_with($path, '/') ? $path : '/' . $path;

        return implode("\n", [
            strtoupper(trim($method)),
            $normalizedPath,
            (string)$timestamp,
            trim($nonce),
        ]);
    }

    /**
     * Sign canonical string with derived key (hex).
     * @param string $canonical
     * @param string $secret
     * 
     * @return string
     */
    public function sign(string $canonical, string $derivedKey): string
    {
        return base64_encode(hash_hmac('sha256', $canonical, hex2bin($derivedKey), true));
    }

    /**
     * Returns the HMAC key from the database or throws an error.
     * @param string $accessKey
     * @throws InvalidAccessKeyException
     * @return HmacKey
     */
    public function getKey(string $accessKey): HmacKey
    {
        $ttl = config('api-guard.client_cache_ttl');
        $cacheKey = HmacCacheKey::accessKey($accessKey);

        // Get key from cache if this option is enabled
        if ($ttl && $cached = Cache::get($cacheKey)) {
            $hmacKey = (new HmacKey())->forceFill($cached);
            $hmacKey->exists = true;
        } else {
            // Key search in the database
            $hmacKey = HmacKey::accessKey($accessKey)->first();

            // Cache data if this option is enabled
            if ($hmacKey && $ttl) {
                Cache::put($cacheKey, $hmacKey->toArray(), now()->addSeconds($ttl));
            }
        }

        // Existence and validity check
        if (!$hmacKey || $hmacKey->isRevoked() || $hmacKey->isExpired()) {
            if ($ttl) Cache::forget($cacheKey);
            throw new InvalidAccessKeyException();
        }

        return $hmacKey;
    }

    /**
     * Build a complete HMAC request DTO for a given HTTP method and path.
     * @param string $accessKey
     * @param string $secret Public access key
     * @param string $method HTTP method (GET, POST, PUT, DELETE etc.)
     * @param string $path Request path without domain (e.g., "/api/users")
     * @return HmacData
     */
    public function build(string $accessKey, string $secret, string $method, string $path): HmacData
    {
        $nonce = Helper::nonce();
        $timestamp = Carbon::now()->timestamp;
        $canonical = $this->canonicalString($method, $path, $timestamp, $nonce);
        $derivedKey = hash('sha256', $secret);
        $signature = $this->sign($canonical, $derivedKey);

        return new HmacData(
            $accessKey,
            $timestamp,
            $nonce,
            $signature
        );
    }
}
