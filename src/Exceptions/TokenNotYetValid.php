<?php

namespace Garest\ApiGuard\Exceptions;

class TokenNotYetValid extends ApiGuardException
{
    protected $message = 'The token is not yet valid';

    public function status(): int
    {
        return 401;
    }

    public function code(): string
    {
        return 'TOKEN_NOT_YET_VALID';
    }
}