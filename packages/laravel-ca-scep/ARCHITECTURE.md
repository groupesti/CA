# Architecture — laravel-ca-scep (Simple Certificate Enrollment Protocol)

## Overview

`laravel-ca-scep` implements an RFC 8894-compliant SCEP server for automated certificate enrollment, primarily targeting network devices and MDM systems. It handles PKCS#7-wrapped SCEP message parsing, challenge password validation, transaction tracking, and signed response building. It depends on `laravel-ca` (core), `laravel-ca-crt` (certificate issuance), `laravel-ca-csr` (CSR processing), and `laravel-ca-key` (key operations).

## Directory Structure

```
src/
├── ScepServiceProvider.php            # Registers parser, builder, challenge manager, server
├── Asn1/
│   └── Maps/
│       └── ScepPkiMessage.php         # ASN.1 map for SCEP PKI message structures
├── Console/
│   └── Commands/
│       ├── ScepSetupCommand.php       # Configure SCEP server (ca-scep:setup)
│       ├── ScepChallengeCommand.php   # Generate challenge passwords (ca-scep:challenge)
│       ├── ScepTransactionListCommand.php # List SCEP transactions
│       └── ScepCleanupCommand.php     # Clean up expired transactions and challenges
├── Contracts/
│   ├── ScepMessageParserInterface.php # Contract for SCEP message parsing
│   └── ScepServerInterface.php        # Contract for the SCEP server service
├── Events/
│   ├── ScepCertificateIssued.php      # Fired when a certificate is issued via SCEP
│   ├── ScepEnrollmentFailed.php       # Fired when enrollment fails
│   └── ScepEnrollmentRequested.php    # Fired when an enrollment request is received
├── Facades/
│   └── CaScep.php                     # Facade resolving ScepServerInterface
├── Http/
│   ├── Controllers/
│   │   └── ScepController.php         # Handles SCEP GET/POST operations (GetCACert, PKIOperation, etc.)
│   └── Middleware/
│       └── ScepContentType.php        # Ensures correct MIME types for SCEP exchanges
├── Models/
│   ├── ScepChallengePassword.php      # Eloquent model for one-time challenge passwords
│   ├── ScepTransaction.php            # Eloquent model for enrollment transactions
│   ├── ScepFailInfo.php               # Lookup subclass for SCEP failure codes
│   ├── ScepMessageType.php            # Lookup subclass for SCEP message types (PKCSReq, CertRep, etc.)
│   └── ScepPkiStatus.php             # Lookup subclass for PKI status codes (SUCCESS, FAILURE, PENDING)
└── Services/
    ├── ScepServer.php                 # Main service: handles GetCACert, GetCACaps, PKIOperation
    ├── ScepMessage.php                # Value object representing a parsed SCEP message
    ├── ScepMessageParser.php          # Parses PKCS#7 enveloped SCEP messages
    ├── ScepMessageBuilder.php         # Builds PKCS#7 signed SCEP response messages
    └── ScepChallengeManager.php       # Generates and validates one-time challenge passwords
```

## Service Provider

`ScepServiceProvider` registers the following:

| Category | Details |
|---|---|
| **Config** | Merges `config/ca-scep.php`; publishes under tag `ca-scep-config` |
| **Singletons** | `ScepChallengeManager`, `ScepMessageBuilder`, `ScepMessageParserInterface` (resolved to `ScepMessageParser`), `ScepServerInterface` (resolved to `ScepServer`) |
| **Alias** | `ca-scep` points to `ScepServerInterface` |
| **Migrations** | `ca_scep_transactions`, `ca_scep_challenge_passwords` tables |
| **Commands** | `ca-scep:setup`, `ca-scep:challenge`, `ca-scep:transaction-list`, `ca-scep:cleanup` |
| **Routes** | Routes under configurable prefix (default `scep`), with `ScepContentType` middleware |

## Key Classes

**ScepServer** -- The main service implementing SCEP operations: `GetCACert` (returns CA certificate chain), `GetCACaps` (returns server capabilities), and `PKIOperation` (processes enrollment requests). It decrypts incoming PKCS#7 messages, validates challenge passwords, extracts CSRs, delegates to the certificate manager for issuance, and returns signed PKCS#7 responses.

**ScepMessageParser** -- Decrypts and parses PKCS#7-enveloped SCEP messages. Extracts the inner PKCS#10 CSR, transaction ID, message type, sender nonce, and challenge password. Uses the RA/CA private key (via `KeyManagerInterface`) for decryption.

**ScepMessageBuilder** -- Constructs PKCS#7-signed SCEP response messages (CertRep). Wraps the issued certificate or error status in a properly formatted SCEP response with recipientNonce, transactionID, and PKI status attributes.

**ScepChallengeManager** -- Manages one-time challenge passwords used for initial enrollment authentication. Generates cryptographically random passwords, stores them with expiration, and validates/consumes them during enrollment (each password is single-use).

**ScepTransaction** -- Eloquent model tracking the state of each SCEP enrollment transaction. Records the transaction ID, status, associated CSR, and issued certificate reference.

## Design Decisions

- **PKCS#7 envelope handling in PHP**: All SCEP message encryption/decryption and signing uses phpseclib rather than shelling out to OpenSSL. This provides full control over the PKCS#7 structures and avoids compatibility issues with OpenSSL CLI versions.

- **Challenge passwords as one-time tokens**: Challenge passwords are consumed on first use, preventing replay attacks. Expired passwords are cleaned up by the `ScepCleanupCommand`.

- **Transaction-based tracking**: Every enrollment request creates a transaction record, providing a complete audit trail and enabling the PENDING status flow where manual approval may be required.

- **ScepContentType middleware**: Applied automatically to all SCEP routes to handle the non-standard MIME types used by SCEP (application/x-pki-message, application/x-x509-ca-cert, etc.).

## PHP 8.4 Features Used

- **`readonly` constructor promotion**: Used in `ScepServer`, `ScepMessageParser`, `ScepMessageBuilder`, and `ScepChallengeManager`.
- **Named arguments**: Used in service construction (e.g., `messageParser:`, `challengeManager:`, `certificateManager:`).
- **Strict types**: Every file declares `strict_types=1`.

## Extension Points

- **ScepServerInterface**: Replace for custom SCEP processing (e.g., integration with MDM platforms).
- **ScepMessageParserInterface**: Bind a custom parser for non-standard SCEP implementations.
- **Events**: Listen to `ScepEnrollmentRequested`, `ScepCertificateIssued`, `ScepEnrollmentFailed` for MDM integration and monitoring.
- **Config `ca-scep.route_prefix`**: Customize the SCEP endpoint URL.
- **Challenge passwords**: The challenge generation and validation logic can be replaced by binding a custom `ScepChallengeManager`.
