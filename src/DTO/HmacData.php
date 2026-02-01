<?php

namespace Garest\ApiGuard\DTO;

use Illuminate\Http\Request;
use Garest\ApiGuard\Exceptions\MissingHeadersException;

/**
 * DTO for storing HMAC request data.
 * Responsible for extracting headers and basic validation.
 */
class HmacData
{
    /** 
     * Public access key. 
     */
    public string $accessKey;

    /**
     * Request timestamp.
     */
    public int $timestamp;

    /**
     * Unique request nonce to protect against replay attacks.
     */
    public string $nonce;

    /**
     * HMAC signature of the request.
     */
    public string $signature;

    /**
     * DTO constructor.
     *
     * @param string $accessKey Public access key.
     * @param int $timestamp Request timestamp.
     * @param string $nonce Unique request nonce to protect against replay attacks.
     * @param string $signature HMAC signature of the request.
     */
    public function __construct(string $accessKey, int $timestamp, string $nonce, string $signature)
    {
        $this->accessKey = $accessKey;
        $this->timestamp = $timestamp;
        $this->nonce = $nonce;
        $this->signature = $signature;
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
        $accessKey = $request->header($headers['access_key']);
        $timestamp = $request->header($headers['timestamp']);
        $nonce = $request->header($headers['nonce']);
        $signature = $request->header($headers['signature']);

        // If at least one header is missing, throw an exception.
        if (!$accessKey || !$timestamp || !$nonce || !$signature) {
            throw new MissingHeadersException();
        }

        // Return a new DTO object
        return new self($accessKey, (int) $timestamp, $nonce, $signature);
    }

    /**
     * Converts an object into an array of header names.
     * @param array $headers ['access_key' => 'Ag-Access-Key', 'timestamp' => 'Ag-Timestamp', 'nonce' => 'Ag-Nonce', 'signature' => 'Ag-Signature']
     * 
     * @return array
     */
    public function toHeaders(array $headers= []): array
    {
        // Get header names from configuration or specified names
        $headers = empty($headers) ? config('api-guard.headers') : $headers;

        return [
            $headers['access_key'] => $this->accessKey,
            $headers['timestamp'] => $this->timestamp,
            $headers['nonce'] => $this->nonce,
            $headers['signature'] => $this->signature,
        ];
    }
}
