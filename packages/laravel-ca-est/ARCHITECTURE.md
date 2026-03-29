# Architecture — laravel-ca-est (Enrollment over Secure Transport)

## Overview

`laravel-ca-est` implements an RFC 7030-compliant EST server for certificate enrollment, re-enrollment, and server-side key generation over HTTPS. EST provides a modern, REST-based alternative to SCEP for certificate management. It supports HTTP Basic and TLS client certificate authentication. It depends on `laravel-ca` (core), `laravel-ca-crt` (certificate issuance), `laravel-ca-csr` (CSR processing), and `laravel-ca-key` (key generation).

## Directory Structure

```
src/
├── EstServiceProvider.php             # Registers response builder, authenticator, server
├── Console/
│   └── Commands/
│       ├── EstSetupCommand.php        # Configure EST server (ca-est:setup)
│       ├── EstEnrollmentListCommand.php # List EST enrollment records
│       └── EstCleanupCommand.php      # Clean up expired enrollments
├── Contracts/
│   └── EstServerInterface.php         # Contract for the EST server service
├── Events/
│   ├── EstCertificateIssued.php       # Fired when a certificate is issued via EST
│   ├── EstEnrollmentRequested.php     # Fired when an enrollment request is received
│   ├── EstReenrollmentCompleted.php   # Fired when re-enrollment completes
│   └── EstServerKeyGenerated.php      # Fired when server-side key generation occurs
├── Facades/
│   └── CaEst.php                      # Facade resolving EstServerInterface
├── Http/
│   ├── Controllers/
│   │   └── EstController.php          # Implements EST endpoints: /cacerts, /simpleenroll, /simplereenroll, /serverkeygen
│   └── Middleware/
│       ├── EstAuthentication.php      # HTTP Basic or TLS client cert authentication
│       └── EstContentType.php         # Handles application/pkcs10 and application/pkcs7-mime types
├── Models/
│   └── EstEnrollment.php             # Eloquent model tracking EST enrollment records
└── Services/
    ├── EstServer.php                  # Main service implementing EST protocol operations
    ├── EstAuthenticator.php           # Handles HTTP Basic auth and TLS client certificate validation
    └── EstResponseBuilder.php         # Builds EST responses in correct PKCS#7/PKCS#10 format
```

## Service Provider

`EstServiceProvider` registers the following:

| Category | Details |
|---|---|
| **Config** | Merges `config/ca-est.php`; publishes under tag `ca-est-config` |
| **Singletons** | `EstResponseBuilder`, `EstAuthenticator`, `EstServerInterface` (resolved to `EstServer`) |
| **Alias** | `ca-est` points to `EstServerInterface` |
| **Migrations** | `ca_est_enrollments` table |
| **Commands** | `ca-est:setup`, `ca-est:enrollment-list`, `ca-est:cleanup` |
| **Routes** | Routes under configurable prefix (default `.well-known/est`), with `EstAuthentication` and `EstContentType` middleware |

## Key Classes

**EstServer** -- Implements the four core EST operations: `/cacerts` (distribute CA certificates), `/simpleenroll` (initial enrollment from PKCS#10 CSR), `/simplereenroll` (certificate renewal), and `/serverkeygen` (server-side key generation with PKCS#7 response). Coordinates with the certificate, CSR, and key managers for actual PKI operations.

**EstAuthenticator** -- Handles EST client authentication. Supports HTTP Basic authentication (username/password) and TLS client certificate authentication (extracting the client cert from the TLS handshake). The authentication method is configurable.

**EstResponseBuilder** -- Formats EST responses according to RFC 7030. Wraps certificates in PKCS#7 certs-only structures, handles Base64 encoding of PKCS#10 CSRs, and sets appropriate Content-Type headers.

**EstEnrollment** -- Eloquent model recording each EST enrollment: the client identity, enrollment type (enroll/reenroll/keygen), associated certificate reference, and timestamps.

## Design Decisions

- **Well-known URI**: EST endpoints are mounted under `/.well-known/est` by default, following RFC 7030 Section 3.2.2. This can be overridden via config for environments where the well-known path is not available.

- **Dual authentication middleware**: `EstAuthentication` supports both HTTP Basic and TLS client certificate auth, applied as route middleware. For re-enrollment, the existing client certificate serves as the authentication credential.

- **Server-side key generation**: The `/serverkeygen` endpoint generates a key pair on the server side and returns both the certificate and the private key (encrypted with the client's public key). This supports devices that cannot generate their own key pairs.

- **Minimal model surface**: EST is simpler than ACME or SCEP, so the package has a single model (`EstEnrollment`) rather than the multi-model approach of the ACME package.

## PHP 8.4 Features Used

- **`readonly` constructor promotion**: Used in `EstServer`, `EstAuthenticator`, and `EstResponseBuilder`.
- **Named arguments**: Used in service construction (`certificateManager:`, `csrManager:`, `keyManager:`, `responseBuilder:`, `authenticator:`).
- **Strict types**: Every file declares `strict_types=1`.

## Extension Points

- **EstServerInterface**: Bind a custom EST server implementation for modified enrollment workflows.
- **EstAuthenticator**: Replace to integrate with external authentication systems (LDAP, RADIUS, OAuth).
- **Events**: Listen to `EstCertificateIssued`, `EstEnrollmentRequested`, `EstReenrollmentCompleted`, `EstServerKeyGenerated` for audit and monitoring.
- **Config `ca-est.route_prefix`**: Change the EST endpoint path.
- **Config `ca-est.middleware`**: Add custom middleware for rate limiting or additional auth.
