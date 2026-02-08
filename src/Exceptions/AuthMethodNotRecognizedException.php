<?php

namespace Garest\ApiGuard\Exceptions;

class AuthMethodNotRecognizedException extends ApiGuardException
{
    protected $message = 'The provided authentication method is not recognized';

    public function status(): int
    {
        return 400;
    }

    public function code(): string
    {
        return 'AUTH_METHOD_NOT_RECOGNIZED';
    }
}
