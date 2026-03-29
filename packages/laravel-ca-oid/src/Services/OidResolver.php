<?php

declare(strict_types=1);

namespace CA\Oid\Services;

use CA\Oid\Contracts\OidResolverInterface;

final class OidResolver implements OidResolverInterface
{
    public function __construct(
        private readonly OidRegistry $registry,
    ) {}

    public function toName(string $oid): ?string
    {
        $info = $this->registry->resolve($oid);

        return $info?->name;
    }

    public function toOid(string $name): ?string
    {
        $info = $this->registry->resolveByName($name);

        return $info?->oid;
    }

    public function getDescription(string $oid): ?string
    {
        $info = $this->registry->resolve($oid);

        return $info?->description;
    }
}
