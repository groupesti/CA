# Laravel CA UI

> Admin dashboard UI for managing a Laravel-based Certificate Authority.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/groupesti/laravel-ca-ui.svg)](https://packagist.org/packages/groupesti/laravel-ca-ui)
[![PHP Version](https://img.shields.io/badge/php-8.4%2B-blue)](https://www.php.net/releases/8.4/en.php)
[![Laravel](https://img.shields.io/badge/laravel-12.x-red)](https://laravel.com)
[![Tests](https://github.com/groupesti/laravel-ca-ui/actions/workflows/tests.yml/badge.svg)](https://github.com/groupesti/laravel-ca-ui/actions/workflows/tests.yml)
[![License](https://img.shields.io/github/license/groupesti/laravel-ca-ui)](LICENSE.md)

## Requirements

- PHP 8.4+
- Laravel 12.x
- `groupesti/laravel-ca` ^1.0
- `groupesti/laravel-ca-key` ^1.0
- `groupesti/laravel-ca-csr` ^1.0
- `groupesti/laravel-ca-crt` ^1.0
- `groupesti/laravel-ca-crl` ^1.0
- `groupesti/laravel-ca-ocsp` ^1.0

## Installation

```bash
composer require groupesti/laravel-ca-ui
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=ca-ui-config
```

Optionally publish views for customization:

```bash
php artisan vendor:publish --tag=ca-ui-views
```

Publish static assets (CSS/JS):

```bash
php artisan vendor:publish --tag=ca-ui-assets
```

## Configuration

The configuration file is published to `config/ca-ui.php`. Available options:

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `enabled` | `bool` | `true` | Enable or disable the entire UI. Set via `CA_UI_ENABLED` env variable. |
| `route_prefix` | `string` | `ca-admin` | URL prefix for all dashboard routes. Set via `CA_UI_ROUTE_PREFIX`. |
| `middleware` | `array` | `['web', 'auth']` | Middleware stack applied to all UI routes. |
| `title` | `string` | `Certificate Authority` | Dashboard page title. Set via `CA_UI_TITLE`. |
| `items_per_page` | `int` | `25` | Number of items per paginated list. |
| `theme.primary_color` | `string` | `indigo` | Tailwind CSS primary color. |
| `theme.sidebar_bg` | `string` | `gray-900` | Sidebar background color class. |
| `theme.sidebar_text` | `string` | `gray-300` | Sidebar text color class. |
| `theme.sidebar_active` | `string` | `indigo-500` | Active sidebar item color class. |
| `theme.header_bg` | `string` | `white` | Header background color class. |
| `theme.content_bg` | `string` | `gray-100` | Main content area background color class. |

## Usage

Once installed, navigate to `/{route_prefix}` (default: `/ca-admin`) in your browser. The dashboard provides:

### Dashboard

The main dashboard displays PKI statistics via the `DashboardStatsService`, including counts of authorities, certificates, pending CSRs, and CRL status.

### Certificate Authorities

Full CRUD management of Certificate Authorities with a visual tree hierarchy.

```
GET    /ca-admin/authorities          — List all CAs
GET    /ca-admin/authorities/create   — Create a new CA
POST   /ca-admin/authorities          — Store a new CA
GET    /ca-admin/authorities/{uuid}   — View CA details
```

### Certificates

Browse, inspect, export, and revoke certificates.

```
GET    /ca-admin/certificates              — List all certificates
GET    /ca-admin/certificates/{uuid}       — View certificate details
POST   /ca-admin/certificates/{uuid}/revoke — Revoke a certificate
GET    /ca-admin/certificates/{uuid}/export — Export certificate
```

### Keys

Browse and inspect cryptographic keys.

```
GET    /ca-admin/keys           — List all keys
GET    /ca-admin/keys/{uuid}    — View key details
```

### Certificate Signing Requests (CSRs)

Review, approve, or reject pending CSRs.

```
GET    /ca-admin/csrs                  — List all CSRs
GET    /ca-admin/csrs/{uuid}           — View CSR details
POST   /ca-admin/csrs/{uuid}/approve   — Approve a CSR
POST   /ca-admin/csrs/{uuid}/reject    — Reject a CSR
```

### Certificate Revocation Lists (CRLs)

View CRLs and trigger regeneration.

```
GET    /ca-admin/crls                       — List all CRLs
POST   /ca-admin/crls/{ca_uuid}/generate    — Generate a new CRL
```

### Audit Log

```
GET    /ca-admin/audit-log    — View audit log entries
```

### Blade Components

The package provides reusable Blade components:

- `<x-ca-stats-card>` — Dashboard statistics card
- `<x-ca-status-badge>` — Certificate/CSR status badge
- `<x-ca-certificate-chain>` — Visual certificate chain display
- `<x-ca-dn-display>` — Distinguished Name formatted display
- `<x-ca-pagination>` — Pagination controls

### Middleware

The package registers a `ca-ui-auth` middleware alias (`CaUiAuthentication`) that you can reference in the `middleware` config array for additional access control.

## Testing

```bash
./vendor/bin/pest
./vendor/bin/pint --test
./vendor/bin/phpstan analyse
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover a security vulnerability, please see [SECURITY](SECURITY.md). Do not open a public issue.

## Credits

- [Groupe STI](https://github.com/groupesti)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
