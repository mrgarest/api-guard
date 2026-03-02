<?php

namespace Garest\ApiGuard\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Encryption\Encrypter;

class Encrypted implements CastsAttributes
{
    protected $encrypter;

    public function __construct()
    {
        $key = config('api-guard.key');
        
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        $this->encrypter = new Encrypter($key, 'AES-256-CBC');
    }

    public function get($model, string $key, $value, array $attributes)
    {
        return $value ? $this->encrypter->decrypt($value) : null;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return $value ? $this->encrypter->encrypt($value) : null;
    }
}