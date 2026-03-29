<?php

declare(strict_types=1);

namespace CA\Est\Facades;

use CA\Est\Contracts\EstServerInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string getCaCerts(\CA\Models\CertificateAuthority $ca)
 * @method static string simpleEnroll(\CA\Models\CertificateAuthority $ca, string $csrBase64, ?array $auth = null)
 * @method static string simpleReenroll(\CA\Models\CertificateAuthority $ca, string $csrBase64, array $clientCert)
 * @method static array serverKeyGen(\CA\Models\CertificateAuthority $ca, string $csrBase64, ?array $auth = null)
 * @method static string fullCmc(\CA\Models\CertificateAuthority $ca, string $cmcRequest)
 * @method static string getCsrAttrs(\CA\Models\CertificateAuthority $ca)
 *
 * @see \CA\Est\Services\EstServer
 */
class CaEst extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return EstServerInterface::class;
    }
}
