<?php

declare(strict_types=1);

namespace CA\OCSP\Asn1\Maps;

use phpseclib3\File\ASN1;

/**
 * RFC 6960 - CertID structure.
 *
 * CertID ::= SEQUENCE {
 *     hashAlgorithm       AlgorithmIdentifier,
 *     issuerNameHash      OCTET STRING,
 *     issuerKeyHash       OCTET STRING,
 *     serialNumber        CertificateSerialNumber
 * }
 */
class CertID
{
    public static function getMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'hashAlgorithm' => [
                    'type' => ASN1::TYPE_SEQUENCE,
                    'children' => [
                        'algorithm' => [
                            'type' => ASN1::TYPE_OBJECT_IDENTIFIER,
                        ],
                        'parameters' => [
                            'type' => ASN1::TYPE_ANY,
                            'optional' => true,
                        ],
                    ],
                ],
                'issuerNameHash' => [
                    'type' => ASN1::TYPE_OCTET_STRING,
                ],
                'issuerKeyHash' => [
                    'type' => ASN1::TYPE_OCTET_STRING,
                ],
                'serialNumber' => [
                    'type' => ASN1::TYPE_INTEGER,
                ],
            ],
        ];
    }
}
