<?php

namespace Garest\ApiGuard\Providers;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Garest\ApiGuard\Console\Commands\CreateHmacKey;
use Garest\ApiGuard\Console\Commands\ResetAuthAttemptsCommand;
use Garest\ApiGuard\Console\Commands\UpdateHmacKey;
use Garest\ApiGuard\Exceptions\ApiGuardException;
use Garest\ApiGuard\Http\Middleware\CheckForAnyScope;
use Garest\ApiGuard\Http\Middleware\AuthHmac;
use Garest\ApiGuard\Http\Middleware\CheckScopes;
use Garest\ApiGuard\Support\AuthAttemptLimiter;
use Garest\ApiGuard\Support\Hmac;

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

        $this->singletons();
    }

    /**
     * Singleton registration.
     * @return void
     */
    private function singletons(): void
    {
        $this->app->singleton('ag.hmac', fn() => new Hmac());
        $this->app->singleton('ag.auth_attempt_limiter', fn() => new AuthAttemptLimiter());
    }

    /**
     * Bootstrap the application services.
     *
     * @param Router $router
     * @return void
     */
    public function boot(Router $router)
    {
        $this->alias($router);
        $this->macros();
        $this->configurePublishing();
        $this->registerDefaultRenderers();
    }

    /**
     * Registration of standard response format.
     */
    protected function registerDefaultRenderers(): void
    {
        $this->callAfterResolving(ExceptionHandler::class, function ($handler) {
            $handler->renderable(function (ApiGuardException $e, $request) {
                return response()->json([
                    'success' => false,
                    'status' => $e->status(),
                    'code' => $e->code(),
                    'message' => $e->getMessage(),
                ], $e->status());
            });
        });
    }

    /**
     * Alias registration.
     * 
     * @param Router $router
     * @return void
     */
    private function alias(Router $router): void
    {
        // Authentication
        // $router->aliasMiddleware('ag.auth_or', AuthWithAny::class);
        $router->aliasMiddleware('ag.hmac', AuthHmac::class);

        // Scopes
        $router->aliasMiddleware('ag.scopes', CheckScopes::class);
        $router->aliasMiddleware('ag.scopes_or', CheckForAnyScope::class);
    }

    /**
     * Register request macros.
     *
     * @return void
     */
    private function macros(): void
    {
        // Set the authenticated HMAC key in the current request
        Request::macro('setHmacKey', fn($v) => $this->attributes->set('hmacKey', $v));

        // Get the authenticated HMAC key from the current request
        Request::macro('getHmacKey', fn() => $this->attributes->get('hmacKey'));

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
            ResetAuthAttemptsCommand::class
        ]);

        $this->publishes([
            __DIR__ . '/../../config/api-guard.php' => config_path('api-guard.php'),
        ], 'api-guard-config');

        $this->publishes([
            __DIR__ . '/../../database/migrations/' => database_path('migrations'),
        ], 'api-guard-migrations');
    }
}
