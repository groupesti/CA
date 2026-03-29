<?php

declare(strict_types=1);

namespace CA\Crt\Contracts;

use CA\DTOs\CertificateOptions;
use phpseclib3\Crypt\Common\PrivateKey;
use phpseclib3\File\X509;

interface CertificateSignerInterface
{
    /**
     * Sign a subject certificate with the issuer's key.
     *
     * @return string The signed certificate in PEM format.
     */
    public function sign(
        X509 $issuerX509,
        X509 $subjectX509,
        PrivateKey $issuerKey,
        CertificateOptions $options,
    ): string;
}
