<?php

declare(strict_types=1);

namespace CA\Policy\Services\Rules;

use CA\Models\CertificateType;
use CA\Policy\Contracts\PolicyRuleInterface;
use CA\Policy\DTOs\PolicyContext;
use CA\Policy\DTOs\PolicyRuleResult;
use CA\Models\PolicySeverity;

class KeyUsageRule implements PolicyRuleInterface
{
    /** @var array<string, array<int, string>> */
    private const array EXPECTED_KEY_USAGE = [
        'root_ca' => ['keyCertSign', 'cRLSign'],
        'intermediate_ca' => ['keyCertSign', 'cRLSign'],
        'server_tls' => ['digitalSignature', 'keyEncipherment'],
        'client_mtls' => ['digitalSignature'],
        'code_signing' => ['digitalSignature'],
        'smime' => ['digitalSignature', 'keyEncipherment'],
        'user' => ['digitalSignature'],
        'computer' => ['digitalSignature', 'keyEncipherment'],
    ];

    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(array $parameters = []) {}

    public function evaluate(PolicyContext $context): PolicyRuleResult
    {
        $type = $context->type ?? $context->options->type;
        $requestedKeyUsage = $context->options->keyUsage;

        if (count($requestedKeyUsage) === 0) {
            return new PolicyRuleResult(
                passed: true,
                severity: $this->getSeverity(),
                message: 'No key usage specified; template defaults will apply.',
                rule: $this->getName(),
            );
        }

        $expected = self::EXPECTED_KEY_USAGE[$type->slug] ?? [];

        if (count($expected) === 0) {
            return new PolicyRuleResult(
                passed: true,
                severity: $this->getSeverity(),
                message: "No key usage expectations defined for certificate type '{$type->slug}'.",
                rule: $this->getName(),
            );
        }

        // Check for CA-only key usages on non-CA certificates.
        if (!$type->isCa()) {
            $caOnlyUsages = ['keyCertSign', 'cRLSign'];
            $violations = array_intersect($requestedKeyUsage, $caOnlyUsages);

            if (count($violations) > 0) {
                return new PolicyRuleResult(
                    passed: false,
                    severity: $this->getSeverity(),
                    message: 'Non-CA certificate requests CA-only key usages: ' . implode(', ', $violations) . '.',
                    rule: $this->getName(),
                );
            }
        }

        return new PolicyRuleResult(
            passed: true,
            severity: $this->getSeverity(),
            message: 'Key usage is appropriate for the certificate type.',
            rule: $this->getName(),
        );
    }

    public function getName(): string
    {
        return 'key_usage';
    }

    public function getDescription(): string
    {
        return 'Enforces that requested key usage matches the certificate type.';
    }

    public function getSeverity(): string
    {
        return PolicySeverity::ERROR;
    }
}
