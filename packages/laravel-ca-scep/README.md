# Laravel CA SCEP

> SCEP (Simple Certificate Enrollment Protocol) server implementation for Laravel CA. Enables automated device certificate enrollment and renewal using RFC 8894-compliant SCEP operations, powered entirely by phpseclib v3 with no OpenSSL dependency.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/groupesti/laravel-ca-scep.svg)](https://packagist.org/packages/groupesti/laravel-ca-scep)
[![PHP Version](https://img.shields.io/badge/php-8.4%2B-blue)](https://www.php.net/releases/8.4/en.php)
[![Laravel](https://img.shields.io/badge/laravel-12.x%20|%2013.x-red)](https://laravel.com)
[![Tests](https://github.com/groupesti/laravel-ca-scep/actions/workflows/tests.yml/badge.svg)](https://github.com/groupesti/laravel-ca-scep/actions/workflows/tests.yml)
[![License](https://img.shields.io/github/license/groupesti/laravel-ca-scep)](LICENSE.md)

## Requirements

- **PHP** 8.4 or higher
- **Laravel** 12.x or 13.x
- **groupesti/laravel-ca** ^1.0
- **groupesti/laravel-ca-crt** ^1.0
- **groupesti/laravel-ca-csr** ^1.0
- **groupesti/laravel-ca-key** ^1.0
- **phpseclib/phpseclib** ^3.0
- RSA key pair on the Certificate Authority (SCEP requires RSA)

## Installation

Install the package via Composer:

```bash
composer require groupesti/laravel-ca-scep
```

The service provider is auto-discovered. To publish the configuration file:

```bash
php artisan vendor:publish --tag=ca-scep-config
```

To publish the migrations:

```bash
php artisan vendor:publish --tag=ca-scep-migrations
```

Run the migrations:

```bash
php artisan migrate
```

## Configuration

The configuration file `config/ca-scep.php` exposes the following options:

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `enabled` | `bool` | `true` | Enable or disable the SCEP server globally. |
| `route_prefix` | `string` | `'scep'` | URL prefix for SCEP endpoints. |
| `ca_id` | `string\|null` | `null` | Default Certificate Authority UUID for SCEP operations. |
| `challenge_password_required` | `bool` | `true` | Require a challenge password for enrollment requests. |
| `challenge_password_ttl` | `int` | `3600` | Challenge password time-to-live in seconds. |
| `allowed_algorithms` | `array` | `['aes-256-cbc', 'aes-128-cbc', '3des']` | Symmetric encryption algorithms allowed for SCEP message enveloping. |
| `allowed_hash` | `array` | `['sha256', 'sha512']` | Hash algorithms allowed for SCEP message signing. |
| `auto_approve` | `bool` | `false` | Automatically approve enrollment requests. When `false`, requests are set to PENDING. |
| `capabilities` | `array` | `['AES', 'POSTPKIOperation', 'SHA-256', 'SHA-512', 'DES3', 'Renewal', 'GetNextCACert']` | SCEP capabilities advertised to clients via GetCACaps. |
| `routes.enabled` | `bool` | `true` | Enable or disable SCEP route registration. |
| `routes.middleware` | `array` | `[]` | Additional middleware to apply to SCEP routes. |

Environment variables:

```env
CA_SCEP_ENABLED=true
CA_SCEP_ROUTE_PREFIX=scep
CA_SCEP_CA_ID=
CA_SCEP_CHALLENGE_REQUIRED=true
CA_SCEP_CHALLENGE_TTL=3600
CA_SCEP_AUTO_APPROVE=false
```

## Usage

### Setting Up SCEP for a Certificate Authority

Verify that a CA is properly configured for SCEP:

```bash
php artisan ca:scep:setup {ca_uuid}
```

This command checks that the CA has an active RSA certificate and displays the SCEP endpoint URL.

### Generating Challenge Passwords

Generate a one-time challenge password for device enrollment:

```bash
php artisan ca:scep:challenge {ca_uuid}
php artisan ca:scep:challenge {ca_uuid} --purpose="iOS MDM enrollment" --ttl=7200
```

The password is displayed once and cannot be retrieved again. Provide it to the device or MDM profile for enrollment.

### SCEP Endpoints

The package registers the standard SCEP endpoint at `/{route_prefix}/{ca_uuid}/pkiclient.exe`:

| Method | URL | Operation | Description |
|--------|-----|-----------|-------------|
| GET | `/{prefix}/{ca_uuid}/pkiclient.exe?operation=GetCACert` | GetCACert | Retrieve CA certificate(s) |
| GET | `/{prefix}/{ca_uuid}/pkiclient.exe?operation=GetCACaps` | GetCACaps | Retrieve server capabilities |
| GET | `/{prefix}/{ca_uuid}/pkiclient.exe?operation=GetNextCACert` | GetNextCACert | Retrieve next CA certificate (rollover) |
| GET | `/{prefix}/{ca_uuid}/pkiclient.exe?operation=PKIOperation&message=<base64>` | PKIOperation | Submit enrollment (GET) |
| POST | `/{prefix}/{ca_uuid}/pkiclient.exe` | PKIOperation | Submit enrollment (POST, binary DER) |

### Using the Facade

```php
use CA\Scep\Facades\CaScep;
use CA\Models\CertificateAuthority;

$ca = CertificateAuthority::findOrFail($caUuid);

// Get CA capabilities
$capabilities = CaScep::handleGetCACaps($ca);

// Get CA certificate (DER or degenerate PKCS#7)
$caCert = CaScep::handleGetCACert($ca);

// Process a PKCS#7 enrollment request
$response = CaScep::handlePKCSReq($ca, pkiMessage: $derEncodedMessage);

// Poll for certificate status
$response = CaScep::handleCertPoll($ca, pkiMessage: $derEncodedMessage);

// Retrieve an issued certificate
$response = CaScep::handleGetCert($ca, pkiMessage: $derEncodedMessage);
```

### Using Dependency Injection

```php
use CA\Scep\Contracts\ScepServerInterface;
use CA\Models\CertificateAuthority;

final class DeviceEnrollmentService
{
    public function __construct(
        private readonly ScepServerInterface $scepServer,
    ) {}

    public function getCaCertificate(CertificateAuthority $ca): string
    {
        return $this->scepServer->handleGetCACert($ca);
    }
}
```

### Listening to Events

The package dispatches events during the enrollment lifecycle:

```php
use CA\Scep\Events\ScepEnrollmentRequested;
use CA\Scep\Events\ScepCertificateIssued;
use CA\Scep\Events\ScepEnrollmentFailed;

// In your EventServiceProvider or listener
Event::listen(ScepEnrollmentRequested::class, function (ScepEnrollmentRequested $event) {
    // $event->transaction, $event->ca
    Log::info('SCEP enrollment requested', ['transaction_id' => $event->transaction->transaction_id]);
});

Event::listen(ScepCertificateIssued::class, function (ScepCertificateIssued $event) {
    // $event->transaction, $event->ca, $event->certificate
    Log::info('SCEP certificate issued', ['certificate_id' => $event->certificate->id]);
});

Event::listen(ScepEnrollmentFailed::class, function (ScepEnrollmentFailed $event) {
    // $event->transaction, $event->ca, $event->reason
    Log::warning('SCEP enrollment failed', ['reason' => $event->reason]);
});
```

### Managing Transactions

List SCEP transactions:

```bash
php artisan ca:scep:transactions
php artisan ca:scep:transactions --ca={ca_uuid} --status=3 --limit=50
```

Clean up expired transactions and challenge passwords:

```bash
php artisan ca:scep:cleanup
php artisan ca:scep:cleanup --dry-run
```

### Querying Transactions Programmatically

```php
use CA\Scep\Models\ScepTransaction;
use CA\Models\ScepPkiStatus;

// Get pending transactions for a CA
$pending = ScepTransaction::query()
    ->forCa($caId)
    ->pending()
    ->notExpired()
    ->get();

// Get successful transactions
$successful = ScepTransaction::query()
    ->forCa($caId)
    ->byStatus(ScepPkiStatus::fromSlug(ScepPkiStatus::SUCCESS))
    ->get();
```

## Testing

Run the test suite:

```bash
./vendor/bin/pest
```

Run with coverage:

```bash
./vendor/bin/pest --coverage
```

Check code formatting:

```bash
./vendor/bin/pint --test
```

Run static analysis:

```bash
./vendor/bin/phpstan analyse
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover a security vulnerability, please see [SECURITY](SECURITY.md) for reporting instructions. Do **not** open a public GitHub issue.

## Credits

- [Groupe STI](https://github.com/groupesti)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
