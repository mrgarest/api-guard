<?php

namespace Garest\ApiGuard\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Garest\ApiGuard\Drivers\HmacDriver;
use Garest\ApiGuard\Facades\AuthAttemptLimiter;
use Garest\ApiGuard\Helper;
use Symfony\Component\HttpFoundation\Response;

class AuthHmac
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * 
     * @throws \Garest\ApiGuard\Exceptions\ApiGuardException
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check whether authentication is locked for the given IP address
        AuthAttemptLimiter::checkLock(Helper::getIp($request));

        // Calling the authentication mechanism
        app(HmacDriver::class)->authenticate($request);

        return $next($request);
    }
}
