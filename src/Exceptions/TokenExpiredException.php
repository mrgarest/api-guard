<?php

namespace Garest\ApiGuard\Exceptions;

class TokenExpiredException extends ApiGuardException
{
    protected $message = 'The token has expired';

    public function status(): int
    {
        return 401;
    }

    public function code(): string
    {
        return 'TOKEN_EXPIRED';
    }
}