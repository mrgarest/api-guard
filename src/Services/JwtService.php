<?php

namespace Garest\ApiGuard\Services;

use Garest\ApiGuard\Models\JwtClient;
use Garest\ApiGuard\Support\Jwt;
use Garest\ApiGuard\Support\Limiter;

class JwtService
{
    public function __construct(
        private Jwt $jwt,
        private Limiter $limiter
    ) {}

    public function getToken(string $clientId, string $ip)
    {
        // Limit on receiving tokens
        $this->limiter->check(
            $ip,
            config('api-guard.jwt.max_tokens', 10),
            config('api-guard.jwt.blocked_time', 600)
        );

        // Getting the JWT model
        $credential = JwtClient::fetchCredential(['client_id' => $clientId]);

        // Creating a token
        $expiresIn = config('api-guard.jwt.expires_in', 3600);
        $accessToken = $this->jwt->encode($credential->secret, $expiresIn, [
            'v' => 1
        ]);

        return [
            'access_token' => $accessToken,
            'expires_in' => $expiresIn
        ];
    }
}
