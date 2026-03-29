<?php

declare(strict_types=1);

namespace CA\Crt\Services;

use CA\Crt\Models\Certificate;
use CA\Crt\Models\CertificateChain;

final class ChainBuilder
{
    /**
     * Build the certificate chain from leaf to root.
     *
     * @return array<int, Certificate>
     */
    public function buildChain(Certificate $certificate): array
    {
        $chain = [$certificate];
        $current = $certificate;
        $seen = [$certificate->id];

        while ($current->issuer_certificate_id !== null) {
            $issuer = $current->issuerCertificate;

            if ($issuer === null) {
                break;
            }

            // Prevent infinite loops on malformed chains
            if (in_array($issuer->id, $seen, true)) {
                break;
            }

            $chain[] = $issuer;
            $seen[] = $issuer->id;
            $current = $issuer;
        }

        return $chain;
    }

    /**
     * Persist the certificate chain to the database.
     *
     * @param array<int, Certificate> $chain
     */
    public function storeChain(Certificate $certificate, array $chain): void
    {
        // Remove existing chain entries
        CertificateChain::where('certificate_id', $certificate->id)->delete();

        foreach ($chain as $depth => $chainCert) {
            if ($chainCert->id === $certificate->id) {
                continue;
            }

            CertificateChain::create([
                'certificate_id' => $certificate->id,
                'parent_certificate_id' => $chainCert->id,
                'depth' => $depth,
            ]);
        }
    }

    /**
     * Build and persist the chain for a certificate.
     */
    public function buildAndStore(Certificate $certificate): void
    {
        $chain = $this->buildChain($certificate);
        $this->storeChain($certificate, $chain);
    }
}
