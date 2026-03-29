<?php

declare(strict_types=1);

namespace CA\Crt\Facades;

use CA\Crt\Contracts\CertificateManagerInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \CA\Crt\Models\Certificate issueFromCsr(\CA\Models\CertificateAuthority $ca, \CA\Csr\Models\Csr $csr, \CA\DTOs\CertificateOptions $options)
 * @method static \CA\Crt\Models\Certificate issueSelfSigned(\CA\Models\CertificateAuthority $ca, \CA\Key\Models\Key $key, \CA\DTOs\CertificateOptions $options)
 * @method static \CA\Crt\Models\Certificate issueIntermediate(\CA\Models\CertificateAuthority $parentCa, \CA\DTOs\DistinguishedName $dn, \CA\Key\Models\Key $key, \CA\DTOs\CertificateOptions $options)
 * @method static \CA\Crt\Models\Certificate revoke(\CA\Crt\Models\Certificate $certificate, \CA\Enums\RevocationReason $reason)
 * @method static \CA\Crt\Models\Certificate renew(\CA\Crt\Models\Certificate $certificate, ?int $validityDays = null)
 * @method static bool verify(\CA\Crt\Models\Certificate $certificate)
 * @method static array getChain(\CA\Crt\Models\Certificate $certificate)
 * @method static string export(\CA\Crt\Models\Certificate $certificate, \CA\Enums\ExportFormat $format)
 * @method static \CA\Crt\Models\Certificate|null findBySerial(\CA\Models\CertificateAuthority $ca, string $serial)
 * @method static \CA\Crt\Models\Certificate|null findByUuid(string $uuid)
 * @method static \Illuminate\Support\Collection getExpiring(int $days)
 *
 * @see \CA\Crt\Services\CertificateManager
 */
final class CaCrt extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CertificateManagerInterface::class;
    }
}
