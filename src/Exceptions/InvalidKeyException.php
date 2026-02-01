<?php

namespace Garest\ApiGuard\Exceptions;

class InvalidAccessKeyException extends ApiGuardException
{
    protected $message = 'The access key is not recognized or is invalid';

    public function status(): int
    {
        return 401;
    }

    public function code(): string
    {
        return 'INVALID_ACCESS_KEY';
    }
}
