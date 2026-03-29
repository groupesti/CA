# Roadmap

## v0.1.0 — Initial Release (2026-03-29)

- [x] CSR generation with CsrBuilder fluent API
- [x] CSR import from PEM format
- [x] CSR validation (signature verification, subject checks)
- [x] CSR approval and rejection workflow
- [x] Eloquent model with status tracking (Pending, Approved, Rejected)
- [x] Artisan commands (create, import, approve, list)
- [x] REST API with form request validation (create, import)
- [x] Events (CsrCreated, CsrApproved, CsrRejected)

## v1.0.0 — Stable Release

- [ ] Comprehensive test suite (90%+ coverage)
- [ ] PHPStan level 9 compliance
- [ ] Complete documentation with builder examples
- [ ] CSR attribute extension support (challengePassword, extensionRequest)
- [ ] Batch CSR import from directory or ZIP
- [ ] CSR template-based generation (predefined subject fields)
- [ ] Integration with policy engine for automatic approval/rejection

## v1.1.0 — Planned

- [ ] CSR approval workflow with multi-level authorization
- [ ] CSR deduplication detection
- [ ] Web-based CSR submission form (integration with laravel-ca-ui)

## Ideas / Backlog

- CRMF (Certificate Request Message Format) support
- CSR generation from browser WebCrypto API
- Automatic CSR-to-certificate pipeline
