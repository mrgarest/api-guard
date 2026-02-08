<?php

namespace Garest\ApiGuard;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Helper
{
    /**
     * Resolve the client IP address from the incoming request.
     *
     * If Cloudflare support is enabled, the IP will be taken from the `CF-Connecting-IP` header. Otherwise, the default request IP is used.
     *
     * @param Request $request
     * @return string
     */
    public static function getIp(Request $request): string
    {
        $cfIp = config('api-guard.cloudflare_ip', false) ? $request->header('CF-Connecting-IP') : null;
        return $cfIp !== null ? $cfIp : $request->ip();
    }

    /**
     * Generate a unique access key.
     * @return string
     */
    public static function accessKey(): string
    {
        return Str::lower(Str::random(32));
    }

    /**
     * Generate a secure secret for the HMAC key.
     * @return string
     */
    public static function secret(): string
    {
        return Str::random(64);
    }

    /**
     * Generate a unique random nonce.
     * @return string
     */
    public static function nonce(): string
    {
        return Str::random(20);
    }
}