<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Key Algorithm
    |--------------------------------------------------------------------------
    */
    'default_algorithm' => env('CA_KEY_DEFAULT_ALGORITHM', 'rsa-4096'),

    /*
    |--------------------------------------------------------------------------
    | Default RSA Bits
    |--------------------------------------------------------------------------
    */
    'default_rsa_bits' => (int) env('CA_KEY_DEFAULT_RSA_BITS', 4096),

    /*
    |--------------------------------------------------------------------------
    | Default EC Curve
    |--------------------------------------------------------------------------
    */
    'default_curve' => env('CA_KEY_DEFAULT_CURVE', 'prime256v1'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Strategy
    |--------------------------------------------------------------------------
    |
    | The strategy used to encrypt private keys at rest.
    | Supported: "laravel"
    |
    */
    'encryption_strategy' => env('CA_KEY_ENCRYPTION_STRATEGY', 'laravel'),

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'driver' => env('CA_KEY_STORAGE_DRIVER', 'database'),
        'disk' => env('CA_KEY_STORAGE_DISK', 'local'),
        'path' => env('CA_KEY_STORAGE_PATH', 'ca-keys'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Key Rotation Settings
    |--------------------------------------------------------------------------
    */
    'key_rotation' => [
        'auto_rotate' => (bool) env('CA_KEY_AUTO_ROTATE', false),
        'rotation_days' => (int) env('CA_KEY_ROTATION_DAYS', 365),
        'keep_old_keys' => (bool) env('CA_KEY_KEEP_OLD_KEYS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'enabled' => (bool) env('CA_KEY_ROUTES_ENABLED', true),
        'prefix' => env('CA_KEY_ROUTES_PREFIX', 'api/ca/keys'),
        'middleware' => ['api'],
    ],

];
