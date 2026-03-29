# Architecture — laravel-ca (Core)

## Overview

`laravel-ca` is the foundational package of the Laravel CA ecosystem. It provides the shared infrastructure that every other package depends on: the Certificate Authority model and hierarchy management, the Lookup-based type system, storage abstraction, encryption strategies, audit logging, shared DTOs, events, exceptions, and the core API routing. It solves the problem of establishing a multi-tenant, extensible CA backbone within a Laravel application without external binary dependencies.

## Directory Structure

```
src/
├── CaServiceProvider.php              # Registers all core bindings, config, routes, commands
├── Console/
│   └── Commands/
│       ├── CaInitCommand.php          # Initialize a new Root or Intermediate CA
│       ├── CaListCommand.php          # List all Certificate Authorities
│       ├── CaStatusCommand.php        # Display status of a specific CA
│       └── SeedLookupsCommand.php     # Seed the ca_lookups table with system records
├── Contracts/
│   ├── AuditableInterface.php         # Contract for models that emit audit entries
│   ├── CertificateAuthorityInterface.php  # Contract for the CA model
│   ├── EncryptionStrategyInterface.php    # Contract for private-key encryption at rest
│   ├── SerializableInterface.php      # Contract for PEM/DER serialization
│   ├── StorageDriverInterface.php     # Contract for storage backends (DB, filesystem)
│   └── TenantResolverInterface.php    # Contract for multi-tenant ID resolution
├── DTOs/
│   ├── CertificateOptions.php         # Immutable value object for cert issuance options
│   ├── DistinguishedName.php          # Immutable RFC 2253 DN representation
│   ├── ExtensionCollection.php        # Typed collection of X.509 extensions
│   └── KeyOptions.php                 # Immutable value object for key generation options
├── Encryption/
│   ├── LaravelEncryptionStrategy.php  # Encrypts private keys via Laravel's Encrypter
│   └── PassphraseEncryptionStrategy.php # Encrypts private keys with a user-supplied passphrase
├── Events/
│   ├── CaCreated.php                  # Fired when a CA is created
│   ├── CaDeleted.php                  # Fired when a CA is deleted
│   ├── CaSuspended.php               # Fired when a CA is suspended
│   ├── CertificateExpiring.php        # Fired when a certificate approaches expiry
│   ├── CertificateIssued.php          # Fired when a certificate is issued
│   ├── CertificateRenewed.php         # Fired when a certificate is renewed
│   ├── CertificateRevoked.php         # Fired when a certificate is revoked
│   ├── CrlGenerated.php              # Fired when a CRL is generated
│   └── OcspResponseGenerated.php      # Fired when an OCSP response is built
├── Exceptions/
│   ├── CaException.php               # General CA operation errors
│   ├── CertificateException.php       # Certificate-level errors
│   ├── CrlException.php              # CRL generation/parsing errors
│   ├── CsrException.php              # CSR validation/creation errors
│   ├── InvalidKeyException.php        # Key loading or algorithm errors
│   ├── OcspException.php             # OCSP processing errors
│   └── StorageException.php          # Storage read/write errors
├── Facades/
│   └── Ca.php                         # Facade resolving CaManager
├── Http/
│   ├── Controllers/
│   │   └── CaController.php          # CRUD API for Certificate Authorities
│   ├── Middleware/
│   │   └── CaAuthentication.php       # Auth guard for CA API endpoints
│   ├── Requests/
│   │   └── CreateCaRequest.php        # Form request validation for CA creation
│   └── Resources/
│       └── CaResource.php            # JSON API resource for CA serialization
├── Models/
│   ├── Lookup.php                     # Base polymorphic lookup model (STI via global scope)
│   ├── CertificateAuthority.php       # Eloquent model for the CA entity
│   ├── CertificateTemplate.php        # Eloquent model for certificate templates
│   ├── AuditLog.php                   # Audit trail model
│   ├── AuthorizationStatus.php        # Lookup subclass for ACME authorization statuses
│   ├── CertificateStatus.php          # Lookup subclass: active, revoked, expired, suspended, on_hold
│   ├── CertificateType.php            # Lookup subclass: root_ca, intermediate_ca, server_tls, etc.
│   ├── ChallengeStatus.php            # Lookup subclass for ACME challenge statuses
│   ├── ChallengeType.php              # Lookup subclass for ACME challenge types
│   ├── CsrStatus.php                  # Lookup subclass: pending, approved, rejected
│   ├── ExportFormat.php               # Lookup subclass: pem, der, pkcs8
│   ├── HashAlgorithm.php              # Lookup subclass: sha256, sha384, sha512
│   ├── KeyAlgorithm.php               # Lookup subclass: rsa-2048, rsa-4096, ecdsa-p256, ed25519, etc.
│   ├── KeyStatus.php                  # Lookup subclass: active, rotated, destroyed
│   ├── NameType.php                   # Lookup subclass for name constraint types
│   ├── OrderStatus.php                # Lookup subclass for ACME order statuses
│   ├── PolicyAction.php               # Lookup subclass for policy engine actions
│   ├── PolicySeverity.php             # Lookup subclass for policy violation severity
│   ├── RevocationReason.php           # Lookup subclass: keyCompromise, caCompromise, etc.
│   ├── ScepFailInfo.php               # Lookup subclass for SCEP failure codes
│   ├── ScepMessageType.php            # Lookup subclass for SCEP message types
│   └── ScepPkiStatus.php             # Lookup subclass for SCEP PKI statuses
├── Services/
│   ├── CaManager.php                  # Core service: create/find/suspend/activate CAs
│   ├── DistinguishedNameBuilder.php   # Fluent builder for DistinguishedName DTOs
│   ├── ExtensionBuilder.php           # Fluent builder for X.509 extension collections
│   ├── SerialNumberGenerator.php      # Cryptographically random serial number generation
│   └── TemplateEngine.php            # Resolves certificate templates to extension sets
├── Storage/
│   ├── StorageManager.php             # Manager pattern selecting database or filesystem driver
│   ├── DatabaseDriver.php             # Stores blobs in the database
│   └── FilesystemDriver.php           # Stores blobs on a Laravel filesystem disk
└── Traits/
    ├── Auditable.php                  # Trait to auto-record model changes in AuditLog
    ├── BelongsToTenant.php            # Trait adding tenant scoping to models
    └── HasCertificateAuthority.php    # Trait adding the `certificateAuthority` relationship
```

## Service Provider

`CaServiceProvider` registers the following:

| Category | Details |
|---|---|
| **Config** | Merges `config/ca.php`; publishes under tag `ca-config` |
| **Singletons** | `StorageManager`, `EncryptionStrategyInterface` (resolved to Laravel or Passphrase strategy via config), `CaManager` |
| **Migrations** | 4 migrations: `ca_lookups`, `certificate_authorities`, `ca_certificate_templates`, `ca_audit_logs` |
| **Commands** | `ca:init`, `ca:list`, `ca:status`, `ca:seed-lookups` |
| **Routes** | API routes under configurable prefix (default `api/ca`), gated by configurable middleware |
| **Middleware** | Alias `ca.auth` pointing to `CaAuthentication` |

## Key Classes

**CaManager** -- The central orchestration service for Certificate Authority lifecycle management. It creates root and intermediate CAs, enforces path-length constraints and validity date bounds, manages suspension and activation cascading to child CAs, provides tenant-scoped querying via a fluent `forTenant()` method, and builds the CA hierarchy tree structure.

**Lookup** -- A polymorphic base model implementing a Single Table Inheritance pattern via Eloquent global scopes. Each subclass (e.g., `CertificateStatus`, `KeyAlgorithm`) sets a static `$lookupType` that automatically filters and tags rows in the shared `ca_lookups` table. Provides cached `fromSlug()` resolution and protection against deleting system records.

**DistinguishedName** -- A `final readonly` DTO representing an X.500/RFC 2253 Distinguished Name. It supports construction from arrays, strings, or named arguments, and provides `toArray()` and `toString()` serialization with proper RFC 2253 character escaping.

**StorageManager** -- Implements the Manager pattern to abstract blob storage. Selects between `DatabaseDriver` and `FilesystemDriver` based on configuration, allowing private keys and certificates to be stored in the database or on a Laravel filesystem disk.

**SerialNumberGenerator** -- Generates cryptographically random serial numbers of configurable byte length (default 20 bytes / 160 bits) for certificates and CRLs.

**CertificateAuthority** -- The Eloquent model representing a CA entity. Supports parent-child relationships for CA hierarchies, tenant scoping, status lifecycle, and stores subject DN as a JSON column.

## Design Decisions

- **Lookup model instead of PHP enums**: The type system uses database-backed Lookup records rather than PHP 8.1 enums. This allows runtime extensibility (new statuses, algorithms, or types can be added without code changes), supports user-facing metadata (descriptions, sort order), and enables the seeder pattern. The trade-off is database queries, mitigated by aggressive caching in `fromSlug()`.

- **Pure PHP cryptography via phpseclib**: All cryptographic operations use `phpseclib/phpseclib` v3 instead of OpenSSL CLI or PHP's `openssl_*` functions. This provides cross-platform portability, avoids shell escaping vulnerabilities, and gives full PHP-level control over ASN.1 encoding.

- **Encryption strategy pattern**: Private keys at rest are encrypted through a pluggable strategy interface. The default `LaravelEncryptionStrategy` uses Laravel's built-in Encrypter (AES-256-CBC with the app key), while `PassphraseEncryptionStrategy` supports user-provided passphrases. This pattern allows custom HSM integrations by implementing `EncryptionStrategyInterface`.

- **Multi-tenancy as opt-in**: Tenant support is disabled by default and activated via `CA_TENANT_ENABLED`. When enabled, models use the `BelongsToTenant` trait and `TenantResolverInterface` to scope all queries. This avoids overhead for single-tenant deployments.

- **Events on every state change**: All CA lifecycle actions (create, suspend, activate, delete) dispatch domain events, enabling audit trails, notifications, and webhook integrations without coupling to the core service.

## PHP 8.4 Features Used

- **`readonly` classes**: DTOs (`DistinguishedName`, `CertificateOptions`, `KeyOptions`, `ExtensionCollection`) are declared as `final readonly class`, enforcing immutability at the language level.
- **Constructor property promotion**: Used throughout services (`CaManager`, `StorageManager`) and DTOs for concise dependency injection.
- **Named arguments**: Used extensively when constructing DTOs and dispatching events (e.g., `new CaManager(storageManager: ..., serialNumberGenerator: ..., encryptionStrategy: ...)`).
- **`match` expressions**: Used in `EncryptionStrategyInterface` resolution and algorithm detection for exhaustive pattern matching.
- **`final` classes**: Services and DTOs are declared `final` to prevent unintended inheritance and enforce composition.
- **Strict types**: Every file declares `strict_types=1`.

## Extension Points

- **EncryptionStrategyInterface**: Implement this interface to provide custom encryption (e.g., HSM, AWS KMS, Vault Transit) for private key storage.
- **StorageDriverInterface**: Implement to add storage backends beyond database and filesystem (e.g., S3 direct, encrypted blob store).
- **TenantResolverInterface**: Implement to integrate with your multi-tenancy framework (e.g., Spatie Multitenancy, Tenancy for Laravel).
- **Events**: Listen to `CaCreated`, `CaSuspended`, `CertificateIssued`, `CertificateRevoked`, etc., to trigger webhooks, Slack notifications, or compliance workflows.
- **Lookup seeder**: Extend `CoreLookupSeeder` or register additional lookup types to add custom certificate types, statuses, or algorithms.
- **Middleware alias `ca.auth`**: Replace the `CaAuthentication` middleware binding to implement custom authorization logic for CA API endpoints.
- **Config overrides**: All defaults (validity periods, hash algorithms, API prefix, storage driver) are configurable via `config/ca.php` and environment variables.
