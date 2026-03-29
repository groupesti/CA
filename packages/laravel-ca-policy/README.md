# Laravel CA Policy

> Certificate issuance policy engine, name constraints (RFC 5280), and configurable validation rules for Laravel CA.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/groupesti/laravel-ca-policy.svg)](https://packagist.org/packages/groupesti/laravel-ca-policy)
[![PHP Version](https://img.shields.io/badge/php-8.4%2B-blue)](https://www.php.net/releases/8.4/en.php)
[![Laravel](https://img.shields.io/badge/laravel-12.x-red)](https://laravel.com)
[![Tests](https://github.com/groupesti/laravel-ca-policy/actions/workflows/tests.yml/badge.svg)](https://github.com/groupesti/laravel-ca-policy/actions/workflows/tests.yml)
[![License](https://img.shields.io/github/license/groupesti/laravel-ca-policy)](LICENSE.md)

## Requirements

- PHP 8.4+
- Laravel 12.x
- `groupesti/laravel-ca` ^1.0
- `groupesti/laravel-ca-crt` ^1.0
- PHP extensions: `dom`, `curl`, `libxml`, `mbstring`, `zip`, `pdo`, `sqlite` (for testing), `openssl`

## Installation

Install the package via Composer:

```bash
composer require groupesti/laravel-ca-policy
```

The service provider is auto-discovered. To publish the configuration file:

```bash
php artisan vendor:publish --tag=ca-policy-config
```

To publish and run the migrations:

```bash
php artisan vendor:publish --tag=ca-policy-migrations
php artisan migrate
```

## Configuration

The configuration file is published to `config/ca-policy.php`. Available options:

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `enforce_policies` | `bool` | `true` | When enabled, all certificate issuance requests are evaluated against configured policies and rules. |
| `default_policy_oid` | `?string` | `null` | Default certificate policy OID embedded in issued certificates when no specific policy is configured. |
| `require_cps_uri` | `bool` | `false` | When enabled, every certificate policy must have a CPS URI defined. |
| `name_constraints_enabled` | `bool` | `true` | Enable or disable name constraint validation (RFC 5280) for issued certificates. |
| `max_path_length_enforcement` | `bool` | `true` | Enforce `basicConstraints` pathLength down the CA chain. |
| `key_usage_enforcement` | `bool` | `true` | Enforce that requested key usage values match the certificate type and CA templates. |
| `validity_enforcement` | `bool` | `true` | Enforce that certificate validity periods do not exceed CA maximums (default 397 days per CA/B Forum). |
| `subject_validation` | `bool` | `true` | Validate subject distinguished names against policy rules and name constraints. |

## Usage

### Policy Evaluation

The `PolicyEngine` evaluates certificate requests against all enabled issuance rules for a CA. Use the `CaPolicy` facade or inject `PolicyEngineInterface`:

```php
use CA\Policy\Facades\CaPolicy;

// Evaluate a certificate request against all issuance rules
$result = CaPolicy::evaluate(ca: $ca, options: $certificateOptions);

if (! $result->isAllowed()) {
    // Request denied — inspect violations
    foreach ($result->getViolations() as $violation) {
        logger()->error($violation);
    }
}

if ($result->hasWarnings()) {
    foreach ($result->warnings as $warning) {
        logger()->warning($warning);
    }
}

// Check the resulting action: 'allow', 'deny', or 'require_approval'
$action = $result->action;
```

### Issuance Validation

Validate key usage, validity periods, and path length constraints independently:

```php
use CA\Policy\Facades\CaPolicy;

$result = CaPolicy::validateIssuance(ca: $ca, options: $certificateOptions);

if (! $result->isAllowed()) {
    // Key usage, validity, or path length violation
}
```

### Subject and Name Constraint Validation

Validate subject distinguished names and SANs against RFC 5280 name constraints:

```php
use CA\Policy\Facades\CaPolicy;

$result = CaPolicy::validateSubject(
    ca: $ca,
    subject: $distinguishedName,
    sans: [
        ['type' => 'dns', 'value' => 'app.example.com'],
        ['type' => 'email', 'value' => 'admin@example.com'],
    ],
);

if (! $result->isAllowed()) {
    // Name constraint violations
    foreach ($result->getViolations() as $v) {
        echo $v; // e.g. "SAN dns:evil.com violates name constraints."
    }
}
```

### Name Constraint Validator (Direct Usage)

Use the `NameConstraintValidator` directly for low-level constraint checking:

```php
use CA\Policy\Services\NameConstraintValidator;

$validator = new NameConstraintValidator();

// Permit only *.example.com DNS names
$validator->addPermitted('.example.com', 'dns');

// Exclude a specific subdomain
$validator->addExcluded('blocked.example.com', 'dns');

// Permit an IP range
$validator->addPermitted('10.0.0.0/8', 'ip');

// Check individual names
$validator->isPermitted('app.example.com', 'dns');     // true
$validator->isPermitted('blocked.example.com', 'dns');  // false
$validator->isPermitted('other.org', 'dns');             // false

// Validate a full subject + SANs
$violations = $validator->validate($distinguishedName, $sans);
```

### Effective Policy

Retrieve the aggregated effective policy for a CA, including all constraints, allowed key usages, EKUs, and rules:

```php
use CA\Policy\Facades\CaPolicy;

$policy = CaPolicy::getEffectivePolicy(ca: $ca);

$policy->policyOid;         // e.g. '2.16.840.1.101.2.1'
$policy->maxValidityDays;   // e.g. 397
$policy->allowedKeyUsages;  // e.g. ['digitalSignature', 'keyEncipherment']
$policy->allowedEkus;       // e.g. ['serverAuth', 'clientAuth']
$policy->nameConstraints;   // array of constraint definitions
$policy->maxPathLength;     // e.g. 2
$policy->rules;             // array of active issuance rules
```

### Built-in Validation Rules

The package ships with 8 validation rules, each implementing `PolicyRuleInterface`:

| Rule | Description |
|------|-------------|
| `KeyAlgorithmRule` | Enforces minimum key sizes (RSA >= 2048 bits, ECDSA >= P-256). |
| `KeyUsageRule` | Validates that requested key usages match the CA policy. |
| `SanRequiredRule` | Ensures a Subject Alternative Name is present when required. |
| `ValidityPeriodRule` | Enforces maximum certificate validity period. |
| `SubjectFieldRule` | Validates required and forbidden subject DN fields. |
| `DomainValidationRule` | Validates domain ownership and format for DV certificates. |
| `WildcardRule` | Controls whether wildcard certificates are allowed and at which level. |
| `CaPathLengthRule` | Enforces CA `basicConstraints` pathLength limits per RFC 5280. |

Rules are configured per CA via `IssuanceRule` models with priority ordering, severity levels (`error`, `warning`, `info`), and configurable failure actions (`allow`, `deny`, `require_approval`).

### Artisan Commands

| Command | Description |
|---------|-------------|
| `ca:policy:create {ca_uuid}` | Create a certificate policy interactively. |
| `ca:policy:list` | List all certificate policies. |
| `ca:policy:evaluate` | Evaluate a certificate request against policies. |
| `ca:name-constraint:add` | Add a name constraint to a CA. |
| `ca:issuance-rule:list` | List all issuance rules. |

### API Routes

All routes are prefixed with the configurable `ca-policy.routes.prefix` (default: `api/ca`) and use the `api` middleware.

**Policies:** `GET|POST /policies`, `POST /policies/evaluate`, `GET|PUT|DELETE /policies/{id}`

**Name Constraints:** `GET|POST /name-constraints`, `GET|PUT|DELETE /name-constraints/{id}`

**Issuance Rules:** `GET|POST /issuance-rules`, `POST /issuance-rules/reorder`, `GET|PUT|DELETE /issuance-rules/{id}`, `POST /issuance-rules/{id}/enable`, `POST /issuance-rules/{id}/disable`

### Custom Rules

Implement `PolicyRuleInterface` to create custom validation rules:

```php
use CA\Policy\Contracts\PolicyRuleInterface;
use CA\Policy\DTOs\PolicyContext;
use CA\Policy\DTOs\PolicyRuleResult;

class MyCustomRule implements PolicyRuleInterface
{
    public function __construct(private readonly array $parameters = []) {}

    public function evaluate(PolicyContext $context): PolicyRuleResult
    {
        // Your validation logic
        return new PolicyRuleResult(
            passed: true,
            severity: $this->getSeverity(),
            message: 'Custom rule passed.',
            rule: $this->getName(),
        );
    }

    public function getName(): string { return 'my_custom_rule'; }
    public function getDescription(): string { return 'My custom validation rule.'; }
    public function getSeverity(): string { return 'error'; }
}
```

Register your rule by creating an `IssuanceRule` model pointing to your class.

## Testing

```bash
./vendor/bin/pest
./vendor/bin/pint --test
./vendor/bin/phpstan analyse
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover a security vulnerability, please see [SECURITY](SECURITY.md). Do **not** open a public issue.

## Credits

- [Groupesti](https://github.com/groupesti)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
