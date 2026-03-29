<?php

declare(strict_types=1);

namespace CA\Scep\Facades;

use CA\Scep\Contracts\ScepServerInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string handleGetCACert(\CA\Models\CertificateAuthority $ca)
 * @method static string handleGetCACaps(\CA\Models\CertificateAuthority $ca)
 * @method static string handlePKCSReq(\CA\Models\CertificateAuthority $ca, string $pkiMessage)
 * @method static string handleGetNextCACert(\CA\Models\CertificateAuthority $ca)
 * @method static string handleCertPoll(\CA\Models\CertificateAuthority $ca, string $pkiMessage)
 * @method static string handleGetCert(\CA\Models\CertificateAuthority $ca, string $pkiMessage)
 * @method static string handleGetCRL(\CA\Models\CertificateAuthority $ca, string $pkiMessage)
 *
 * @see \CA\Scep\Services\ScepServer
 */
final class CaScep extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ScepServerInterface::class;
    }
}
