<?php

declare(strict_types=1);

namespace CA\Contracts;

interface AuditableInterface
{
    public function getAuditAction(): string;

    public function getAuditMetadata(): array;
}
