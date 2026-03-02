<?php

namespace Garest\ApiGuard\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void checkSignature(\Garest\ApiGuard\DTO\HmacRequestData $data, string $method, string $path, string $derivedKey)
 * @method static void checkReplay(string $accessKey, string $nonce)
 * @method static void checkTime(int $timestamp)
 * @method static string canonicalString(string $method, string $path, int $timestamp, string $nonce)
 * @method static string sign(string $canonical, string $derivedKey)
 * @method static \Garest\ApiGuard\DTO\HmacRequestData build(string $accessKey, string $secret, string $method, string $path)
 *
 * @see \Garest\ApiGuard\Support\Hmac
 */
class Hmac extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'ag.hmac';
    }
}

