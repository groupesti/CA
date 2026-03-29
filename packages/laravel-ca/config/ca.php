<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy Configuration
    |--------------------------------------------------------------------------
    */
    'tenant' => [
        'enabled' => env('CA_TENANT_ENABLED', false),
        'resolver' => env('CA_TENANT_RESOLVER', \CA\Contracts\TenantResolverInterface::class),
        'column' => 'tenant_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'default' => env('CA_STORAGE_DRIVER', 'database'),

        'drivers' => [
            'database' => [
                'driver' => 'database',
                'connection' => env('CA_DB_CONNECTION', null),
                'table' => 'ca_storage',
            ],

            'filesystem' => [
                'driver' => 'filesystem',
                'disk' => env('CA_STORAGE_DISK', 'local'),
                'base_path' => env('CA_STORAGE_PATH', 'ca-storage'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Encryption Configuration
    |--------------------------------------------------------------------------
    */
    'encryption' => [
        'default' => env('CA_ENCRYPTION_STRATEGY', 'laravel'),

        'strategies' => [
            'laravel' => [
                'driver' => 'laravel',
            ],

            'passphrase' => [
                'driver' => 'passphrase',
                'cipher' => 'aes-256-cbc',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Certificate Settings
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'key_algorithm' => env('CA_DEFAULT_KEY_ALGORITHM', 'rsa-4096'),
        'hash_algorithm' => env('CA_DEFAULT_HASH_ALGORITHM', 'sha256'),

        'validity' => [
            'root_ca' => (int) env('CA_ROOT_CA_VALIDITY_DAYS', 3650),
            'intermediate_ca' => (int) env('CA_INTERMEDIATE_CA_VALIDITY_DAYS', 1825),
            'server_tls' => (int) env('CA_SERVER_TLS_VALIDITY_DAYS', 397),
            'client_mtls' => (int) env('CA_CLIENT_MTLS_VALIDITY_DAYS', 365),
            'code_signing' => (int) env('CA_CODE_SIGNING_VALIDITY_DAYS', 365),
            'smime' => (int) env('CA_SMIME_VALIDITY_DAYS', 365),
            'domain_controller' => (int) env('CA_DOMAIN_CONTROLLER_VALIDITY_DAYS', 365),
            'user' => (int) env('CA_USER_VALIDITY_DAYS', 365),
            'computer' => (int) env('CA_COMPUTER_VALIDITY_DAYS', 365),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Serial Number Generation
    |--------------------------------------------------------------------------
    */
    'serial_number' => [
        'generator' => env('CA_SERIAL_GENERATOR', 'random'),
        'bytes' => (int) env('CA_SERIAL_BYTES', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | CRL (Certificate Revocation List) Configuration
    |--------------------------------------------------------------------------
    */
    'crl' => [
        'enabled' => env('CA_CRL_ENABLED', true),
        'lifetime_hours' => (int) env('CA_CRL_LIFETIME_HOURS', 24),
        'overlap_hours' => (int) env('CA_CRL_OVERLAP_HOURS', 12),
        'distribution_points' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | OCSP Configuration
    |--------------------------------------------------------------------------
    */
    'ocsp' => [
        'enabled' => env('CA_OCSP_ENABLED', true),
        'responder_url' => env('CA_OCSP_RESPONDER_URL'),
        'response_lifetime_minutes' => (int) env('CA_OCSP_RESPONSE_LIFETIME_MINUTES', 60),
        'nonce_required' => env('CA_OCSP_NONCE_REQUIRED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    */
    'api' => [
        'enabled' => env('CA_API_ENABLED', true),
        'prefix' => env('CA_API_PREFIX', 'api/ca'),
        'middleware' => explode(',', env('CA_API_MIDDLEWARE', 'api,ca.auth')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Microsoft OID Extensions
    |--------------------------------------------------------------------------
    */
    'microsoft' => [
        'oids' => [
            'certificate_template_name' => '1.3.6.1.4.1.311.20.2',
            'certificate_template_v2' => '1.3.6.1.4.1.311.21.7',
            'application_policies' => '1.3.6.1.4.1.311.21.10',
            'enrollment_agent' => '1.3.6.1.4.1.311.20.2.1',
            'smart_card_logon' => '1.3.6.1.4.1.311.20.2.2',
            'ntds_ca_security' => '1.3.6.1.4.1.311.25.2',
            'domain_controller' => '1.3.6.1.4.1.311.20.2',
            'kdc_authentication' => '1.3.6.1.5.2.3.5',
        ],
    ],

];
