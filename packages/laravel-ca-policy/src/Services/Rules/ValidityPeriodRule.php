<?php

declare(strict_types=1);

namespace CA\Policy\Services\Rules;

use CA\Policy\Contracts\PolicyRuleInterface;
use CA\Policy\DTOs\PolicyContext;
use CA\Policy\DTOs\PolicyRuleResult;
use CA\Models\PolicySeverity;

class ValidityPeriodRule implements PolicyRuleInterface
{
    private readonly int $maxDays;

    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->maxDays = (int) ($parameters['max_days'] ?? 397);
    }

    public function evaluate(PolicyContext $context): PolicyRuleResult
    {
        $requestedDays = $context->options->validityDays;

        if ($requestedDays > $this->maxDays) {
            return new PolicyRuleResult(
                passed: false,
                severity: $this->getSeverity(),
                message: "Requested validity of {$requestedDays} days exceeds the maximum of {$this->maxDays} days.",
                rule: $this->getName(),
            );
        }

        return new PolicyRuleResult(
            passed: true,
            severity: $this->getSeverity(),
            message: "Validity period of {$requestedDays} days is within the allowed maximum.",
            rule: $this->getName(),
        );
    }

    public function getName(): string
    {
        return 'validity_period';
    }

    public function getDescription(): string
    {
        return 'Enforces maximum certificate validity period.';
    }

    public function getSeverity(): string
    {
        return PolicySeverity::ERROR;
    }
}
