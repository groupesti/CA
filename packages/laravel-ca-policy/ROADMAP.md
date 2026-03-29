# Roadmap

## v0.1.0 — Initial Release (2026-03-29)

- [x] Policy engine for certificate issuance rule evaluation
- [x] Built-in rules: KeyAlgorithm, KeyUsage, ValidityPeriod, SubjectField, SanRequired, DomainValidation, Wildcard, CaPathLength
- [x] Name constraint validation (permitted/excluded subtrees)
- [x] CertificatePolicy, IssuanceRule, NameConstraint, and PolicyConstraint models
- [x] Policy severity levels and configurable actions (allow, warn, deny)
- [x] Artisan commands (policy-create, policy-list, policy-evaluate, issuance-rule-list, name-constraint-add)
- [x] REST API with form request validation
- [x] Events for policy evaluation

## v1.0.0 — Stable Release

- [ ] Comprehensive test suite (90%+ coverage)
- [ ] PHPStan level 9 compliance
- [ ] Complete documentation with policy configuration examples
- [ ] Custom rule plugin system (implement PolicyRuleInterface)
- [ ] Policy versioning and change history
- [ ] Policy simulation mode (dry-run evaluation)
- [ ] Integration with CSR approval workflow

## v1.1.0 — Planned

- [ ] Policy templates for common use cases (WebPKI, enterprise, IoT)
- [ ] Policy inheritance between CA hierarchy levels
- [ ] Compliance reporting against industry standards (CAB Forum, ETSI)

## Ideas / Backlog

- Visual policy editor (integration with laravel-ca-ui)
- Policy import/export in standard formats
- Automated policy testing framework
- Certificate Practice Statement (CPS) generation from policies
