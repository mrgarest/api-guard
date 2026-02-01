<?php

namespace Garest\ApiGuard\Exceptions;

class ReplayDetectedException extends ApiGuardException
{
    protected $message = 'The request appears to be a replay and cannot be processed';

    public function status(): int
    {
        return 409;
    }

    public function code(): string
    {
        return 'REPLAY_DETECTED';
    }
}