<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Validity Period
    |--------------------------------------------------------------------------
    |
    | The default number of days a certificate is valid for when no explicit
    | validity period is specified during issuance.
    |
    */
    'default_validity_days' => (int) env('CA_CRT_DEFAULT_VALIDITY_DAYS', 365),

    /*
    |--------------------------------------------------------------------------
    | Renewal Threshold
    |--------------------------------------------------------------------------
    |
    | Number of days before expiry when a certificate becomes eligible for
    | renewal and starts showing up in expiring certificate scans.
    |
    */
    'renewal_threshold_days' => (int) env('CA_CRT_RENEWAL_THRESHOLD_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Auto Renew
    |--------------------------------------------------------------------------
    |
    | Whether certificates should be automatically renewed when they are
    | approaching expiration within the renewal threshold window.
    |
    */
    'auto_renew' => (bool) env('CA_CRT_AUTO_RENEW', false),

    /*
    |--------------------------------------------------------------------------
    | Default Hash Algorithm
    |--------------------------------------------------------------------------
    |
    | The default hash algorithm used for certificate signing when no
    | explicit algorithm is specified.
    |
    */
    'default_hash' => env('CA_CRT_DEFAULT_HASH', 'sha256'),

    /*
    |--------------------------------------------------------------------------
    | Microsoft Compatibility
    |--------------------------------------------------------------------------
    |
    | Enable Microsoft AD CS compatible extensions and OIDs in issued
    | certificates. Required for Active Directory integration.
    |
    */
    'microsoft_compatibility' => (bool) env('CA_CRT_MICROSOFT_COMPATIBILITY', false),

    /*
    |--------------------------------------------------------------------------
    | Export Formats
    |--------------------------------------------------------------------------
    |
    | The list of allowed export formats for certificate export operations.
    |
    */
    'export_formats' => ['pem', 'der', 'pkcs7'],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'enabled' => (bool) env('CA_CRT_ROUTES_ENABLED', true),
        'prefix' => env('CA_CRT_ROUTES_PREFIX', 'api/ca/certificates'),
        'middleware' => ['api'],
    ],

];
