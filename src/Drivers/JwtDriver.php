<?php

namespace Garest\ApiGuard\Drivers;

use Illuminate\Http\Request;
use Garest\ApiGuard\DTO\JwtRequestData;
use Garest\ApiGuard\Models\JwtClient;
use Garest\ApiGuard\Support\Jwt;

class JwtDriver
{
    public function __construct(
        private Jwt $jwt
    ) {}

    /**
     * Preliminary analysis of the request for compliance with authentication requirements.
     * 
     * @param Request $request
     * @return bool
     */
    public function matches(Request $request): bool
    {
        $headers = config('api-guard.headers');

        return $request->hasHeader($headers['client_id'])
            && $request->hasHeader($headers['access_token']);
    }

    /**
     * Performs a full authentication cycle.
     * @throws \Garest\ApiGuard\Exceptions\ApiGuardException
     */
    public function authenticate($request)
    {
        $data = JwtRequestData::fromRequest($request);

        // Getting the JWT model
        $credential = JwtClient::fetchCredential(['client_id' => $data->clientId]);

        // Token validation and decryption
        $this->jwt->decode($credential->secret, $data->accessToken);

        $request->setAuthCredential($credential);
    }
}
