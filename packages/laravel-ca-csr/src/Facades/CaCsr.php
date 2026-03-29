<?php

declare(strict_types=1);

namespace CA\Csr\Facades;

use CA\Csr\Contracts\CsrManagerInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \CA\Csr\Models\Csr create(\CA\DTOs\DistinguishedName $dn, \CA\Key\Models\Key $key, array $extensions = [], ?\CA\Models\CertificateTemplate $template = null)
 * @method static \CA\Csr\Models\Csr import(string $csrPem)
 * @method static bool validate(\CA\Csr\Models\Csr $csr)
 * @method static \CA\Csr\Models\Csr approve(\CA\Csr\Models\Csr $csr, ?string $approvedBy = null)
 * @method static \CA\Csr\Models\Csr reject(\CA\Csr\Models\Csr $csr, string $reason, ?string $rejectedBy = null)
 * @method static \CA\DTOs\DistinguishedName getSubjectDN(\CA\Csr\Models\Csr $csr)
 * @method static \phpseclib3\Crypt\Common\PublicKey getPublicKey(\CA\Csr\Models\Csr $csr)
 * @method static \CA\Csr\Models\Csr|null findByUuid(string $uuid)
 *
 * @see \CA\Csr\Services\CsrManager
 */
class CaCsr extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CsrManagerInterface::class;
    }
}
