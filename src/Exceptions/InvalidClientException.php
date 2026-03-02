<?php

namespace Garest\ApiGuard\Exceptions;

class InvalidClientException extends ApiGuardException
{
    protected $message = 'The client is invalid';

    public function status(): int
    {
        return 400;
    }

    public function code(): string
    {
        return 'INVALID_CLIENT';
    }
}