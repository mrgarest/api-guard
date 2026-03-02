# ApiGuard (API Authentication for Laravel)

ApiGuard is a lightweight package for Laravel designed for secure API client authentication that does not require the creation or use of user models.

## Features

- Client-based authentication (no users).
- Scope-based authorization.
- Caching for performance.
- Logging failed authentication attempts.
- Blocking clients after failed authentication attempts.

## Installation

```bash
composer require garest/api-guard
```

Publish config:

```bash
php artisan vendor:publish --tag=api-guard-config
```

Publish migrations:

```bash
php artisan vendor:publish --tag=api-guard-migrations
```

Run migrations:

```bash
php artisan migrate
```

Generating an encryption key and automatically adding it to the env file:

```bash
php artisan ag:key-generate
```

The generated key is important because it will be used to encrypt data in the database. If you change it, the old keys may not work!

## Usage

Currently, ApiGuard supports two authentication methods for your API:

- [JWT](https://github.com/mrgarest/api-guard/blob/main/docs/jwt.md)
- [HMAC](https://github.com/mrgarest/api-guard/blob/main/docs/hmac.md)

## Error Rendering

If you want to display custom errors instead of standard ones, you can do so by intercepting the ApiGuardException exception in `bootstrap/app.php`.

```php
use Garest\ApiGuard\Exceptions\ApiGuardException;

withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (ApiGuardException $e) {
        return response()->json([
            'status' => $e->status(),
            'code' => $e->code(),
            'message' => $e->getMessage(),
        ], $e->status());
    });
})
```

## Failed Authentication Listener

You can hook into failed API authentication attempts via a Laravel event listener:

```php
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Garest\ApiGuard\Events\AuthFailed;

Event::listen(AuthFailed::class, function ($event) {
    // Access failed request and exception
    $request = $event->request;
    $exception = $event->exception;

    // Example: log failure
    Log::warning('Authentication failed', [
        'ip' => $request->ip(),
        'path' => $request->path(),
        'method' => $request->method(),
        'message' => $exception->getMessage(),
    ]);
});
```

This allows you to track, log, or notify whenever a client fails authentication.