<?php

namespace Garest\ApiGuard\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Garest\ApiGuard\Drivers\HmacDriver;
use Garest\ApiGuard\Exceptions\AuthMethodNotRecognizedException;
use Garest\ApiGuard\Facades\AuthAttemptLimiter;
use Garest\ApiGuard\Helper;
use Symfony\Component\HttpFoundation\Response;

class AuthWithAny
{
    protected array $map = [
        'hmac' => HmacDriver::class
    ];

    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * 
     * @throws \Garest\ApiGuard\Exceptions\ApiGuardException
     */
    public function handle(Request $request, Closure $next, ...$methods): Response
    {
        // Check whether authentication is locked for the given IP address
        AuthAttemptLimiter::checkLock(Helper::getIp($request));

        foreach ($methods as $method) {
            // Checking for driver existence
            if (!isset($this->map[$method])) {
                continue;
            }

            // Create a driver instance via the app container
            $driver = app($this->map[$method]);

            // Checking whether a request contains signs of this authentication method
            if ($driver->matches($request)) {
                $driver->authenticate($request);
                return $next($request);
            }
        }

        // Error call if the authorization driver could not be recognized
        throw new AuthMethodNotRecognizedException();
    }
}
