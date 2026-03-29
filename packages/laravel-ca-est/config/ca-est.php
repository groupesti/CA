<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | EST Protocol Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable EST protocol endpoints globally.
    |
    */

    'enabled' => env('CA_EST_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The URL prefix for EST endpoints, per RFC 7030 Section 3.2.2.
    | Default: .well-known/est
    |
    */

    'route_prefix' => env('CA_EST_ROUTE_PREFIX', '.well-known/est'),

    /*
    |--------------------------------------------------------------------------
    | Default CA
    |--------------------------------------------------------------------------
    |
    | The default Certificate Authority UUID to use for EST operations
    | when no CA label is specified in the URL.
    |
    */

    'ca_id' => env('CA_EST_CA_ID'),

    /*
    |--------------------------------------------------------------------------
    | Authentication Settings
    |--------------------------------------------------------------------------
    */

    'require_client_cert' => env('CA_EST_REQUIRE_CLIENT_CERT', false),

    'allow_basic_auth' => env('CA_EST_ALLOW_BASIC_AUTH', true),

    'allow_certificate_auth' => env('CA_EST_ALLOW_CERTIFICATE_AUTH', true),

    /*
    |--------------------------------------------------------------------------
    | Server-Side Key Generation
    |--------------------------------------------------------------------------
    |
    | Enable server-side key generation (/serverkeygen endpoint).
    |
    */

    'server_keygen_enabled' => env('CA_EST_SERVER_KEYGEN_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Default Key Algorithm
    |--------------------------------------------------------------------------
    |
    | Default algorithm for server-side key generation.
    | Supported: rsa-2048, rsa-4096, ecdsa-p256, ecdsa-p384, ecdsa-p521, ed25519
    |
    */

    'default_key_algorithm' => env('CA_EST_DEFAULT_KEY_ALGORITHM', 'rsa-2048'),

    /*
    |--------------------------------------------------------------------------
    | CMC (Certificate Management over CMS)
    |--------------------------------------------------------------------------
    |
    | Enable Full CMC support (/fullcmc endpoint).
    |
    */

    'cmc_enabled' => env('CA_EST_CMC_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware applied to EST routes.
    |
    */

    'middleware' => [
        'api',
    ],

    /*
    |--------------------------------------------------------------------------
    | Certificate Validity
    |--------------------------------------------------------------------------
    |
    | Default validity period (in days) for certificates issued via EST.
    |
    */

    'default_validity_days' => env('CA_EST_DEFAULT_VALIDITY_DAYS', 365),

    /*
    |--------------------------------------------------------------------------
    | Enrollment Record Retention
    |--------------------------------------------------------------------------
    |
    | Number of days to retain enrollment records before cleanup.
    |
    */

    'enrollment_retention_days' => env('CA_EST_ENROLLMENT_RETENTION_DAYS', 90),

];
