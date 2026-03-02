<?php

namespace Garest\ApiGuard\Providers;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Garest\ApiGuard\Console\Commands\CreateHmacKey;
use Garest\ApiGuard\Console\Commands\CreateJwtClient;
use Garest\ApiGuard\Console\Commands\KeyGenerateCommand;
use Garest\ApiGuard\Console\Commands\UpdateHmacKey;
use Garest\ApiGuard\Console\Commands\UpdateJwtClient;
use Garest\ApiGuard\Exceptions\ApiGuardException;
use Garest\ApiGuard\Http\Middleware\CheckForAnyScope;
use Garest\ApiGuard\Http\Middleware\AuthHmac;
use Garest\ApiGuard\Http\Middleware\AuthJwt;
use Garest\ApiGuard\Http\Middleware\AuthWithAny;
use Garest\ApiGuard\Http\Middleware\CheckScopes;
use Garest\ApiGuard\Support\Client;
use Garest\ApiGuard\Support\Hmac;
use Garest\ApiGuard\Support\Jwt;
use Garest\ApiGuard\Support\Limiter;

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
        $this->app->singleton('ag.jwt', fn() => new Jwt());
        $this->app->singleton('ag.limiter', fn() => new Limiter());
        $this->app->singleton('ag.client', fn() => new Client());
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
        $router->aliasMiddleware('ag.auth_or', AuthWithAny::class);
        $router->aliasMiddleware('ag.hmac', AuthHmac::class);
        $router->aliasMiddleware('ag.jwt', AuthJwt::class);

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
        Request::macro('setAuthCredential', fn($v) => $this->attributes->set('authCredential', $v));
        Request::macro('getAuthCredential', fn() => $this->attributes->get('authCredential'));
        Request::macro('hasAuthCredential', fn(): bool => $this->attributes->has('authCredential'));
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

        // Command registration
        $this->commands([
            KeyGenerateCommand::class,
            CreateHmacKey::class,
            UpdateHmacKey::class,
            CreateJwtClient::class,
            UpdateJwtClient::class
        ]);

        // Registration of configuration
        $this->publishes([
            __DIR__ . '/../../config/api-guard.php' => config_path('api-guard.php'),
        ], 'api-guard-config');

        // Registration of migrations
        $this->registerMigrations(['create_ag_failed_auths_table.php'], 'api-guard-migrations');
        $this->registerMigrations(['create_ag_hmac_keys_table.php'], 'api-guard-hmac-migration');
        $this->registerMigrations(['create_ag_jwt_clients_table.php'], 'api-guard-jwt-migration');
    }

    /**
     * Registering a group of migrations under a specific tag
     * @param array $fileNames
     * @param string $tagName
     */
    protected function registerMigrations(array $fileNames, string $tagName): void
    {
        $existingMigrations = collect(glob(database_path('migrations/*.php')));
        $publishMap = [];

        foreach ($fileNames as $fileName) {
            // Check for duplicates (ignoring the date in the prefix)
            $alreadyPublished = $existingMigrations->some(fn($path) => str_ends_with($path, '_' . $fileName));

            if (!$alreadyPublished) {
                $source = __DIR__ . '/../../database/migrations/' . $fileName;
                $target = database_path('migrations/' . date('Y_m_d_His') . '_' . $fileName);

                $publishMap[$source] = $target;

                // Delay so that migrations have different times and proceed in order
                sleep(1);
            }
        }

        if (!empty($publishMap)) {
            $this->publishes($publishMap, $tagName);
        }
    }
}
