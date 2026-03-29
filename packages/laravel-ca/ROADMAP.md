# Roadmap

## v0.1.0 -- Core Foundation (Released 2026-03-29)

- [x] CertificateAuthority model with UUID, soft deletes, and hierarchical relationships
- [x] CaManager service for Root and Intermediate CA creation and lifecycle management
- [x] 18 database-backed Lookup models replacing static enums
- [x] CoreLookupSeeder for populating all lookup tables
- [x] CertificateTemplate model for reusable issuance profiles
- [x] AuditLog model with polymorphic subject/actor tracking
- [x] Multi-tenant support via BelongsToTenant trait and TenantResolverInterface
- [x] Auditable trait for automatic audit logging on model events
- [x] HasCertificateAuthority trait for related models
- [x] EncryptionStrategyInterface with Laravel and Passphrase implementations
- [x] StorageManager with Database and Filesystem drivers
- [x] SerialNumberGenerator (RFC 5280-compliant)
- [x] DistinguishedNameBuilder and ExtensionBuilder services
- [x] TemplateEngine service
- [x] DTOs: DistinguishedName, CertificateOptions, KeyOptions, ExtensionCollection
- [x] Artisan commands: ca:init, ca:list, ca:status, ca:seed-lookups
- [x] REST API with CRUD routes and hierarchy endpoint
- [x] CaAuthentication middleware
- [x] 9 domain events for CA and certificate lifecycle
- [x] 7 specific exception classes
- [x] 6 contracts/interfaces
- [x] Ca facade
- [x] Database migrations for ca_lookups, certificate_authorities, ca_certificate_templates, ca_audit_logs
- [x] Configuration file with tenancy, storage, encryption, defaults, CRL, OCSP, API, and Microsoft OID sections

## v0.2.0 -- Key Management (Planned)

- [ ] Key model with lifecycle management (generate, rotate, destroy)
- [ ] Key generation service using phpseclib (RSA, ECDSA, Ed25519)
- [ ] Key storage encryption via EncryptionStrategyInterface
- [ ] Key rotation workflow with automatic child re-signing
- [ ] Key export in PEM, DER, and PKCS#12 formats
- [ ] Artisan command: ca:rotate-key

## v0.3.0 -- Certificate Issuance (Planned)

- [ ] Certificate model with full X.509 v3 field support
- [ ] CSR parsing and validation service
- [ ] Certificate signing service (Root and Intermediate signing)
- [ ] Template-based issuance with policy enforcement
- [ ] Certificate chain building and validation
- [ ] Artisan command: ca:issue

## v0.4.0 -- Revocation (Planned)

- [ ] CRL generation and publishing service
- [ ] OCSP responder implementation
- [ ] Certificate revocation workflow with reason codes
- [ ] Delta CRL support
- [ ] Artisan commands: ca:generate-crl, ca:revoke

## v1.0.0 -- Stable Release

- [ ] Full test coverage (>= 90%)
- [ ] PHPStan level 9 pass on entire codebase
- [ ] Complete API documentation
- [ ] Laravel 12.x and 13.x dual support
- [ ] Performance benchmarks and optimization
- [ ] CA health check and status monitoring service
- [ ] CA backup and disaster recovery tooling
- [ ] UPGRADE.md for migration from 0.x

## Ideas / Backlog

- SCEP protocol server implementation
- ACME protocol server (RFC 8555) implementation
- EST protocol (RFC 7030) support
- Hardware Security Module (HSM) integration via PKCS#11
- Web-based admin panel (separate package)
- Certificate transparency log submission
- Automated certificate renewal scheduler
- Prometheus/Grafana metrics exporter
- LDAP directory publishing
- Microsoft AD CS compatibility layer
- Cross-certification support between CAs
- CA key ceremony workflow support
- GraphQL API support for CA management
- Import/export CA configurations between environments
