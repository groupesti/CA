<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | ACME Server Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable the ACME server endpoints.
    |
    */
    'enabled' => env('CA_ACME_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The URL prefix for all ACME endpoints.
    |
    */
    'route_prefix' => env('CA_ACME_ROUTE_PREFIX', 'acme'),

    /*
    |--------------------------------------------------------------------------
    | Terms of Service URL
    |--------------------------------------------------------------------------
    |
    | URL to the ACME server's terms of service document.
    |
    */
    'terms_of_service_url' => env('CA_ACME_TOS_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Website URL
    |--------------------------------------------------------------------------
    |
    | URL to the ACME server operator's website.
    |
    */
    'website_url' => env('CA_ACME_WEBSITE_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Default CA ID
    |--------------------------------------------------------------------------
    |
    | The default Certificate Authority ID used for ACME operations.
    |
    */
    'ca_id' => env('CA_ACME_CA_ID', null),

    /*
    |--------------------------------------------------------------------------
    | External Account Required
    |--------------------------------------------------------------------------
    |
    | Whether external account binding is required for new accounts.
    |
    */
    'external_account_required' => env('CA_ACME_EXTERNAL_ACCOUNT_REQUIRED', false),

    /*
    |--------------------------------------------------------------------------
    | Challenge Types
    |--------------------------------------------------------------------------
    |
    | Supported ACME challenge types.
    |
    */
    'challenge_types' => ['http-01', 'dns-01', 'tls-alpn-01'],

    /*
    |--------------------------------------------------------------------------
    | Order Validity Hours
    |--------------------------------------------------------------------------
    |
    | How long an order remains valid before expiring.
    |
    */
    'order_validity_hours' => env('CA_ACME_ORDER_VALIDITY_HOURS', 168),

    /*
    |--------------------------------------------------------------------------
    | Authorization Validity Hours
    |--------------------------------------------------------------------------
    |
    | How long an authorization remains valid before expiring.
    |
    */
    'authorization_validity_hours' => env('CA_ACME_AUTHORIZATION_VALIDITY_HOURS', 168),

    /*
    |--------------------------------------------------------------------------
    | Challenge Timeout Seconds
    |--------------------------------------------------------------------------
    |
    | Maximum time to wait for challenge validation.
    |
    */
    'challenge_timeout_seconds' => env('CA_ACME_CHALLENGE_TIMEOUT_SECONDS', 30),

    /*
    |--------------------------------------------------------------------------
    | Nonce TTL Seconds
    |--------------------------------------------------------------------------
    |
    | Time-to-live for nonces in seconds.
    |
    */
    'nonce_ttl_seconds' => env('CA_ACME_NONCE_TTL_SECONDS', 3600),

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware applied to ACME routes.
    |
    */
    'middleware' => ['api'],

];
