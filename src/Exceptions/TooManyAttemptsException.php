<?php

namespace Garest\ApiGuard\Exceptions;

class TooManyAttemptsException extends ApiGuardException
{
    protected $message = 'Authentication temporarily blocked due to excessive attempts';

    public function status(): int
    {
        return 423;
    }

    public function code(): string
    {
        return 'TOO_MANY_ATTEMPTS';
    }
}
