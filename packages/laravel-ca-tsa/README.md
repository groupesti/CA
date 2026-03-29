# Laravel CA TSA

> RFC 3161 Time-Stamp Authority (TSA) for the Laravel CA ecosystem, with timestamp creation, verification, and full ASN.1 support via phpseclib v3.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/groupesti/laravel-ca-tsa.svg)](https://packagist.org/packages/groupesti/laravel-ca-tsa)
[![PHP Version](https://img.shields.io/badge/php-8.4%2B-blue)](https://www.php.net/releases/8.4/)
[![Laravel](https://img.shields.io/badge/laravel-12.x%20%7C%2013.x-red)](https://laravel.com)
[![Tests](https://github.com/groupesti/laravel-ca-tsa/actions/workflows/tests.yml/badge.svg)](https://github.com/groupesti/laravel-ca-tsa/actions/workflows/tests.yml)
[![License](https://img.shields.io/github/license/groupesti/laravel-ca-tsa)](LICENSE.md)

## Requirements

- PHP 8.4+
- Laravel 12.x or 13.x
- `groupesti/laravel-ca` ^1.0
- `groupesti/laravel-ca-crt` ^1.0
- `groupesti/laravel-ca-key` ^1.0
- `phpseclib/phpseclib` ^3.0

## Installation

Install the package via Composer:

```bash
composer require groupesti/laravel-ca-tsa
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=ca-tsa-config
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag=ca-tsa-migrations
php artisan migrate
```

## Configuration

The configuration file is published to `config/ca-tsa.php`. Available options:

| Key | Env Variable | Default | Description |
|-----|-------------|---------|-------------|
| `enabled` | `CA_TSA_ENABLED` | `true` | Enable or disable the TSA service and routes. |
| `route_prefix` | `CA_TSA_ROUTE_PREFIX` | `tsa` | URL prefix for TSA endpoints. |
| `ca_id` | `CA_TSA_CA_ID` | `null` | UUID of the Certificate Authority used for timestamping. |
| `default_hash` | `CA_TSA_DEFAULT_HASH` | `sha256` | Default hash algorithm for timestamp tokens. |
| `policy_oid` | `CA_TSA_POLICY_OID` | `1.2.3.4.1` | OID identifying the TSA policy. Replace with your own. |
| `ordering` | `CA_TSA_ORDERING` | `false` | Whether the TSA guarantees ordering of timestamps. |
| `include_tsa_cert` | `CA_TSA_INCLUDE_CERT` | `true` | Include the TSA signing certificate in responses. |
| `accuracy_seconds` | `CA_TSA_ACCURACY_SECONDS` | `1` | Clock accuracy -- seconds component. |
| `accuracy_millis` | `CA_TSA_ACCURACY_MILLIS` | `0` | Clock accuracy -- milliseconds component. |
| `accuracy_micros` | `CA_TSA_ACCURACY_MICROS` | `0` | Clock accuracy -- microseconds component. |
| `nonce_required` | `CA_TSA_NONCE_REQUIRED` | `false` | Whether a nonce is required in timestamp requests. |
| `serial_number_bits` | `CA_TSA_SERIAL_BITS` | `64` | Number of random bits for timestamp serial numbers. |
| `middleware` | -- | `['api']` | Middleware applied to TSA routes. |

## Usage

### Setting Up the TSA

Run the setup command to initialize the Time-Stamp Authority:

```bash
php artisan ca-tsa:setup
```

Check the current status of the TSA:

```bash
php artisan ca-tsa:status
```

### Handling Timestamp Requests (HTTP)

The package automatically registers HTTP routes under the configured prefix (default: `/tsa`). Clients send RFC 3161 TimeStampReq (TSQ) messages as DER-encoded binary data and receive TimeStampResp (TSR) responses.

### Creating Timestamps Programmatically

```php
use CA\Tsa\Facades\CaTsa;

// Handle a raw DER-encoded TSQ request and get a DER-encoded TSR response
$tsrDer = CaTsa::handleRequest(tsqDer: $tsqRequest);

// Or create a timestamp directly with specific parameters
$tsrDer = CaTsa::createTimestamp(
    hashAlgorithm: 'sha256',
    hashedMessage: hash(algo: 'sha256', data: $document, binary: true),
    nonce: random_bytes(length: 8),
    certReq: true,
    policyOid: '1.2.3.4.1',
);
```

### Parsing Timestamp Requests

```php
use CA\Tsa\Facades\CaTsa;

$parsed = CaTsa::parseRequest(tsqDer: $rawTsq);
// Returns: [
//     'version' => 1,
//     'hashAlgorithm' => 'sha256',
//     'hashedMessage' => '...',
//     'nonce' => '...',
//     'certReq' => true,
//     'policyOid' => '1.2.3.4.1',
// ]
```

### Verifying Timestamps

```php
use CA\Tsa\Contracts\TsaVerifierInterface;

$verifier = app(TsaVerifierInterface::class);

// Verify a timestamp response
$isValid = $verifier->verify(tsrDer: $tsrResponse);

// Verify against original data
$isValid = $verifier->verify(
    tsrDer: $tsrResponse,
    originalData: $document,
);

// Extract TSTInfo from a timestamp response
$tstInfo = $verifier->extractTstInfo(tsrDer: $tsrResponse);
// Returns: [
//     'version' => 1,
//     'policy' => '1.2.3.4.1',
//     'hashAlgorithm' => 'sha256',
//     'hashedMessage' => '...',
//     'serialNumber' => '...',
//     'genTime' => '2026-03-29T12:00:00Z',
//     'accuracy' => [...],
//     'ordering' => false,
//     'nonce' => '...',
// ]
```

### Using Dependency Injection

```php
use CA\Tsa\Contracts\TsaServerInterface;

class DocumentSigningService
{
    public function __construct(
        private readonly TsaServerInterface $tsaServer,
    ) {}

    public function timestamp(string $documentHash): string
    {
        return $this->tsaServer->createTimestamp(
            hashAlgorithm: 'sha256',
            hashedMessage: $documentHash,
            nonce: random_bytes(length: 8),
            certReq: true,
            policyOid: null,
        );
    }
}
```

### Artisan Commands

| Command | Description |
|---------|-------------|
| `ca-tsa:setup` | Initialize the Time-Stamp Authority for a CA. |
| `ca-tsa:status` | Display the current TSA status and configuration. |
| `ca-tsa:verify` | Verify a timestamp response (TSR) file. |
| `ca-tsa:token-list` | List issued timestamp tokens. |

### Events

The package dispatches the following events:

- `CA\Tsa\Events\TimestampCreated` -- fired when a new timestamp token is issued.
- `CA\Tsa\Events\TimestampVerified` -- fired when a timestamp is successfully verified.

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
