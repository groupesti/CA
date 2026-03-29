# Laravel CA OCSP

> RFC 6960 Online Certificate Status Protocol (OCSP) responder for the Laravel CA ecosystem, implemented in pure PHP using phpseclib v3.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/groupesti/laravel-ca-ocsp.svg)](https://packagist.org/packages/groupesti/laravel-ca-ocsp)
[![PHP Version](https://img.shields.io/badge/php-8.4%2B-blue)](https://www.php.net/releases/8.4/)
[![Laravel](https://img.shields.io/badge/laravel-12.x%20%7C%2013.x-red)](https://laravel.com)
[![Tests](https://github.com/groupesti/laravel-ca-ocsp/actions/workflows/tests.yml/badge.svg)](https://github.com/groupesti/laravel-ca-ocsp/actions/workflows/tests.yml)
[![License](https://img.shields.io/github/license/groupesti/laravel-ca-ocsp)](LICENSE.md)

## Requirements

- PHP 8.4+
- Laravel 12.x or 13.x
- `groupesti/laravel-ca` ^1.0
- `groupesti/laravel-ca-crt` ^1.0
- `phpseclib/phpseclib` ^3.0

## Installation

Install the package via Composer:

```bash
composer require groupesti/laravel-ca-ocsp
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=ca-ocsp-config
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag=ca-ocsp-migrations
php artisan migrate
```

## Configuration

The configuration file is published to `config/ca-ocsp.php`. Available options:

| Key | Env Variable | Default | Description |
|-----|-------------|---------|-------------|
| `enabled` | `CA_OCSP_ENABLED` | `true` | Enable or disable the OCSP responder routes. |
| `cache_seconds` | `CA_OCSP_CACHE_SECONDS` | `3600` | Number of seconds to cache OCSP responses. |
| `nonce_required` | `CA_OCSP_NONCE_REQUIRED` | `false` | Whether a nonce is required in OCSP requests. |
| `route_prefix` | `CA_OCSP_ROUTE_PREFIX` | `ocsp` | URL prefix for OCSP endpoints. |
| `responder_certificate_validity_days` | `CA_OCSP_RESPONDER_CERT_VALIDITY_DAYS` | `30` | Validity period (in days) for the OCSP responder certificate. |
| `default_response_validity_hours` | `CA_OCSP_RESPONSE_VALIDITY_HOURS` | `24` | Default validity period (in hours) for OCSP responses. |
| `middleware` | -- | `[]` | Middleware applied to OCSP routes. |

## Usage

### Setting Up the OCSP Responder

Run the setup command to initialize the OCSP responder for a Certificate Authority:

```bash
php artisan ca-ocsp:setup
```

Check the current status of the OCSP responder:

```bash
php artisan ca-ocsp:status
```

### Handling OCSP Requests Programmatically

The package exposes HTTP routes automatically under the configured prefix. You can also use the facade or inject the interface directly:

```php
use CA\OCSP\Facades\CaOcsp;

// Handle a raw DER-encoded OCSP request and get a DER-encoded response
$derResponse = CaOcsp::handleRequest(derEncodedRequest: $derRequest);
```

### Using Dependency Injection

```php
use CA\OCSP\Contracts\OcspResponderInterface;

class CertificateStatusController
{
    public function __construct(
        private readonly OcspResponderInterface $responder,
    ) {}

    public function check(string $derRequest): string
    {
        return $this->responder->handleRequest(derEncodedRequest: $derRequest);
    }
}
```

### Parsing and Building Responses Manually

```php
use CA\OCSP\Facades\CaOcsp;

// Parse a DER-encoded OCSP request into structured data
$parsed = CaOcsp::parseRequest(derRequest: $rawDer);
// Returns: ['requests' => [...], 'nonce' => '...']

// Build a DER-encoded OCSP response from individual single responses
$response = CaOcsp::buildResponse(
    singleResponses: $responses,
    signingKey: $privateKey,
    responderCert: $cert,
    nonce: $parsed['nonce'],
);
```

### Resolving Certificate Status

You can implement custom certificate status resolution by binding your own implementation:

```php
use CA\OCSP\Contracts\CertificateStatusResolverInterface;
use CA\OCSP\DTOs\CertStatusResult;

$this->app->singleton(
    CertificateStatusResolverInterface::class,
    MyCustomStatusResolver::class,
);
```

### Artisan Commands

| Command | Description |
|---------|-------------|
| `ca-ocsp:setup` | Initialize the OCSP responder for a Certificate Authority. |
| `ca-ocsp:status` | Display the current OCSP responder status and health. |

### Events

The package dispatches the following events:

- `CA\OCSP\Events\OcspResponseGenerated` -- fired after an OCSP response is generated.

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
