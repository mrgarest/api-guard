<?php

namespace Garest\ApiGuard\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Garest\ApiGuard\Drivers\HmacDriver;
use Garest\ApiGuard\Drivers\JwtDriver;
use Garest\ApiGuard\Exceptions\AuthMethodNotRecognizedException;
use Garest\ApiGuard\Facades\Client;
use Garest\ApiGuard\Support\Limiter;
use Symfony\Component\HttpFoundation\Response;

class AuthWithAny
{
    protected array $map = [
        'hmac' => HmacDriver::class,
        'jwt' => JwtDriver::class
    ];

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
    public function handle(Request $request, Closure $next, ...$methods): Response
    {
        // Checks whether the key is blocked. If it is blocked, it throws an error.
        $this->limiter->checkBlocked(Client::ip($request));

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
