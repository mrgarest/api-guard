<?php

namespace Garest\ApiGuard\Cache;

class HmacCacheKey extends CacheKey
{
    protected const MAIN = self::ROOT . 'hmac:';

    public static function accessKey(string $accessKey): string
    {
        return self::MAIN . 'access_key:' . md5($accessKey);
    }

    public static function nonce(string $accessKey, string $nonce): string
    {
        return self::MAIN . 'nonce:' . md5("$accessKey:$nonce");
    }
}
