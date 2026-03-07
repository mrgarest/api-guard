<?php

namespace Garest\ApiGuard\Support;

use Garest\ApiGuard\Exceptions\InvalidSignatureException;
use Garest\ApiGuard\Exceptions\InvalidTokenException;
use Garest\ApiGuard\Exceptions\TokenExpiredException;
use Garest\ApiGuard\Exceptions\TokenNotYetValid;

class Jwt
{
    /**
     * Replaces characters so that the token can be safely transmitted in a URL without additional encoding.
     * @param string $data
     * 
     * @return string
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Returns characters back to their standard appearance
     * @param string $data
     * 
     * @return string|null
     */
    private function base64UrlDecode(string $data): ?string
    {
        return base64_decode(strtr($data, '-_', '+/'), true) ?: null;
    }

    /**
     * Token encoding
     * @param string $secret
     * @param int $expiresIn
     * @param array $data
     * 
     * @return string
     */
    public function encode(string $secret, int $expiresIn = 3600, array $data = []): string
    {
        // Header
        // $header = $this->base64UrlEncode(json_encode([
        //     'alg' => 'HS256',
        //     'typ' => 'JWT'
        // ]));
        $header = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9';

        $now = time();

        // Payload
        $payload = $this->base64UrlEncode(json_encode(array_merge([
            'iat' => $now,
            'exp' => $now + $expiresIn,
            'jti' => bin2hex(random_bytes(8))
        ], $data)));

        // Signature
        $base64Signature = $this->base64UrlEncode(hash_hmac('sha256', "$header.$payload", $secret, true));

        return "$header.$payload.$base64Signature";
    }

    /**
     * Token validation and decryption.
     * @param string $secret
     * @param string $token
     * 
     * @throws InvalidTokenException
     * @throws InvalidSignatureException
     * @throws TokenExpiredException
     * @throws TokenNotYetValid
     * 
     * @return array
     */
    public function decode(string $secret, string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new InvalidTokenException();
        }

        [$header, $payload, $signature] = $parts;

        // Signature verification
        $expectedSignature = hash_hmac('sha256', "$header.$payload", $secret, true);
        if (!hash_equals($this->base64UrlEncode($expectedSignature), $signature)) {
            throw new InvalidSignatureException();
        }

        $decodedPayload = json_decode($this->base64UrlDecode($payload), true);
        $nowTimestamp = time();

        // Verifying that the token has not expired
        if (!isset($decodedPayload['exp']) || $nowTimestamp > $decodedPayload['exp']) {
            throw new TokenExpiredException();
        }

        // Verification or token can now be used
        if (isset($decodedPayload['iat']) && $decodedPayload['iat'] > $nowTimestamp) {
            throw new TokenNotYetValid();
        }

        return $decodedPayload;
    }
}
