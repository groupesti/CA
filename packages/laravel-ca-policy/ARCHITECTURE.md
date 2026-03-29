# Architecture — laravel-ca-policy (Certificate Policy Engine)

## Overview

`laravel-ca-policy` provides a rule-based policy engine for controlling certificate issuance. It enforces organizational policies through configurable rules covering name constraints, key algorithm requirements, validity periods, wildcard restrictions, SAN requirements, key usage, and CA path length. Policies can be evaluated before certificate issuance to prevent non-compliant certificates. It depends on `laravel-ca` (core) and `laravel-ca-crt` (certificate model).

## Directory Structure

```
src/
├── PolicyServiceProvider.php          # Registers name constraint validator, policy engine
├── Console/
│   └── Commands/
│       ├── PolicyCreateCommand.php    # Create a new policy (ca-policy:create)
│       ├── PolicyListCommand.php      # List policies with status
│       ├── PolicyEvaluateCommand.php  # Evaluate a policy against a certificate/CSR
│       ├── NameConstraintAddCommand.php   # Add a name constraint
│       └── IssuanceRuleListCommand.php    # List issuance rules
├── Contracts/
│   ├── PolicyEngineInterface.php      # Contract for the policy engine
│   ├── NameConstraintInterface.php    # Contract for name constraint validation
│   └── PolicyRuleInterface.php        # Contract that all policy rules implement
├── DTOs/
│   ├── PolicyContext.php              # Context data passed to rules during evaluation
│   ├── PolicyResult.php               # Readonly result: allowed, violations, warnings, action
│   ├── PolicyRuleResult.php           # Result of a single rule evaluation
│   └── EffectivePolicy.php           # Computed effective policy from merged rules
├── Events/
│   ├── PolicyEvaluated.php            # Fired when a policy is evaluated
│   ├── PolicyViolation.php            # Fired when a policy violation is detected
│   └── IssuanceRuleCreated.php        # Fired when a new issuance rule is created
├── Facades/
│   └── CaPolicy.php                   # Facade resolving PolicyEngineInterface
├── Http/
│   ├── Controllers/
│   │   ├── PolicyController.php       # REST API for policy management
│   │   ├── NameConstraintController.php # REST API for name constraint management
│   │   └── IssuanceRuleController.php # REST API for issuance rule management
│   ├── Requests/
│   │   ├── CreatePolicyRequest.php    # Validation for policy creation
│   │   ├── CreateNameConstraintRequest.php # Validation for name constraints
│   │   └── CreateIssuanceRuleRequest.php   # Validation for issuance rules
│   └── Resources/
│       ├── PolicyResource.php         # JSON API resource for policies
│       ├── NameConstraintResource.php # JSON API resource for name constraints
│       └── IssuanceRuleResource.php   # JSON API resource for issuance rules
├── Models/
│   ├── CertificatePolicy.php          # Eloquent model for certificate policies
│   ├── NameConstraint.php             # Eloquent model: permitted/excluded DNS, email, URI, IP subtrees
│   ├── PolicyConstraint.php           # Eloquent model for policy constraint mappings
│   ├── IssuanceRule.php               # Eloquent model for individual issuance rules
│   ├── NameType.php                   # Lookup subclass for name constraint types
│   ├── PolicyAction.php               # Lookup subclass for policy actions (allow, deny, warn)
│   └── PolicySeverity.php            # Lookup subclass for violation severity levels
└── Services/
    ├── PolicyEngine.php               # Core engine: evaluates all applicable rules against a context
    ├── NameConstraintValidator.php     # Validates names against permitted/excluded subtrees
    └── Rules/
        ├── CaPathLengthRule.php       # Enforces CA path length constraints
        ├── DomainValidationRule.php   # Validates domain ownership/authorization
        ├── KeyAlgorithmRule.php       # Enforces minimum key algorithm requirements
        ├── KeyUsageRule.php           # Validates Key Usage and Extended Key Usage
        ├── SanRequiredRule.php        # Enforces Subject Alternative Name presence
        ├── SubjectFieldRule.php       # Validates required/forbidden subject DN fields
        ├── ValidityPeriodRule.php     # Enforces maximum validity period limits
        └── WildcardRule.php           # Controls wildcard certificate issuance
```

## Service Provider

`PolicyServiceProvider` registers the following:

| Category | Details |
|---|---|
| **Config** | Merges `config/ca-policy.php`; publishes under tag `ca-policy-config` |
| **Singletons** | `NameConstraintInterface` (resolved to `NameConstraintValidator`), `PolicyEngineInterface` (resolved to `PolicyEngine`) |
| **Migrations** | 4 tables: `ca_certificate_policies`, `ca_name_constraints`, `ca_policy_constraints`, `ca_issuance_rules` |
| **Commands** | `ca-policy:create`, `ca-policy:list`, `ca-policy:evaluate`, `ca-policy:name-constraint-add`, `ca-policy:issuance-rule-list` |
| **Routes** | API routes under configurable prefix (default `api/ca`) |

## Key Classes

**PolicyEngine** -- The central evaluation engine. Given a `PolicyContext` (containing the CSR, target CA, requested certificate type, extensions, etc.), it loads all applicable policies and their rules, evaluates each rule, collects violations and warnings, and returns a `PolicyResult` indicating whether issuance should proceed, be blocked, or proceed with warnings.

**NameConstraintValidator** -- Validates subject names and SANs against configured name constraints. Supports permitted and excluded subtrees for DNS names, email addresses, URIs, and IP address ranges. Implements RFC 5280 Name Constraints semantics.

**Policy Rules (Services/Rules/)** -- Eight built-in rules implementing `PolicyRuleInterface`:
- `CaPathLengthRule`: Prevents CA chains from exceeding configured depth.
- `DomainValidationRule`: Verifies domain authorization.
- `KeyAlgorithmRule`: Enforces minimum algorithm strength (e.g., no RSA-2048, ECDSA required).
- `KeyUsageRule`: Validates Key Usage and Extended Key Usage combinations.
- `SanRequiredRule`: Requires Subject Alternative Name presence (per CA/Browser Forum).
- `SubjectFieldRule`: Enforces required/forbidden DN fields.
- `ValidityPeriodRule`: Caps certificate validity (e.g., 397 days for TLS).
- `WildcardRule`: Controls or blocks wildcard certificate issuance.

**PolicyResult** -- A `final readonly` DTO carrying the evaluation outcome: whether issuance is allowed, an array of violations, an array of warnings, and the recommended action.

**PolicyContext** -- A DTO containing all information needed for rule evaluation: the CSR, target CA, certificate type, requested extensions, validity period, and subject information.

## Design Decisions

- **Rule-based engine with interface contract**: Every policy rule implements `PolicyRuleInterface`, making it trivial to add custom organizational rules. The engine iterates all registered rules without knowing their implementation details.

- **Three-level severity**: Policy evaluations produce `allow` (proceed), `warn` (proceed but log), or `deny` (block issuance). This allows organizations to gradually tighten policies without immediately breaking workflows.

- **Name constraints as RFC 5280 semantics**: The `NameConstraintValidator` follows the exact RFC 5280 Section 4.2.1.10 algorithm for permitted/excluded subtree matching, ensuring compatibility with standard PKI validation.

- **Separate models for each concept**: Policies, name constraints, policy constraints, and issuance rules each have their own Eloquent model. This normalized design avoids JSON-blob anti-patterns and enables fine-grained querying and management.

- **Multiple controllers**: The policy domain has three controllers (Policy, NameConstraint, IssuanceRule) rather than one, following RESTful resource design principles.

## PHP 8.4 Features Used

- **`final readonly` classes**: DTOs (`PolicyResult`, `PolicyRuleResult`, `EffectivePolicy`, `PolicyContext`) use `final readonly class`.
- **Constructor property promotion**: Used in all services and DTOs.
- **Named arguments**: Used in DTO construction and service wiring.
- **Strict types**: Every file declares `strict_types=1`.

## Extension Points

- **PolicyRuleInterface**: Implement custom rules for organization-specific policies (e.g., approval workflows, LDAP group checks, geographic restrictions).
- **PolicyEngineInterface**: Replace the engine entirely for alternative policy evaluation strategies.
- **NameConstraintInterface**: Bind a custom name constraint validator.
- **Events**: Listen to `PolicyEvaluated`, `PolicyViolation`, `IssuanceRuleCreated` for compliance monitoring and alerting.
- **Config**: Customize route prefix and middleware via `config/ca-policy.php`.
