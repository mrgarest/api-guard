<?php

namespace Garest\ApiGuard\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Garest\ApiGuard\Drivers\JwtDriver;
use Symfony\Component\HttpFoundation\Response;

class AuthJwt
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
        // Calling the authentication mechanism
        app(JwtDriver::class)->authenticate($request);

        return $next($request);
    }
}
