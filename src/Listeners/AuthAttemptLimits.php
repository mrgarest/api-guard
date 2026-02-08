<?php

namespace Garest\ApiGuard\Listeners;

use Garest\ApiGuard\Events\AuthFailed;
use Garest\ApiGuard\Exceptions\TooManyAttemptsException;
use Garest\ApiGuard\Facades\AuthAttemptLimiter;
use Garest\ApiGuard\Helper;

class AuthAttemptLimits
{
    public function handle(AuthFailed $event): void
    {
        if (!config('api-guard.auth_attempt_limits.enabled', false)) {
            return;
        }

        $ip = Helper::getIp($event->request);
        $attempts = AuthAttemptLimiter::incrementAttempts($ip);

        // Lock the access key or IP if max authentication attempts are exceeded.
        if (AuthAttemptLimiter::isExceeded($attempts)) {
            AuthAttemptLimiter::lock($ip);
            throw new TooManyAttemptsException();
        }
    }
}
