<?php

declare(strict_types=1);

namespace CA\OCSP\Asn1;

use CA\Exceptions\OcspException;
use CA\OCSP\Asn1\Maps\OCSPRequest;
use phpseclib3\File\ASN1;

class OcspRequestParser
{
    /**
     * OID for the OCSP nonce extension.
     */
    private const OID_OCSP_NONCE = '1.3.6.1.5.5.7.48.1.2';

    /**
     * Map of hash algorithm OIDs to names.
     *
     * @var array<string, string>
     */
    private const HASH_ALGORITHM_OIDS = [
        '1.3.14.3.2.26' => 'sha1',
        '2.16.840.1.101.3.4.2.1' => 'sha256',
        '2.16.840.1.101.3.4.2.2' => 'sha384',
        '2.16.840.1.101.3.4.2.3' => 'sha512',
    ];

    /**
     * Parse a DER-encoded OCSP request.
     *
     * @return array{requests: array<int, array{issuerNameHash: string, issuerKeyHash: string, serialNumber: string, hashAlgorithm: string}>, nonce: ?string}
     *
     * @throws OcspException
     */
    public function parse(string $derBytes): array
    {
        ASN1::loadOIDs(self::HASH_ALGORITHM_OIDS);

        $decoded = ASN1::decodeBER($derBytes);

        if ($decoded === null || $decoded === [] || !isset($decoded[0])) {
            throw new OcspException('Failed to decode OCSP request: invalid BER data.');
        }

        $mapped = ASN1::asn1map($decoded[0], OCSPRequest::getMap());

        if ($mapped === null) {
            throw new OcspException('Failed to map OCSP request to ASN1 structure.');
        }

        $requests = [];
        $nonce = null;

        // Extract request list
        $requestList = $mapped['tbsRequest']['requestList'] ?? [];

        // Normalize: if it has 'reqCert' directly, wrap it
        if (isset($requestList['reqCert'])) {
            $requestList = [$requestList];
        }

        foreach ($requestList as $request) {
            $certId = $request['reqCert'] ?? $request;

            $hashAlgOid = $certId['hashAlgorithm']['algorithm'] ?? '';
            $hashAlgorithm = self::HASH_ALGORITHM_OIDS[$hashAlgOid] ?? $hashAlgOid;

            $issuerNameHash = $certId['issuerNameHash'] ?? '';
            $issuerKeyHash = $certId['issuerKeyHash'] ?? '';
            $serialNumber = $certId['serialNumber'] ?? '';

            // Convert Element objects to string if needed
            if ($issuerNameHash instanceof ASN1\Element) {
                $issuerNameHash = (string) $issuerNameHash;
            }
            if ($issuerKeyHash instanceof ASN1\Element) {
                $issuerKeyHash = (string) $issuerKeyHash;
            }

            $requests[] = [
                'issuerNameHash' => is_string($issuerNameHash) ? $issuerNameHash : bin2hex($issuerNameHash),
                'issuerKeyHash' => is_string($issuerKeyHash) ? $issuerKeyHash : bin2hex($issuerKeyHash),
                'serialNumber' => is_object($serialNumber) ? $serialNumber->toString() : (string) $serialNumber,
                'hashAlgorithm' => $hashAlgorithm,
            ];
        }

        // Extract nonce from request extensions
        $extensions = $mapped['tbsRequest']['requestExtensions'] ?? [];
        if (isset($extensions['extnId'])) {
            $extensions = [$extensions];
        }

        foreach ($extensions as $extension) {
            $extnId = $extension['extnId'] ?? '';
            if ($extnId === self::OID_OCSP_NONCE || $extnId === 'id-pkix-ocsp-nonce') {
                $nonce = $extension['extnValue'] ?? null;
                break;
            }
        }

        return [
            'requests' => $requests,
            'nonce' => $nonce,
        ];
    }
}
