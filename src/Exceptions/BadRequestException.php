<?php

namespace Garest\ApiGuard\Exceptions;

class BadRequestException extends ApiGuardException
{
    protected $message = 'The request is invalid or cannot be processed';

    public function status(): int
    {
        return 400;
    }

    public function code(): string
    {
        return 'BAD_REQUEST';
    }
}
