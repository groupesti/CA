<?php

declare(strict_types=1);

namespace CA\Policy\Services\Rules;

use CA\Policy\Contracts\PolicyRuleInterface;
use CA\Policy\DTOs\PolicyContext;
use CA\Policy\DTOs\PolicyRuleResult;
use CA\Models\PolicySeverity;

class DomainValidationRule implements PolicyRuleInterface
{
    /** @var array<int, string> */
    private readonly array $allowedDomains;

    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->allowedDomains = (array) ($parameters['allowed_domains'] ?? []);
    }

    public function evaluate(PolicyContext $context): PolicyRuleResult
    {
        if (count($this->allowedDomains) === 0) {
            return new PolicyRuleResult(
                passed: true,
                severity: $this->getSeverity(),
                message: 'No domain restrictions configured.',
                rule: $this->getName(),
            );
        }

        $dnsNames = $this->extractDnsNames($context);

        if (count($dnsNames) === 0) {
            return new PolicyRuleResult(
                passed: true,
                severity: $this->getSeverity(),
                message: 'No DNS names to validate.',
                rule: $this->getName(),
            );
        }

        $violations = [];

        foreach ($dnsNames as $dns) {
            if (!$this->isDomainAllowed($dns)) {
                $violations[] = $dns;
            }
        }

        if (count($violations) > 0) {
            return new PolicyRuleResult(
                passed: false,
                severity: $this->getSeverity(),
                message: 'DNS names not in allowed domains: ' . implode(', ', $violations) . '.',
                rule: $this->getName(),
            );
        }

        return new PolicyRuleResult(
            passed: true,
            severity: $this->getSeverity(),
            message: 'All DNS names are within allowed domains.',
            rule: $this->getName(),
        );
    }

    public function getName(): string
    {
        return 'domain_validation';
    }

    public function getDescription(): string
    {
        return 'Validates DNS names against a list of allowed domains.';
    }

    public function getSeverity(): string
    {
        return PolicySeverity::ERROR;
    }

    /**
     * @return array<int, string>
     */
    private function extractDnsNames(PolicyContext $context): array
    {
        $names = [];

        // CN that looks like a domain.
        if ($context->subject?->commonName !== null) {
            $cn = $context->subject->commonName;
            if (preg_match('/^(\*\.)?[a-zA-Z0-9\-]+(\.[a-zA-Z0-9\-]+)+$/', $cn)) {
                $names[] = $cn;
            }
        }

        // DNS SANs.
        $sans = $context->sans ?? $context->options->subjectAlternativeNames;

        if ($sans !== null) {
            foreach ($sans as $san) {
                $type = is_array($san) ? ($san['type'] ?? '') : 'dns';
                $value = is_array($san) ? ($san['value'] ?? '') : (string) $san;

                if (strtolower($type) === 'dns' && $value !== '') {
                    $names[] = $value;
                }
            }
        }

        return $names;
    }

    private function isDomainAllowed(string $dns): bool
    {
        // Strip wildcard prefix for matching.
        $domain = preg_replace('/^\*\./', '', $dns);

        if ($domain === null) {
            return false;
        }

        $domain = strtolower($domain);

        foreach ($this->allowedDomains as $allowed) {
            $allowed = strtolower($allowed);

            // Exact match.
            if ($domain === $allowed) {
                return true;
            }

            // Subdomain match: dns is a subdomain of allowed.
            if (str_ends_with($domain, '.' . $allowed)) {
                return true;
            }
        }

        return false;
    }
}
