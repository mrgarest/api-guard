<?php

namespace Garest\ApiGuard\Exceptions;

class MissingHeadersException extends ApiGuardException
{
    protected $message = 'One or more required headers are missing from the request';

    public function status(): int
    {
        return 400;
    }

    public function code(): string
    {
        return 'MISSING_HEADERS';
    }
}