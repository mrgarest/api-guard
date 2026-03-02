<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ApiGuard
    |--------------------------------------------------------------------------
    |
    | Here you can configure the library.
    |
    */

    // Data encryption key in the database
    'key' => env('API_GUARD_KEY', '4c979d9fb245623ac3d1c3880c46ae0b'),

    // Model caching time (in seconds) (null - disables cache)
    'model_cache_ttl' => 300,

    // Allows you to use the client's IP address from the Cloudflare header
    'cloudflare_ip' => false,

    /*
    |--------------------------------------------------------------------------
    | Failed authentication attempts
    |--------------------------------------------------------------------------
    |
    | Here you can configure the behavior in case of failed client authentication.
    |
    */
    'failed_auth' => [
        // The maximum number of failed authentication attempts a client can make in 60 seconds
        'max_attempts' => 3,
        
        // Time (in seconds) of blocking when the number of failed authentication attempts is exceeded
        'blocked_time' => 600
    ],

    /*
    |--------------------------------------------------------------------------
    | JWT
    |--------------------------------------------------------------------------
    |
    | Configuration for JWT authentication.
    |
    */
    'jwt' => [
        // Time after which the token expires (in seconds)
        'expires_in' => 3600,

        // The maximum number of tokens a client can receive in 60 seconds
        'max_tokens' => 10,

        // Time (in seconds) of blocking when the number of tokens received is exceeded
        'blocked_time' => 600
    ],

    /*
    |--------------------------------------------------------------------------
    | HMAC
    |--------------------------------------------------------------------------
    |
    | Configuration for HMAC authentication.
    |
    */
    'hmac' => [
        // The permissible time difference between the client and server timestamps (in seconds)
        'time_window' => 60,

        // The lifetime of the nonce in the cache (in seconds). After that, the request can be reused with a new nonce (null - disables the cache and this function)
        'nonce_ttl' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Log
    |--------------------------------------------------------------------------
    |
    | Configuring logging to the database.
    |
    */
    'log' => [
        'auth_failed' => true
    ],

    /*
    |--------------------------------------------------------------------------
    | Header Names
    |--------------------------------------------------------------------------
    |
    | Names of HTTP headers used for authentication.
    |
    | client_id - Client identifier.
    | access_token - Access token.
    | access_key - Public access key.
    | timestamp - Unix request timestamp.
    | nonce - unique string for protection against replay attacks.
    | signature - HMAC signature of the request.
    |
    */
    'headers' => [
        'client_id' => 'Ag-Client-Id',
        'access_token' => 'Ag-Access-Token',
        'access_key' => 'Ag-Access-Key',
        'timestamp' => 'Ag-Timestamp',
        'nonce' => 'Ag-Nonce',
        'signature' => 'Ag-Signature'
    ]
];
