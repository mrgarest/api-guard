<?php

namespace Garest\ApiGuard\Http\Middleware;

use Closure;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Garest\ApiGuard\Auth;
use Garest\ApiGuard\DTO\HmacData;
use Garest\ApiGuard\Events\AuthFailed;
use Garest\ApiGuard\Exceptions\ApiGuardException;
use Garest\ApiGuard\Exceptions\MissingScopeException;
use Garest\ApiGuard\Helper;
use Garest\ApiGuard\Hmac;
use Symfony\Component\HttpFoundation\Response;

class HmacMiddleware
{
    public function __construct(
        protected Hmac $hmac,
        protected Dispatcher $events
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * 
     * @throws ApiGuardException
     */
    public function handle(Request $request, Closure $next, string ...$scopes): Response
    {
        Auth::checkLock(Helper::getIp($request));

        try {
            $data = HmacData::fromRequest($request);

            // Checking the request time
            $this->hmac->checkTime($data->timestamp);

            // Checking replay via nonce
            $this->hmac->checkReplay($data->accessKey, $data->nonce);

            // Get the HMAC secret hash from the database.
            $hmacKey = $this->hmac->getKey($data->accessKey);

            // Checking the HMAC signature
            $this->hmac->checkSignature($data, $request->method(), $request->path(), $hmacKey->secret);

            // Scopes check
            if (!empty($scopes) && !$hmacKey->hasScope($scopes)) {
                throw new MissingScopeException();
            }

            $request->attributes->set('hmacKey', $hmacKey);
            
            return $next($request);
        } catch (ApiGuardException $e) {

            $this->events->dispatch(new AuthFailed(
                request: $request,
                exception: $e
            ));
            throw $e;
        }
    }
}
