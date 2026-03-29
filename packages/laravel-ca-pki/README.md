# Laravel CA PKI

> Complete PKI system for Laravel -- installs all CA packages with a single command.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/groupesti/laravel-ca-pki.svg)](https://packagist.org/packages/groupesti/laravel-ca-pki)
[![PHP Version](https://img.shields.io/badge/php-8.4%2B-blue)](https://www.php.net/releases/8.4/en.php)
[![Laravel](https://img.shields.io/badge/laravel-12.x%20|%2013.x-red)](https://laravel.com)
[![License](https://img.shields.io/github/license/groupesti/laravel-ca-pki)](LICENSE.md)

## Requirements

- **PHP** 8.4 or higher
- **Laravel** 12.x or 13.x
- **OpenSSL** PHP extension
- **Composer** 2.x

## Installation

Install the entire PKI system with a single command:

```bash
composer require groupesti/laravel-ca-pki
```

This meta-package installs all 15 packages that make up the Laravel CA ecosystem. There is no service provider or configuration file for this package itself -- each sub-package registers its own service provider via Laravel's package auto-discovery.

After installation, publish configuration files for the packages you need:

```bash
# Publish all CA configurations at once
php artisan vendor:publish --provider="Groupesti\LaravelCa\LaravelCaServiceProvider"
php artisan vendor:publish --provider="Groupesti\LaravelCaKey\LaravelCaKeyServiceProvider"
php artisan vendor:publish --provider="Groupesti\LaravelCaCrt\LaravelCaCrtServiceProvider"
# ... and so on for each package you want to configure
```

Run the migrations:

```bash
php artisan migrate
```

## Included Packages

This meta-package requires all of the following packages:

| Package | Description |
|---|---|
| [`groupesti/laravel-ca`](https://github.com/groupesti/laravel-ca) | Modular Certificate Authority system for Laravel -- Core Package |
| [`groupesti/laravel-ca-key`](https://github.com/groupesti/laravel-ca-key) | Key management for Laravel CA -- RSA, ECDSA, Ed25519 |
| [`groupesti/laravel-ca-csr`](https://github.com/groupesti/laravel-ca-csr) | CSR (Certificate Signing Request) management for Laravel CA |
| [`groupesti/laravel-ca-crt`](https://github.com/groupesti/laravel-ca-crt) | X.509 certificate management for Laravel CA |
| [`groupesti/laravel-ca-crl`](https://github.com/groupesti/laravel-ca-crl) | CRL (Certificate Revocation List) management for Laravel CA |
| [`groupesti/laravel-ca-ocsp`](https://github.com/groupesti/laravel-ca-ocsp) | OCSP responder for Laravel CA using pure PHP (phpseclib v3) |
| [`groupesti/laravel-ca-pkcs12`](https://github.com/groupesti/laravel-ca-pkcs12) | PKCS#12 (PFX) bundle management for Laravel CA |
| [`groupesti/laravel-ca-oid`](https://github.com/groupesti/laravel-ca-oid) | OID (Object Identifier) registry for the Laravel CA system |
| [`groupesti/laravel-ca-acme`](https://github.com/groupesti/laravel-ca-acme) | ACME protocol (RFC 8555) implementation for Laravel CA |
| [`groupesti/laravel-ca-scep`](https://github.com/groupesti/laravel-ca-scep) | SCEP (Simple Certificate Enrollment Protocol) server for Laravel CA |
| [`groupesti/laravel-ca-est`](https://github.com/groupesti/laravel-ca-est) | EST (Enrollment over Secure Transport) protocol for Laravel CA (RFC 7030) |
| [`groupesti/laravel-ca-tsa`](https://github.com/groupesti/laravel-ca-tsa) | RFC 3161 Time-Stamp Authority for Laravel CA |
| [`groupesti/laravel-ca-cms`](https://github.com/groupesti/laravel-ca-cms) | CMS/PKCS#7 cryptographic message syntax for Laravel CA (RFC 5652) |
| [`groupesti/laravel-ca-policy`](https://github.com/groupesti/laravel-ca-policy) | Certificate Policy, CPS, name constraints, and issuance rules for Laravel CA |
| [`groupesti/laravel-ca-ui`](https://github.com/groupesti/laravel-ca-ui) | Admin dashboard UI for Laravel CA |

## Configuration

This meta-package has no configuration of its own. Each sub-package ships its own configuration file. Refer to the individual package documentation for configuration details:

- **Core CA** (`config/ca.php`) -- Root and intermediate CA settings, storage driver, HSM configuration.
- **Key** (`config/ca-key.php`) -- Key generation defaults, algorithm preferences, key storage backend.
- **CSR** (`config/ca-csr.php`) -- CSR validation rules, default subject fields.
- **CRT** (`config/ca-crt.php`) -- Certificate profiles, validity periods, extension defaults.
- **CRL** (`config/ca-crl.php`) -- CRL generation schedule, distribution points.
- **OCSP** (`config/ca-ocsp.php`) -- OCSP responder URL, signing certificate, response caching.
- **PKCS12** (`config/ca-pkcs12.php`) -- PFX export defaults, encryption algorithm.
- **OID** (`config/ca-oid.php`) -- Custom OID registrations, enterprise OID arc.
- **ACME** (`config/ca-acme.php`) -- ACME server endpoints, challenge types, account settings.
- **SCEP** (`config/ca-scep.php`) -- SCEP enrollment settings, challenge passwords.
- **EST** (`config/ca-est.php`) -- EST server configuration, authentication methods.
- **TSA** (`config/ca-tsa.php`) -- Timestamp authority settings, hash algorithms, serial number source.
- **CMS** (`config/ca-cms.php`) -- CMS signing and encryption defaults.
- **Policy** (`config/ca-policy.php`) -- Certificate policies, CPS URI, name constraints, issuance rules.
- **UI** (`config/ca-ui.php`) -- Dashboard route prefix, middleware, theme settings.

## Quick Start

After installing the meta-package and running migrations, you can begin using the full PKI system:

```php
use Groupesti\LaravelCaKey\Facades\CaKey;
use Groupesti\LaravelCaCsr\Facades\CaCsr;
use Groupesti\LaravelCaCrt\Facades\CaCrt;

// Generate a key pair
$key = CaKey::generate(algorithm: 'EC', curve: 'P-384');

// Create a CSR
$csr = CaCsr::create(
    keyPair: $key,
    subject: [
        'commonName' => 'example.com',
        'organizationName' => 'My Organization',
    ],
);

// Issue a certificate
$certificate = CaCrt::issue(csr: $csr, profile: 'server');
```

## Testing

Since this is a meta-package with no code of its own, tests reside in the individual sub-packages. To run the full test suite across all packages, use the workspace-level test command:

```bash
# Run tests for a specific package
cd packages/laravel-ca-key
./vendor/bin/pest

# Run code formatting check
./vendor/bin/pint --test

# Run static analysis
./vendor/bin/phpstan analyse
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details. Contributions should be made to the individual sub-packages, not this meta-package.

## Security

If you discover a security vulnerability, please review our [Security Policy](SECURITY.md).

## Credits

- [Groupe STI](https://github.com/groupesti)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
