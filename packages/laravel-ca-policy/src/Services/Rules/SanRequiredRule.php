<?php

declare(strict_types=1);

namespace CA\Policy\Services\Rules;

use CA\Models\CertificateType;
use CA\Policy\Contracts\PolicyRuleInterface;
use CA\Policy\DTOs\PolicyContext;
use CA\Policy\DTOs\PolicyRuleResult;
use CA\Models\PolicySeverity;

class SanRequiredRule implements PolicyRuleInterface
{
    /** @var array<int, string> */
    private readonly array $requiredForTypes;

    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->requiredForTypes = (array) ($parameters['required_for_types'] ?? [
            CertificateType::SERVER_TLS,
        ]);
    }

    public function evaluate(PolicyContext $context): PolicyRuleResult
    {
        $type = $context->type ?? $context->options->type;

        if (!in_array($type->slug, $this->requiredForTypes, true)) {
            return new PolicyRuleResult(
                passed: true,
                severity: $this->getSeverity(),
                message: "SAN not required for certificate type '{$type->slug}'.",
                rule: $this->getName(),
            );
        }

        $sans = $context->sans ?? $context->options->subjectAlternativeNames;

        if ($sans === null || count($sans) === 0) {
            return new PolicyRuleResult(
                passed: false,
                severity: $this->getSeverity(),
                message: "Subject Alternative Name is required for '{$type->slug}' certificates per CA/B Forum Baseline Requirements.",
                rule: $this->getName(),
            );
        }

        return new PolicyRuleResult(
            passed: true,
            severity: $this->getSeverity(),
            message: 'Subject Alternative Name is present.',
            rule: $this->getName(),
        );
    }

    public function getName(): string
    {
        return 'san_required';
    }

    public function getDescription(): string
    {
        return 'Requires Subject Alternative Name for server certificates per CA/B Forum Baseline Requirements.';
    }

    public function getSeverity(): string
    {
        return PolicySeverity::ERROR;
    }
}
