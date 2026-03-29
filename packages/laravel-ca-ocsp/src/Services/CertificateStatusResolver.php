<?php

declare(strict_types=1);

namespace CA\OCSP\Services;

use CA\Crt\Models\Certificate;
use CA\Models\CertificateStatus;
use CA\Models\RevocationReason;
use CA\Models\CertificateAuthority;
use CA\OCSP\Asn1\CertIdParser;
use CA\OCSP\Contracts\CertificateStatusResolverInterface;
use CA\OCSP\DTOs\CertStatusResult;
use Carbon\Carbon;
use phpseclib3\File\X509;

class CertificateStatusResolver implements CertificateStatusResolverInterface
{
    public function __construct(
        private readonly CertIdParser $certIdParser,
    ) {}

    /**
     * Resolve the certificate status for the given CertID fields.
     */
    public function resolve(
        string $issuerNameHash,
        string $issuerKeyHash,
        string $serialNumber,
        string $hashAlgorithm,
    ): CertStatusResult {
        $now = Carbon::now();
        $nextUpdate = $now->copy()->addHours((int) config('ca-ocsp.default_response_validity_hours', 24));

        // Find the CA that matches the issuer hashes
        $ca = $this->matchCa($issuerNameHash, $issuerKeyHash, $hashAlgorithm);

        if ($ca === null) {
            return new CertStatusResult(
                status: 'unknown',
                thisUpdate: $now,
                nextUpdate: $nextUpdate,
            );
        }

        // Look up the certificate by serial number
        $certificate = Certificate::query()
            ->forCa($ca->id)
            ->bySerial($serialNumber)
            ->first();

        if ($certificate === null) {
            return new CertStatusResult(
                status: 'unknown',
                thisUpdate: $now,
                nextUpdate: $nextUpdate,
            );
        }

        // Determine status
        if ($certificate->isRevoked()) {
            $revocationReason = null;
            if ($certificate->revocation_reason !== null) {
                $revocationReason = RevocationReason::tryFrom((int) $certificate->revocation_reason);
            }

            return new CertStatusResult(
                status: 'revoked',
                revocationTime: $certificate->revoked_at ? Carbon::parse($certificate->revoked_at) : $now,
                revocationReason: $revocationReason,
                thisUpdate: $now,
                nextUpdate: $nextUpdate,
            );
        }

        if ($certificate->status === CertificateStatus::ACTIVE) {
            return new CertStatusResult(
                status: 'good',
                thisUpdate: $now,
                nextUpdate: $nextUpdate,
            );
        }

        // Expired or other non-active states
        return new CertStatusResult(
            status: 'unknown',
            thisUpdate: $now,
            nextUpdate: $nextUpdate,
        );
    }

    /**
     * Find a CA by matching issuerNameHash and issuerKeyHash against known CAs.
     */
    private function matchCa(
        string $issuerNameHash,
        string $issuerKeyHash,
        string $hashAlgorithm,
    ): ?CertificateAuthority {
        $authorities = CertificateAuthority::query()->active()->get();

        foreach ($authorities as $ca) {
            // Find the CA's own certificate to get the DER-encoded subject and public key
            $caCert = Certificate::query()
                ->forCa($ca->id)
                ->where('type', 'ca')
                ->first();

            if ($caCert === null || $caCert->certificate_der === null) {
                // Try using PEM if DER is not available
                if ($caCert?->certificate_pem) {
                    $x509 = new X509();
                    $certData = $x509->loadX509($caCert->certificate_pem);
                    if ($certData === false) {
                        continue;
                    }
                } else {
                    continue;
                }
            } else {
                $x509 = new X509();
                $certData = $x509->loadX509($caCert->certificate_der);
                if ($certData === false) {
                    continue;
                }
            }

            // Get DER-encoded issuer DN
            $subjectDnDer = $x509->getDN(X509::DN_DER);
            if ($subjectDnDer === false) {
                continue;
            }

            // Get the raw public key bytes (the BIT STRING value)
            $publicKeyDer = $this->extractPublicKeyBytes($x509);
            if ($publicKeyDer === null) {
                continue;
            }

            // Compute hashes
            $computedNameHash = $this->certIdParser->computeIssuerNameHash($subjectDnDer, $hashAlgorithm);
            $computedKeyHash = $this->certIdParser->computeIssuerKeyHash($publicKeyDer, $hashAlgorithm);

            if ($computedNameHash === $issuerNameHash && $computedKeyHash === $issuerKeyHash) {
                return $ca;
            }
        }

        return null;
    }

    /**
     * Extract the raw public key bytes from an X509 certificate.
     * Per RFC 6960, this is the value of the BIT STRING subjectPublicKey.
     */
    private function extractPublicKeyBytes(X509 $x509): ?string
    {
        $cert = $x509->getCurrentCert();

        if (!isset($cert['tbsCertificate']['subjectPublicKeyInfo']['subjectPublicKey'])) {
            return null;
        }

        $publicKeyBitString = $cert['tbsCertificate']['subjectPublicKeyInfo']['subjectPublicKey'];

        if (is_string($publicKeyBitString)) {
            return $publicKeyBitString;
        }

        return null;
    }
}
