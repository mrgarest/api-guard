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

    // The permissible time difference between the client and server timestamps (in seconds)
    'time_window' => 60,

    // The lifetime of the nonce in the cache (in seconds). After that, the request can be reused with a new nonce (null - disables the cache and this function)
    'nonce_ttl' => 60,

    // Time-to-live (in seconds) for caching API clients (null - disables cache)
    'client_cache_ttl' => 300,

    // Allows you to use the client's IP address from the Cloudflare header
    'cloudflare_ip' => false,

    /*
    |--------------------------------------------------------------------------
    | Authentication Attempt Limits
    |--------------------------------------------------------------------------
    |
    | Limits failed authentication attempts to protect against brute-force attacks.
    | When the limit is exceeded, further attempts are blocked for a specified duration.
    |
    */
    'auth_attempt_limits' => [
        'enabled' => false,

        // Maximum failed attempts before limiting
        'max_attempts' => 3,

        // Time window (in seconds) during which failed attempts are counted
        'decay' => 60,

        // Lock duration after limit exceeded (seconds)
        'lock' => 300
    ],

    /*
    |--------------------------------------------------------------------------
    | Log
    |--------------------------------------------------------------------------
    |
    | Configuring logging to the database
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
    | access_key - public access key.
    | timestamp - Unix request timestamp
    | nonce - unique string for protection against replay attacks
    | signature - HMAC signature of the request
    |
    */
    'headers' => [
        'access_key' => 'Ag-Access-Key',
        'timestamp' => 'Ag-Timestamp',
        'nonce' => 'Ag-Nonce',
        'signature' => 'Ag-Signature'
    ]
];
