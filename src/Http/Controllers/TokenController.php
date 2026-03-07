<?php

namespace Garest\ApiGuard\Http\Controllers;

use Garest\ApiGuard\Exceptions\InvalidClientException;
use Garest\ApiGuard\Exceptions\InvalidGrantTypeException;
use Garest\ApiGuard\Facades\Client;
use Garest\ApiGuard\Http\Resources\SuccessResource;
use Garest\ApiGuard\Services\JwtService;
use Illuminate\Http\Request;

class TokenController
{
    public function __construct(
        private JwtService $jwtService
    ) {}

    public function __invoke(Request $request)
    {
        $clientId = $request->input('client_id');
        $grantType = $request->input('grant_type');

        // Validation
        if (!$clientId) {
            throw new InvalidClientException();
        }

        if (!$grantType || $grantType !== 'jwt_token') {
            throw new InvalidGrantTypeException();
        }

        $token = $this->jwtService->getToken(
            clientId: $clientId,
            ip: Client::ip($request)
        );

        return new SuccessResource($token);
    }
}
