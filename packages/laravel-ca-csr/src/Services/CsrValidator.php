<?php

declare(strict_types=1);

namespace CA\Csr\Services;

use CA\Csr\Contracts\CsrValidatorInterface;
use CA\Csr\Models\Csr;
use phpseclib3\File\X509;

final class CsrValidator implements CsrValidatorInterface
{
    /** @var array<int, string> */
    private array $errors = [];

    public function validate(Csr $csr): bool
    {
        $this->errors = [];

        $this->validateSignature($csr);
        $this->validateDN($csr, config('ca-csr.required_dn_fields', ['CN']));

        return $this->errors === [];
    }

    public function validateSignature(Csr $csr): bool
    {
        try {
            $x509 = new X509();
            $csrData = $x509->loadCSR($csr->csr_pem);

            if ($csrData === false) {
                $this->errors[] = 'Failed to parse CSR PEM data.';
                return false;
            }

            $isValid = $x509->validateSignature();

            if ($isValid !== true) {
                $this->errors[] = 'CSR signature verification failed.';
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            $this->errors[] = 'CSR signature validation error: ' . $e->getMessage();
            return false;
        }
    }

    public function validateDN(Csr $csr, array $rules = []): bool
    {
        $subjectDn = $csr->subject_dn ?? [];
        $valid = true;

        foreach ($rules as $field) {
            if (!isset($subjectDn[$field]) || trim((string) $subjectDn[$field]) === '') {
                $this->errors[] = "Required DN field '{$field}' is missing or empty.";
                $valid = false;
            }
        }

        // Validate country code length (ISO 3166-1 alpha-2).
        if (isset($subjectDn['C']) && strlen((string) $subjectDn['C']) !== 2) {
            $this->errors[] = 'Country code (C) must be exactly 2 characters.';
            $valid = false;
        }

        // Validate email format.
        if (isset($subjectDn['emailAddress']) && !filter_var($subjectDn['emailAddress'], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Email address in DN is not a valid email.';
            $valid = false;
        }

        return $valid;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
