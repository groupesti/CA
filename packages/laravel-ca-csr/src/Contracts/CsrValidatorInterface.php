<?php

declare(strict_types=1);

namespace CA\Csr\Contracts;

use CA\Csr\Models\Csr;

interface CsrValidatorInterface
{
    /**
     * Run all validations on the CSR.
     */
    public function validate(Csr $csr): bool;

    /**
     * Verify the CSR's self-signature.
     */
    public function validateSignature(Csr $csr): bool;

    /**
     * Validate DN fields against the given rules.
     *
     * @param  array<int, string>  $rules  Required field names (e.g. ['CN', 'O'])
     */
    public function validateDN(Csr $csr, array $rules = []): bool;

    /**
     * Return accumulated validation errors.
     *
     * @return array<int, string>
     */
    public function getErrors(): array;
}
