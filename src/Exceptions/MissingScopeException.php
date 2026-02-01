<?php

namespace Garest\ApiGuard\Exceptions;

class MissingScopeException extends ApiGuardException
{
    protected $message = 'Required scopes are missing to access this resource';

    public function status(): int
    {
        return 403;
    }

    public function code(): string
    {
        return 'MISSING_SCOPES';
    }
}
