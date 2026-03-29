<?php

declare(strict_types=1);

namespace CA\Crt\Contracts;

use CA\Crt\Models\Certificate;

interface CertificateValidatorInterface
{
    /**
     * Perform full validation of a certificate.
     */
    public function validate(Certificate $certificate): bool;

    /**
     * Validate the certificate's signature chain up to root.
     */
    public function validateChain(Certificate $certificate): bool;

    /**
     * Validate that the certificate has not expired.
     */
    public function validateExpiry(Certificate $certificate): bool;

    /**
     * Check whether the certificate has been revoked.
     */
    public function isRevoked(Certificate $certificate): bool;

    /**
     * Get validation error messages from the last validation run.
     *
     * @return array<int, string>
     */
    public function getErrors(): array;
}
