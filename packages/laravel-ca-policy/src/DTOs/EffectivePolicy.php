<?php

declare(strict_types=1);

namespace CA\Policy\DTOs;

final readonly class EffectivePolicy
{
    /**
     * @param array<string, mixed> $nameConstraints
     * @param array<string, mixed> $policyConstraints
     * @param array<int, string>   $allowedKeyUsages
     * @param array<int, string>   $allowedEkus
     * @param array<int, mixed>    $rules
     */
    public function __construct(
        public ?string $policyOid = null,
        public ?string $cpsUri = null,
        public array $nameConstraints = [],
        public array $policyConstraints = [],
        public int $maxPathLength = -1,
        public array $allowedKeyUsages = [],
        public array $allowedEkus = [],
        public int $maxValidityDays = 397,
        public array $rules = [],
    ) {}
}
