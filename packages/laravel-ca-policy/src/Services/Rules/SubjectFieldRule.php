<?php

declare(strict_types=1);

namespace CA\Policy\Services\Rules;

use CA\Policy\Contracts\PolicyRuleInterface;
use CA\Policy\DTOs\PolicyContext;
use CA\Policy\DTOs\PolicyRuleResult;
use CA\Models\PolicySeverity;

class SubjectFieldRule implements PolicyRuleInterface
{
    /** @var array<int, string> */
    private readonly array $requiredFields;

    /** @var array<int, string> */
    private readonly array $forbiddenFields;

    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->requiredFields = (array) ($parameters['required_fields'] ?? []);
        $this->forbiddenFields = (array) ($parameters['forbidden_fields'] ?? []);
    }

    public function evaluate(PolicyContext $context): PolicyRuleResult
    {
        if ($context->subject === null) {
            if (count($this->requiredFields) > 0) {
                return new PolicyRuleResult(
                    passed: false,
                    severity: $this->getSeverity(),
                    message: 'Subject DN is required but not provided.',
                    rule: $this->getName(),
                );
            }

            return new PolicyRuleResult(
                passed: true,
                severity: $this->getSeverity(),
                message: 'No subject DN to validate.',
                rule: $this->getName(),
            );
        }

        $dnArray = $context->subject->toArray();
        $missing = [];
        $forbidden = [];

        foreach ($this->requiredFields as $field) {
            if (!isset($dnArray[$field]) || $dnArray[$field] === '') {
                $missing[] = $field;
            }
        }

        foreach ($this->forbiddenFields as $field) {
            if (isset($dnArray[$field]) && $dnArray[$field] !== '') {
                $forbidden[] = $field;
            }
        }

        if (count($missing) > 0) {
            return new PolicyRuleResult(
                passed: false,
                severity: $this->getSeverity(),
                message: 'Required subject fields missing: ' . implode(', ', $missing) . '.',
                rule: $this->getName(),
            );
        }

        if (count($forbidden) > 0) {
            return new PolicyRuleResult(
                passed: false,
                severity: $this->getSeverity(),
                message: 'Forbidden subject fields present: ' . implode(', ', $forbidden) . '.',
                rule: $this->getName(),
            );
        }

        return new PolicyRuleResult(
            passed: true,
            severity: $this->getSeverity(),
            message: 'Subject fields meet policy requirements.',
            rule: $this->getName(),
        );
    }

    public function getName(): string
    {
        return 'subject_field';
    }

    public function getDescription(): string
    {
        return 'Enforces required and forbidden distinguished name fields.';
    }

    public function getSeverity(): string
    {
        return PolicySeverity::ERROR;
    }
}
