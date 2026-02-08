<?php

namespace Garest\ApiGuard\Exceptions;

use Garest\ApiGuard\Events\AuthFailed;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use RuntimeException;

abstract class ApiGuardException extends RuntimeException implements HttpExceptionInterface 
{
    public function getStatusCode(): int 
    { 
        return $this->status(); 
    }

    public function getHeaders(): array 
    { 
        return []; 
    }

    public function status(): int
    {
        return 401;
    }

    public function code(): string
    {
        return 'API_GUARD_ERROR';
    }

    /**
     * Error logging.
     */
    public function report(): bool
    {
        event(new AuthFailed(request(), $this));
        return true;
    }
}
