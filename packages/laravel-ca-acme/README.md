# Laravel CA ACME

> ACME protocol (RFC 8555) server implementation for Laravel CA — automated certificate management with full protocol support.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/groupesti/laravel-ca-acme.svg)](https://packagist.org/packages/groupesti/laravel-ca-acme)
[![PHP Version](https://img.shields.io/badge/php-8.4%2B-blue)](https://www.php.net/releases/8.4/en.php)
[![Laravel](https://img.shields.io/badge/laravel-12.x%20%7C%2013.x-red)](https://laravel.com)
[![Tests](https://github.com/groupesti/laravel-ca-acme/actions/workflows/tests.yml/badge.svg)](https://github.com/groupesti/laravel-ca-acme/actions/workflows/tests.yml)
[![License](https://img.shields.io/github/license/groupesti/laravel-ca-acme)](LICENSE.md)

## Requirements

- PHP 8.4+
- Laravel 12.x or 13.x
- `groupesti/laravel-ca` ^1.0
- `groupesti/laravel-ca-crt` ^1.0
- `groupesti/laravel-ca-csr` ^1.0
- `groupesti/laravel-ca-key` ^1.0
- `phpseclib/phpseclib` ^3.0

## Installation

```bash
composer require groupesti/laravel-ca-acme
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=ca-acme-config
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag=ca-acme-migrations
php artisan migrate
```

Run the setup command to initialize the ACME server:

```bash
php artisan ca:acme:setup
```

## Configuration

The configuration file is published to `config/ca-acme.php`. Available options:

| Key | Env Variable | Default | Description |
|-----|-------------|---------|-------------|
| `enabled` | `CA_ACME_ENABLED` | `true` | Enable or disable the ACME server endpoints globally. |
| `route_prefix` | `CA_ACME_ROUTE_PREFIX` | `acme` | URL prefix for all ACME endpoints. |
| `terms_of_service_url` | `CA_ACME_TOS_URL` | `null` | URL to the ACME server's terms of service. |
| `website_url` | `CA_ACME_WEBSITE_URL` | `null` | URL to the ACME server operator's website. |
| `ca_id` | `CA_ACME_CA_ID` | `null` | Default Certificate Authority ID for ACME operations. |
| `external_account_required` | `CA_ACME_EXTERNAL_ACCOUNT_REQUIRED` | `false` | Whether external account binding is required. |
| `challenge_types` | — | `['http-01', 'dns-01', 'tls-alpn-01']` | Supported ACME challenge types. |
| `order_validity_hours` | `CA_ACME_ORDER_VALIDITY_HOURS` | `168` | How long an order remains valid (hours). |
| `authorization_validity_hours` | `CA_ACME_AUTHORIZATION_VALIDITY_HOURS` | `168` | How long an authorization remains valid (hours). |
| `challenge_timeout_seconds` | `CA_ACME_CHALLENGE_TIMEOUT_SECONDS` | `30` | Maximum time for challenge validation (seconds). |
| `nonce_ttl_seconds` | `CA_ACME_NONCE_TTL_SECONDS` | `3600` | Nonce time-to-live (seconds). |
| `middleware` | — | `['api']` | Middleware applied to ACME routes. |

## Usage

### ACME Protocol Endpoints

The package registers the following RFC 8555 compliant endpoints under the configured route prefix (default: `/acme`):

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/acme/directory` | ACME directory resource |
| HEAD/GET | `/acme/new-nonce` | Request a new nonce |
| POST | `/acme/new-account` | Create or find an account |
| POST | `/acme/new-order` | Submit a new certificate order |
| POST | `/acme/order/{uuid}` | Retrieve order details |
| POST | `/acme/order/{uuid}/finalize` | Finalize an order with a CSR |
| POST | `/acme/authorization/{uuid}` | Retrieve authorization details |
| POST | `/acme/challenge/{uuid}` | Respond to a challenge |
| POST | `/acme/certificate/{uuid}` | Download the issued certificate |
| POST | `/acme/revoke-cert` | Revoke a certificate |

### Using the Facade

```php
use CA\Acme\Facades\CaAcme;

// Get the ACME directory
$directory = CaAcme::directory();

// Generate a nonce
$nonce = CaAcme::newNonce();

// Create an account
$account = CaAcme::newAccount(
    payload: [
        'termsOfServiceAgreed' => true,
        'contact' => ['mailto:admin@example.com'],
    ],
    jwk: $jwkJson,
);

// Create a new order
$order = CaAcme::newOrder(
    account: $account,
    identifiers: [
        ['type' => 'dns', 'value' => 'example.com'],
        ['type' => 'dns', 'value' => '*.example.com'],
    ],
);

// Finalize an order with a CSR
$order = CaAcme::finalizeOrder(order: $order, csr: $base64UrlEncodedCsr);

// Download the certificate chain
$pemChain = CaAcme::getCertificate(order: $order);

// Revoke a certificate
CaAcme::revokeCertificate(certPem: $certificatePem, reason: 0);
```

### Using Dependency Injection

```php
use CA\Acme\Contracts\AcmeServerInterface;

class MyController
{
    public function __construct(
        private readonly AcmeServerInterface $acmeServer,
    ) {}
}
```

### Artisan Commands

| Command | Description |
|---------|-------------|
| `ca:acme:setup` | Interactive setup wizard for ACME configuration. Options: `--ca`, `--tos-url`, `--website-url`, `--prefix`. |
| `ca:acme:accounts` | List ACME accounts. Options: `--status`, `--ca`, `--limit`. |
| `ca:acme:orders` | List ACME orders. Options: `--status`, `--account`, `--limit`. |
| `ca:acme:cleanup` | Remove expired nonces, orders, and authorizations. Supports `--dry-run`. |

```bash
# Initialize the ACME server
php artisan ca:acme:setup --ca=your-ca-id

# List ACME accounts
php artisan ca:acme:accounts --status=valid

# List ACME orders
php artisan ca:acme:orders --status=pending --limit=20

# Clean up expired data (preview first)
php artisan ca:acme:cleanup --dry-run
php artisan ca:acme:cleanup
```

### Scheduling Cleanup

Add the cleanup command to your scheduler to automatically remove expired data:

```php
// In routes/console.php
Schedule::command('ca:acme:cleanup')->hourly();
```

### Events

The package dispatches the following events:

- `AcmeAccountCreated` — when a new ACME account is registered.
- `AcmeOrderCreated` — when a new certificate order is submitted.
- `AcmeChallengeCompleted` — when a challenge is successfully validated.
- `AcmeOrderFinalized` — when an order is finalized with a valid CSR.
- `AcmeCertificateIssued` — when a certificate is issued and ready for download.

### Challenge Validators

Three challenge types are supported out of the box:

- **HTTP-01** (`Http01Validator`) — HTTP-based domain validation.
- **DNS-01** (`Dns01Validator`) — DNS TXT record validation.
- **TLS-ALPN-01** (`TlsAlpn01Validator`) — TLS Application-Layer Protocol Negotiation validation.

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

- [Groupesti](https://github.com/groupesti)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [LICENSE.md](LICENSE.md) for more information.
