# Roadmap

## v0.1.0 — Initial Release (2026-03-29)

- [x] RFC 8555 ACME server implementation
- [x] Account management (registration, key rollover)
- [x] Order and authorization workflow with state machine
- [x] HTTP-01, DNS-01, and TLS-ALPN-01 challenge validators
- [x] JWS verification middleware for ACME requests
- [x] Nonce management service
- [x] OrderProcessor for certificate issuance finalization
- [x] Artisan commands (setup, cleanup, order-list, account-list)
- [x] Events (AccountCreated, OrderCreated, OrderFinalized, ChallengeCompleted, CertificateIssued)
- [x] ACME-specific content type middleware

## v1.0.0 — Stable Release

- [ ] Comprehensive test suite (90%+ coverage)
- [ ] PHPStan level 9 compliance
- [ ] Complete RFC 8555 compliance test suite
- [ ] External Account Binding (EAB) support
- [ ] Rate limiting per account and per IP
- [ ] Pre-authorization support (RFC 8555 Section 7.4.1)
- [ ] Certificate revocation via ACME (RFC 8555 Section 7.6)

## v1.1.0 — Planned

- [ ] ACME Renewal Information (ARI) support (RFC 8739)
- [ ] Wildcard certificate issuance
- [ ] ACME STAR (Short-Term, Auto-Renewed) certificates

## Ideas / Backlog

- ACME client library for testing and interoperability
- Certbot / ACME.sh compatibility test suite
- Subdomain validation delegation (RFC 9444)
- ACME account deactivation and cleanup policies
