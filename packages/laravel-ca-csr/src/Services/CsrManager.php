<?php

declare(strict_types=1);

namespace CA\Csr\Services;

use CA\Csr\Contracts\CsrManagerInterface;
use CA\Csr\Contracts\CsrValidatorInterface;
use CA\Models\CsrStatus;
use CA\Csr\Events\CsrApproved;
use CA\Csr\Events\CsrCreated;
use CA\Csr\Events\CsrRejected;
use CA\Csr\Models\Csr;
use CA\DTOs\DistinguishedName;
use CA\Exceptions\CsrException;
use CA\Key\Contracts\KeyManagerInterface;
use CA\Key\Models\Key;
use CA\Models\CertificateTemplate;
use phpseclib3\Crypt\Common\PublicKey;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\File\X509;

final class CsrManager implements CsrManagerInterface
{
    public function __construct(
        private readonly KeyManagerInterface $keyManager,
        private readonly CsrValidatorInterface $validator,
    ) {}

    public function create(
        DistinguishedName $dn,
        Key $key,
        array $extensions = [],
        ?CertificateTemplate $template = null,
    ): Csr {
        $privateKey = $this->keyManager->decryptPrivateKey($key);
        $publicKey = $privateKey->getPublicKey();

        $subject = new X509();
        $subject->setPublicKey($publicKey);

        $dnArray = $dn->toArray();
        $this->applyDnToX509($subject, $dnArray);

        $issuer = new X509();
        $issuer->setPrivateKey($privateKey);
        $this->applyDnToX509($issuer, $dnArray);

        // Build SAN list from extensions.
        $sanList = $this->buildSanFromExtensions($extensions);

        if ($sanList !== []) {
            $subject->setExtension('id-ce-subjectAltName', $sanList);
        }

        // Apply custom extensions.
        foreach ($extensions as $ext) {
            if (!is_array($ext) || !isset($ext['oid'])) {
                continue;
            }
            if ($ext['oid'] === 'id-ce-subjectAltName') {
                continue; // Already handled above.
            }
            $subject->setExtension(
                $ext['oid'],
                $ext['value'] ?? true,
                $ext['critical'] ?? false,
            );
        }

        $csrX509 = new X509();
        $csrPem = $csrX509->signCSR($issuer, $subject);

        if ($csrPem === false) {
            throw new CsrException('Failed to generate CSR.');
        }

        $csrPem = $csrX509->saveCSR($csrPem);

        if ($csrPem === false) {
            throw new CsrException('Failed to encode CSR to PEM.');
        }

        $validityDays = config('ca-csr.default_validity_days', 30);
        $autoApprove = config('ca-csr.auto_approve', false);

        $csr = Csr::create([
            'ca_id' => $key->ca_id,
            'tenant_id' => $key->tenant_id,
            'key_id' => $key->id,
            'template_id' => $template?->id,
            'subject_dn' => $dnArray,
            'san' => $sanList !== [] ? $sanList : null,
            'csr_pem' => $csrPem,
            'status' => $autoApprove ? CsrStatus::APPROVED : CsrStatus::PENDING,
            'requested_by' => $this->resolveCurrentUser(),
            'approved_by' => $autoApprove ? 'auto' : null,
            'expires_at' => now()->addDays((int) $validityDays),
        ]);

        event(new CsrCreated($csr));

        if ($autoApprove) {
            event(new CsrApproved($csr, 'auto'));
        }

        return $csr;
    }

    public function import(string $csrPem): Csr
    {
        $x509 = new X509();
        $csrData = $x509->loadCSR($csrPem);

        if ($csrData === false) {
            throw new CsrException('Failed to parse the provided CSR PEM.');
        }

        $isValid = $x509->validateSignature();

        if ($isValid !== true) {
            throw new CsrException('CSR signature verification failed.');
        }

        $subjectDn = $this->extractDnFromX509($x509);
        $sanList = $this->extractSanFromX509($x509);

        $validityDays = config('ca-csr.default_validity_days', 30);

        $csr = Csr::create([
            'subject_dn' => $subjectDn,
            'san' => $sanList !== [] ? $sanList : null,
            'csr_pem' => trim($csrPem),
            'status' => CsrStatus::PENDING,
            'requested_by' => $this->resolveCurrentUser(),
            'expires_at' => now()->addDays((int) $validityDays),
        ]);

        event(new CsrCreated($csr));

        return $csr;
    }

    public function validate(Csr $csr): bool
    {
        return $this->validator->validate($csr);
    }

    public function approve(Csr $csr, ?string $approvedBy = null): Csr
    {
        if (!$csr->isPending()) {
            throw new CsrException("Only pending CSRs can be approved. Current status: {$csr->status}");
        }

        $csr->update([
            'status' => CsrStatus::APPROVED,
            'approved_by' => $approvedBy ?? $this->resolveCurrentUser(),
        ]);

        $csr->refresh();

        event(new CsrApproved($csr, $approvedBy));

        return $csr;
    }

    public function reject(Csr $csr, string $reason, ?string $rejectedBy = null): Csr
    {
        if (!$csr->isPending()) {
            throw new CsrException("Only pending CSRs can be rejected. Current status: {$csr->status}");
        }

        $csr->update([
            'status' => CsrStatus::REJECTED,
            'rejection_reason' => $reason,
        ]);

        $csr->refresh();

        event(new CsrRejected($csr, $reason));

        return $csr;
    }

    public function getSubjectDN(Csr $csr): DistinguishedName
    {
        $x509 = new X509();
        $csrData = $x509->loadCSR($csr->csr_pem);

        if ($csrData === false) {
            throw new CsrException('Failed to parse CSR PEM.');
        }

        $dn = $this->extractDnFromX509($x509);

        return DistinguishedName::fromArray($dn);
    }

    public function getPublicKey(Csr $csr): PublicKey
    {
        $x509 = new X509();
        $csrData = $x509->loadCSR($csr->csr_pem);

        if ($csrData === false) {
            throw new CsrException('Failed to parse CSR PEM.');
        }

        $publicKeyPem = $x509->getPublicKey();

        if ($publicKeyPem === false) {
            throw new CsrException('Failed to extract public key from CSR.');
        }

        return $publicKeyPem;
    }

    public function findByUuid(string $uuid): ?Csr
    {
        return Csr::where('uuid', $uuid)->first();
    }

    // ---- Private helpers ----

    private function applyDnToX509(X509 $x509, array $dn): void
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

        $x509->setDN(['rdnSequence' => []], false);

        // Use setDNProp for each field.
        foreach ($dn as $shortName => $value) {
            $oid = $mapping[$shortName] ?? $shortName;
            $x509->setDNProp($oid, $value);
        }
    }

    /**
     * Build SAN array from extension data.
     *
     * @param  array<int|string, mixed>  $extensions
     * @return array<int, array<string, string>>
     */
    private function buildSanFromExtensions(array $extensions): array
    {
        $sanList = [];
        $allowedTypes = config('ca-csr.allowed_san_types', ['dns', 'ip', 'email', 'uri']);

        foreach ($extensions as $ext) {
            if (!is_array($ext)) {
                continue;
            }

            $type = $ext['type'] ?? null;
            $value = $ext['value'] ?? null;

            if ($type === null || $value === null) {
                continue;
            }

            if (!in_array($type, $allowedTypes, true)) {
                continue;
            }

            $sanEntry = match ($type) {
                'dns' => ['dNSName' => $value],
                'ip' => ['iPAddress' => $value],
                'email' => ['rfc822Name' => $value],
                'uri' => ['uniformResourceIdentifier' => $value],
                default => null,
            };

            if ($sanEntry !== null) {
                $sanList[] = $sanEntry;
            }
        }

        return $sanList;
    }

    /**
     * Extract DN fields from a loaded X509 CSR.
     *
     * @return array<string, string>
     */
    private function extractDnFromX509(X509 $x509): array
    {
        $dn = [];
        $dnProps = $x509->getDN(X509::DN_ASN1);

        $mapping = [
            'id-at-commonName' => 'CN',
            'id-at-organizationName' => 'O',
            'id-at-organizationalUnitName' => 'OU',
            'id-at-countryName' => 'C',
            'id-at-stateOrProvinceName' => 'ST',
            'id-at-localityName' => 'L',
            'id-emailAddress' => 'emailAddress',
            'id-at-serialNumber' => 'serialNumber',
        ];

        foreach ($mapping as $oid => $shortName) {
            $value = $x509->getDNProp($oid);
            if ($value !== false && $value !== []) {
                $dn[$shortName] = is_array($value) ? $value[0] : $value;
            }
        }

        return $dn;
    }

    /**
     * Extract SAN entries from a loaded X509 CSR.
     *
     * @return array<int, array<string, string>>
     */
    private function extractSanFromX509(X509 $x509): array
    {
        $san = $x509->getExtension('id-ce-subjectAltName');

        if ($san === false || !is_array($san)) {
            return [];
        }

        return $san;
    }

    private function resolveCurrentUser(): ?string
    {
        if (function_exists('auth') && auth()->check()) {
            return (string) auth()->id();
        }

        return null;
    }
}
