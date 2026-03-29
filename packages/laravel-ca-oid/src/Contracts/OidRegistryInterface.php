<?php

declare(strict_types=1);

namespace CA\Oid\Contracts;

use CA\Oid\DTOs\OidInfo;
use CA\Oid\Models\OidEntry;
use Illuminate\Support\Collection;

interface OidRegistryInterface
{
    public function resolve(string $oid): ?OidInfo;

    public function resolveByName(string $name): ?OidInfo;

    public function register(string $oid, string $name, string $description = '', ?string $category = null): OidEntry;

    public function exists(string $oid): bool;

    public function search(string $query): Collection;

    public function getByCategory(string $category): Collection;

    public function validate(string $oid): bool;

    public function format(string $oid): string;

    public function unregister(string $oid): bool;
}
