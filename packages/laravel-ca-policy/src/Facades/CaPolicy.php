<?php

declare(strict_types=1);

namespace CA\Policy\Facades;

use CA\Policy\Contracts\PolicyEngineInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \CA\Policy\DTOs\PolicyResult evaluate(\CA\Models\CertificateAuthority $ca, \CA\DTOs\CertificateOptions $options)
 * @method static \CA\Policy\DTOs\PolicyResult validateIssuance(\CA\Models\CertificateAuthority $ca, \CA\DTOs\CertificateOptions $options)
 * @method static \CA\Policy\DTOs\PolicyResult validateSubject(\CA\Models\CertificateAuthority $ca, \CA\DTOs\DistinguishedName $subject, ?array $sans = null)
 * @method static \CA\Policy\DTOs\EffectivePolicy getEffectivePolicy(\CA\Models\CertificateAuthority $ca)
 *
 * @see \CA\Policy\Services\PolicyEngine
 */
class CaPolicy extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PolicyEngineInterface::class;
    }
}
