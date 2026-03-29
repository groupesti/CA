# Architecture — laravel-ca-acme (ACME Protocol Server)

## Overview

`laravel-ca-acme` implements an RFC 8555-compliant ACME (Automatic Certificate Management Environment) server within Laravel. It handles account registration, order creation, domain authorization with multiple challenge types (HTTP-01, DNS-01, TLS-ALPN-01), certificate finalization, and nonce management. This allows the Laravel CA to act as a Let's Encrypt-compatible certificate authority. It depends on `laravel-ca` (core), `laravel-ca-crt` (certificate issuance), `laravel-ca-csr` (CSR processing), and `laravel-ca-key` (key operations).

## Directory Structure

```
src/
├── AcmeServiceProvider.php            # Registers server, validators, verifier, nonce manager
├── Console/
│   └── Commands/
│       ├── AcmeSetupCommand.php       # Initialize ACME server configuration (ca-acme:setup)
│       ├── AcmeAccountListCommand.php # List registered ACME accounts
│       ├── AcmeOrderListCommand.php   # List ACME orders with status
│       └── AcmeCleanupCommand.php     # Clean up expired nonces and stale orders
├── Contracts/
│   ├── AcmeServerInterface.php        # Contract for the ACME server service
│   └── ChallengeValidatorInterface.php # Contract for challenge validation implementations
├── Events/
│   ├── AcmeAccountCreated.php         # Fired when an ACME account is registered
│   ├── AcmeOrderCreated.php           # Fired when an order is placed
│   ├── AcmeChallengeCompleted.php     # Fired when a challenge is validated
│   ├── AcmeOrderFinalized.php         # Fired when an order is finalized
│   └── AcmeCertificateIssued.php      # Fired when a certificate is issued via ACME
├── Facades/
│   └── CaAcme.php                     # Facade resolving AcmeServerInterface
├── Http/
│   ├── Controllers/
│   │   └── AcmeController.php         # Implements all ACME API endpoints (directory, newNonce, newAccount, newOrder, etc.)
│   └── Middleware/
│       ├── AcmeContentType.php        # Ensures application/jose+json content type
│       └── AcmeJwsMiddleware.php      # Validates JWS signatures on ACME requests
├── Models/
│   ├── AcmeAccount.php                # Eloquent model for ACME account (JWK, contact, status)
│   ├── AcmeOrder.php                  # Eloquent model for certificate orders
│   ├── AcmeAuthorization.php          # Eloquent model for domain authorizations
│   ├── AcmeChallenge.php              # Eloquent model for individual challenges
│   ├── AcmeNonce.php                  # Eloquent model for replay nonce storage
│   ├── AuthorizationStatus.php        # Lookup subclass for authorization statuses
│   ├── ChallengeStatus.php            # Lookup subclass for challenge statuses
│   ├── ChallengeType.php              # Lookup subclass for challenge types (http-01, dns-01, tls-alpn-01)
│   └── OrderStatus.php               # Lookup subclass for order statuses
├── Order/
│   └── OrderProcessor.php            # Orchestrates the order lifecycle: pending -> ready -> processing -> valid
└── Services/
    ├── AcmeServer.php                 # Main service implementing the ACME protocol logic
    ├── JwsVerifier.php                # Verifies JWS (JSON Web Signature) on ACME requests
    ├── NonceManager.php               # Generates, validates, and cleans up replay nonces
    └── Challenges/
        ├── Http01Validator.php        # HTTP-01 challenge validation (well-known token check)
        ├── Dns01Validator.php         # DNS-01 challenge validation (TXT record check)
        └── TlsAlpn01Validator.php     # TLS-ALPN-01 challenge validation
```

## Service Provider

`AcmeServiceProvider` registers the following:

| Category | Details |
|---|---|
| **Config** | Merges `config/ca-acme.php`; publishes under tag `ca-acme-config` |
| **Singletons** | `JwsVerifier`, `NonceManager`, `OrderProcessor`, `AcmeServerInterface` (resolved to `AcmeServer`) |
| **Challenge validators** | `acme.validator.http-01`, `acme.validator.dns-01`, `acme.validator.tls-alpn-01` (bound by name) |
| **Alias** | `ca-acme` points to `AcmeServerInterface` |
| **Migrations** | 5 tables: `ca_acme_accounts`, `ca_acme_orders`, `ca_acme_authorizations`, `ca_acme_challenges`, `ca_acme_nonces` |
| **Commands** | `ca-acme:setup`, `ca-acme:account-list`, `ca-acme:order-list`, `ca-acme:cleanup` |
| **Routes** | Loaded via `loadRoutesFrom()` (standard ACME URL structure), gated by `ca-acme.enabled` config |

## Key Classes

**AcmeServer** -- Implements the complete RFC 8555 ACME server protocol. Handles directory resource, account creation and updates, order placement with identifier validation, authorization and challenge management, order finalization (CSR submission and certificate issuance), and certificate download. Coordinates with the certificate, CSR, and key packages for actual PKI operations.

**JwsVerifier** -- Validates the JSON Web Signature (JWS) on every ACME request. Extracts the JWK from the protected header, verifies the signature against the account's public key, and checks the replay nonce. Supports both RS256 and ES256 algorithms.

**NonceManager** -- Manages ACME replay protection nonces. Generates cryptographically random nonces, stores them in the `AcmeNonce` model, validates them on incoming requests (each nonce is single-use), and provides cleanup for expired nonces.

**OrderProcessor** -- Orchestrates the ACME order lifecycle state machine: `pending` (awaiting authorization) -> `ready` (all authorizations valid) -> `processing` (CSR submitted) -> `valid` (certificate issued). Handles status transitions and triggers certificate issuance when the order reaches the `processing` state.

**Challenge validators** -- Three implementations of `ChallengeValidatorInterface`, each validating a different ACME challenge type. `Http01Validator` checks for the token at `/.well-known/acme-challenge/`. `Dns01Validator` queries for the expected TXT record. `TlsAlpn01Validator` validates the TLS-ALPN extension.

## Design Decisions

- **Named binding for challenge validators**: Challenge validators are bound by name (`acme.validator.http-01`, etc.) rather than a tagged set, allowing individual replacement and lazy resolution based on the challenge type.

- **Routes loaded directly**: Unlike other packages that conditionally register routes, the ACME package loads routes via `loadRoutesFrom()` because ACME URLs follow a strict standard path structure that cannot be arbitrarily prefixed.

- **Full ACME state machine**: The `OrderProcessor` implements the complete ACME order state machine as defined in RFC 8555 Section 7.1.6, including all valid transitions and error states.

- **Separate models for each ACME entity**: Rather than a single polymorphic model, each ACME concept (account, order, authorization, challenge, nonce) has its own Eloquent model with appropriate relationships, matching the ACME specification's resource hierarchy.

## PHP 8.4 Features Used

- **Strict types**: Every file declares `strict_types=1`.
- **Constructor property promotion**: Used in all services.
- **Named arguments**: Used in event dispatch and service construction.
- **`match` expressions**: Used in challenge type resolution and order status transitions.

## Extension Points

- **ChallengeValidatorInterface**: Implement custom challenge validators for proprietary validation methods.
- **AcmeServerInterface**: Bind a custom server implementation for modified ACME flows.
- **Events**: Listen to `AcmeAccountCreated`, `AcmeOrderCreated`, `AcmeChallengeCompleted`, `AcmeOrderFinalized`, `AcmeCertificateIssued` for monitoring and webhook integrations.
- **Config `ca-acme.enabled`**: Disable the entire ACME subsystem.
- **Middleware**: The `AcmeJwsMiddleware` can be extended or replaced for custom authentication flows.
