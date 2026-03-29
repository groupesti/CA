<?php

declare(strict_types=1);

namespace CA\OCSP\Asn1;

/**
 * Helper to compute CertID hash components per RFC 6960.
 */
class CertIdParser
{
    /**
     * Map of algorithm names to PHP hash function names.
     *
     * @var array<string, string>
     */
    private const HASH_ALGORITHMS = [
        'sha1' => 'sha1',
        'sha256' => 'sha256',
        'sha384' => 'sha384',
        'sha512' => 'sha512',
    ];

    /**
     * Compute the hash of the issuer's distinguished name (DER-encoded).
     */
    public function computeIssuerNameHash(string $issuerDnDer, string $hashAlg): string
    {
        $algo = self::HASH_ALGORITHMS[$hashAlg] ?? 'sha1';

        return hash($algo, $issuerDnDer, binary: true);
    }

    /**
     * Compute the hash of the issuer's public key (DER-encoded, without the BIT STRING wrapper).
     */
    public function computeIssuerKeyHash(string $issuerPublicKeyDer, string $hashAlg): string
    {
        $algo = self::HASH_ALGORITHMS[$hashAlg] ?? 'sha1';

        return hash($algo, $issuerPublicKeyDer, binary: true);
    }
}
