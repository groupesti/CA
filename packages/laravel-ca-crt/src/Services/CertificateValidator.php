<?php

declare(strict_types=1);

namespace CA\Crt\Services;

use CA\Crt\Contracts\CertificateValidatorInterface;
use CA\Crt\Models\Certificate;
use CA\Models\CertificateStatus;
use phpseclib3\File\X509;

final class CertificateValidator implements CertificateValidatorInterface
{
    /** @var array<int, string> */
    private array $errors = [];

    public function validate(Certificate $certificate): bool
    {
        $this->errors = [];

        $expiryValid = $this->validateExpiry($certificate);
        $notRevoked = !$this->isRevoked($certificate);
        $chainValid = $this->validateChain($certificate);

        return $expiryValid && $notRevoked && $chainValid;
    }

    public function validateChain(Certificate $certificate): bool
    {
        try {
            $x509 = new X509();
            $x509->loadX509($certificate->certificate_pem);

            // Walk the issuer chain and validate each signature
            $current = $certificate;

            while ($current->issuer_certificate_id !== null) {
                $issuer = $current->issuerCertificate;

                if ($issuer === null) {
                    $this->errors[] = 'Incomplete certificate chain: missing issuer for certificate ' . $current->serial_number;
                    return false;
                }

                $issuerX509 = new X509();
                $issuerX509->loadCA($issuer->certificate_pem);

                $subjectX509 = new X509();
                $subjectX509->loadX509($current->certificate_pem);

                $result = $subjectX509->validateSignature();

                if ($result !== true) {
                    $this->errors[] = 'Signature validation failed for certificate ' . $current->serial_number;
                    return false;
                }

                $current = $issuer;
            }

            // For the root (self-signed), verify its own signature
            if ($current->issuer_certificate_id === null) {
                $rootX509 = new X509();
                $rootX509->loadCA($current->certificate_pem);
                $rootX509->loadX509($current->certificate_pem);

                $result = $rootX509->validateSignature();

                if ($result !== true) {
                    $this->errors[] = 'Root certificate self-signature validation failed for ' . $current->serial_number;
                    return false;
                }
            }

            return true;
        } catch (\Throwable $e) {
            $this->errors[] = 'Chain validation error: ' . $e->getMessage();
            return false;
        }
    }

    public function validateExpiry(Certificate $certificate): bool
    {
        if ($certificate->isExpired()) {
            $this->errors[] = 'Certificate has expired on ' . $certificate->not_after->toIso8601String();
            return false;
        }

        if ($certificate->not_before !== null && $certificate->not_before->isFuture()) {
            $this->errors[] = 'Certificate is not yet valid until ' . $certificate->not_before->toIso8601String();
            return false;
        }

        return true;
    }

    public function isRevoked(Certificate $certificate): bool
    {
        if ($certificate->status === CertificateStatus::REVOKED) {
            $this->errors[] = 'Certificate has been revoked'
                . ($certificate->revocation_reason !== null ? ' (reason: ' . $certificate->revocation_reason . ')' : '');
            return true;
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
