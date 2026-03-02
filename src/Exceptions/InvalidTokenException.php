<?php

namespace Garest\ApiGuard\Exceptions;

class InvalidTokenException extends ApiGuardException
{
    protected $message = 'The token is invalid';

    public function status(): int
    {
        return 401;
    }

    public function code(): string
    {
        return 'INVALID_TOKEN';
    }
}