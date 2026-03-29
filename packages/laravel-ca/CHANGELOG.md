# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-03-29

### Added

- **CertificateAuthority model** with UUID primary keys, soft deletes, hierarchical parent/child relationships, and tenant isolation via the `BelongsToTenant` trait.
- **CaManager service** for creating Root and Intermediate CAs, managing CA lifecycle (activate, suspend), querying CA hierarchy, and tenant-scoped operations via `forTenant()`.
- **18 Lookup models** sharing a single `ca_lookups` table via the base `Lookup` model with automatic type-scoped global scopes: `KeyAlgorithm`, `CertificateStatus`, `CertificateType`, `RevocationReason`, `HashAlgorithm`, `ExportFormat`, `KeyStatus`, `ScepMessageType`, `ScepPkiStatus`, `ScepFailInfo`, `ChallengeStatus`, `ChallengeType`, `OrderStatus`, `AuthorizationStatus`, `CsrStatus`, `PolicySeverity`, `PolicyAction`, `NameType`.
- **CoreLookupSeeder** for populating all lookup tables with default system entries.
- **CertificateTemplate model** for defining reusable certificate profiles with key usage, extended key usage, basic constraints, subject rules, and SAN types.
- **AuditLog model** with polymorphic subject/actor relationships for tracking all CA operations.
- **Multi-tenant support** via the `BelongsToTenant` trait with automatic global scope filtering, configurable tenant column, and pluggable `TenantResolverInterface`.
- **Auditable trait** for automatic audit logging on model events.
- **HasCertificateAuthority trait** for models that belong to a CA.
- **EncryptionStrategyInterface** contract with two built-in implementations: `LaravelEncryptionStrategy` (uses APP_KEY) and `PassphraseEncryptionStrategy` (user-supplied passphrase, AES-256-CBC).
- **StorageManager** with pluggable drivers: `DatabaseDriver` and `FilesystemDriver` for storing keys and certificates.
- **SerialNumberGenerator** service for RFC 5280-compliant serial number generation.
- **DistinguishedNameBuilder** and **ExtensionBuilder** services for constructing X.509 subjects and extensions.
- **TemplateEngine** service for rendering certificate templates into certificate options.
- **DTOs:** `DistinguishedName` (readonly class with RFC 2253 parsing), `CertificateOptions`, `KeyOptions`, `ExtensionCollection`.
- **Artisan commands:** `ca:init` (interactive Root CA creation), `ca:list` (list all CAs), `ca:status` (show CA details), `ca:seed-lookups` (seed lookup tables).
- **REST API** with routes for CRUD operations on Certificate Authorities and hierarchy visualization, protected by configurable middleware.
- **CaController** with `CaResource` JSON API resource and `CreateCaRequest` form request validation.
- **CaAuthentication middleware** for API route protection.
- **Events:** `CaCreated`, `CaDeleted`, `CaSuspended`, `CertificateExpiring`, `CertificateIssued`, `CertificateRenewed`, `CertificateRevoked`, `CrlGenerated`, `OcspResponseGenerated`.
- **Exceptions:** `CaException`, `CertificateException`, `CrlException`, `CsrException`, `InvalidKeyException`, `OcspException`, `StorageException`.
- **Contracts:** `CertificateAuthorityInterface`, `EncryptionStrategyInterface`, `StorageDriverInterface`, `TenantResolverInterface`, `AuditableInterface`, `SerializableInterface`.
- **Ca facade** proxying to the `CaManager` singleton.
- **CaServiceProvider** registering all bindings, config, migrations, commands, routes, and middleware.
- **Database migrations** for `ca_lookups`, `certificate_authorities`, `ca_certificate_templates`, and `ca_audit_logs` tables.
- **Configuration file** (`config/ca.php`) with sections for tenancy, storage, encryption, defaults, serial numbers, CRL, OCSP, API, and Microsoft OID extensions.
- **Microsoft OID support** for Active Directory and Windows PKI integration.
