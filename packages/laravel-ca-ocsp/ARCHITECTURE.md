# Architecture — laravel-ca-ocsp (Online Certificate Status Protocol)

## Overview

`laravel-ca-ocsp` implements an RFC 6960-compliant OCSP responder within Laravel. It parses binary OCSP requests, resolves certificate revocation status from the database, and builds signed OCSP responses -- all using pure PHP ASN.1 encoding via phpseclib, with no external OCSP tools required. It depends on `laravel-ca` (core) and `laravel-ca-crt` (certificate and revocation data).

## Directory Structure

```
src/
├── OcspServiceProvider.php            # Registers parsers, builder, resolver, responder
├── Asn1/
│   ├── CertIdParser.php               # Parses CertID structures from OCSP requests
│   ├── OcspRequestParser.php          # Parses full OCSPRequest ASN.1 structures
│   ├── OcspResponseBuilder.php        # Builds signed BasicOCSPResponse ASN.1
│   └── Maps/
│       ├── BasicOCSPResponse.php      # ASN.1 map for BasicOCSPResponse
│       ├── CertID.php                 # ASN.1 map for CertID
│       ├── CertStatus.php            # ASN.1 map for CertStatus (good/revoked/unknown)
│       ├── OCSPRequest.php            # ASN.1 map for OCSPRequest
│       ├── OCSPResponse.php           # ASN.1 map for OCSPResponse wrapper
│       ├── ResponseData.php           # ASN.1 map for ResponseData
│       ├── SingleResponse.php         # ASN.1 map for SingleResponse
│       └── TBSRequest.php            # ASN.1 map for TBSRequest
├── Console/
│   └── Commands/
│       ├── OcspSetupCommand.php       # Configure OCSP responder certificate (ca-ocsp:setup)
│       └── OcspStatusCommand.php      # Display OCSP responder status and statistics
├── Contracts/
│   ├── CertificateStatusResolverInterface.php # Contract for resolving cert status
│   └── OcspResponderInterface.php     # Contract for the OCSP responder service
├── DTOs/
│   └── CertStatusResult.php           # Readonly DTO: status, revocationTime, reason, thisUpdate, nextUpdate
├── Events/
│   └── OcspResponseGenerated.php      # Fired when an OCSP response is built
├── Facades/
│   └── CaOcsp.php                     # Facade resolving OcspResponderInterface
├── Http/
│   ├── Controllers/
│   │   └── OcspController.php         # Handles GET and POST OCSP requests (binary HTTP)
│   └── Middleware/
│       └── OcspContentType.php        # Ensures correct Content-Type for OCSP exchanges
├── Models/
│   ├── OcspResponse.php               # Eloquent model storing OCSP response history
│   └── OcspResponderCertificate.php   # Eloquent model for the OCSP signing certificate
└── Services/
    ├── CertificateStatusResolver.php  # Resolves certificate status by matching CertID against stored certificates
    └── OcspResponder.php              # Full request/response pipeline: parse, resolve, sign, respond
```

## Service Provider

`OcspServiceProvider` registers the following:

| Category | Details |
|---|---|
| **Config** | Merges `config/ca-ocsp.php`; publishes under tag `ca-ocsp-config` |
| **Singletons** | `CertIdParser`, `OcspRequestParser`, `OcspResponseBuilder`, `CertificateStatusResolverInterface` (resolved to `CertificateStatusResolver`), `OcspResponderInterface` (resolved to `OcspResponder`) |
| **Alias** | `ca-ocsp` points to `OcspResponderInterface` |
| **Migrations** | `ca_ocsp_responses`, `ca_ocsp_responder_certificates` tables |
| **Commands** | `ca-ocsp:setup`, `ca-ocsp:status` |
| **Routes** | Routes under configurable prefix (default `ocsp`), no middleware by default for public OCSP access |

## Key Classes

**OcspResponder** -- The main pipeline service. It accepts raw binary OCSP request data, delegates parsing to `OcspRequestParser`, resolves each queried certificate's status via `CertificateStatusResolver`, builds the ASN.1 response via `OcspResponseBuilder`, signs it with the OCSP responder's private key, and returns the binary response. Handles both single and multi-certificate requests.

**OcspRequestParser** -- Decodes DER-encoded OCSP requests into structured PHP data. Extracts the TBSRequest, list of CertID objects, and optional nonce extension. Uses the ASN.1 Maps for structure definition.

**OcspResponseBuilder** -- Constructs a DER-encoded BasicOCSPResponse containing SingleResponse entries for each queried certificate. Sets the response status, embeds thisUpdate/nextUpdate timestamps, and signs with the responder key.

**CertificateStatusResolver** -- Takes a parsed CertID (issuer name hash, issuer key hash, serial number) and resolves the certificate's current status (`good`, `revoked`, `unknown`) by querying the certificate database. Returns a `CertStatusResult` DTO with revocation details if applicable.

**CertStatusResult** -- A `readonly` DTO carrying the resolved status, optional revocation time and reason, and thisUpdate/nextUpdate timestamps.

## Design Decisions

- **Pure PHP ASN.1**: All OCSP request parsing and response building is done in PHP using phpseclib's ASN.1 engine with custom Maps. This avoids shelling out to OpenSSL and ensures full control over the binary protocol.

- **ASN.1 Maps directory**: The `Asn1/Maps/` directory contains class-based ASN.1 structure definitions (not YAML or JSON). Each map class defines the ASN.1 type tree for its corresponding RFC structure, used by phpseclib's ASN.1 encoder/decoder.

- **Public endpoint (no auth by default)**: OCSP endpoints are intentionally public (no middleware by default) since OCSP clients (browsers, TLS libraries) need unauthenticated access. Custom middleware can be added via config.

- **Response caching via model**: OCSP responses are stored in `OcspResponse` model, allowing the controller to serve cached responses for repeated queries and providing an audit trail.

## PHP 8.4 Features Used

- **`readonly` classes**: `CertStatusResult` is a `readonly class` with immutable properties including typed `Carbon` instances.
- **Constructor property promotion**: Used in all services and the DTO.
- **Named arguments**: Used in `CertStatusResult` construction and service wiring.
- **Strict types**: Every file declares `strict_types=1`.

## Extension Points

- **CertificateStatusResolverInterface**: Replace to integrate with external OCSP sources or custom status lookup logic.
- **OcspResponderInterface**: Bind a custom responder for alternative OCSP processing (e.g., delegated responders).
- **Events**: Listen to `OcspResponseGenerated` for monitoring OCSP traffic and response metrics.
- **Config `ca-ocsp.middleware`**: Add authentication or rate-limiting middleware for OCSP endpoints.
- **Config `ca-ocsp.response_lifetime_minutes`**: Control how long OCSP responses remain valid (affects nextUpdate).
