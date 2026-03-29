<?php

declare(strict_types=1);

namespace CA\Crt\Services;

use CA\Crt\Contracts\CertificateSignerInterface;
use CA\Crt\Events\CertificateRenewed;
use CA\Crt\Models\Certificate;
use CA\DTOs\CertificateOptions;
use CA\Models\CertificateStatus;
use CA\Models\HashAlgorithm;
use CA\Exceptions\CertificateException;
use CA\Key\Contracts\KeyManagerInterface;
use CA\Services\SerialNumberGenerator;
use phpseclib3\File\X509;

final class CertificateRenewer
{
    public function __construct(
        private readonly CertificateSignerInterface $signer,
        private readonly KeyManagerInterface $keyManager,
        private readonly SerialNumberGenerator $serialGenerator,
        private readonly ChainBuilder $chainBuilder,
    ) {}

    /**
     * Renew a certificate, producing a new certificate with the same subject and extensions.
     */
    public function renew(Certificate $certificate, ?int $validityDays = null): Certificate
    {
        $this->validateRenewable($certificate);

        $days = $validityDays ?? (int) config('ca-crt.default_validity_days', 365);

        $issuer = $certificate->issuerCertificate;
        $ca = $certificate->certificateAuthority;

        if ($ca === null) {
            throw new CertificateException('Cannot renew a certificate without a certificate authority.');
        }

        // Rebuild the options from the original certificate
        $options = new CertificateOptions(
            type: $certificate->type,
            validityDays: $days,
            hashAlgorithm: HashAlgorithm::from((string) config('ca-crt.default_hash', 'sha256')),
            keyUsage: $certificate->key_usage ?? [],
            extendedKeyUsage: $certificate->extended_key_usage ?? [],
            subjectAlternativeNames: $certificate->san,
            isCa: $certificate->isCa() ? true : false,
            templateId: $certificate->template_id,
        );

        // Load the subject public key from the existing certificate
        $subjectX509 = new X509();
        $subjectX509->loadX509($certificate->certificate_pem);

        // Determine the issuer: self-signed certificates are their own issuer
        if ($issuer !== null) {
            $issuerX509 = new X509();
            $issuerX509->loadX509($issuer->certificate_pem);
            $issuerKey = $this->keyManager->decryptPrivateKey($issuer->key);
        } else {
            // Self-signed: use own key
            $issuerX509 = $subjectX509;
            $issuerKey = $this->keyManager->decryptPrivateKey($certificate->key);
        }

        // Build the new subject certificate
        $newSubjectX509 = new X509();
        $newSubjectX509->setPublicKey($subjectX509->getPublicKey());
        $newSubjectX509->setDN($subjectX509->getDN(X509::DN_STRING));

        $pem = $this->signer->sign($issuerX509, $newSubjectX509, $issuerKey, $options);

        // Parse the signed cert for fingerprint
        $parsedX509 = new X509();
        $parsedX509->loadX509($pem);

        $derBody = $this->pemToDer($pem);
        $fingerprint = hash('sha256', $derBody);
        $formattedFingerprint = implode(':', str_split($fingerprint, 2));

        $serialNumber = $parsedX509->getCurrentCert()['tbsCertificate']['serialNumber']->toHex();

        $newCertificate = Certificate::create([
            'ca_id' => $certificate->ca_id,
            'tenant_id' => $certificate->tenant_id,
            'key_id' => $certificate->key_id,
            'csr_id' => $certificate->csr_id,
            'issuer_certificate_id' => $certificate->issuer_certificate_id,
            'template_id' => $certificate->template_id,
            'serial_number' => strtoupper($serialNumber),
            'type' => $certificate->type,
            'subject_dn' => $certificate->subject_dn,
            'san' => $certificate->san,
            'certificate_pem' => $pem,
            'certificate_der' => $derBody,
            'fingerprint_sha256' => $formattedFingerprint,
            'status' => CertificateStatus::ACTIVE,
            'not_before' => now(),
            'not_after' => now()->addDays($days),
            'key_usage' => $certificate->key_usage,
            'extended_key_usage' => $certificate->extended_key_usage,
            'ms_template_oid' => $certificate->ms_template_oid,
            'ms_template_name' => $certificate->ms_template_name,
            'metadata' => array_merge($certificate->metadata ?? [], [
                'renewed_from' => $certificate->uuid,
                'renewed_at' => now()->toIso8601String(),
            ]),
        ]);

        // Revoke the old certificate with SUPERSEDED reason
        $certificate->update([
            'status' => CertificateStatus::REVOKED,
            'revocation_reason' => 'superseded',
            'revoked_at' => now(),
        ]);

        $this->chainBuilder->buildAndStore($newCertificate);

        event(new CertificateRenewed($newCertificate, $certificate));

        return $newCertificate;
    }

    private function validateRenewable(Certificate $certificate): void
    {
        if ($certificate->isRevoked()) {
            throw new CertificateException('Cannot renew a revoked certificate.');
        }

        if ($certificate->key === null) {
            throw new CertificateException('Cannot renew a certificate without an associated key.');
        }
    }

    private function pemToDer(string $pem): string
    {
        $pem = preg_replace('/-----BEGIN CERTIFICATE-----/', '', $pem);
        $pem = preg_replace('/-----END CERTIFICATE-----/', '', $pem);
        $pem = preg_replace('/\s+/', '', $pem);

        $der = base64_decode($pem, true);

        if ($der === false) {
            throw new CertificateException('Failed to decode PEM to DER.');
        }

        return $der;
    }
}
