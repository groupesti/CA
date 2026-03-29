<?php

declare(strict_types=1);

namespace CA\Csr\Contracts;

use CA\Csr\Models\Csr;
use CA\DTOs\DistinguishedName;
use CA\Key\Models\Key;
use CA\Models\CertificateTemplate;
use phpseclib3\Crypt\Common\PublicKey;

interface CsrManagerInterface
{
    /**
     * Create a new CSR.
     *
     * @param  array<string, mixed>  $extensions
     */
    public function create(
        DistinguishedName $dn,
        Key $key,
        array $extensions = [],
        ?CertificateTemplate $template = null,
    ): Csr;

    /**
     * Import an external CSR from PEM.
     */
    public function import(string $csrPem): Csr;

    /**
     * Validate a CSR (signature + DN requirements).
     */
    public function validate(Csr $csr): bool;

    /**
     * Approve a CSR.
     */
    public function approve(Csr $csr, ?string $approvedBy = null): Csr;

    /**
     * Reject a CSR.
     */
    public function reject(Csr $csr, string $reason, ?string $rejectedBy = null): Csr;

    /**
     * Extract the subject DN from a CSR.
     */
    public function getSubjectDN(Csr $csr): DistinguishedName;

    /**
     * Extract the public key from a CSR.
     */
    public function getPublicKey(Csr $csr): PublicKey;

    /**
     * Find a CSR by its UUID.
     */
    public function findByUuid(string $uuid): ?Csr;
}
