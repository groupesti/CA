<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | TSA Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable the Time-Stamp Authority service.
    |
    */
    'enabled' => env('CA_TSA_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The URL prefix for TSA endpoints.
    |
    */
    'route_prefix' => env('CA_TSA_ROUTE_PREFIX', 'tsa'),

    /*
    |--------------------------------------------------------------------------
    | Default Certificate Authority
    |--------------------------------------------------------------------------
    |
    | The UUID of the CA used for timestamping by default.
    |
    */
    'ca_id' => env('CA_TSA_CA_ID'),

    /*
    |--------------------------------------------------------------------------
    | Default Hash Algorithm
    |--------------------------------------------------------------------------
    |
    | The default hash algorithm for timestamp tokens.
    |
    */
    'default_hash' => env('CA_TSA_DEFAULT_HASH', 'sha256'),

    /*
    |--------------------------------------------------------------------------
    | TSA Policy OID
    |--------------------------------------------------------------------------
    |
    | The OID identifying this TSA's policy. Replace with your own policy OID.
    |
    */
    'policy_oid' => env('CA_TSA_POLICY_OID', '1.2.3.4.1'),

    /*
    |--------------------------------------------------------------------------
    | Ordering
    |--------------------------------------------------------------------------
    |
    | Whether the TSA guarantees ordering of timestamps.
    |
    */
    'ordering' => (bool) env('CA_TSA_ORDERING', false),

    /*
    |--------------------------------------------------------------------------
    | Include TSA Certificate
    |--------------------------------------------------------------------------
    |
    | Whether to include the TSA signing certificate in the response by default.
    |
    */
    'include_tsa_cert' => (bool) env('CA_TSA_INCLUDE_CERT', true),

    /*
    |--------------------------------------------------------------------------
    | Accuracy
    |--------------------------------------------------------------------------
    |
    | The accuracy of the TSA clock.
    |
    */
    'accuracy_seconds' => (int) env('CA_TSA_ACCURACY_SECONDS', 1),
    'accuracy_millis' => (int) env('CA_TSA_ACCURACY_MILLIS', 0),
    'accuracy_micros' => (int) env('CA_TSA_ACCURACY_MICROS', 0),

    /*
    |--------------------------------------------------------------------------
    | Nonce Required
    |--------------------------------------------------------------------------
    |
    | Whether a nonce is required in timestamp requests.
    |
    */
    'nonce_required' => (bool) env('CA_TSA_NONCE_REQUIRED', false),

    /*
    |--------------------------------------------------------------------------
    | Serial Number Bits
    |--------------------------------------------------------------------------
    |
    | The number of random bits for timestamp serial numbers.
    |
    */
    'serial_number_bits' => (int) env('CA_TSA_SERIAL_BITS', 64),

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware applied to TSA routes.
    |
    */
    'middleware' => ['api'],

];
