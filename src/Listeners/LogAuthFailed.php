<?php

namespace Garest\ApiGuard\Listeners;

use Garest\ApiGuard\Events\AuthFailed;
use Garest\ApiGuard\Exceptions\ApiGuardException;
use Garest\ApiGuard\Models\FailedAuth;

class LogAuthFailed
{
    public function handle(AuthFailed $event): void
    {
        if (!config('api-guard.log.auth_failed', true)) {
            return;
        }

        if (!$event->exception instanceof ApiGuardException) {
            return;
        }

        FailedAuth::createFromEvent($event);
    }
}
