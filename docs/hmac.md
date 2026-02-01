# Lightweight HMAC Authentication

This is a simplified HMAC authentication method. It protects API endpoints by verifying requests from trusted clients and helps detect and block malicious access attempts.

## Managing Access Keys

Create a new HMAC access key using the Artisan command:

```bash
php artisan ag:hmac-key-create
```

⚠️ The Secret Key secret key is stored only as a hash.
Be sure to save it immediately after creation — it cannot be recovered later.

You can also update some preferences using this command:

```bash
php artisan ag:hmac-key-update
```

## How HMAC Authentication Works

Each API request must contain a set of HMAC headers that allow the server to verify its authenticity and integrity.

| Header          | Description                   |
| --------------- | ----------------------------- |
| `Ag-Access-Key` | Public access key             |
| `Ag-Timestamp`  | Unix timestamp (UTC)          |
| `Ag-Nonce`      | Unique random string          |
| `Ag-Signature`  | Base64-encoded HMAC signature |

_If necessary, standard header names can be customized using the configuration file._

### Canonical String

The HMAC signature is generated from a canonical string consisting of the following values:

```
METHOD\n
/PATH\n
TIMESTAMP\n
NONCE
```

Example:

```
POST
/api/orders
1769684252
0J5T8aQTylPdGUwN
```

### Signature Algorithm

```text
HMAC-SHA256(
  canonical_string,
  SHA256(secret_key)
)
```

The result is Base64-encoded and sent as `Ag-Signature`.

### Postman Example (Pre-request Script)

The following pre-request script automatically generates valid HMAC headers for any request in Postman:

```js
const accessKey = "YOUR_ACCESS_KEY";
const plainSecret = "YOUR_SECRET_KEY";

const timestamp = Math.floor(Date.now() / 1000).toString();
const nonce = Math.random().toString(36).substring(2, 18);
const method = pm.request.method.toUpperCase();
const path = pm.request.url.getPath();

const canonicalString = `${method}\n${path}\n${timestamp}\n${nonce}`;
const derivedKey = CryptoJS.SHA256(plainSecret).toString();
const signature = CryptoJS.HmacSHA256(
  canonicalString,
  CryptoJS.enc.Hex.parse(derivedKey),
).toString(CryptoJS.enc.Base64);

pm.request.headers.add({ key: "Ag-Access-Key", value: accessKey });
pm.request.headers.add({ key: "Ag-Timestamp", value: timestamp });
pm.request.headers.add({ key: "Ag-Nonce", value: nonce });
pm.request.headers.add({ key: "Ag-Signature", value: signature });
```

### Building HMAC Headers in Laravel

You can generate HMAC headers programmatically in Laravel using the `build` method in the `Hmac` class:

```php
use Garest\ApiGuard\Hmac;

$hmac = new Hmac();
$hmacData = $hmac->build(
    'YOUR_ACCESS_KEY',
    'YOUR_SECRET_KEY',
    'METHOD',
    'PATH'
);

$response = Http::withHeaders($hmacData->toArray())->post(...);
```

## Middleware Usage

### Registration

Before using HMAC authentication, you must register the middleware.

In Laravel 12, this is done in `bootstrap/app.php`:

```php
use Garest\ApiGuard\Http\Middleware\HmacMiddleware;

withMiddleware(function (Middleware $middleware) {
    $middleware->alias(['ag.hmac' => HmacMiddleware::class]);
})
```

### Route

```php
Route::middleware('ag.hmac')->get('/orders', function () {
    return response()->json(['ok' => true]);
});
```

Middleware with scopes

```php
Route::middleware('ag.hmac:read,write')->get('/orders', function () {
    return response()->json(['ok' => true]);
});
```

### Accessing the Authenticated Key

Retrieve the `HmacKey` model instance from the request:

```php
$hmacKey = request()->hmacKey();
```

Check authentication:

```php
if (request()->hasHmacKey()) {
    //
}
```
