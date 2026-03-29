<?php

declare(strict_types=1);

namespace CA\Oid\Facades;

use CA\Oid\Contracts\OidRegistryInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \CA\Oid\DTOs\OidInfo|null resolve(string $oid)
 * @method static \CA\Oid\DTOs\OidInfo|null resolveByName(string $name)
 * @method static \CA\Oid\Models\OidEntry register(string $oid, string $name, string $description = '', ?string $category = null)
 * @method static bool exists(string $oid)
 * @method static \Illuminate\Support\Collection search(string $query)
 * @method static \Illuminate\Support\Collection getByCategory(string $category)
 * @method static bool validate(string $oid)
 * @method static string format(string $oid)
 * @method static bool unregister(string $oid)
 *
 * @see \CA\Oid\Services\OidRegistry
 */
class CaOid extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return OidRegistryInterface::class;
    }
}
