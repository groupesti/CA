<?php

declare(strict_types=1);

namespace CA\Oid\Contracts;

interface OidResolverInterface
{
    public function toName(string $oid): ?string;

    public function toOid(string $name): ?string;

    public function getDescription(string $oid): ?string;
}
