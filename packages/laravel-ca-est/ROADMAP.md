# Roadmap

## v0.1.0 — Initial Release

- [x] RFC 7030 `/cacerts` endpoint
- [x] RFC 7030 `/simpleenroll` endpoint
- [x] RFC 7030 `/simplereenroll` endpoint
- [x] RFC 7030 `/serverkeygen` endpoint
- [x] RFC 7030 `/fullcmc` endpoint (behind feature flag)
- [x] RFC 7030 `/csrattrs` endpoint
- [x] HTTP Basic and client certificate authentication
- [x] `EstEnrollment` model with enrollment tracking
- [x] Artisan commands: setup, enrollment-list, cleanup
- [x] Events for enrollment lifecycle

## v0.2.0 — Planned

- [ ] EST-over-CoAP support (RFC 9148)
- [ ] Rate limiting per client identity
- [ ] Enrollment approval workflow (manual approval mode)
- [ ] Webhook notifications for enrollment events

## v1.0.0 — Stable Release

- [ ] Full test coverage (90%+)
- [ ] Production hardening and performance optimization
- [ ] Comprehensive integration tests with real PKI chains
- [ ] ACME-EST bridge support

## Ideas / Backlog

- EST proxy mode for forwarding requests to upstream CAs
- Enrollment templates with predefined CSR attributes per CA
- SCEP-to-EST migration tooling
