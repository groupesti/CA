<?php

declare(strict_types=1);

namespace CA\OCSP\Contracts;

use CA\OCSP\DTOs\CertStatusResult;

interface CertificateStatusResolverInterface
{
    /**
     * Resolve the certificate status for the given CertID fields.
     */
    public function resolve(
        string $issuerNameHash,
        string $issuerKeyHash,
        string $serialNumber,
        string $hashAlgorithm,
    ): CertStatusResult;
}
