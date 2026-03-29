# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-03-29

### Added

- `PolicyEngine` service with `evaluate()`, `validateIssuance()`, `validateSubject()`, and `getEffectivePolicy()` methods for comprehensive certificate issuance policy evaluation.
- `CaPolicy` facade providing static access to the policy engine.
- `PolicyEngineInterface` and `PolicyRuleInterface` contracts for extensibility.
- `NameConstraintValidator` implementing RFC 5280 name constraints with support for DNS suffix matching, email domain matching, IP CIDR matching, URI host matching, and directory name prefix matching.
- `NameConstraintInterface` contract for custom name constraint implementations.
- 8 built-in validation rules: `KeyAlgorithmRule`, `KeyUsageRule`, `SanRequiredRule`, `ValidityPeriodRule`, `SubjectFieldRule`, `DomainValidationRule`, `WildcardRule`, `CaPathLengthRule`.
- `IssuanceRule` model with priority ordering, severity levels (`error`, `warning`, `info`), configurable failure actions (`allow`, `deny`, `require_approval`), and certificate type scoping.
- `NameConstraint` model with permitted/excluded subtree types for DNS, email, IP, URI, and directory name constraints.
- `CertificatePolicy` model for managing policy OIDs, CPS URIs, and default policy selection.
- `PolicyConstraint` model for certificate path policy constraints.
- `PolicyContext`, `PolicyResult`, `PolicyRuleResult`, and `EffectivePolicy` readonly DTOs.
- `PolicySeverity` (`error`, `warning`, `info`) and `PolicyAction` (`allow`, `deny`, `require_approval`) lookup models.
- `PolicyEvaluated`, `PolicyViolation`, and `IssuanceRuleCreated` events.
- Artisan commands: `ca:policy:create`, `ca:policy:list`, `ca:policy:evaluate`, `ca:name-constraint:add`, `ca:issuance-rule:list`.
- RESTful API routes for policies, name constraints, and issuance rules with CRUD operations, reordering, and enable/disable endpoints.
- Form request validation classes: `CreatePolicyRequest`, `CreateIssuanceRuleRequest`, `CreateNameConstraintRequest`.
- API resource classes: `PolicyResource`, `IssuanceRuleResource`, `NameConstraintResource`.
- Database migrations for `ca_certificate_policies`, `ca_name_constraints`, `ca_policy_constraints`, and `ca_issuance_rules` tables.
- Publishable configuration file (`ca-policy.php`) with toggles for policy enforcement, name constraints, key usage, validity, path length, and subject validation.
- Auto-discovery support via `composer.json` extra Laravel configuration.
