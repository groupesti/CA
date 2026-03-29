<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Enable caching of OID lookups for improved performance.
    |
    */
    'cache_enabled' => env('CA_OID_CACHE_ENABLED', true),

    'cache_ttl' => (int) env('CA_OID_CACHE_TTL', 86400),

    /*
    |--------------------------------------------------------------------------
    | Custom OID Configuration
    |--------------------------------------------------------------------------
    |
    | Whether to allow registration of custom/enterprise OIDs.
    |
    */
    'allow_custom_oids' => env('CA_OID_ALLOW_CUSTOM', true),

    /*
    |--------------------------------------------------------------------------
    | Auto-Register on Use
    |--------------------------------------------------------------------------
    |
    | When enabled, unknown OIDs encountered during resolution will be
    | automatically registered in the database.
    |
    */
    'auto_register_on_use' => env('CA_OID_AUTO_REGISTER', false),

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware applied to the OID API routes. By default, routes require
    | authentication via the 'api' guard.
    |
    */
    'route_middleware' => ['auth:api'],

];
