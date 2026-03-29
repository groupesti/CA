<?php

declare(strict_types=1);

namespace CA\Scep\Contracts;

use CA\Models\CertificateAuthority;

interface ScepServerInterface
{
    /**
     * Handle GetCACert operation - return CA certificate(s) as degenerate PKCS#7.
     */
    public function handleGetCACert(CertificateAuthority $ca): string;

    /**
     * Handle GetCACaps operation - return capabilities as newline-separated text.
     */
    public function handleGetCACaps(CertificateAuthority $ca): string;

    /**
     * Handle PKCSReq operation - process a PKCS#7 enrollment request.
     */
    public function handlePKCSReq(CertificateAuthority $ca, string $pkiMessage): string;

    /**
     * Handle GetNextCACert operation - return next CA certificate for rollover.
     */
    public function handleGetNextCACert(CertificateAuthority $ca): string;

    /**
     * Handle CertPoll operation - check enrollment status.
     */
    public function handleCertPoll(CertificateAuthority $ca, string $pkiMessage): string;

    /**
     * Handle GetCert operation - retrieve an issued certificate.
     */
    public function handleGetCert(CertificateAuthority $ca, string $pkiMessage): string;

    /**
     * Handle GetCRL operation - retrieve the certificate revocation list.
     */
    public function handleGetCRL(CertificateAuthority $ca, string $pkiMessage): string;
}
