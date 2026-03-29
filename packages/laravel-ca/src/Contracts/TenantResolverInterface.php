<?php

declare(strict_types=1);

namespace CA\Contracts;

interface TenantResolverInterface
{
    public function resolve(): ?string;
}
