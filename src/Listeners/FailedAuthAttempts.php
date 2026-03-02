<?php

namespace Garest\ApiGuard\Listeners;

use Garest\ApiGuard\Events\AuthFailed;
use Garest\ApiGuard\Facades\Client;
use Garest\ApiGuard\Support\Limiter;

class FailedAuthAttempts
{
    public function __construct(
        private Limiter $limiter
    ) {}

    public function handle(AuthFailed $event): void
    {
        $this->limiter->check(
            Client::ip($event->request),
            config('api-guard.failed_auth.max_attempts', 3),
            config('api-guard.failed_auth.blocked_time', 600)
        );
    }
}
