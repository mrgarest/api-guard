<?php

namespace Garest\ApiGuard\DTO;

use Illuminate\Http\Request;
use Garest\ApiGuard\Exceptions\MissingHeadersException;

/**
 * DTO for storing JWT request data.
 * Responsible for extracting headers and basic validation.
 */
class JwtRequestData
{
    /** 
     * Client identifier. 
     */
    public string $clientId;

    /** 
     * Access token. 
     */
    public string $accessToken;

    /**
     * DTO constructor.
     *
     * @param string $clientId Client identifier.
     * @param string $accessToken Access token.
     */
    public function __construct(string $clientId, string $accessToken)
    {
        $this->clientId = $clientId;
        $this->accessToken = $accessToken;
    }

    /**
     * Creates a DTO from an HTTP request.
     * Checks for the presence of all necessary headers and throws an exception if they are missing.
     *
     * @param Request $request
     * @return self
     * @throws MissingHeadersException
     */
    public static function fromRequest(Request $request): self
    {
        // Get the header names from the configuration
        $headers = config('api-guard.headers');

        // Extract header values
        $clientId = $request->header($headers['client_id']);
        $accessToken = $request->header($headers['access_token']);

        // If at least one header is missing, throw an exception.
        if (!$clientId || !$accessToken) {
            throw new MissingHeadersException();
        }

        // Return a new DTO object
        return new self($clientId, $accessToken);
    }

    /**
     * Converts an object into an array of header names.
     * @param array $headers ['client_id' => 'Ag-Client-Id', 'access_token' => 'Ag-Access-Key']
     * 
     * @return array
     */
    public function toHeaders(array $headers= []): array
    {
        // Get header names from configuration or specified names
        $headers = empty($headers) ? config('api-guard.headers') : $headers;

        return [
            $headers['client_id'] => $this->clientId,
            $headers['access_token'] => $this->accessToken
        ];
    }
}
