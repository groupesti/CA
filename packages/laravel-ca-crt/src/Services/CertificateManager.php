<?php

declare(strict_types=1);

namespace CA\Crt\Services;

use CA\Crt\Contracts\CertificateManagerInterface;
use CA\Crt\Contracts\CertificateSignerInterface;
use CA\Crt\Contracts\CertificateValidatorInterface;
use CA\Crt\Events\CertificateIssued;
use CA\Crt\Events\CertificateRevoked;
use CA\Crt\Models\Certificate;
use CA\Csr\Models\Csr;
use CA\DTOs\CertificateOptions;
use CA\DTOs\DistinguishedName;
use CA\Models\CertificateStatus;
use CA\Models\CertificateType;
use CA\Models\ExportFormat;
use CA\Models\HashAlgorithm;
use CA\Models\RevocationReason;
use CA\Exceptions\CertificateException;
use CA\Key\Contracts\KeyManagerInterface;
use CA\Key\Models\Key;
use CA\Models\CertificateAuthority;
use CA\Services\SerialNumberGenerator;
use Illuminate\Support\Collection;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\File\X509;

final class CertificateManager implements CertificateManagerInterface
{
    public function __construct(
        private readonly CertificateSignerInterface $signer,
        private readonly CertificateValidatorInterface $validator,
        private readonly CertificateExporter $exporter,
        private readonly CertificateRenewer $renewer,
        private readonly ChainBuilder $chainBuilder,
        private readonly KeyManagerInterface $keyManager,
        private readonly SerialNumberGenerator $serialGenerator,
    ) {}

    public function issueFromCsr(
        CertificateAuthority $ca,
        Csr $csr,
        CertificateOptions $options,
    ): Certificate {
        // Load the CA's signing certificate
        $caCert = $this->findCaCertificate($ca);

        // Load the issuer certificate into X509
        $issuerX509 = new X509();
        $issuerX509->loadX509($caCert->certificate_pem);

        // Load the CSR
        $csrX509 = new X509();
        $csrX509->loadCSR($csr->csr_pem);

        // Build the subject from the CSR
        $subjectX509 = new X509();
        $subjectPublicKey = $csrX509->getPublicKey();

        if ($subjectPublicKey === false) {
            throw new CertificateException('Failed to extract public key from CSR.');
        }

        $subjectX509->setPublicKey($subjectPublicKey);

        // Set the subject DN from the CSR
        $dn = $csr->subject_dn;
        $subjectX509->setDN($this->buildDnArray($dn));

        // Decrypt the CA's private key for signing
        $issuerKey = $this->keyManager->decryptPrivateKey($caCert->key);

        // Sign the certificate
        $pem = $this->signer->sign($issuerX509, $subjectX509, $issuerKey, $options);

        // Parse signed cert for metadata
        $parsedX509 = new X509();
        $parsedX509->loadX509($pem);

        $derBody = $this->pemToDer($pem);
        $fingerprint = $this->computeFingerprint($derBody);
        $serialNumber = $parsedX509->getCurrentCert()['tbsCertificate']['serialNumber']->toHex();

        $certificate = Certificate::create([
            'ca_id' => $ca->id,
            'tenant_id' => $ca->tenant_id,
            'key_id' => $csr->key_id,
            'csr_id' => $csr->id,
            'issuer_certificate_id' => $caCert->id,
            'template_id' => $options->templateId ?? $csr->template_id,
            'serial_number' => strtoupper($serialNumber),
            'type' => $options->type,
            'subject_dn' => $csr->subject_dn,
            'san' => $options->subjectAlternativeNames ?? $csr->san,
            'certificate_pem' => $pem,
            'certificate_der' => $derBody,
            'fingerprint_sha256' => $fingerprint,
            'status' => CertificateStatus::ACTIVE,
            'not_before' => now(),
            'not_after' => now()->addDays($options->validityDays),
            'key_usage' => $options->keyUsage !== [] ? $options->keyUsage : null,
            'extended_key_usage' => $options->extendedKeyUsage !== [] ? $options->extendedKeyUsage : null,
            'metadata' => [
                'issued_from' => 'csr',
                'csr_id' => $csr->id,
            ],
        ]);

        $this->chainBuilder->buildAndStore($certificate);

        event(new CertificateIssued($certificate));

        return $certificate;
    }

    public function issueSelfSigned(
        CertificateAuthority $ca,
        Key $key,
        CertificateOptions $options,
    ): Certificate {
        $privateKey = $this->keyManager->decryptPrivateKey($key);
        $publicKey = $privateKey->getPublicKey();

        // Build the subject/issuer (same for self-signed)
        $subjectDn = $ca->getSubjectDN();

        $issuerX509 = new X509();
        $issuerX509->setPublicKey($publicKey);
        $issuerX509->setDN($this->buildDnArray($subjectDn));

        $subjectX509 = new X509();
        $subjectX509->setPublicKey($publicKey);
        $subjectX509->setDN($this->buildDnArray($subjectDn));

        // Force CA-specific options for self-signed root
        $rootOptions = new CertificateOptions(
            type: $options->type,
            validityDays: $options->validityDays,
            hashAlgorithm: $options->hashAlgorithm ?? HashAlgorithm::from(
                (string) config('ca-crt.default_hash', 'sha256'),
            ),
            keyUsage: $options->keyUsage !== [] ? $options->keyUsage : ['keyCertSign', 'cRLSign'],
            extendedKeyUsage: $options->extendedKeyUsage,
            subjectAlternativeNames: $options->subjectAlternativeNames,
            isCa: true,
            pathLength: $options->pathLength,
            customExtensions: $options->customExtensions,
            templateId: $options->templateId,
        );

        $pem = $this->signer->sign($issuerX509, $subjectX509, $privateKey, $rootOptions);

        $parsedX509 = new X509();
        $parsedX509->loadX509($pem);

        $derBody = $this->pemToDer($pem);
        $fingerprint = $this->computeFingerprint($derBody);
        $serialNumber = $parsedX509->getCurrentCert()['tbsCertificate']['serialNumber']->toHex();

        $certificate = Certificate::create([
            'ca_id' => $ca->id,
            'tenant_id' => $ca->tenant_id,
            'key_id' => $key->id,
            'issuer_certificate_id' => null, // Self-signed; will reference itself post-creation
            'serial_number' => strtoupper($serialNumber),
            'type' => $options->type,
            'subject_dn' => $subjectDn,
            'certificate_pem' => $pem,
            'certificate_der' => $derBody,
            'fingerprint_sha256' => $fingerprint,
            'status' => CertificateStatus::ACTIVE,
            'not_before' => now(),
            'not_after' => now()->addDays($options->validityDays),
            'key_usage' => $rootOptions->keyUsage !== [] ? $rootOptions->keyUsage : null,
            'extended_key_usage' => $rootOptions->extendedKeyUsage !== [] ? $rootOptions->extendedKeyUsage : null,
            'metadata' => [
                'issued_from' => 'self_signed',
                'is_root' => true,
            ],
        ]);

        // Self-referential issuer
        $certificate->update(['issuer_certificate_id' => $certificate->id]);

        return $certificate;
    }

    public function issueIntermediate(
        CertificateAuthority $parentCa,
        DistinguishedName $dn,
        Key $key,
        CertificateOptions $options,
    ): Certificate {
        $parentCert = $this->findCaCertificate($parentCa);

        $issuerX509 = new X509();
        $issuerX509->loadX509($parentCert->certificate_pem);

        $privateKeySubject = $this->keyManager->decryptPrivateKey($key);
        $publicKey = $privateKeySubject->getPublicKey();

        $subjectX509 = new X509();
        $subjectX509->setPublicKey($publicKey);
        $subjectX509->setDN($this->buildDnArray($dn->toArray()));

        $parentKey = $this->keyManager->decryptPrivateKey($parentCert->key);

        // Force CA-specific options for intermediate
        $intOptions = new CertificateOptions(
            type: $options->type,
            validityDays: $options->validityDays,
            hashAlgorithm: $options->hashAlgorithm ?? HashAlgorithm::from(
                (string) config('ca-crt.default_hash', 'sha256'),
            ),
            keyUsage: $options->keyUsage !== [] ? $options->keyUsage : ['keyCertSign', 'cRLSign'],
            extendedKeyUsage: $options->extendedKeyUsage,
            subjectAlternativeNames: $options->subjectAlternativeNames,
            isCa: true,
            pathLength: $options->pathLength ?? 0,
            customExtensions: $options->customExtensions,
            templateId: $options->templateId,
        );

        $pem = $this->signer->sign($issuerX509, $subjectX509, $parentKey, $intOptions);

        $parsedX509 = new X509();
        $parsedX509->loadX509($pem);

        $derBody = $this->pemToDer($pem);
        $fingerprint = $this->computeFingerprint($derBody);
        $serialNumber = $parsedX509->getCurrentCert()['tbsCertificate']['serialNumber']->toHex();

        $certificate = Certificate::create([
            'ca_id' => $parentCa->id,
            'tenant_id' => $parentCa->tenant_id,
            'key_id' => $key->id,
            'issuer_certificate_id' => $parentCert->id,
            'serial_number' => strtoupper($serialNumber),
            'type' => CertificateType::INTERMEDIATE_CA,
            'subject_dn' => $dn->toArray(),
            'certificate_pem' => $pem,
            'certificate_der' => $derBody,
            'fingerprint_sha256' => $fingerprint,
            'status' => CertificateStatus::ACTIVE,
            'not_before' => now(),
            'not_after' => now()->addDays($options->validityDays),
            'key_usage' => $intOptions->keyUsage !== [] ? $intOptions->keyUsage : null,
            'extended_key_usage' => $intOptions->extendedKeyUsage !== [] ? $intOptions->extendedKeyUsage : null,
            'metadata' => [
                'issued_from' => 'intermediate',
                'parent_ca_id' => $parentCa->id,
            ],
        ]);

        $this->chainBuilder->buildAndStore($certificate);

        event(new CertificateIssued($certificate));

        return $certificate;
    }

    public function revoke(Certificate $certificate, RevocationReason $reason): Certificate
    {
        if ($certificate->isRevoked()) {
            throw new CertificateException('Certificate is already revoked.');
        }

        $certificate->update([
            'status' => CertificateStatus::REVOKED,
            'revocation_reason' => $reason->name,
            'revoked_at' => now(),
        ]);

        $certificate->refresh();

        event(new CertificateRevoked($certificate, $reason));

        return $certificate;
    }

    public function renew(Certificate $certificate, ?int $validityDays = null): Certificate
    {
        return $this->renewer->renew($certificate, $validityDays);
    }

    public function verify(Certificate $certificate): bool
    {
        return $this->validator->validate($certificate);
    }

    /**
     * @return array<int, Certificate>
     */
    public function getChain(Certificate $certificate): array
    {
        return $this->chainBuilder->buildChain($certificate);
    }

    public function export(Certificate $certificate, ExportFormat $format): string
    {
        return $this->exporter->export($certificate, $format);
    }

    public function findBySerial(CertificateAuthority $ca, string $serial): ?Certificate
    {
        return Certificate::query()
            ->where('ca_id', $ca->id)
            ->where('serial_number', strtoupper($serial))
            ->first();
    }

    public function findByUuid(string $uuid): ?Certificate
    {
        return Certificate::query()
            ->where('uuid', $uuid)
            ->first();
    }

    public function getExpiring(int $days): Collection
    {
        return Certificate::query()
            ->expiring($days)
            ->get();
    }

    /**
     * Find the active CA certificate for signing.
     */
    private function findCaCertificate(CertificateAuthority $ca): Certificate
    {
        $caCert = Certificate::query()
            ->where('ca_id', $ca->id)
            ->where('status', CertificateStatus::ACTIVE)
            ->whereIn('type', [CertificateType::ROOT_CA, CertificateType::INTERMEDIATE_CA])
            ->orderByDesc('not_before')
            ->first();

        if ($caCert === null) {
            throw new CertificateException(
                'No active CA certificate found for certificate authority: ' . $ca->id,
            );
        }

        return $caCert;
    }

    /**
     * Convert a DN array to phpseclib format.
     */
    private function buildDnArray(array $dn): array
    {
        $mapping = [
            'CN' => 'id-at-commonName',
            'O' => 'id-at-organizationName',
            'OU' => 'id-at-organizationalUnitName',
            'C' => 'id-at-countryName',
            'ST' => 'id-at-stateOrProvinceName',
            'L' => 'id-at-localityName',
            'emailAddress' => 'id-emailAddress',
            'serialNumber' => 'id-at-serialNumber',
        ];

        $result = ['rdnSequence' => []];

        foreach ($dn as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $oid = $mapping[$key] ?? $key;
            $result['rdnSequence'][] = [
                [
                    'type' => $oid,
                    'value' => ['utf8String' => $value],
                ],
            ];
        }

        return $result;
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

    private function computeFingerprint(string $der): string
    {
        $hash = hash('sha256', $der);

        return implode(':', str_split($hash, 2));
    }
}
