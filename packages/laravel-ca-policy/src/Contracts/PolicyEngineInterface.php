<?php

declare(strict_types=1);

namespace CA\Policy\Contracts;

use CA\DTOs\CertificateOptions;
use CA\DTOs\DistinguishedName;
use CA\Models\CertificateAuthority;
use CA\Policy\DTOs\EffectivePolicy;
use CA\Policy\DTOs\PolicyResult;

interface PolicyEngineInterface
{
    public function evaluate(CertificateAuthority $ca, CertificateOptions $options): PolicyResult;

    public function validateIssuance(CertificateAuthority $ca, CertificateOptions $options): PolicyResult;

    public function validateSubject(
        CertificateAuthority $ca,
        DistinguishedName $subject,
        ?array $sans = null,
    ): PolicyResult;

    public function getEffectivePolicy(CertificateAuthority $ca): EffectivePolicy;
}
