<?php

declare(strict_types=1);

namespace CA\Crt\Contracts;

use CA\Crt\Models\Certificate;
use CA\Csr\Models\Csr;
use CA\DTOs\CertificateOptions;
use CA\DTOs\DistinguishedName;
use CA\Models\ExportFormat;
use CA\Models\RevocationReason;
use CA\Key\Models\Key;
use CA\Models\CertificateAuthority;
use Illuminate\Support\Collection;

interface CertificateManagerInterface
{
    /**
     * Issue a certificate from an approved CSR.
     */
    public function issueFromCsr(
        CertificateAuthority $ca,
        Csr $csr,
        CertificateOptions $options,
    ): Certificate;

    /**
     * Issue a self-signed certificate (for Root CA).
     */
    public function issueSelfSigned(
        CertificateAuthority $ca,
        Key $key,
        CertificateOptions $options,
    ): Certificate;

    /**
     * Issue an intermediate CA certificate signed by a parent CA.
     */
    public function issueIntermediate(
        CertificateAuthority $parentCa,
        DistinguishedName $dn,
        Key $key,
        CertificateOptions $options,
    ): Certificate;

    /**
     * Revoke a certificate with the given reason.
     */
    public function revoke(Certificate $certificate, RevocationReason $reason): Certificate;

    /**
     * Renew a certificate, optionally with a new validity period.
     */
    public function renew(Certificate $certificate, ?int $validityDays = null): Certificate;

    /**
     * Verify a certificate's validity (signature chain, expiry, revocation).
     */
    public function verify(Certificate $certificate): bool;

    /**
     * Get the full certificate chain from leaf to root.
     *
     * @return array<int, Certificate>
     */
    public function getChain(Certificate $certificate): array;

    /**
     * Export a certificate in the specified format.
     */
    public function export(Certificate $certificate, ExportFormat $format): string;

    /**
     * Find a certificate by serial number within a CA.
     */
    public function findBySerial(CertificateAuthority $ca, string $serial): ?Certificate;

    /**
     * Find a certificate by its UUID.
     */
    public function findByUuid(string $uuid): ?Certificate;

    /**
     * Get certificates expiring within the given number of days.
     */
    public function getExpiring(int $days): Collection;
}
