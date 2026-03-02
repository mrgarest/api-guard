<?php

namespace Garest\ApiGuard\Http\Controllers;

use Garest\ApiGuard\Exceptions\BadRequestException;
use Garest\ApiGuard\Exceptions\InvalidClientException;
use Garest\ApiGuard\Exceptions\InvalidGrantTypeException;
use Garest\ApiGuard\Facades\Client;
use Garest\ApiGuard\Http\Resources\SuccessResource;
use Garest\ApiGuard\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TokenController
{
    public function __construct(
        private JwtService $jwtService
    ) {}

    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'grant_type' => 'required|string|in:jwt_token',
            'client_id' => 'required|string',
        ]);

        // Error handling
        if ($validator->fails()) {
            $field = $validator->errors()->keys()[0];

            match ($field) {
                'grant_type' => throw new InvalidGrantTypeException(),
                'client_id' => throw new InvalidClientException(),
                default => throw new BadRequestException()
            };
        }

        // Data after validation
        $data = $validator->validated();

        $token = $this->jwtService->getToken(
            clientId: $data['client_id'],
            ip: Client::ip($request)
        );

        return new SuccessResource($token);
    }
}
