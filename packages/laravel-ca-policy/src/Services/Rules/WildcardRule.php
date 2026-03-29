<?php

declare(strict_types=1);

namespace CA\Policy\Services\Rules;

use CA\Policy\Contracts\PolicyRuleInterface;
use CA\Policy\DTOs\PolicyContext;
use CA\Policy\DTOs\PolicyRuleResult;
use CA\Models\PolicySeverity;

class WildcardRule implements PolicyRuleInterface
{
    private readonly bool $allowed;
    private readonly int $maxLevel;

    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->allowed = (bool) ($parameters['allowed'] ?? true);
        $this->maxLevel = (int) ($parameters['max_level'] ?? 1);
    }

    public function evaluate(PolicyContext $context): PolicyRuleResult
    {
        $sans = $context->sans ?? $context->options->subjectAlternativeNames;
        $wildcardNames = [];

        // Check CN for wildcard.
        if ($context->subject?->commonName !== null && str_contains($context->subject->commonName, '*')) {
            $wildcardNames[] = $context->subject->commonName;
        }

        // Check SANs for wildcards.
        if ($sans !== null) {
            foreach ($sans as $san) {
                $value = is_array($san) ? ($san['value'] ?? '') : (string) $san;
                if (str_contains($value, '*')) {
                    $wildcardNames[] = $value;
                }
            }
        }

        if (count($wildcardNames) === 0) {
            return new PolicyRuleResult(
                passed: true,
                severity: $this->getSeverity(),
                message: 'No wildcard names present.',
                rule: $this->getName(),
            );
        }

        if (!$this->allowed) {
            return new PolicyRuleResult(
                passed: false,
                severity: $this->getSeverity(),
                message: 'Wildcard certificates are not permitted by policy.',
                rule: $this->getName(),
            );
        }

        // Check wildcard level.
        foreach ($wildcardNames as $name) {
            $wildcardCount = substr_count($name, '*');

            if ($wildcardCount > $this->maxLevel) {
                return new PolicyRuleResult(
                    passed: false,
                    severity: $this->getSeverity(),
                    message: "Wildcard name '{$name}' exceeds the maximum wildcard level of {$this->maxLevel}.",
                    rule: $this->getName(),
                );
            }
        }

        return new PolicyRuleResult(
            passed: true,
            severity: $this->getSeverity(),
            message: 'Wildcard names meet policy requirements.',
            rule: $this->getName(),
        );
    }

    public function getName(): string
    {
        return 'wildcard';
    }

    public function getDescription(): string
    {
        return 'Controls wildcard certificate issuance and maximum wildcard depth.';
    }

    public function getSeverity(): string
    {
        return PolicySeverity::ERROR;
    }
}
