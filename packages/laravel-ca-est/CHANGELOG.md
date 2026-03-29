# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-03-29

### Added

- Initial release of the EST (Enrollment over Secure Transport) package.
- `EstServer` service implementing the full RFC 7030 protocol.
- `EstResponseBuilder` for constructing PKCS#7 and CSR attribute responses.
- `EstAuthenticator` supporting HTTP Basic and TLS client certificate authentication.
- `EstController` with endpoints for all EST operations.
- CA certificate chain retrieval (`GET /cacerts`).
- Simple enrollment (`POST /simpleenroll`) with automatic CSR validation and certificate issuance.
- Simple re-enrollment (`POST /simplereenroll`) with client certificate subject matching.
- Server-side key generation (`POST /serverkeygen`) with configurable key algorithms.
- CSR attributes endpoint (`GET /csrattrs`) returning suggested OIDs.
- Full CMC support (`POST /fullcmc`) with optional enable/disable.
- `EstAuthentication` middleware for request authentication.
- `EstContentType` middleware for content-type validation.
- `EstEnrollment` model for tracking enrollment records.
- `EstSetupCommand` Artisan command for initializing EST on a CA.
- `EstEnrollmentListCommand` Artisan command for listing enrollments.
- `EstCleanupCommand` Artisan command for removing expired enrollment records.
- Events: `EstEnrollmentRequested`, `EstCertificateIssued`, `EstReenrollmentCompleted`, `EstServerKeyGenerated`.
- Configurable route prefix, authentication modes, key algorithms, and validity periods.
- Database migrations for enrollment tracking.
