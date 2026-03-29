# Roadmap

## v0.1.0 — Initial Release

- [x] RFC 3161 timestamp request handling (TSQ/TSR)
- [x] Pure PHP ASN.1 encoding/decoding for all RFC 3161 structures
- [x] Timestamp token generation with configurable policy and accuracy
- [x] Timestamp verification with TSTInfo extraction
- [x] HTTP endpoint for timestamp requests
- [x] TSA info endpoint
- [x] TimestampToken and TsaCertificate models
- [x] Artisan commands: setup, status, verify, token-list
- [x] Events: TimestampCreated, TimestampVerified

## v0.2.0 — Planned

- [ ] Support for SHA-3 family hash algorithms (sha3-256, sha3-384, sha3-512)
- [ ] Timestamp token renewal and extension
- [ ] Bulk timestamp request support
- [ ] Rate limiting per client IP

## v1.0.0 — Stable Release

- [ ] Full test coverage (90%+)
- [ ] Long-term validation (LTV) support
- [ ] TSA certificate chain inclusion in responses
- [ ] Production hardening and performance optimization

## Ideas / Backlog

- RFC 5816 ESSCertIDv2 support
- Timestamp archiving and long-term storage strategies
- Integration with external NTP sources for accuracy validation
- CLI tool for offline timestamp verification
