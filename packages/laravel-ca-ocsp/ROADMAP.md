# Roadmap

## v0.1.0 — Initial Release (2026-03-29)

- [x] OCSP responder implementing RFC 6960 (POST and GET)
- [x] ASN.1 request parsing and response building
- [x] Certificate status resolution (good, revoked, unknown)
- [x] Nonce support with configurable enforcement
- [x] OCSP responder certificate management
- [x] CertID parsing with issuer hash matching
- [x] Content type middleware for OCSP HTTP transport
- [x] Response caching support
- [x] Artisan commands (setup, status)
- [x] Events (OcspResponseGenerated)
- [x] CertStatusResult readonly DTO for structured status data

## v0.2.0 — Planned

- [ ] OCSP stapling helper for web server integration
- [ ] Response logging and analytics dashboard
- [ ] Delegated responder support (authorized responders)
- [ ] Extended revocation checking extension

## v1.0.0 — Stable Release

- [ ] Comprehensive test suite (90%+ coverage)
- [ ] PHPStan level 9 compliance
- [ ] Complete documentation with deployment examples
- [ ] Performance benchmarks under high request volume
- [ ] RFC 6960 compliance test suite
- [ ] Production hardening and security audit

## v1.1.0 — Planned

- [ ] Lightweight OCSP profile (RFC 5019)
- [ ] OCSP response archival for audit purposes
- [ ] Multiple hash algorithm support for CertID matching

## Ideas / Backlog

- OCSP-over-DNS support
- Multi-responder load balancing
- Integration with hardware security modules (HSM) for signing
- OCSP response pre-generation for high-traffic certificates
- OCSP monitoring and alerting integration
- High-availability OCSP responder with failover
