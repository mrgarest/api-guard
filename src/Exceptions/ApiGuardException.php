<?php

namespace Garest\ApiGuard\Exceptions;

use RuntimeException;

abstract class ApiGuardException extends RuntimeException
{
    public function status(): int
    {
        return 401;
    }

    public function code(): string
    {
        return 'API_GUARD_ERROR';
    }
}
