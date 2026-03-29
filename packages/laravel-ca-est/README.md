# Laravel CA EST

> EST (Enrollment over Secure Transport) protocol implementation for Laravel CA, fully compliant with RFC 7030.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/groupesti/laravel-ca-est.svg)](https://packagist.org/packages/groupesti/laravel-ca-est)
[![PHP Version](https://img.shields.io/badge/php-8.4%2B-blue)](https://www.php.net/releases/8.4/en.php)
[![Laravel](https://img.shields.io/badge/laravel-12.x%20|%2013.x-red)](https://laravel.com)
[![Tests](https://github.com/groupesti/laravel-ca-est/actions/workflows/tests.yml/badge.svg)](https://github.com/groupesti/laravel-ca-est/actions/workflows/tests.yml)
[![License](https://img.shields.io/github/license/groupesti/laravel-ca-est)](LICENSE.md)

## Requirements

- PHP 8.4+
- Laravel 12.x or 13.x
- `groupesti/laravel-ca` ^1.0
- `groupesti/laravel-ca-crt` ^1.0
- `groupesti/laravel-ca-csr` ^1.0
- `groupesti/laravel-ca-key` ^1.0
- `phpseclib/phpseclib` ^3.0
- OpenSSL PHP extension

## Installation

```bash
composer require groupesti/laravel-ca-est
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=ca-est-config
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag=ca-est-migrations
php artisan migrate
```

## Configuration

The configuration file is published to `config/ca-est.php`. Below is a description of each key:

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `enabled` | `bool` | `true` | Enable or disable EST protocol endpoints globally. |
| `route_prefix` | `string` | `.well-known/est` | URL prefix for EST endpoints per RFC 7030 Section 3.2.2. |
| `ca_id` | `string\|null` | `null` | Default Certificate Authority UUID when no CA label is specified. |
| `require_client_cert` | `bool` | `false` | Require TLS client certificate for all endpoints. |
| `allow_basic_auth` | `bool` | `true` | Allow HTTP Basic authentication for enrollment. |
| `allow_certificate_auth` | `bool` | `true` | Allow TLS client certificate authentication. |
| `server_keygen_enabled` | `bool` | `true` | Enable the `/serverkeygen` endpoint. |
| `default_key_algorithm` | `string` | `rsa-2048` | Default algorithm for server-side key generation. Supported: `rsa-2048`, `rsa-4096`, `ecdsa-p256`, `ecdsa-p384`, `ecdsa-p521`, `ed25519`. |
| `cmc_enabled` | `bool` | `false` | Enable Full CMC support (`/fullcmc` endpoint). |
| `middleware` | `array` | `['api']` | Middleware applied to EST routes. |
| `default_validity_days` | `int` | `365` | Default validity period (in days) for certificates issued via EST. |
| `enrollment_retention_days` | `int` | `90` | Number of days to retain enrollment records before cleanup. |

Environment variables follow the pattern `CA_EST_*` (e.g. `CA_EST_ENABLED`, `CA_EST_CA_ID`).

## Usage

### Setup

Run the setup command to initialize EST for a Certificate Authority:

```bash
php artisan ca-est:setup
```

### API Endpoints

All endpoints are served under `/.well-known/est/{label}/` where `{label}` identifies the Certificate Authority by UUID or alias.

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| GET | `/{label}/cacerts` | No | Retrieve the CA certificate chain (PKCS#7). |
| GET | `/{label}/csrattrs` | No | Get suggested CSR attributes. |
| POST | `/{label}/simpleenroll` | Yes | Submit a PKCS#10 CSR for enrollment. |
| POST | `/{label}/simplereenroll` | Yes (client cert) | Re-enroll with a new CSR. |
| POST | `/{label}/serverkeygen` | Yes | Server-side key generation and enrollment. |
| POST | `/{label}/fullcmc` | Yes | Full CMC request (if enabled). |

### Programmatic Usage

```php
use CA\Est\Contracts\EstServerInterface;
use CA\Models\CertificateAuthority;

$estServer = app(EstServerInterface::class);
$ca = CertificateAuthority::find($caId);

// Get CA certificates
$pkcs7 = $estServer->getCaCerts(ca: $ca);

// Simple enrollment
$certPkcs7 = $estServer->simpleEnroll(
    ca: $ca,
    csrBase64: $csrBase64,
    auth: ['identity' => 'user@example.com'],
);

// Re-enrollment
$certPkcs7 = $estServer->simpleReenroll(
    ca: $ca,
    csrBase64: $csrBase64,
    clientCert: ['identity' => 'user@example.com', 'pem' => $clientCertPem],
);

// Server-side key generation
$result = $estServer->serverKeyGen(
    ca: $ca,
    csrBase64: $csrBase64,
    auth: ['identity' => 'user@example.com'],
);
// $result['private_key'] — PEM-encoded PKCS#8 private key
// $result['certificate'] — PKCS#7 certificate chain

// Get CSR attributes
$csrAttrs = $estServer->getCsrAttrs(ca: $ca);
```

### Events

The package dispatches the following events:

- `EstEnrollmentRequested` -- fired when any enrollment request is received.
- `EstCertificateIssued` -- fired when a certificate is successfully issued.
- `EstReenrollmentCompleted` -- fired when a re-enrollment completes.
- `EstServerKeyGenerated` -- fired when server-side key generation completes.

### Artisan Commands

| Command | Description |
|---------|-------------|
| `ca-est:setup` | Initialize EST for a Certificate Authority. |
| `ca-est:enrollment-list` | List enrollment records. |
| `ca-est:cleanup` | Remove expired enrollment records. |

## Testing

```bash
./vendor/bin/pest
./vendor/bin/pint --test
./vendor/bin/phpstan analyse
```

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover a security vulnerability, please see [SECURITY.md](SECURITY.md). Do **not** open a public issue.

## Credits

- [GroupESTI](https://github.com/groupesti)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [LICENSE.md](LICENSE.md) for more information.
