# Roadmap

## v0.1.0 — Initial Release (done)

- [x] 90+ standard OIDs (X.509, PKCS, algorithms, curves, hashes)
- [x] 30+ Microsoft OIDs (certificate templates, EKU, enrollment, AD)
- [x] Extended Validation OIDs (DV, OV, EV, CA policies)
- [x] OID resolution (dotted-decimal to name and reverse)
- [x] OID validation and formatting
- [x] Search by keyword and filter by category
- [x] Custom OID registration and unregistration
- [x] Database-backed registry with Eloquent model
- [x] Readonly OidInfo DTO
- [x] Configurable caching with TTL
- [x] Artisan commands (seed, list, search, resolve, register)
- [x] REST API endpoints
- [x] Events for registration/unregistration

## v0.2.0 — Planned

- [ ] OID tree visualization (parent/child relationships)
- [ ] Bulk import/export of custom OIDs (JSON/CSV)
- [ ] OID deprecation tracking (mark OIDs as deprecated with replacement)
- [ ] Cache warmup command

## v1.0.0 — Stable Release

- [ ] Full test coverage (90%+)
- [ ] Private Enterprise Number (PEN) registry integration
- [ ] OID arc ownership validation

## Ideas / Backlog

- GraphQL API for OID queries
- OID diffing between two registry snapshots
- Integration with external OID repositories (oid-info.com)
