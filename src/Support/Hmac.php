<?php

namespace Garest\ApiGuard\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Garest\ApiGuard\DTO\HmacRequestData;
use Garest\ApiGuard\Exceptions\InvalidTimestampException;
use Garest\ApiGuard\Exceptions\InvalidSignatureException;
use Garest\ApiGuard\Exceptions\ReplayDetectedException;
use Illuminate\Support\Str;

class Hmac
{
    /**
     * Checking the HMAC signature.
     * @param HmacRequestData $data
     * @param string $method
     * @param string $path
     * @param string $derivedKey
     * 
     * @throws InvalidSignatureException
     */
    public function checkSignature(HmacRequestData $data, string $method, string $path, string $derivedKey): void
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
        $ttl = config('api-guard.hmac.nonce_ttl', 60);
        if ($ttl === null) return;
        $key = 'ag:hmac:nonce:' . md5("$accessKey:$nonce");
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
        $window = config('api-guard.hmac.time_window', 30);

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
     * Build a complete HMAC request DTO for a given HTTP method and path.
     * @param string $accessKey
     * @param string $secret Public access key
     * @param string $method HTTP method (GET, POST, PUT, DELETE etc.)
     * @param string $path Request path without domain (e.g., "/api/users")
     * @return HmacRequestData
     */
    public function build(string $accessKey, string $secret, string $method, string $path): HmacRequestData
    {
        $nonce = Str::random(20);
        $timestamp = Carbon::now()->timestamp;
        $canonical = $this->canonicalString($method, $path, $timestamp, $nonce);
        $derivedKey = hash('sha256', $secret);
        $signature = $this->sign($canonical, $derivedKey);

        return new HmacRequestData(
            $accessKey,
            $timestamp,
            $nonce,
            $signature
        );
    }
}
