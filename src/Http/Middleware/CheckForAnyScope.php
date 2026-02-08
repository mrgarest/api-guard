<?php

namespace Garest\ApiGuard\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Garest\ApiGuard\Exceptions\MissingScopeException;
use Symfony\Component\HttpFoundation\Response;

class CheckForAnyScope
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * 
     * @throws \Garest\ApiGuard\Exceptions\ApiGuardException
     */
    public function handle(Request $request, Closure $next, ...$scopes): Response
    {
        $hmacKey = $request->getHmacKey();
        if (!$hmacKey || !$hmacKey->hasAnyScope($scopes)) {
            throw new MissingScopeException();
        }
        return $next($request);
    }
}
