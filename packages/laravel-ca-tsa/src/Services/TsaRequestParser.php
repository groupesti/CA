<?php

declare(strict_types=1);

namespace CA\Tsa\Services;

use CA\Tsa\Asn1\Maps\TimeStampReq;
use phpseclib3\File\ASN1;
use RuntimeException;

/**
 * Parse and validate RFC 3161 TimeStampReq structures.
 */
class TsaRequestParser
{
    /**
     * Supported hash algorithm OIDs mapped to names and expected digest lengths.
     */
    private const HASH_ALGORITHMS = [
        '1.3.14.3.2.26' => ['name' => 'sha1', 'length' => 20],
        '2.16.840.1.101.3.4.2.1' => ['name' => 'sha256', 'length' => 32],
        '2.16.840.1.101.3.4.2.2' => ['name' => 'sha384', 'length' => 48],
        '2.16.840.1.101.3.4.2.3' => ['name' => 'sha512', 'length' => 64],
        '2.16.840.1.101.3.4.2.4' => ['name' => 'sha224', 'length' => 28],
    ];

    /**
     * Parse a DER-encoded TimeStampReq.
     *
     * @return array{version: int, hashAlgorithm: string, hashAlgorithmOid: string, hashedMessage: string, nonce: ?string, certReq: bool, policyOid: ?string}
     *
     * @throws RuntimeException If the request is malformed or invalid.
     */
    public function parse(string $tsqDer): array
    {
        if ($tsqDer === '') {
            throw new RuntimeException('Empty timestamp request.');
        }

        $asn1 = new ASN1();

        $decoded = $asn1->decodeBER($tsqDer);

        if ($decoded === null || !isset($decoded[0])) {
            throw new RuntimeException('Failed to decode timestamp request DER.');
        }

        $mapped = $asn1->asn1map($decoded[0], TimeStampReq::getMap());

        if ($mapped === null || $mapped === false) {
            throw new RuntimeException('Failed to map timestamp request to ASN.1 structure.');
        }

        // Validate version
        $version = isset($mapped['version']) ? (int) $mapped['version']->toString() : 0;
        if ($version !== 1) {
            throw new RuntimeException("Unsupported timestamp request version: {$version}. Only v1 is supported.");
        }

        // Extract hash algorithm
        $hashAlgOid = $mapped['messageImprint']['hashAlgorithm']['algorithm'] ?? null;
        if ($hashAlgOid === null) {
            throw new RuntimeException('Missing hash algorithm in message imprint.');
        }

        if (!isset(self::HASH_ALGORITHMS[$hashAlgOid])) {
            throw new RuntimeException("Unsupported hash algorithm OID: {$hashAlgOid}.");
        }

        $hashAlgInfo = self::HASH_ALGORITHMS[$hashAlgOid];
        $hashAlgorithm = $hashAlgInfo['name'];

        // Extract hashed message
        $hashedMessage = $mapped['messageImprint']['hashedMessage'] ?? null;
        if ($hashedMessage === null || $hashedMessage === '') {
            throw new RuntimeException('Missing hashed message in message imprint.');
        }

        // The hashedMessage comes as a raw binary string from ASN1 decoding
        $hashedMessageBin = $hashedMessage;

        // Validate hash length matches algorithm
        $expectedLength = $hashAlgInfo['length'];
        $actualLength = strlen($hashedMessageBin);
        if ($actualLength !== $expectedLength) {
            throw new RuntimeException(
                "Hash length mismatch for {$hashAlgorithm}: expected {$expectedLength} bytes, got {$actualLength}.",
            );
        }

        // Extract optional fields
        $nonce = null;
        if (isset($mapped['nonce'])) {
            $nonce = $mapped['nonce']->toString();
        }

        $certReq = false;
        if (isset($mapped['certReq'])) {
            $certReq = (bool) $mapped['certReq'];
        }

        $policyOid = $mapped['reqPolicy'] ?? null;

        return [
            'version' => $version,
            'hashAlgorithm' => $hashAlgorithm,
            'hashAlgorithmOid' => $hashAlgOid,
            'hashedMessage' => bin2hex($hashedMessageBin),
            'hashedMessageRaw' => $hashedMessageBin,
            'nonce' => $nonce,
            'certReq' => $certReq,
            'policyOid' => $policyOid,
        ];
    }

    /**
     * Get the list of supported hash algorithm names.
     *
     * @return array<string>
     */
    public function supportedAlgorithms(): array
    {
        return array_column(self::HASH_ALGORITHMS, 'name');
    }

    /**
     * Get the OID for a hash algorithm name.
     */
    public function getOidForAlgorithm(string $name): ?string
    {
        foreach (self::HASH_ALGORITHMS as $oid => $info) {
            if ($info['name'] === $name) {
                return $oid;
            }
        }

        return null;
    }

    /**
     * Get the algorithm name for an OID.
     */
    public function getAlgorithmForOid(string $oid): ?string
    {
        return self::HASH_ALGORITHMS[$oid]['name'] ?? null;
    }
}
