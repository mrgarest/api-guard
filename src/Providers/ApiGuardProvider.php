<?php

namespace Garest\ApiGuard\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Garest\ApiGuard\Commands\CreateHmacKey;
use Garest\ApiGuard\Commands\UpdateHmacKey;

class ApiGuardProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__  . '/../../config/api-guard.php', 'api-guard');

        $this->app->register(EventServiceProvider::class);
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRequestMacros();
        $this->configurePublishing();
    }

    /**
     * Register request macros.
     *
     * @return void
     */
    private function registerRequestMacros(): void
    {
        // Get the authenticated HMAC key from the current request
        Request::macro('hmacKey', fn() => $this->attributes->get('hmacKey'));

        // Check if an HMAC key is attached to the request
        Request::macro('hasHmacKey', fn(): bool => $this->attributes->has('hmacKey'));
    }

    /**
     * Configure publishing for the package.
     *
     * @return void
     */
    private function configurePublishing(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            CreateHmacKey::class,
            UpdateHmacKey::class,
        ]);

        $this->publishes([
            __DIR__ . '/../../config/api-guard.php' => config_path('api-guard.php'),
        ], 'api-guard-config');

        $this->publishes([
            __DIR__ . '/../../database/migrations/' => database_path('migrations'),
        ], 'api-guard-migrations');
    }
}
