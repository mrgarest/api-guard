<?php

namespace Garest\ApiGuard\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string encode(string $secret, int $expiresIn = 3600, array $data = [])
 * @method static array decode(string $secret, string $token)
 *
 * @see \Garest\ApiGuard\Support\Jwt
 */
class Jwt extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'ag.jwt';
    }
}