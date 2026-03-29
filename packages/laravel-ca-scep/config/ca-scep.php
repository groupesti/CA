<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | SCEP Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable the SCEP protocol server globally.
    |
    */
    'enabled' => env('CA_SCEP_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The URL prefix for SCEP endpoints.
    |
    */
    'route_prefix' => env('CA_SCEP_ROUTE_PREFIX', 'scep'),

    /*
    |--------------------------------------------------------------------------
    | Default CA ID
    |--------------------------------------------------------------------------
    |
    | The default Certificate Authority ID to use for SCEP operations
    | when none is specified.
    |
    */
    'ca_id' => env('CA_SCEP_CA_ID'),

    /*
    |--------------------------------------------------------------------------
    | Challenge Password
    |--------------------------------------------------------------------------
    |
    | SCEP challenge password settings for enrollment authorization.
    |
    */
    'challenge_password_required' => env('CA_SCEP_CHALLENGE_REQUIRED', true),

    'challenge_password_ttl' => env('CA_SCEP_CHALLENGE_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Allowed Encryption Algorithms
    |--------------------------------------------------------------------------
    |
    | Symmetric encryption algorithms allowed for SCEP message enveloping.
    |
    */
    'allowed_algorithms' => ['aes-256-cbc', 'aes-128-cbc', '3des'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Hash Algorithms
    |--------------------------------------------------------------------------
    |
    | Hash algorithms allowed for SCEP message signing.
    |
    */
    'allowed_hash' => ['sha256', 'sha512'],

    /*
    |--------------------------------------------------------------------------
    | Auto Approve
    |--------------------------------------------------------------------------
    |
    | Whether to automatically approve SCEP enrollment requests.
    | When false, requests will be set to PENDING status.
    |
    */
    'auto_approve' => env('CA_SCEP_AUTO_APPROVE', false),

    /*
    |--------------------------------------------------------------------------
    | Capabilities
    |--------------------------------------------------------------------------
    |
    | SCEP capabilities advertised to clients via GetCACaps.
    |
    */
    'capabilities' => [
        'AES',
        'POSTPKIOperation',
        'SHA-256',
        'SHA-512',
        'DES3',
        'Renewal',
        'GetNextCACert',
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware to apply to SCEP routes. SCEP typically does not use
    | authentication middleware since devices enroll before having credentials.
    |
    */
    'routes' => [
        'enabled' => true,
        'middleware' => [],
    ],

];
