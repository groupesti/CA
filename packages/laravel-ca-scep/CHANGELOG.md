# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-03-29

### Added
- `ScepServer` service implementing full SCEP server operations (GetCACert, GetCACaps, PKCSReq, CertPoll, GetCert, GetCRL, GetNextCACert).
- `ScepMessageParser` for parsing PKCS#7 SignedData envelopes and decrypting EnvelopedData content using phpseclib v3.
- `ScepMessageBuilder` for constructing SCEP CertRep responses (SUCCESS, PENDING, FAILURE) as PKCS#7 SignedData structures.
- `ScepMessage` immutable DTO representing a parsed SCEP message with transaction ID, nonces, and encrypted/decrypted content.
- `ScepPkiMessage` ASN.1 maps class with OID constants and helper methods for SCEP attribute, algorithm, and content type OIDs.
- `ScepTransaction` Eloquent model with UUID primary keys, query scopes (`forCa`, `byStatus`, `byTransactionId`, `pending`, `expired`, `notExpired`), and relationship to CA and Certificate models.
- `ScepChallengePassword` Eloquent model for one-time challenge password management with hash storage, expiration, and usage tracking.
- `ScepChallengeManager` service for generating, validating, and cleaning up challenge passwords.
- `ScepController` handling GET and POST SCEP requests with proper content-type routing (`application/x-pki-message`, `application/x-x509-ca-cert`, `application/x-x509-ca-ra-cert`, `text/plain`).
- `ScepContentType` middleware for automatic SCEP response content-type headers.
- `ScepServerInterface` and `ScepMessageParserInterface` contracts for dependency injection.
- `CaScep` facade proxying to the `ScepServerInterface`.
- `ScepServiceProvider` with config merging, migration loading, route registration, and singleton bindings.
- `ca:scep:setup` Artisan command to verify SCEP readiness for a Certificate Authority.
- `ca:scep:challenge` Artisan command to generate one-time challenge passwords with optional purpose and TTL.
- `ca:scep:transactions` Artisan command to list SCEP transactions with CA, status, and limit filters.
- `ca:scep:cleanup` Artisan command to remove expired transactions and challenge passwords with dry-run support.
- `ScepEnrollmentRequested`, `ScepCertificateIssued`, and `ScepEnrollmentFailed` events for enrollment lifecycle hooks.
- API routes at `/{prefix}/{ca_uuid}/pkiclient.exe` (GET and POST) following the standard SCEP URL convention.
- Database migrations for `ca_scep_transactions` and `ca_scep_challenge_passwords` tables.
- Support for AES-256-CBC, AES-128-CBC, and 3DES symmetric encryption algorithms.
- Support for SHA-256 and SHA-512 hash algorithms.
- Configuration file (`config/ca-scep.php`) with options for enabling/disabling, route prefix, challenge password TTL, auto-approve, capabilities, and allowed algorithms.
