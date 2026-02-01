<?php

namespace Garest\ApiGuard\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Garest\ApiGuard\Events\AuthFailed;
use Garest\ApiGuard\Listeners\AuthAttemptLimits;
use Garest\ApiGuard\Listeners\LogAuthFailed;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        AuthFailed::class => [
            AuthAttemptLimits::class,
            LogAuthFailed::class,
        ],
    ];
}