<?php

namespace Garest\ApiGuard\Exceptions;

class InvalidGrantTypeException extends ApiGuardException
{
    protected $message = 'The provided grant type is invalid';

    public function status(): int
    {
        return 400;
    }

    public function code(): string
    {
        return 'INVALID_GRANT_TYPE';
    }
}
