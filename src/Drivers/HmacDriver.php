<?php

namespace Garest\ApiGuard\Drivers;

use Illuminate\Http\Request;
use Garest\ApiGuard\DTO\HmacData;
use Garest\ApiGuard\Support\Hmac;

class HmacDriver
{
    public function __construct(protected Hmac $hmac) {}

    /**
     * Preliminary analysis of the request for compliance with authentication requirements.
     * 
     * @param Request $request
     * @return bool
     */
    public function matches(Request $request): bool
    {
        $headers = config('api-guard.headers');

        return $request->hasHeader($headers['access_key'])
            && $request->hasHeader($headers['signature']);
    }

    /**
     * Performs a full authentication cycle.
     * @throws \Garest\ApiGuard\Exceptions\ApiGuardException
     */
    public function authenticate($request)
    {
        $data = HmacData::fromRequest($request);

        // Checking the request time
        $this->hmac->checkTime($data->timestamp);

        // Checking replay via nonce
        $this->hmac->checkReplay($data->accessKey, $data->nonce);

        // Get the HMAC secret hash from the database.
        $hmacKey = $this->hmac->getKey($data->accessKey);

        // Checking the HMAC signature
        $this->hmac->checkSignature($data, $request->method(), $request->path(), $hmacKey->secret);

        $request->setHmacKey($hmacKey);
    }
}
