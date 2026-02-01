<?php

namespace Garest\ApiGuard\Exceptions;

class InvalidTimestampException extends ApiGuardException
{
    protected $message = 'The provided timestamp failed validation';

    public function status(): int
    {
        return 401;
    }

    public function code(): string
    {
        return 'INVALID_TIMESTAMP';
    }
}