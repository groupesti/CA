# Architecture — laravel-ca-tsa (Time Stamping Authority)

## Overview

`laravel-ca-tsa` implements an RFC 3161-compliant Time Stamping Authority (TSA) server. It accepts timestamp requests containing a message digest, generates signed timestamp tokens that prove the data existed at a specific point in time, and supports token verification. Uses pure PHP ASN.1 encoding for all TSP protocol structures. It depends on `laravel-ca` (core), `laravel-ca-crt` (TSA certificate), and `laravel-ca-key` (TSA signing key).

## Directory Structure

```
src/
├── TsaServiceProvider.php             # Registers parser, builder, serial generator, server, verifier
├── Asn1/
│   └── Maps/
│       ├── Accuracy.php               # ASN.1 map for Accuracy structure
│       ├── MessageImprint.php         # ASN.1 map for MessageImprint (hash algorithm + hash value)
│       ├── PKIStatusInfo.php          # ASN.1 map for PKIStatusInfo
│       ├── TimeStampReq.php           # ASN.1 map for TimeStampReq (RFC 3161)
│       ├── TimeStampResp.php          # ASN.1 map for TimeStampResp
│       └── TSTInfo.php               # ASN.1 map for TSTInfo (the signed timestamp token content)
├── Console/
│   └── Commands/
│       ├── TsaSetupCommand.php        # Configure TSA (ca-tsa:setup)
│       ├── TsaStatusCommand.php       # Display TSA status and statistics
│       ├── TsaVerifyCommand.php       # Verify a timestamp token (ca-tsa:verify)
│       └── TsaTokenListCommand.php    # List issued timestamp tokens
├── Contracts/
│   ├── TsaServerInterface.php         # Contract for the TSA server service
│   └── TsaVerifierInterface.php       # Contract for timestamp token verification
├── Events/
│   ├── TimestampCreated.php           # Fired when a timestamp token is created
│   └── TimestampVerified.php          # Fired when a timestamp token is verified
├── Facades/
│   └── CaTsa.php                      # Facade resolving TsaServerInterface
├── Http/
│   ├── Controllers/
│   │   └── TsaController.php         # Handles TSA HTTP requests (POST timestamp requests)
│   └── Middleware/
│       └── TsaContentType.php        # Ensures application/timestamp-query content type
├── Models/
│   ├── TimestampToken.php             # Eloquent model storing issued timestamp tokens
│   └── TsaCertificate.php            # Eloquent model for the TSA signing certificate
└── Services/
    ├── TsaServer.php                  # Main service: parse request, generate token, sign response
    ├── TsaRequestParser.php           # Parses DER-encoded TimeStampReq structures
    ├── TsaResponseBuilder.php         # Builds signed TimeStampResp structures
    ├── TsaSerialGenerator.php         # Generates unique serial numbers for timestamp tokens
    └── TsaVerifier.php                # Verifies timestamp token signatures and content
```

## Service Provider

`TsaServiceProvider` registers the following:

| Category | Details |
|---|---|
| **Config** | Merges `config/ca-tsa.php`; publishes under tag `ca-tsa-config` |
| **Singletons** | `TsaSerialGenerator`, `TsaRequestParser`, `TsaResponseBuilder`, `TsaServerInterface` (resolved to `TsaServer`), `TsaVerifierInterface` (resolved to `TsaVerifier`) |
| **Alias** | `ca-tsa` points to `TsaServerInterface` |
| **Migrations** | `ca_tsa_tokens`, `ca_tsa_certificates` tables |
| **Commands** | `ca-tsa:setup`, `ca-tsa:status`, `ca-tsa:verify`, `ca-tsa:token-list` |
| **Routes** | Routes under configurable prefix (default `tsa`), gated by `ca-tsa.enabled` config |

## Key Classes

**TsaServer** -- The main TSA service. Accepts raw binary timestamp requests, delegates parsing to `TsaRequestParser`, validates the request (supported hash algorithm, nonce handling), generates a serial number, builds the TSTInfo structure with the current time, signs it with the TSA key, and returns the binary TimeStampResp via `TsaResponseBuilder`.

**TsaRequestParser** -- Parses DER-encoded RFC 3161 TimeStampReq structures. Extracts the MessageImprint (hash algorithm OID and digest value), optional nonce, certificate request flag, and TSA policy OID.

**TsaResponseBuilder** -- Constructs DER-encoded TimeStampResp structures. Builds the TSTInfo (version, policy, messageImprint, serialNumber, genTime, accuracy), wraps it in a CMS SignedData structure, signs with the TSA private key, and assembles the final response with PKIStatusInfo.

**TsaVerifier** -- Verifies timestamp tokens by checking: the CMS signature validity against the TSA certificate, the TSTInfo content (serial, time, policy), and the messageImprint matches the claimed digest. Implements `TsaVerifierInterface`.

**TsaSerialGenerator** -- Generates unique, monotonically increasing serial numbers for timestamp tokens, ensuring no two tokens share a serial.

## Design Decisions

- **Pure PHP ASN.1 for TSP**: All RFC 3161 structures (TimeStampReq, TimeStampResp, TSTInfo) are encoded/decoded using phpseclib ASN.1 with custom Maps. This avoids OpenSSL CLI dependency and provides full control over the binary protocol.

- **Separate server and verifier**: The TSA server (creates tokens) and verifier (validates tokens) are independent services with separate interfaces. This allows deploying verification-only instances without TSA signing key access.

- **Accuracy support**: The TSA response includes an Accuracy field (seconds, milliseconds, microseconds) as defined in RFC 3161, allowing relying parties to determine the precision of the timestamp.

- **TSA certificate model**: The TSA signing certificate is tracked as a separate model (`TsaCertificate`) rather than reusing the general certificate model, since TSA certificates have specific requirements (Extended Key Usage: timeStamping).

## PHP 8.4 Features Used

- **`readonly` constructor promotion**: Used in `TsaServer`, `TsaRequestParser`, `TsaResponseBuilder`, `TsaVerifier`.
- **Named arguments**: Used in service construction and event dispatch.
- **Strict types**: Every file declares `strict_types=1`.

## Extension Points

- **TsaServerInterface**: Bind a custom TSA implementation for specialized timestamping workflows.
- **TsaVerifierInterface**: Replace for custom verification logic (e.g., cross-referencing with external TSA services).
- **Events**: Listen to `TimestampCreated`, `TimestampVerified` for audit and monitoring.
- **Config `ca-tsa.enabled`**: Disable the TSA subsystem entirely.
- **Config `ca-tsa.middleware`**: Add authentication or rate-limiting middleware.
