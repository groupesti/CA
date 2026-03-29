<?php

declare(strict_types=1);

namespace CA\Policy\Contracts;

use CA\DTOs\DistinguishedName;

interface NameConstraintInterface
{
    public function isPermitted(string $name, string $type): bool;

    public function addPermitted(string $subtree, string $type): void;

    public function addExcluded(string $subtree, string $type): void;

    /**
     * Validate a distinguished name and optional SANs against name constraints.
     *
     * @param array<int, array{type: string, value: string}>|null $sans
     * @return array<int, string> List of violation messages.
     */
    public function validate(DistinguishedName $subject, ?array $sans = null): array;
}
