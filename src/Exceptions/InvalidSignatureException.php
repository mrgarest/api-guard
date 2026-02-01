<?php

namespace Garest\ApiGuard\Exceptions;

class InvalidSignatureException extends ApiGuardException
{
    protected $message = 'The request signature is invali.';

    public function status(): int
    {
        return 401;
    }

    public function code(): string
    {
        return 'INVALID_SIGNATURE';
    }
}
