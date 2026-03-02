<?php

namespace Garest\ApiGuard\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Garest\ApiGuard\Drivers\HmacDriver;
use Garest\ApiGuard\Facades\Client;
use Garest\ApiGuard\Support\Limiter;
use Symfony\Component\HttpFoundation\Response;

class AuthHmac
{
    public function __construct(
        private Limiter $limiter
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * 
     * @throws \Garest\ApiGuard\Exceptions\ApiGuardException
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Checks whether the key is blocked. If it is blocked, it throws an error.
        $this->limiter->checkBlocked(Client::ip($request));

        // Calling the authentication mechanism
        app(HmacDriver::class)->authenticate($request);

        return $next($request);
    }
}
