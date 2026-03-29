# Laravel CA Key

> Cryptographic key management for Laravel Certificate Authority -- RSA, ECDSA, and Ed25519 key generation, import, export, rotation, and encrypted storage via phpseclib v3.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/groupesti/laravel-ca-key.svg)](https://packagist.org/packages/groupesti/laravel-ca-key)
[![PHP Version](https://img.shields.io/badge/php-8.4%2B-blue)](https://www.php.net/releases/8.4/en.php)
[![Laravel](https://img.shields.io/badge/laravel-12.x%20%7C%2013.x-red)](https://laravel.com)
[![Tests](https://github.com/groupesti/laravel-ca-key/actions/workflows/tests.yml/badge.svg)](https://github.com/groupesti/laravel-ca-key/actions/workflows/tests.yml)
[![License](https://img.shields.io/github/license/groupesti/laravel-ca-key)](LICENSE.md)

## Requirements

- PHP 8.4+
- Laravel 12.x or 13.x
- `groupesti/laravel-ca` ^1.0
- `phpseclib/phpseclib` ^3.0
- PHP extensions: `mbstring`, `openssl`, `json`, `pdo`

## Installation

Install the package via Composer:

```bash
composer require groupesti/laravel-ca-key
```

The service provider is auto-discovered. To publish the configuration file:

```bash
php artisan vendor:publish --tag=ca-key-config
```

To publish the migrations:

```bash
php artisan vendor:publish --tag=ca-key-migrations
```

Run the migrations:

```bash
php artisan migrate
```

## Configuration

The configuration file `config/ca-key.php` exposes the following options:

| Key | Default | Description |
|-----|---------|-------------|
| `default_algorithm` | `rsa-4096` | Default key algorithm when none is specified. Supported: `rsa-2048`, `rsa-4096`, `ecdsa-p256`, `ecdsa-p384`, `ecdsa-p521`, `ed25519`. |
| `default_rsa_bits` | `4096` | Default RSA key size in bits. |
| `default_curve` | `prime256v1` | Default elliptic curve for ECDSA keys. |
| `encryption_strategy` | `laravel` | Strategy used to encrypt private keys at rest. |
| `storage.driver` | `database` | Storage driver for key material (`database`). |
| `storage.disk` | `local` | Filesystem disk when using file-based storage. |
| `storage.path` | `ca-keys` | Subdirectory for file-based key storage. |
| `key_rotation.auto_rotate` | `false` | Enable automatic key rotation. |
| `key_rotation.rotation_days` | `365` | Days between automatic rotations. |
| `key_rotation.keep_old_keys` | `true` | Whether to retain rotated keys (soft delete). |
| `routes.enabled` | `true` | Enable the built-in API routes. |
| `routes.prefix` | `api/ca/keys` | URL prefix for the API routes. |
| `routes.middleware` | `['api']` | Middleware applied to the API routes. |

## Usage

### Generate a key pair

```php
use CA\Key\Facades\CaKey;
use CA\Models\KeyAlgorithm;

// Generate an RSA 4096-bit key
$key = CaKey::generate(algorithm: KeyAlgorithm::RSA_4096);

// Generate an ECDSA P-256 key for a specific CA and tenant
$key = CaKey::generate(
    algorithm: KeyAlgorithm::ECDSA_P256,
    params: ['ca_id' => $ca->id, 'usage' => 'signing'],
    tenantId: 'tenant-abc',
);

// Generate an Ed25519 key
$key = CaKey::generate(algorithm: KeyAlgorithm::ED25519);
```

### Import an existing key

```php
use CA\Key\Facades\CaKey;

$key = CaKey::import(
    keyData: $pemString,
    format: 'pem',
    options: [
        'passphrase' => 'my-secret',
        'ca_id' => $ca->id,
        'usage' => 'certificate',
    ],
);
```

### Export a key

```php
use CA\Key\Facades\CaKey;
use CA\Models\ExportFormat;

// Export private key as PEM
$pem = CaKey::export(key: $key, format: ExportFormat::PEM);

// Export private key as encrypted PEM
$encryptedPem = CaKey::export(
    key: $key,
    format: ExportFormat::PEM,
    passphrase: 'export-passphrase',
);

// Export private key as DER
$der = CaKey::export(key: $key, format: ExportFormat::DER);
```

### Rotate a key

```php
use CA\Key\Facades\CaKey;

// Generates a new key with the same algorithm and parameters; marks the old key as rotated
$newKey = CaKey::rotate(key: $oldKey);
```

### Destroy a key

```php
use CA\Key\Facades\CaKey;

// Wipes encrypted private key data and soft-deletes the record
CaKey::destroy(key: $key);
```

### Lookup by fingerprint

```php
use CA\Key\Facades\CaKey;

$key = CaKey::getByFingerprint('ab:cd:ef:...');
```

### Decrypt a private key for use

```php
use CA\Key\Facades\CaKey;

$privateKey = CaKey::decryptPrivateKey(key: $key);
// Returns a \phpseclib3\Crypt\Common\PrivateKey instance
```

### Artisan commands

```bash
# Generate a new key interactively
php artisan ca:key:generate

# Generate with specific options
php artisan ca:key:generate --algorithm=ecdsa-p256 --ca=1 --tenant=abc --usage=signing

# List keys with filters
php artisan ca:key:list
php artisan ca:key:list --algorithm=rsa-4096 --status=active

# Export a key (public by default)
php artisan ca:key:export <uuid>
php artisan ca:key:export <uuid> --private --passphrase=secret --output=/path/to/key.pem
php artisan ca:key:export <uuid> --format=der --output=/path/to/key.der

# Rotate a key
php artisan ca:key:rotate <uuid>
```

### API routes

When `routes.enabled` is `true`, the following endpoints are registered under the configured prefix (default `api/ca/keys`):

| Method | URI | Action |
|--------|-----|--------|
| `GET` | `/` | List all keys (paginated) |
| `POST` | `/` | Generate a new key |
| `GET` | `/{uuid}` | Show key metadata |
| `DELETE` | `/{uuid}` | Destroy a key |
| `POST` | `/{uuid}/export` | Export a key |
| `POST` | `/{uuid}/rotate` | Rotate a key |

### Events

The package dispatches the following events:

- `CA\Key\Events\KeyGenerated` -- fired after a key is generated or imported.
- `CA\Key\Events\KeyRotated` -- fired after a key rotation, contains both old and new keys.
- `CA\Key\Events\KeyDeleted` -- fired after a key is destroyed, contains the fingerprint.

### Eloquent scopes

The `Key` model provides query scopes:

```php
use CA\Key\Models\Key;

Key::active()->get();
Key::byTenant('tenant-abc')->get();
Key::byAlgorithm('rsa-4096')->get();
Key::byFingerprint('ab:cd:ef:...')->first();
```

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
