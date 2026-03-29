<?php

declare(strict_types=1);

namespace CA\Facades;

use CA\Services\CaManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \CA\Models\CertificateAuthority createRootCA(\CA\DTOs\DistinguishedName $dn, \CA\Enums\KeyAlgorithm $algorithm, array $keyParams = [], int $validityDays = 3650, ?string $tenantId = null, ?int $pathLength = null)
 * @method static \CA\Models\CertificateAuthority createIntermediateCA(\CA\Models\CertificateAuthority $parent, \CA\DTOs\DistinguishedName $dn, \CA\Enums\KeyAlgorithm $algorithm, array $keyParams = [], int $validityDays = 1825, ?int $pathLength = null)
 * @method static array getHierarchy(\CA\Models\CertificateAuthority $ca)
 * @method static void suspend(\CA\Models\CertificateAuthority $ca)
 * @method static void activate(\CA\Models\CertificateAuthority $ca)
 * @method static self forTenant(string $tenantId)
 * @method static \CA\Models\CertificateAuthority|null find(string $uuid)
 * @method static \Illuminate\Support\Collection all()
 *
 * @see \CA\Services\CaManager
 */
class Ca extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CaManager::class;
    }
}
