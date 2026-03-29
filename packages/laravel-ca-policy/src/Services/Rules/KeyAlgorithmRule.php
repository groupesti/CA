<?php

declare(strict_types=1);

namespace CA\Policy\Services\Rules;

use CA\Policy\Contracts\PolicyRuleInterface;
use CA\Policy\DTOs\PolicyContext;
use CA\Policy\DTOs\PolicyRuleResult;
use CA\Models\PolicySeverity;

class KeyAlgorithmRule implements PolicyRuleInterface
{
    private readonly int $minRsaBits;
    private readonly string $minEcCurve;

    /** @var array<string, int> */
    private const array EC_CURVE_STRENGTH = [
        'P-256' => 1,
        'secp256r1' => 1,
        'prime256v1' => 1,
        'P-384' => 2,
        'secp384r1' => 2,
        'P-521' => 3,
        'secp521r1' => 3,
    ];

    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->minRsaBits = (int) ($parameters['min_rsa_bits'] ?? 2048);
        $this->minEcCurve = (string) ($parameters['min_ec_curve'] ?? 'P-256');
    }

    public function evaluate(PolicyContext $context): PolicyRuleResult
    {
        // Validate the requested certificate's key algorithm, not the CA's own key
        $keyAlgorithm = $context->metadata['key_algorithm']
            ?? $context->options->customExtensions['key_algorithm']
            ?? null;

        if ($keyAlgorithm === null) {
            return new PolicyRuleResult(
                passed: true,
                severity: $this->getSeverity(),
                message: 'No key algorithm information available to validate.',
                rule: $this->getName(),
            );
        }

        // Check RSA key size.
        if (preg_match('/rsa[_-]?(\d+)/i', $keyAlgorithm, $matches)) {
            $bits = (int) $matches[1];

            if ($bits < $this->minRsaBits) {
                return new PolicyRuleResult(
                    passed: false,
                    severity: $this->getSeverity(),
                    message: "RSA key size {$bits} bits is below the minimum of {$this->minRsaBits} bits.",
                    rule: $this->getName(),
                );
            }
        }

        // Check EC curve strength.
        $minStrength = self::EC_CURVE_STRENGTH[$this->minEcCurve] ?? 1;

        foreach (self::EC_CURVE_STRENGTH as $curve => $strength) {
            if (stripos($keyAlgorithm, $curve) !== false && $strength < $minStrength) {
                return new PolicyRuleResult(
                    passed: false,
                    severity: $this->getSeverity(),
                    message: "EC curve '{$curve}' is weaker than the minimum required '{$this->minEcCurve}'.",
                    rule: $this->getName(),
                );
            }
        }

        return new PolicyRuleResult(
            passed: true,
            severity: $this->getSeverity(),
            message: 'Key algorithm meets minimum requirements.',
            rule: $this->getName(),
        );
    }

    public function getName(): string
    {
        return 'key_algorithm';
    }

    public function getDescription(): string
    {
        return 'Enforces minimum key sizes: RSA >= 2048 bits, ECDSA >= P-256.';
    }

    public function getSeverity(): string
    {
        return PolicySeverity::ERROR;
    }
}
