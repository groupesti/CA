<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default CSR Validity (days)
    |--------------------------------------------------------------------------
    |
    | How many days a pending CSR remains valid before it expires.
    |
    */
    'default_validity_days' => (int) env('CA_CSR_VALIDITY_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Auto-Approve
    |--------------------------------------------------------------------------
    |
    | When enabled, newly created CSRs are automatically approved.
    |
    */
    'auto_approve' => (bool) env('CA_CSR_AUTO_APPROVE', false),

    /*
    |--------------------------------------------------------------------------
    | Required DN Fields
    |--------------------------------------------------------------------------
    |
    | Distinguished Name fields that must be present in every CSR.
    |
    */
    'required_dn_fields' => ['CN'],

    /*
    |--------------------------------------------------------------------------
    | Allowed SAN Types
    |--------------------------------------------------------------------------
    |
    | Subject Alternative Name types that may appear in CSRs.
    |
    */
    'allowed_san_types' => ['dns', 'ip', 'email', 'uri'],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'enabled' => (bool) env('CA_CSR_ROUTES_ENABLED', true),
        'prefix' => env('CA_CSR_ROUTES_PREFIX', 'api/ca/csrs'),
        'middleware' => ['api'],
    ],

];
