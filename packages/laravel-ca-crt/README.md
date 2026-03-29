# Laravel CA Certificate

> X.509 certificate management for Laravel: issue, revoke, renew, export, and validate certificates with full chain building and Microsoft AD CS template support.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/groupesti/laravel-ca-crt.svg)](https://packagist.org/packages/groupesti/laravel-ca-crt)
[![PHP Version](https://img.shields.io/badge/php-8.4%2B-blue)](https://www.php.net/releases/8.4/en.php)
[![Laravel](https://img.shields.io/badge/laravel-12.x%20|%2013.x-red)](https://laravel.com)
[![Tests](https://github.com/groupesti/laravel-ca-crt/actions/workflows/tests.yml/badge.svg)](https://github.com/groupesti/laravel-ca-crt/actions/workflows/tests.yml)
[![License](https://img.shields.io/github/license/groupesti/laravel-ca-crt)](LICENSE.md)

## Requirements

- **PHP** 8.4+
- **Laravel** 12.x or 13.x
- **PHP Extensions**: OpenSSL, mbstring, JSON
- **Companion packages**:
    - `groupesti/laravel-ca` ^1.0 (core CA models and DTOs)
    - `groupesti/laravel-ca-key` ^1.0 (key management)
    - `groupesti/laravel-ca-csr` ^1.0 (certificate signing request management)
    - `phpseclib/phpseclib` ^3.0 (X.509 cryptographic operations)

## Installation

Install the package via Composer:

```bash
composer require groupesti/laravel-ca-crt
```

The service provider is auto-discovered. Publish the configuration file:

```bash
php artisan vendor:publish --tag=ca-crt-config
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag=ca-crt-migrations
php artisan migrate
```

## Configuration

The configuration file is published to `config/ca-crt.php`.

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `default_validity_days` | `int` | `365` | Default certificate validity period in days. |
| `default_hash` | `string` | `sha256` | Default hash algorithm for signing (`sha256`, `sha384`, `sha512`). |
| `routes.enabled` | `bool` | `true` | Enable or disable the built-in API routes. |
| `routes.prefix` | `string` | `api/ca/certificates` | URL prefix for certificate API routes. |
| `routes.middleware` | `array` | `['api']` | Middleware applied to certificate API routes. |

## Usage

### Issuing a Server TLS Certificate

Use the `ServerCertificateBuilder` to create certificate options, then issue from a CSR:

```php
use CA\Crt\Builders\ServerCertificateBuilder;
use CA\Crt\Facades\CaCrt;

$options = (new ServerCertificateBuilder())
    ->addDnsName('example.com')
    ->addDnsName('www.example.com')
    ->addWildcard('example.com')
    ->addIpAddress('192.168.1.1')
    ->setValidityDays(730)
    ->build();

$certificate = CaCrt::issueFromCsr(
    ca: $certificateAuthority,
    csr: $csr,
    options: $options,
);
```

### Issuing a Client mTLS Certificate

```php
use CA\Crt\Builders\ClientCertificateBuilder;
use CA\Crt\Facades\CaCrt;

$options = (new ClientCertificateBuilder())
    ->setEmail('user@example.com')
    ->setUserId('user-12345')
    ->setValidityDays(365)
    ->build();

$certificate = CaCrt::issueFromCsr(
    ca: $certificateAuthority,
    csr: $csr,
    options: $options,
);
```

### Issuing an S/MIME Certificate

```php
use CA\Crt\Builders\SmimeCertificateBuilder;

$options = (new SmimeCertificateBuilder())
    ->setEmail('alice@example.com')
    ->setName('Alice Smith')
    ->setValidityDays(365)
    ->build();

$certificate = CaCrt::issueFromCsr($ca, $csr, $options);
```

### Issuing a Code Signing Certificate

```php
use CA\Crt\Builders\CodeSigningCertificateBuilder;

$options = (new CodeSigningCertificateBuilder())
    ->setPublisher('Acme Corp')
    ->setValidityDays(730)
    ->build();

$certificate = CaCrt::issueFromCsr($ca, $csr, $options);
```

### Microsoft AD CS Compatible Certificates

The `AdcsCertificateBuilder` generates certificates with Microsoft-specific extensions (Certificate Template Information OID `1.3.6.1.4.1.311.21.7`, Certificate Template Name OID `1.3.6.1.4.1.311.20.2`, Application Policies OID `1.3.6.1.4.1.311.21.10`):

```php
use CA\Crt\Builders\AdcsCertificateBuilder;

// Web Server template
$options = (new AdcsCertificateBuilder())
    ->forWebServer()
    ->addDnsName('intranet.corp.local')
    ->build();

// User template (client auth + email protection + EFS)
$options = (new AdcsCertificateBuilder())
    ->forUser()
    ->build();

// Computer template (client + server auth)
$options = (new AdcsCertificateBuilder())
    ->forComputer()
    ->addDnsName('server01.corp.local')
    ->build();

// Domain Controller template
$options = (new AdcsCertificateBuilder())
    ->forDomainController()
    ->addDnsName('dc01.corp.local')
    ->build();

// Code Signing template
$options = (new AdcsCertificateBuilder())
    ->forCodeSigning()
    ->build();

// S/MIME template
$options = (new AdcsCertificateBuilder())
    ->forSmime()
    ->build();

// Custom Microsoft template
$options = (new AdcsCertificateBuilder())
    ->setMsTemplate(
        oid: '1.3.6.1.4.1.311.21.8.xxxxx',
        name: 'CustomTemplate',
        majorVersion: 2,
        minorVersion: 1,
    )
    ->setValidityDays(365)
    ->build();
```

### Resolving Microsoft Templates

Use the `TemplateResolver` to look up built-in templates by name or OID:

```php
use CA\Crt\Templates\TemplateResolver;

$template = TemplateResolver::resolve('WebServer');
$template = TemplateResolver::resolve('1.3.6.1.4.1.311.21.8.10159878...');

// Register a custom template
TemplateResolver::register($myCustomTemplate);

// Check if a template exists
if (TemplateResolver::has('WebServer')) {
    // ...
}
```

### Self-Signed Root CA Certificate

```php
use CA\DTOs\CertificateOptions;
use CA\Models\CertificateType;

$options = new CertificateOptions(
    type: CertificateType::ROOT_CA,
    validityDays: 3650,
    keyUsage: ['keyCertSign', 'cRLSign'],
    isCa: true,
    pathLength: null,
);

$rootCert = CaCrt::issueSelfSigned(
    ca: $certificateAuthority,
    key: $rootKey,
    options: $options,
);
```

### Intermediate CA Certificate

```php
use CA\DTOs\DistinguishedName;
use CA\DTOs\CertificateOptions;
use CA\Models\CertificateType;

$dn = new DistinguishedName(
    CN: 'Intermediate CA',
    O: 'Acme Corp',
    C: 'CA',
);

$options = new CertificateOptions(
    type: CertificateType::INTERMEDIATE_CA,
    validityDays: 1825,
    keyUsage: ['keyCertSign', 'cRLSign'],
    isCa: true,
    pathLength: 0,
);

$intermediateCert = CaCrt::issueIntermediate(
    parentCa: $rootCa,
    dn: $dn,
    key: $intermediateKey,
    options: $options,
);
```

### Revoking a Certificate

```php
use CA\Models\RevocationReason;

$certificate = CaCrt::revoke(
    certificate: $certificate,
    reason: RevocationReason::KEY_COMPROMISE,
);
```

### Renewing a Certificate

Renewal creates a new certificate with the same subject and extensions, and automatically revokes the old certificate with the `superseded` reason:

```php
$newCertificate = CaCrt::renew(
    certificate: $certificate,
    validityDays: 365,
);
```

### Verifying a Certificate

Validates expiry, revocation status, and the full signature chain up to the root:

```php
$isValid = CaCrt::verify($certificate);
```

### Certificate Chain

```php
$chain = CaCrt::getChain($certificate);
// Returns: [leaf, intermediate, ..., root]
```

### Exporting Certificates

```php
use CA\Models\ExportFormat;

$pem = CaCrt::export($certificate, ExportFormat::PEM);
$der = CaCrt::export($certificate, ExportFormat::DER);
$pkcs7 = CaCrt::export($certificate, ExportFormat::PKCS7);
```

### Querying Certificates

```php
// Find by serial number
$cert = CaCrt::findBySerial($ca, 'AB12CD34');

// Find by UUID
$cert = CaCrt::findByUuid('550e8400-e29b-41d4-a716-446655440000');

// Get certificates expiring within 30 days
$expiring = CaCrt::getExpiring(days: 30);
```

### Artisan Commands

| Command | Description |
|---------|-------------|
| `ca-crt:issue` | Issue a new certificate from a CSR. |
| `ca-crt:list` | List certificates with optional filters. |
| `ca-crt:revoke` | Revoke a certificate by UUID or serial number. |
| `ca-crt:renew` | Renew an existing certificate. |
| `ca-crt:export` | Export a certificate in PEM, DER, or PKCS#7 format. |
| `ca-crt:verify` | Verify a certificate's validity and chain. |
| `ca-crt:expiring-scan` | Scan for certificates expiring within a given window. |

### REST API Endpoints

When routes are enabled (`ca-crt.routes.enabled = true`), the following endpoints are available under the configured prefix (default `api/ca/certificates`):

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/` | List certificates (filterable by `ca_id`, `type`, `status`, `tenant_id`). |
| `POST` | `/` | Issue a certificate from a CSR. |
| `GET` | `/{uuid}` | Show a single certificate. |
| `POST` | `/{uuid}/revoke` | Revoke a certificate. |
| `POST` | `/{uuid}/renew` | Renew a certificate. |
| `GET` | `/{uuid}/chain` | Get the certificate chain. |
| `GET` | `/{uuid}/export` | Export a certificate (query param: `format`). |
| `GET` | `/{uuid}/verify` | Verify a certificate. |
| `GET` | `/expiring` | List certificates expiring within `days` (query param). |

### Events

| Event | Dispatched When |
|-------|-----------------|
| `CertificateIssued` | A new certificate is issued (from CSR or intermediate). |
| `CertificateRevoked` | A certificate is revoked. |
| `CertificateRenewed` | A certificate is renewed (includes both old and new certificate). |
| `CertificateExpiring` | A certificate is approaching its expiration date. |

## Testing

```bash
./vendor/bin/pest
```

Run code style checks:

```bash
./vendor/bin/pint --test
```

Run static analysis:

```bash
./vendor/bin/phpstan analyse
```

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover a security vulnerability, please see [SECURITY.md](SECURITY.md). Do **not** open a public issue.

## Credits

- [Groupe STI](https://github.com/groupesti)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [LICENSE.md](LICENSE.md) for more information.
