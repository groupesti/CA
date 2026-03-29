<?php

declare(strict_types=1);

namespace CA\Policy\DTOs;

final readonly class PolicyResult
{
    /**
     * @param array<int, string> $violations
     * @param array<int, string> $warnings
     */
    public function __construct(
        public bool $allowed,
        public array $violations = [],
        public array $warnings = [],
        public ?string $action = null,
    ) {}

    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    public function hasWarnings(): bool
    {
        return count($this->warnings) > 0;
    }

    /**
     * @return array<int, string>
     */
    public function getViolations(): array
    {
        return $this->violations;
    }
}
