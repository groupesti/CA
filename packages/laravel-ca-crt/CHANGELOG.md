# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-03-29

### Added

- `Certificate` Eloquent model with UUID support, soft deletes, multi-tenancy, and auditing.
- `CertificateManager` service implementing full certificate lifecycle: issue from CSR, self-signed root, intermediate CA, revoke, renew, verify, export, and chain retrieval.
- `CertificateSigner` service for X.509 certificate signing with configurable hash algorithms, key usage, extended key usage, SAN, basic constraints, and custom extensions.
- `CertificateValidator` service for certificate validation including expiry checks, revocation status, and full signature chain verification from leaf to root.
- `CertificateExporter` service supporting PEM, DER, and PKCS#7 export formats, including chain PEM export and degenerate PKCS#7 construction.
- `CertificateRenewer` service that creates renewed certificates preserving the original subject, extensions, and Microsoft template metadata, automatically revoking the old certificate with `superseded` reason.
- `ChainBuilder` service for building and persisting certificate chains from leaf to root with circular reference protection.
- `CertificateChain` model for database-backed chain persistence with depth tracking.
- Certificate builder pattern with five specialized builders:
    - `ServerCertificateBuilder` for TLS server certificates with DNS names, IP addresses, and wildcard support.
    - `ClientCertificateBuilder` for mTLS client certificates with email and user ID support.
    - `SmimeCertificateBuilder` for S/MIME email protection certificates.
    - `CodeSigningCertificateBuilder` for code signing certificates.
    - `AdcsCertificateBuilder` for Microsoft AD CS compatible certificates with built-in templates (WebServer, User, Computer, DomainController, CodeSigning, S/MIME).
- Six built-in Microsoft AD CS certificate templates (`WebServerTemplate`, `UserTemplate`, `ComputerTemplate`, `DomainControllerTemplate`, `CodeSigningTemplate`, `SmimeTemplate`) implementing `MicrosoftTemplateInterface`.
- `TemplateResolver` for looking up Microsoft templates by name or OID, with support for custom template registration.
- `CaCrt` Facade providing a convenient static API over `CertificateManagerInterface`.
- `CrtServiceProvider` registering all services, config, migrations, commands, and API routes.
- Contract interfaces: `CertificateManagerInterface`, `CertificateSignerInterface`, `CertificateValidatorInterface`, `MicrosoftTemplateInterface`.
- Seven Artisan commands: `ca-crt:issue`, `ca-crt:list`, `ca-crt:revoke`, `ca-crt:renew`, `ca-crt:export`, `ca-crt:verify`, `ca-crt:expiring-scan`.
- REST API with `CertificateController` for listing, issuing, showing, revoking, renewing, chain retrieval, export, verification, and expiring certificate queries.
- Form request validation classes: `IssueCertificateRequest`, `RevokeCertificateRequest`, `RenewCertificateRequest`.
- `CertificateResource` JSON API resource.
- Events: `CertificateIssued`, `CertificateRevoked`, `CertificateRenewed`, `CertificateExpiring`.
- Configurable API routes with prefix and middleware support, togglable via config.
