# Laravel CA OID

> OID (Object Identifier) registry and resolver for the Laravel CA ecosystem, with database persistence, caching, and built-in standard OID collections.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/groupesti/laravel-ca-oid.svg)](https://packagist.org/packages/groupesti/laravel-ca-oid)
[![PHP Version](https://img.shields.io/badge/php-8.4%2B-blue)](https://www.php.net/releases/8.4/)
[![Laravel](https://img.shields.io/badge/laravel-12.x%20%7C%2013.x-red)](https://laravel.com)
[![Tests](https://github.com/groupesti/laravel-ca-oid/actions/workflows/tests.yml/badge.svg)](https://github.com/groupesti/laravel-ca-oid/actions/workflows/tests.yml)
[![License](https://img.shields.io/github/license/groupesti/laravel-ca-oid)](LICENSE.md)

## Requirements

- PHP 8.4+
- Laravel 12.x or 13.x
- `groupesti/laravel-ca` ^1.0

## Installation

Install the package via Composer:

```bash
composer require groupesti/laravel-ca-oid
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=ca-oid-config
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag=ca-oid-migrations
php artisan migrate
```

Seed the database with standard OIDs (X.509, Extended Validation, Microsoft):

```bash
php artisan ca-oid:seed
```

## Configuration

The configuration file is published to `config/ca-oid.php`. Available options:

| Key | Env Variable | Default | Description |
|-----|-------------|---------|-------------|
| `cache_enabled` | `CA_OID_CACHE_ENABLED` | `true` | Enable caching of OID lookups for improved performance. |
| `cache_ttl` | `CA_OID_CACHE_TTL` | `86400` | Cache time-to-live in seconds (default: 24 hours). |
| `allow_custom_oids` | `CA_OID_ALLOW_CUSTOM` | `true` | Whether to allow registration of custom/enterprise OIDs. |
| `auto_register_on_use` | `CA_OID_AUTO_REGISTER` | `false` | Automatically register unknown OIDs encountered during resolution. |
| `route_middleware` | -- | `['auth:api']` | Middleware applied to the OID API routes. |

## Usage

### Resolving OIDs

```php
use CA\Oid\Facades\CaOid;

// Resolve an OID to its information
$info = CaOid::resolve(oid: '2.5.4.3');
// Returns OidInfo DTO: name, description, category

// Resolve by name
$info = CaOid::resolveByName(name: 'commonName');

// Quick name/OID conversion via the resolver
$name = app(\CA\Oid\Contracts\OidResolverInterface::class)->toName(oid: '2.5.4.3');
// Returns: 'commonName'

$oid = app(\CA\Oid\Contracts\OidResolverInterface::class)->toOid(name: 'commonName');
// Returns: '2.5.4.3'
```

### Registering Custom OIDs

```php
use CA\Oid\Facades\CaOid;

$entry = CaOid::register(
    oid: '1.3.6.1.4.1.99999.1',
    name: 'myCustomExtension',
    description: 'Custom X.509 extension for internal use',
    category: 'custom',
);
```

### Searching and Browsing

```php
use CA\Oid\Facades\CaOid;

// Search OIDs by keyword
$results = CaOid::search(query: 'certificate');

// Get all OIDs in a category
$evOids = CaOid::getByCategory(category: 'extended-validation');

// Check if an OID exists
$exists = CaOid::exists(oid: '2.5.4.3'); // true

// Validate OID format
$valid = CaOid::validate(oid: '2.5.4.3'); // true

// Format an OID string
$formatted = CaOid::format(oid: '2.5.4.3');
```

### Unregistering OIDs

```php
use CA\Oid\Facades\CaOid;

CaOid::unregister(oid: '1.3.6.1.4.1.99999.1');
```

### Using Dependency Injection

```php
use CA\Oid\Contracts\OidRegistryInterface;

class CertificateBuilder
{
    public function __construct(
        private readonly OidRegistryInterface $registry,
    ) {}

    public function resolveExtension(string $oid): ?string
    {
        $info = $this->registry->resolve(oid: $oid);
        return $info?->name;
    }
}
```

### Artisan Commands

| Command | Description |
|---------|-------------|
| `ca-oid:seed` | Seed the database with standard OID collections (X.509, EV, Microsoft). |
| `ca-oid:list` | List all registered OIDs. |
| `ca-oid:search {query}` | Search OIDs by name, OID value, or description. |
| `ca-oid:register` | Interactively register a new custom OID. |
| `ca-oid:resolve {oid}` | Resolve an OID and display its information. |

### Built-in OID Collections

The package ships with pre-defined OID collections:

- **StandardOids** -- Common X.509 and PKIX OIDs.
- **ExtendedValidationOids** -- CA/Browser Forum EV certificate OIDs.
- **MicrosoftOids** -- Microsoft-specific certificate extension OIDs.

### Events

The package dispatches the following events:

- `CA\Oid\Events\OidRegistered` -- fired when a new OID is registered.
- `CA\Oid\Events\OidUnregistered` -- fired when an OID is removed.

### API Routes

The package registers API routes under the configured prefix (default: `api/ca`) for programmatic OID lookups. Routes are protected by the configured middleware (default: `auth:api`).

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

If you discover a security vulnerability, please see [SECURITY](SECURITY.md) for reporting instructions.

## Credits

- [Groupesti](https://github.com/groupesti)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
