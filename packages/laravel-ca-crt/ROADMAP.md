# Roadmap

## v0.1.0 — Initial Release (2026-03-29)

- [x] Certificate signing and issuance service
- [x] Specialized builders (Server, Client, CodeSigning, S/MIME, AD CS)
- [x] Microsoft AD CS template support (WebServer, User, Computer, DomainController, CodeSigning, S/MIME)
- [x] Certificate chain building and validation
- [x] Certificate renewal and revocation workflows
- [x] Certificate export (PEM, DER, and chain formats)
- [x] Expiring certificate scanner
- [x] TemplateResolver for Microsoft-compatible template selection
- [x] Artisan commands (issue, renew, revoke, list, verify, export, expiring-scan)
- [x] Events for certificate lifecycle

## v1.0.0 — Stable Release

- [ ] Comprehensive test suite (90%+ coverage)
- [ ] PHPStan level 9 compliance
- [ ] Complete documentation with builder examples
- [ ] Certificate transparency (CT) log submission
- [ ] Automated renewal scheduling via Laravel scheduler
- [ ] Certificate search and filtering API
- [ ] Custom extension support in certificate builders

## v1.1.0 — Planned

- [ ] Let's Encrypt-style automatic domain validation certificates
- [ ] Certificate lifecycle dashboard metrics
- [ ] Bulk certificate issuance from CSV/template

## Ideas / Backlog

- Certificate linting (zlint integration)
- ACME-based auto-renewal integration
- Certificate inventory reporting and compliance checks
- Additional AD CS templates (KerberosAuthentication, RAS and IAS Server)
