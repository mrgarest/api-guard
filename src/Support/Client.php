<?php

namespace Garest\ApiGuard\Support;

use Illuminate\Http\Request;

class Client
{
    /**
     * Resolve the client IP address from the incoming request.
     *
     * If Cloudflare support is enabled, the IP will be taken from the `CF-Connecting-IP` header. Otherwise, the default request IP is used.
     *
     * @param Request $request
     * @return string
     */
    public function ip(Request $request): string
    {
        $cfIp = config('api-guard.cloudflare_ip', false) ? $request->header('CF-Connecting-IP') : null;
        return $cfIp !== null ? $cfIp : $request->ip();
    }
}
