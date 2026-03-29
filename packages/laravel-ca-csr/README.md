# Laravel CA CSR

> Certificate Signing Request (CSR) management package for the Laravel CA ecosystem.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/groupesti/laravel-ca-csr.svg)](https://packagist.org/packages/groupesti/laravel-ca-csr)
[![PHP Version](https://img.shields.io/badge/php-8.4%2B-blue)](https://www.php.net/releases/8.4/en.php)
[![Laravel](https://img.shields.io/badge/laravel-12.x%20|%2013.x-red)](https://laravel.com)
[![Tests](https://github.com/groupesti/laravel-ca-csr/actions/workflows/tests.yml/badge.svg)](https://github.com/groupesti/laravel-ca-csr/actions/workflows/tests.yml)
[![License](https://img.shields.io/github/license/groupesti/laravel-ca-csr)](LICENSE.md)

## Requirements

- PHP 8.4+
- Laravel 12.x or 13.x
- `groupesti/laravel-ca` ^1.0
- `groupesti/laravel-ca-key` ^1.0
- `phpseclib/phpseclib` ^3.0

## Installation

```bash
composer require groupesti/laravel-ca-csr
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=ca-csr-config
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag=ca-csr-migrations
php artisan migrate
```

The service provider and facade are auto-discovered via Laravel's package discovery.

## Configuration

The configuration file is published to `config/ca-csr.php`. Available options:

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `default_validity_days` | `int` | `30` | Number of days a pending CSR remains valid before expiring. |
| `auto_approve` | `bool` | `false` | When enabled, newly created CSRs are automatically approved. |
| `required_dn_fields` | `array` | `['CN']` | Distinguished Name fields that must be present in every CSR. |
| `allowed_san_types` | `array` | `['dns', 'ip', 'email', 'uri']` | Subject Alternative Name types allowed in CSRs. |
| `routes.enabled` | `bool` | `true` | Enable or disable the built-in API routes. |
| `routes.prefix` | `string` | `api/ca/csrs` | URL prefix for the API routes. |
| `routes.middleware` | `array` | `['api']` | Middleware applied to the API routes. |

Environment variables:

```dotenv
CA_CSR_VALIDITY_DAYS=30
CA_CSR_AUTO_APPROVE=false
CA_CSR_ROUTES_ENABLED=true
CA_CSR_ROUTES_PREFIX=api/ca/csrs
```

## Usage

### Creating a CSR with the Builder

```php
use CA\Csr\Services\CsrBuilder;
use CA\DTOs\DistinguishedName;

$csr = app(CsrBuilder::class)
    ->subject(new DistinguishedName(
        CN: 'example.com',
        O: 'My Organization',
        C: 'CA',
    ))
    ->key($key)
    ->addDnsName('example.com')
    ->addDnsName('www.example.com')
    ->addIpAddress('192.168.1.1')
    ->addEmail('admin@example.com')
    ->template($template)
    ->build();
```

### Creating a CSR via the Facade

```php
use CA\Csr\Facades\CaCsr;
use CA\DTOs\DistinguishedName;

$csr = CaCsr::create(
    dn: new DistinguishedName(CN: 'example.com', O: 'Acme Corp', C: 'CA'),
    key: $key,
    extensions: [
        ['type' => 'dns', 'value' => 'example.com'],
        ['type' => 'ip', 'value' => '10.0.0.1'],
    ],
);
```

### Importing an External CSR

```php
use CA\Csr\Facades\CaCsr;

$csr = CaCsr::import($pemString);
```

The imported CSR is automatically validated (signature verification) and stored with `PENDING` status.

### Approval Workflow

```php
use CA\Csr\Facades\CaCsr;

// Approve a pending CSR
$csr = CaCsr::approve(csr: $csr, approvedBy: 'admin@example.com');

// Reject a pending CSR
$csr = CaCsr::reject(csr: $csr, reason: 'Invalid organization name');
```

### Validating a CSR

```php
$isValid = CaCsr::validate($csr);
```

### Retrieving CSR Information

```php
// Get the subject Distinguished Name
$dn = CaCsr::getSubjectDN($csr);

// Get the public key
$publicKey = CaCsr::getPublicKey($csr);

// Find a CSR by UUID
$csr = CaCsr::findByUuid('550e8400-e29b-41d4-a716-446655440000');
```

### Using Model Scopes

```php
use CA\Csr\Models\Csr;

// Get all pending CSRs
$pending = Csr::pending()->get();

// Get all approved CSRs
$approved = Csr::approved()->get();

// Filter by certificate authority
$csrs = Csr::forCa($caId)->get();

// Filter by template
$csrs = Csr::forTemplate($templateId)->get();
```

### Events

The package dispatches the following events:

| Event | Description |
|-------|-------------|
| `CsrCreated` | Fired when a new CSR is created or imported. |
| `CsrApproved` | Fired when a CSR is approved. |
| `CsrRejected` | Fired when a CSR is rejected. |

### Artisan Commands

| Command | Description |
|---|---|
| `php artisan ca:csr:create --key=UUID --cn=example.com --san=dns:www.example.com` | Create a new CSR interactively. |
| `php artisan ca:csr:list --status=pending --ca=UUID` | List CSRs with optional filters (status, CA, tenant). |
| `php artisan ca:csr:import /path/to/file.csr --ca=UUID` | Import a PEM-encoded CSR from a file. |
| `php artisan ca:csr:approve UUID` | Approve a pending CSR by UUID. |

### API Routes

When routes are enabled, the following endpoints are available:

| Method | URI | Action |
|--------|-----|--------|
| `GET` | `/api/ca/csrs` | List all CSRs |
| `POST` | `/api/ca/csrs` | Create a new CSR |
| `POST` | `/api/ca/csrs/import` | Import an external CSR |
| `GET` | `/api/ca/csrs/{uuid}` | Show a specific CSR |
| `POST` | `/api/ca/csrs/{uuid}/approve` | Approve a pending CSR |
| `POST` | `/api/ca/csrs/{uuid}/reject` | Reject a pending CSR |

## Testing

```bash
./vendor/bin/pest
```

Run code formatting checks:

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
