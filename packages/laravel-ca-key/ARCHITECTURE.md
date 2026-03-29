# Architecture — laravel-ca-key (Key Management)

## Overview

`laravel-ca-key` handles the complete lifecycle of cryptographic key pairs: generation, storage, import, export, rotation, and destruction. It supports RSA (2048/4096-bit), ECDSA (P-256, P-384, P-521), and Ed25519 algorithms through a generator registry pattern, and delegates private key encryption at rest to the core package's `EncryptionStrategyInterface`.

## Directory Structure

```
src/
├── KeyServiceProvider.php             # Registers generators, manager, exporter, commands, routes
├── Console/
│   └── Commands/
│       ├── KeyGenerateCommand.php     # Generate a new key pair (ca-key:generate)
│       ├── KeyListCommand.php         # List all keys with status and fingerprint
│       ├── KeyExportCommand.php       # Export a key to PEM/DER/PKCS8
│       └── KeyRotateCommand.php       # Rotate a key (generate new, mark old as rotated)
├── Contracts/
│   ├── KeyGeneratorInterface.php      # Contract for algorithm-specific key generators
│   ├── KeyManagerInterface.php        # Contract for the main key management service
│   └── KeyStorageInterface.php        # Contract for key persistence backends
├── Events/
│   ├── KeyGenerated.php               # Fired when a new key pair is created
│   ├── KeyRotated.php                 # Fired when a key is rotated (carries old + new)
│   └── KeyDeleted.php                 # Fired when a key is destroyed
├── Facades/
│   └── CaKey.php                      # Facade resolving KeyManagerInterface
├── Generators/
│   ├── RsaKeyGenerator.php            # RSA key generation via phpseclib
│   ├── EcdsaKeyGenerator.php          # ECDSA key generation (P-256, P-384, P-521)
│   └── Ed25519KeyGenerator.php        # Ed25519 key generation
├── Http/
│   ├── Controllers/
│   │   └── KeyController.php          # REST API for key CRUD operations
│   ├── Requests/
│   │   └── GenerateKeyRequest.php     # Form request validation for key generation
│   └── Resources/
│       └── KeyResource.php            # JSON API resource (excludes private material)
├── Models/
│   ├── Key.php                        # Eloquent model storing key metadata and encrypted private key
│   └── KeyStatus.php                  # Lookup subclass for key lifecycle statuses
└── Services/
    ├── KeyGenerator.php               # Generator registry: delegates to algorithm-specific generators
    ├── KeyManager.php                 # Full lifecycle: generate, import, export, rotate, destroy
    └── KeyExporter.php                # Serializes keys to PEM, DER, or PKCS8 format
```

## Service Provider

`KeyServiceProvider` registers the following:

| Category | Details |
|---|---|
| **Config** | Merges `config/ca-key.php`; publishes under tag `ca-key-config` |
| **Singletons** | `KeyGenerator` (with RSA, ECDSA, Ed25519 generators registered), `KeyExporter`, `KeyManagerInterface` (resolved to `KeyManager`) |
| **Alias** | `ca-key` points to `KeyManagerInterface` |
| **Migrations** | `ca_keys` table |
| **Commands** | `ca-key:generate`, `ca-key:list`, `ca-key:export`, `ca-key:rotate` |
| **Routes** | API routes under configurable prefix (default `api/ca/keys`) |

## Key Classes

**KeyManager** -- The main service implementing `KeyManagerInterface`. It orchestrates key generation (delegating to the generator registry), encrypts private keys before storage, computes SHA-256 fingerprints over the DER-encoded public key, handles import from PEM/DER with automatic algorithm detection, manages rotation (generates new key while marking old as `rotated`), and supports secure destruction (blanks encrypted material before soft delete).

**KeyGenerator** -- A registry of algorithm-specific generators implementing `KeyGeneratorInterface`. Each generator (`RsaKeyGenerator`, `EcdsaKeyGenerator`, `Ed25519KeyGenerator`) knows how to produce a key pair for its algorithm family. The registry dispatches based on the `KeyAlgorithm` lookup slug.

**Key (Model)** -- Eloquent model storing the public key PEM, encrypted private key blob, algorithm identifier, SHA-256 fingerprint, status, and optional CA association. The private key is never stored in plaintext; it is always encrypted via the configured `EncryptionStrategyInterface`.

**KeyExporter** -- Converts phpseclib key objects into the requested output format (PEM, DER, PKCS8), optionally applying passphrase protection for PEM/PKCS8 exports.

## Design Decisions

- **Generator registry pattern**: Rather than a monolithic generator with switch/case logic, each algorithm family has its own generator class implementing `KeyGeneratorInterface`. This makes adding new algorithms (e.g., ML-KEM post-quantum) a matter of adding one class and registering it.

- **Fingerprint-based identity**: Keys are identified by SHA-256 fingerprint of the public key DER encoding, not by database ID. This provides a stable, content-addressable identifier that survives database migrations and matches how keys are identified in X.509 certificates (Subject Key Identifier).

- **Encryption at rest is mandatory**: There is no code path that stores a private key in plaintext. The `EncryptionStrategyInterface` is always invoked, even if the "laravel" strategy simply uses the app key.

- **Rotation creates a new record**: Key rotation does not modify the existing key in place. It creates a new key and marks the old one as `rotated`, preserving a full audit trail and allowing rollback if needed.

## PHP 8.4 Features Used

- **`final` classes**: `KeyManager` is declared `final` to prevent subclassing.
- **`readonly` constructor promotion**: Used in `KeyManager`, generators, and exporter for immutable dependency injection.
- **Named arguments**: Used extensively in events (`new KeyRotated(oldKey: ..., newKey: ...)`) and model creation.
- **`match` expressions**: Used in algorithm detection (`detectAlgorithmSlug`) for exhaustive curve and key-type matching.
- **Strict types**: Every file declares `strict_types=1`.

## Extension Points

- **KeyGeneratorInterface**: Implement to add support for new key algorithms (e.g., post-quantum algorithms like ML-DSA/Dilithium).
- **KeyManagerInterface**: Bind your own implementation to replace the default key management logic.
- **KeyStorageInterface**: Implement for alternative key storage backends (e.g., hardware security modules).
- **Events**: Listen to `KeyGenerated`, `KeyRotated`, `KeyDeleted` for audit, compliance, or notification workflows.
- **Config `ca-key.default_rsa_bits`**: Override default RSA key size globally.
- **Route/middleware config**: Disable routes or change prefix and middleware via `config/ca-key.php`.
