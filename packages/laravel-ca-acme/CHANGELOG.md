# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-03-29

### Added
- Initial release of the ACME protocol (RFC 8555) server for Laravel CA.
- `AcmeServer` service implementing the full ACME protocol flow.
- `JwsVerifier` service for JSON Web Signature verification.
- `NonceManager` service for replay-nonce management with configurable TTL.
- `OrderProcessor` service for certificate order lifecycle management.
- Challenge validators: `Http01Validator`, `Dns01Validator`, `TlsAlpn01Validator`.
- Eloquent models: `AcmeAccount`, `AcmeOrder`, `AcmeAuthorization`, `AcmeChallenge`, `AcmeNonce`.
- Enum-backed status models: `OrderStatus`, `AuthorizationStatus`, `ChallengeStatus`, `ChallengeType`.
- Full ACME API routes: directory, newNonce, newAccount, newOrder, authorization, challenge, finalize, certificate download, revoke.
- JWS authentication middleware (`AcmeJwsMiddleware`) for authenticated endpoints.
- Content-type middleware (`AcmeContentType`) for ACME-specific headers.
- Events: `AcmeAccountCreated`, `AcmeOrderCreated`, `AcmeChallengeCompleted`, `AcmeOrderFinalized`, `AcmeCertificateIssued`.
- Artisan commands: `ca:acme:setup`, `ca:acme:accounts`, `ca:acme:orders`, `ca:acme:cleanup`.
- Database migrations for accounts, orders, authorizations, challenges, and nonces.
- Publishable configuration file (`config/ca-acme.php`).
- `CaAcme` facade for static access to the ACME server.
