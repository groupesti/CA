<?php

declare(strict_types=1);

namespace CA\Policy\DTOs;

final readonly class PolicyRuleResult
{
    public function __construct(
        public bool $passed,
        public string $severity,
        public string $message,
        public ?string $rule = null,
    ) {}
}
