<?php

namespace Garest\ApiGuard\Listeners;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Garest\ApiGuard\Cache\CacheKey;
use Garest\ApiGuard\Events\AuthFailed;
use Garest\ApiGuard\Exceptions\TooManyAttemptsException;
use Garest\ApiGuard\Helper;

class AuthAttemptLimits
{
    public function handle(AuthFailed $event): void
    {
        $config = config('api-guard.auth_attempt_limits');

        if (!($config['enabled'] ?? false)) {
            return;
        }

        $ip = Helper::getIp($event->request);
        $key = CacheKey::attemptLimits($ip);

        $attempts = Cache::get($key, 0) + 1;

        // Store attempts with decay time
        Cache::put($key, $attempts, Carbon::now()->addSecond($config['decay'] ?? 60));

        // Lock the access key or IP if max authentication attempts are exceeded.
        if ($attempts > ($config['max_attempts'] ?? 3)) {
            $key = CacheKey::authLock($ip);
            Cache::put($key, true, Carbon::now()->addSecond($config['lock_duration'] ?? 300));

            throw new TooManyAttemptsException();
        }
    }
}
