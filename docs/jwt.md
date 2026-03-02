# JWT Authentication

This method implements API client authentication via short-lived JWT tokens.

## How JWT Authentication Works

The client sends a request to the designated controller to obtain an access token.
After successful verification, the server generates a JWT that the client can use to further access protected API routes.

If a client fails to pass verification to obtain a token (for example, due to exceeding the limit or invalid data), or if an invalid or expired token is transmitted when accessing the API, the system automatically:

- Temporarily restrict the client's access.
- Record the relevant incident data.
- Store the information in the database for further analysis.

This mechanism minimizes abuse, detects suspicious activity, and ensures control over API access.

## Migrations

In addition to the main migration, when installing the package, you need to publish the migration for JWT:

```bash
php artisan vendor:publish --tag=api-guard-jwt-migration
```

Run migrations:

```bash
php artisan migrate
```

## Сlient management

Create a new JWT client using the Artisan command:

```bash
php artisan ag:jwt-client-create
```

⚠️ The secret key is stored only in encrypted form.
Be sure to save it immediately after creation — it cannot be recovered later.

You can also update some preferences using this command:

```bash
php artisan ag:jwt-client-update
```

## Routes

### Route & Controller Registration

In order for your client to receive an access token, you need to register a special route with the appropriate controller.

```php
use Garest\ApiGuard\Http\Controllers\TokenController;

Route::post('/api-guard/token', TokenController::class);
```

### Access token request

To obtain an access token via a specific route, you must send the `client_id` in a request to this endpoint.

```bash
curl --location 'https://example.com/api/api-guard/token' \
--header 'Content-Type: application/x-www-form-urlencoded' \
--data-urlencode 'grant_type=jwt_token' \
--data-urlencode 'client_id=YOUR_CLIENT_ID'
```

Example of a successful response:

```json
{
  "success": true,
  "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE3NzIzOTI3NDgsImV4cCI6MTc3MjM5NjM0OCwianRpIjoiODZmMWQwZTgzMmRiMTRhYyJ9.6yqMmXVebd-G-n6gkQk3MYqDbd6qB00D9jGjEiD_4kQ",
  "expires_in": 3600
}
```

### Route protection

To secure your routes and ensure that incoming requests are properly authenticated, use the `ag.jwt` middleware.

```php
Route::middleware('ag.jwt')->get('/orders', function () {
    return response()->json(['ok' => true]);
});
```

In addition, you can declare a middleware `ag.scopes` or `ag.scopes_or` to check permissions.

```php
Route::middleware(['ag.jwt', 'ag.scopes:read'])->get('/orders', function (Request $request) {
    return response()->json(['ok' => true]);
});

Route::middleware(['ag.jwt', 'ag.scopes_or:read,write'])->get('/orders', function (Request $request) {
    return response()->json(['ok' => true]);
});
```

### Request for protected routes

Example request for protected routes with `ag.jwt` middleware:

```bash
curl --location 'https://example.com/api/orders' \
--header 'Ag-Client-Id: YOUR_CLIENT_ID' \
--header 'Ag-Access-Token: YOUR_ACCESS_TOKEN'
```

## JWT client model

This package also allows you to access the JWT client model in your controllers.

Get client model:

```php
$credential = request()->getAuthCredential();
```

Check the availability of the client model:

```php
if (request()->hasAuthCredential()) {
    //
}
```
