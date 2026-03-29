<?php

declare(strict_types=1);

namespace CA\Policy\Services;

use CA\DTOs\CertificateOptions;
use CA\DTOs\DistinguishedName;
use CA\Models\CertificateAuthority;
use CA\Policy\Contracts\NameConstraintInterface;
use CA\Policy\Contracts\PolicyEngineInterface;
use CA\Policy\Contracts\PolicyRuleInterface;
use CA\Policy\DTOs\EffectivePolicy;
use CA\Policy\DTOs\PolicyContext;
use CA\Policy\DTOs\PolicyResult;
use CA\Policy\DTOs\PolicyRuleResult;
use CA\Models\PolicyAction;
use CA\Models\PolicySeverity;
use CA\Policy\Events\PolicyEvaluated;
use CA\Policy\Events\PolicyViolation;
use CA\Policy\Models\CertificatePolicy;
use CA\Policy\Models\IssuanceRule;
use CA\Policy\Models\NameConstraint;
use CA\Policy\Models\PolicyConstraint;

class PolicyEngine implements PolicyEngineInterface
{
    public function __construct(
        private readonly NameConstraintInterface $nameConstraintValidator,
    ) {}

    public function evaluate(CertificateAuthority $ca, CertificateOptions $options): PolicyResult
    {
        if (!config('ca-policy.enforce_policies', true)) {
            return new PolicyResult(allowed: true);
        }

        $context = new PolicyContext(
            ca: $ca,
            options: $options,
            type: $options->type,
            sans: $options->subjectAlternativeNames,
        );

        $violations = [];
        $warnings = [];
        $denied = false;
        $requiresApproval = false;

        // Load all enabled rules for this CA, sorted by priority.
        $rules = IssuanceRule::query()
            ->where('ca_id', $ca->id)
            ->enabled()
            ->forType($options->type)
            ->byPriority()
            ->get();

        foreach ($rules as $issuanceRule) {
            $ruleInstance = $this->resolveRule($issuanceRule);

            if ($ruleInstance === null) {
                $warnings[] = "Could not resolve rule class: {$issuanceRule->rule_class}";
                continue;
            }

            $result = $ruleInstance->evaluate($context);

            if (!$result->passed) {
                $severity = $issuanceRule->severity ?? $result->severity;
                $action = $issuanceRule->action_on_failure ?? PolicyAction::DENY;

                if ($severity === PolicySeverity::ERROR) {
                    if ($action === PolicyAction::DENY) {
                        $denied = true;
                        $violations[] = $result->message;

                        event(new PolicyViolation($ca, $issuanceRule->name, $result->message, $severity));
                    } elseif ($action === PolicyAction::REQUIRE_APPROVAL) {
                        $requiresApproval = true;
                        $warnings[] = "[Requires Approval] {$result->message}";
                    }
                } elseif ($severity === PolicySeverity::WARNING) {
                    $warnings[] = $result->message;

                    event(new PolicyViolation($ca, $issuanceRule->name, $result->message, $severity));
                }
            }
        }

        $action = $denied
            ? PolicyAction::DENY
            : ($requiresApproval ? PolicyAction::REQUIRE_APPROVAL : PolicyAction::ALLOW);

        $policyResult = new PolicyResult(
            allowed: !$denied,
            violations: $violations,
            warnings: $warnings,
            action: $action,
        );

        event(new PolicyEvaluated($ca, $options, $policyResult));

        return $policyResult;
    }

    public function validateIssuance(CertificateAuthority $ca, CertificateOptions $options): PolicyResult
    {
        $violations = [];
        $warnings = [];

        // Key usage enforcement.
        if (config('ca-policy.key_usage_enforcement', true)) {
            $effectivePolicy = $this->getEffectivePolicy($ca);

            if (count($effectivePolicy->allowedKeyUsages) > 0 && count($options->keyUsage) > 0) {
                foreach ($options->keyUsage as $ku) {
                    if (!in_array($ku, $effectivePolicy->allowedKeyUsages, true)) {
                        $violations[] = "Key usage '{$ku}' is not allowed by the CA policy.";
                    }
                }
            }

            if (count($effectivePolicy->allowedEkus) > 0 && count($options->extendedKeyUsage) > 0) {
                foreach ($options->extendedKeyUsage as $eku) {
                    if (!in_array($eku, $effectivePolicy->allowedEkus, true)) {
                        $violations[] = "Extended key usage '{$eku}' is not allowed by the CA policy.";
                    }
                }
            }
        }

        // Validity enforcement.
        if (config('ca-policy.validity_enforcement', true)) {
            $effectivePolicy ??= $this->getEffectivePolicy($ca);

            if ($effectivePolicy->maxValidityDays > 0 && $options->validityDays > $effectivePolicy->maxValidityDays) {
                $violations[] = "Requested validity of {$options->validityDays} days exceeds the maximum of {$effectivePolicy->maxValidityDays} days.";
            }

            // Certificate must not outlive the CA.
            if ($ca->not_after !== null) {
                $certExpiry = now()->addDays($options->validityDays);
                if ($certExpiry->greaterThan($ca->not_after)) {
                    $warnings[] = "Certificate would expire after the issuing CA ({$ca->not_after->toDateString()}).";
                }
            }
        }

        // Path length enforcement.
        if (config('ca-policy.max_path_length_enforcement', true) && $options->isCa === true) {
            $caDepth = $ca->getChainDepth();
            $caPathLength = $ca->path_length;

            if ($caPathLength !== null && $caDepth >= $caPathLength) {
                $violations[] = "CA path length constraint ({$caPathLength}) would be exceeded at chain depth {$caDepth}.";
            }
        }

        $allowed = count($violations) === 0;

        return new PolicyResult(
            allowed: $allowed,
            violations: $violations,
            warnings: $warnings,
            action: $allowed ? PolicyAction::ALLOW : PolicyAction::DENY,
        );
    }

    public function validateSubject(
        CertificateAuthority $ca,
        DistinguishedName $subject,
        ?array $sans = null,
    ): PolicyResult {
        if (!config('ca-policy.subject_validation', true)) {
            return new PolicyResult(allowed: true);
        }

        $violations = [];
        $warnings = [];

        // Name constraint validation.
        if (config('ca-policy.name_constraints_enabled', true)) {
            $validator = clone $this->nameConstraintValidator;

            $constraints = NameConstraint::query()
                ->where('ca_id', $ca->id)
                ->where('enabled', true)
                ->get();

            if ($validator instanceof NameConstraintValidator) {
                $validator->loadFromModels($constraints);
            }

            $constraintViolations = $validator->validate($subject, $sans);
            $violations = array_merge($violations, $constraintViolations);
        }

        $allowed = count($violations) === 0;

        return new PolicyResult(
            allowed: $allowed,
            violations: $violations,
            warnings: $warnings,
            action: $allowed ? PolicyAction::ALLOW : PolicyAction::DENY,
        );
    }

    public function getEffectivePolicy(CertificateAuthority $ca): EffectivePolicy
    {
        $policy = CertificatePolicy::query()
            ->where('ca_id', $ca->id)
            ->where('enabled', true)
            ->where('is_default', true)
            ->first();

        $policyOid = $policy?->policy_oid ?? config('ca-policy.default_policy_oid');
        $cpsUri = $policy?->cps_uri;

        // Aggregate name constraints.
        $nameConstraints = NameConstraint::query()
            ->where('ca_id', $ca->id)
            ->where('enabled', true)
            ->get()
            ->map(fn (NameConstraint $nc): array => [
                'type' => $nc->type,
                'name_type' => $nc->name_type,
                'value' => $nc->value,
            ])
            ->toArray();

        // Aggregate policy constraints.
        $policyConstraints = PolicyConstraint::query()
            ->where('ca_id', $ca->id)
            ->where('enabled', true)
            ->get()
            ->map(fn (PolicyConstraint $pc): array => [
                'constraint_type' => $pc->constraint_type,
                'skip_certs' => $pc->skip_certs,
            ])
            ->toArray();

        // Max path length from the CA itself.
        $maxPathLength = $ca->path_length ?? -1;

        // Gather allowed key usages and EKUs from templates.
        $allowedKeyUsages = [];
        $allowedEkus = [];
        $maxValidityDays = 397; // CA/B Forum baseline default.

        $templates = $ca->templates()->where('is_active', true)->get();

        foreach ($templates as $template) {
            if (is_array($template->key_usage)) {
                $allowedKeyUsages = array_merge($allowedKeyUsages, $template->key_usage);
            }
            if (is_array($template->extended_key_usage)) {
                $allowedEkus = array_merge($allowedEkus, $template->extended_key_usage);
            }
            if ($template->validity_days !== null && $template->validity_days > $maxValidityDays) {
                $maxValidityDays = $template->validity_days;
            }
        }

        $allowedKeyUsages = array_unique($allowedKeyUsages);
        $allowedEkus = array_unique($allowedEkus);

        // Gather rules.
        $rules = IssuanceRule::query()
            ->where('ca_id', $ca->id)
            ->enabled()
            ->byPriority()
            ->get()
            ->map(fn (IssuanceRule $rule): array => [
                'name' => $rule->name,
                'rule_class' => $rule->rule_class,
                'severity' => $rule->severity,
                'priority' => $rule->priority,
            ])
            ->toArray();

        return new EffectivePolicy(
            policyOid: $policyOid,
            cpsUri: $cpsUri,
            nameConstraints: $nameConstraints,
            policyConstraints: $policyConstraints,
            maxPathLength: $maxPathLength,
            allowedKeyUsages: array_values($allowedKeyUsages),
            allowedEkus: array_values($allowedEkus),
            maxValidityDays: $maxValidityDays,
            rules: $rules,
        );
    }

    private function resolveRule(IssuanceRule $issuanceRule): ?PolicyRuleInterface
    {
        $class = $issuanceRule->rule_class;

        if (!class_exists($class)) {
            return null;
        }

        if (!is_subclass_of($class, PolicyRuleInterface::class)) {
            return null;
        }

        $parameters = $issuanceRule->parameters ?? [];

        return new $class($parameters);
    }
}
