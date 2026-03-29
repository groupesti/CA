# Roadmap

## v0.1.0 — Initial Release (2026-03-29)

- [x] SCEP server implementing draft-nourse-scep (RFC 8894)
- [x] PKCSReq, CertRep, GetCACert, GetCACaps message handling
- [x] SCEP message parser and builder with ASN.1 support
- [x] Challenge password management for enrollment authorization
- [x] Transaction tracking with ScepTransaction model
- [x] Content type middleware for SCEP HTTP transport
- [x] Artisan commands (setup, challenge, transaction-list, cleanup)
- [x] Events (EnrollmentRequested, EnrollmentFailed, CertificateIssued)

## v1.0.0 — Stable Release

- [ ] Comprehensive test suite (90%+ coverage)
- [ ] PHPStan level 9 compliance
- [ ] Complete documentation with MDM integration examples
- [ ] GetCRL and GetNextCACert operation support
- [ ] SCEP renewal (RenewalReq) support
- [ ] Challenge password policy (expiration, single-use, reuse limits)
- [ ] SCEP proxy mode for enterprise CA backends

## v1.1.0 — Planned

- [ ] SCEP v2 support when RFC is finalized
- [ ] Intune / Jamf / AirWatch integration guides
- [ ] SCEP enrollment approval workflow (manual mode)

## Ideas / Backlog

- SCEP-to-EST migration tooling
- SCEP load testing for large MDM deployments
- SCEP client library for testing
- NDES (Network Device Enrollment Service) compatibility layer
